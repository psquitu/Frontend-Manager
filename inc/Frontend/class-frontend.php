<?php

namespace WPFM\Frontend;

use stdClass;
use WP_Query;

class Frontend
{
    private $postType = 'post';
    public Frontend_Account $wpfm_account;
    public Shortcode $shortcodes;
    public Frontend_Form $frontend_form;
    public Frontend_Dashboard $frontend_dashboard;
    public Registration $registration_form;
    public Login $login;

    public function __construct()
    {
        $this->wpfm_account = new Frontend_Account();
        $this->shortcodes = new Shortcode();
        $this->frontend_form = new Frontend_Form();
        $this->frontend_dashboard = new Frontend_Dashboard();
        $this->registration_form = new Registration();
        $this->login = new Login();
        $this->postType = get_settings_value('post_type') ?: $this->postType;
        add_action('wpfm_content_' . $this->postType, [$this, 'display_posts'], 10, 2);
        add_action('wp_enqueue_scripts', [$this, 'wpfm_scripts']);
        add_action('wp_loaded', [$this, 'update_profile']);
        add_action('wpfm_content_submit-post', [$this, 'submit_posts'], 10, 2);
        add_action('wpfm_content_dashboard', [$this, 'dashboard'], 10, 2);
        add_action('wpfm_content_edit-profile', [$this, 'edit_profile'], 10, 2);
        add_action('comment_form_after_fields', [$this, 'add_captcha_on_non_loggedIn_form_comment']);
        add_filter('comment_form_field_comment', [$this, '_captcha_on_loggedIn_form_comment']);
        add_action('pre_comment_on_post', [$this, 'verify_comment_captcha']);
    }

    public function display_posts($sections, $activeSection): void
    {
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $action = isset($_REQUEST['action']) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : '';
        $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_key(wp_unslash($_REQUEST['_wpnonce'])) : '';
        if ($action == 'del' && is_user_logged_in() && wp_verify_nonce($nonce, 'wpfm_delete')) {
            $userdata = get_userdata(get_current_user_id());
            $pid = isset($_REQUEST['pid']) ? sanitize_text_field(wp_unslash($_REQUEST['pid'])) : '';
            $section = isset($_REQUEST['section']) ? sanitize_text_field(wp_unslash($_REQUEST['section'])) : '';
            $maybe_delete = get_post($pid);
            if (($maybe_delete->post_author == $userdata->ID) || current_user_can('delete_others_pages')) {
                wp_trash_post($pid);
                $redirect = add_query_arg(['section' => $section], get_permalink());
                set_flash_notice(esc_html__('Post deleted successfully.', 'frontend-manager'), 'success', 'posts');
                wp_redirect($redirect);
                exit;
            } else {
                set_flash_notice(esc_html__('You are not the post author. Cheating huh!', 'frontend-manager'), 'error', 'posts');
            }
        }
        $args = apply_filters('wpfm_posts_args', [
            'post_type' => $this->postType,
            'author' => apply_filters('wpfm_posts_author', get_current_user_id()),
            'post_status' => apply_filters('wpfm_posts_status', ['draft', 'future', 'pending', 'publish', 'private']),
            'posts_per_page' => 10,
            'paged' => $paged,
            //'no_found_rows' => true,
        ]);
        $myPosts = new WP_Query($args);
        wpfm_load_template('dashboard/posts.php', [
            'posts' => apply_filters('wpfm_posts', $myPosts),
            'paged' => $paged,
            'sections' => $sections,
            'activeSection' => $activeSection,
        ]);
    }

    public function wpfm_scripts(): void
    {
        wp_enqueue_style('wpfm-frontend', WPFM_ASSET_URI . '/css/frontend.css', [], WPFM_VERSION);
        wp_enqueue_script('wpfm-frontend-js', WPFM_ASSET_URI . '/js/frontend.js', ['jquery'], WPFM_VERSION, true);
    }

    /**
     * Show submit post form
     * @param $sections
     * @param $activeSection
     * @return void
     */
    public function submit_posts($sections, $activeSection): void
    {
        wpfm_load_template('dashboard/submit-post.php', [
            'sections' => $sections,
            'activeSection' => $activeSection,
        ]);
    }

    /**
     * Dashboard display
     * @param $sections
     * @param $activeSection
     * @return void
     */
    public function dashboard($sections, $activeSection)
    {
        wpfm_load_template('dashboard/dashboard.php', [
            'sections' => $sections,
            'activeSection' => $activeSection,
        ]);
    }

    /**
     * show edit profile form
     * @param $sections
     * @param $activeSection
     * @return void
     */
    public function edit_profile($sections, $activeSection)
    {
        $eye_icon_src = WPFM_ASSET_URI . '/images/eye.svg';
        $eye_close_icon_src = WPFM_ASSET_URI . '/images/eye-close.svg';
        wp_enqueue_script('password-strength-meter');
        wpfm_load_template('dashboard/edit-profile.php', [
            'sections' => $sections,
            'activeSection' => $activeSection,
            'eye_icon_src' => $eye_icon_src,
            'eye_close_icon_src' => $eye_close_icon_src,
        ]);
    }

    /**
     * Save profile
     * @return void
     */
    public function update_profile()
    {
        if (isset($_REQUEST['action']) && isset($_REQUEST['update_profile']) && $_REQUEST['action'] == 'wpfm_update_profile') {
            $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_key(wp_unslash($_REQUEST['_wpnonce'])) : '';
            if (isset($nonce) && !wp_verify_nonce($nonce, 'wpfm-update-profile')) {
                set_flash_notice(esc_html__('Authentication fail!', 'frontend-manager'), 'error', 'update_profile');
            }
            global $current_user;
            $first_name = !empty($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '';
            $last_name = !empty($_POST['last_name']) ? sanitize_text_field(wp_unslash($_POST['last_name'])) : '';
            $email = !empty($_POST['email']) ? sanitize_text_field(wp_unslash($_POST['email'])) : '';
            $current_password = !empty($_POST['current_password']) ? sanitize_text_field(wp_unslash($_POST['current_password'])) : '';
            $pass1 = !empty($_POST['pass1']) ? sanitize_text_field(wp_unslash($_POST['pass1'])) : '';
            $pass2 = !empty($_POST['pass2']) ? sanitize_text_field(wp_unslash($_POST['pass2'])) : '';
            $save_pass = true;
            $errors = [];
            if (empty($first_name)) {
                $errors['first_name'] = esc_html__('First Name is a required field.', 'frontend-manager');
            }
            if (empty($last_name)) {
                $errors['last_name'] = esc_html__('Last Name is a required field.', 'frontend-manager');
            }
            if (empty($email)) {
                $errors['email'] = esc_html__('Email is a required field.', 'frontend-manager');
            }
            $user = new stdClass();
            $user->ID = $current_user->ID;
            $user->first_name = $first_name;
            $user->last_name = $last_name;
            if ($email) {
                $email = sanitize_email($email);
                if (!is_email($email)) {
                    $errors['email'] = esc_html__('Please provide a valid email address.', 'frontend-manager');
                } else if (email_exists($email) && $email !== $current_user->user_email) {
                    $errors['email'] = esc_html__('This email address is already registered.', 'frontend-manager');
                }
                $user->user_email = $email;
            }
            if (!empty($current_password) && empty($pass1) && empty($pass2)) {
                $errors['pass1'] = esc_html__('Please fill out all password fields.', 'frontend-manager');
                $save_pass = false;
            } else if (!empty($pass1) && empty($current_password)) {
                $errors['current_password'] = esc_html__('Please enter your current password.', 'frontend-manager');
                $save_pass = false;
            } else if (!empty($pass1) && empty($pass2)) {
                $errors['pass2'] = esc_html__('Please re-enter your password.', 'frontend-manager');
                $save_pass = false;
            } else if ((!empty($pass1) || !empty($pass2)) && $pass1 !== $pass2) {
                $errors['pass2'] = esc_html__('New passwords do not match.', 'frontend-manager');
                $save_pass = false;
            } else if (!empty($pass1) && !wp_check_password($current_password, $current_user->user_pass, $current_user->ID)) {
                $errors['current_password'] = esc_html__('Your current password is incorrect.', 'frontend-manager');
                $save_pass = false;
            }

            do_action('wpfm_before_update_profile', $user, $_POST);

            if (empty($errors)) {
                if ($pass1 && $save_pass) {
                    $user->user_pass = $pass1;
                }
                $result = wp_update_user($user);
                if (is_wp_error($result)) {
                    $errors['update_fail'] = esc_html__('User update fail.', 'frontend-manager');
                } else {
                    do_action('wpfm_after_update_profile', $result);
                    set_flash_notice(get_settings_value('update_message', 'wpfm_settings', esc_html__('Profile updated successfully.', 'frontend-manager')), 'success', 'update_profile');
                }
            }

            if ($errors) {
                set_flash_notice($errors, 'error', 'update_profile');
            }

        }
    }

    /**
     * Add captcha on comment form non logged in user
     * @return void
     */
    public function add_captcha_on_non_loggedIn_form_comment()
    {
        if ('on' == get_settings_value('enable_post_comment_recaptcha', 'wpfm_settings', 'off')) {
            echo wp_kses(wpfm_recaptcha_get_html(get_settings_value('recaptcha_site_key'), true, null, is_ssl()), [
                'div' => [
                    'class' => [],
                    'data-sitekey' => [],
                ],

                'script' => [
                    'src' => []
                ],

                'noscript' => [],

                'iframe' => [
                    'src' => [],
                    'height' => [],
                    'width' => [],
                    'frameborder' => [],
                ],
                'br' => [],
                'textarea' => [
                    'name' => [],
                    'rows' => [],
                    'cols' => [],
                ],
                'input' => [
                    'type' => [],
                    'value' => [],
                    'name' => [],
                ]
            ]);
        }
    }

    /**
     * Add captcha on logged in form
     * @param $field
     * @return string
     */
    public function _captcha_on_loggedIn_form_comment($field)
    {
        if (is_user_logged_in()) {
            $captcha = wp_kses(wpfm_recaptcha_get_html(get_settings_value('recaptcha_site_key'), true, null, is_ssl()), [
                'div' => [
                    'class' => [],
                    'data-sitekey' => [],
                ],

                'script' => [
                    'src' => []
                ],

                'noscript' => [],

                'iframe' => [
                    'src' => [],
                    'height' => [],
                    'width' => [],
                    'frameborder' => [],
                ],
                'br' => [],
                'textarea' => [
                    'name' => [],
                    'rows' => [],
                    'cols' => [],
                ],
                'input' => [
                    'type' => [],
                    'value' => [],
                    'name' => [],
                ]
            ]);
            return $field . $captcha;
        }
        return $field;
    }

    /**
     * Verify comment captcha
     * @return void
     */
    public function verify_comment_captcha()
    {
        if ('on' == get_settings_value('enable_post_comment_recaptcha', 'wpfm_settings', 'off') && isset($_POST['g-recaptcha-response'])) {
            if (empty($_POST['g-recaptcha-response'])) {
                $validation_error = esc_html__('Empty reCaptcha Field.', 'frontend-manager');
            } else {
                $site_key = get_settings_value('recaptcha_site_key');
                $private_key = get_settings_value('recaptcha_secret_key');
                $rremote_addr = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
                $g_recaptcha_response = isset($_POST['g-recaptcha-response']) ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) : '';

                if (!class_exists('WPFM_ReCaptcha')) {
                    require_once WPFM_INCLUDES . '/lib/class-recaptchalib-noCaptcha.php';
                }

                $response = null;
                $reCaptcha = new \WPFM_ReCaptcha($private_key);

                $resp = $reCaptcha->verifyResponse(
                    $rremote_addr,
                    $g_recaptcha_response
                );

                if (!$resp->success) {
                    $validation_error = esc_html__('noCaptcha reCAPTCHA validation failed.', 'frontend-manager');
                }
            }
            wp_die(sprintf('<strong>%1$s</strong> %2$s <p><a href="javascript:history.back()">« %3$s</a></p>', esc_html__('Error:', 'frontend-manager'), $validation_error, esc_html__('Back')));
        }
    }
}
