<?php

namespace WPFM\Frontend;

use WeDevs\Wpuf\Render_Form;

class Login
{
    public function __construct()
    {
        add_action('init', [$this, 'submit_login']);
        add_action('init', [$this, 'lost_reset_password_handle']);
    }

    /**
     *  Shows the login form
     * @param $atts
     * @return void
     */
    public function login_form($atts)
    {
        ob_start();
        extract($atts);
        $getdata = wp_unslash($_GET);
        if (is_user_logged_in()) {
            wpfm_load_template('logged-in.php', [
                'user' => wp_get_current_user(),
            ]);
        } else {
            $action = isset($getdata['action']) ? sanitize_text_field($getdata['action']) : 'login';
            $login_page = Frontend_Form::login_url();
            $args = [
                'action_url' => $login_page,
                'redirect_to' => isset($getdata['redirect_to']) ? $getdata['redirect_to'] : '',
            ];
            switch ($action) {
                case 'lostpassword':
                    $checkemail = isset($getdata['checkemail']) ? sanitize_text_field($getdata['checkemail']) : '';

                    if (!Frontend_Form::display_notice('lostpassword')) {
                        wpfm_load_template('lost-pass-form.php', $args);
                        break;
                    }
                    if ('confirm' === $checkemail) {
                        set_flash_notice(esc_html__('Check your e-mail for the confirmation link.', 'frontend-manager'), 'error', 'lostpassword');
                    }

                    if (!$checkemail) {
                        set_flash_notice(esc_html__('Please enter your username or email address. You will receive a link to create a new password via email.', 'frontend-manager'), 'error', 'lostpassword');
                    }

                    wpfm_load_template('lost-pass-form.php', $args);
                    break;

                case 'rp':
                case 'resetpass':
                    $reset = isset($getdata['reset']) ? sanitize_text_field($getdata['reset']) : '';
                    if ($reset === 'true') {
                        set_flash_notice(esc_html__('Your password has been reset successfully', 'frontend-manager'), 'success', 'login');
                        wpfm_load_template('login-form.php', $args);
                        break;
                    } else {
                        set_flash_notice(esc_html__('Enter your new password below.', 'frontend-manager'), 'success', 'resetpassword');
                        $args['eye_icon_src'] = file_exists(WPFM_ROOT . '/assets/images/eye.svg') ? WPFM_ASSET_URI . '/images/eye.svg' : '';
                        $args['eye_close_icon_src'] = WPFM_ASSET_URI . '/images/eye-close.svg';
                        wpfm_load_template('reset-pass-form.php', $args);
                    }
                    break;
                default:
                    $loggedout = isset($getdata['loggedout']) ? sanitize_text_field($getdata['loggedout']) : '';
                    if ($loggedout === 'true') {
                        set_flash_notice(esc_htmlesc_html__('You are now logged out.', 'frontend-manager'), 'error', 'login');
                    }
                    $args['redirect_to'] = $this->get_login_redirect_url($args['redirect_to']);
                    wpfm_load_template('login-form.php', $args);
                    break;
            }
        }
        return ob_get_clean();
    }

    /**
     * get login redirect url
     * @param $redirect_to
     * @return string|null
     */
    public function get_login_redirect_url($redirect_to = ''): ?string
    {
        if (!empty($redirect_to)) {
            return esc_url_raw($redirect_to);
        }
        $redirect_after_login = get_settings_value('redirect_after_login_page', 'wpfm_settings', null);
        if ($redirect_after_login) {
            if ('previous_page' === $redirect_after_login) {
                $prev_url = wp_get_referer();
                if ($prev_url) {
                    return $prev_url;
                }

                global $wp;
                return home_url(add_query_arg([], $wp->request));
            }

            $redirect_page_link = get_permalink($redirect_after_login);

            if (!empty($redirect_page_link)) {
                return $redirect_page_link;
            }
        }

        return home_url();
    }

    /**
     * Process Login Form
     * @return void
     */
    public function submit_login()
    {
        if (isset($_POST['action']) && 'login' == $_POST['action'] && !empty($_POST['wpfm-login-nonce'])) {
            $credentials = [];

            $nonce = sanitize_key(wp_unslash($_POST['wpfm-login-nonce']));

            if (isset($nonce) && !wp_verify_nonce($nonce, 'wpfm_login_action')) {
                set_flash_notice(esc_html__('Nonce is invalid', 'frontend-manager'), 'error', 'login');

                return;
            }

            $username = isset($_POST['username']) ? esc_attr(wp_unslash($_POST['username'])) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';

            if (empty($username)) {
                set_flash_notice(esc_html__('Username is required.', 'frontend-manager'), 'error', 'login');

                return;
            }

            if (empty($password)) {
                set_flash_notice(esc_html__('Password is required.', 'frontend-manager'), 'error', 'login');

                return;
            }

            if (isset($_POST['g-recaptcha-response'])) {
                if (empty($_POST['g-recaptcha-response'])) {
                    set_flash_notice(esc_html__('Empty reCaptcha Field.', 'frontend-manager'), 'error', 'login');
                    return;
                } else {
                    $no_captcha = 1;
                    $invisible_captcha = 0;
                    if (!$this->wpfm_validate_re_captcha($no_captcha, $invisible_captcha)) {
                        return;
                    }
                }
            }

            if (is_email($username) && apply_filters('wpfm_get_username_from_email', true)) {
                $user = get_user_by('email', $username);

                if (isset($user->user_login)) {
                    $credentials['user_login'] = $user->user_login;
                } else {
                    set_flash_notice(esc_html__('A user could not be found with this email address.', 'frontend-manager'), 'error', 'login');

                    return;
                }
            } else {
                $credentials['user_login'] = $username;
            }

            $credentials['user_password'] = $password;
            $credentials['remember'] = isset($_POST['rememberme']) ? sanitize_text_field(wp_unslash($_POST['rememberme'])) : '';

            if (isset($user->user_login)) {
                $validate = wp_authenticate_email_password(null, trim($username), $credentials['user_password']);

                if (is_wp_error($validate)) {
                    $msg = str_replace(wp_login_url(), Frontend_Form::login_url(), $validate->get_error_message());
                    set_flash_notice($msg, 'error', 'login');
                    return;
                }
            }

            $secure_cookie = is_ssl() ? true : false;
            $user = wp_signon(apply_filters('wpfm_login_credentials', $credentials), $secure_cookie);

            //try with old implementation, which is wrong but we must support that
            if (is_wp_error($user)) {
                $credentials['user_login'] = sanitize_text_field(wp_unslash($_POST['username']));
                $credentials['user_password'] = sanitize_text_field(wp_unslash($_POST['password']));

                $user = wp_signon(apply_filters('wpfm_login_credentials', $credentials), $secure_cookie);
            }

            if (is_wp_error($user)) {
                $msg = str_replace(wp_login_url(), Frontend_Form::login_url(), $user->get_error_message());
                set_flash_notice($msg, 'error', 'login');
                return;
            } else {
                $redirect = $this->get_login_redirect_url();
                wp_redirect(apply_filters('wpfm_login_redirect', $redirect, $user));
                exit;
            }
        }
    }

    /**
     * Handle reset password form
     *
     * @return void
     */
    public function lost_reset_password_handle()
    {
        if (!isset($_POST['action']) || (isset($_POST['action']) && 'lostpassword' == $_POST['action'])) {
            return;
        }

        // process lost password form
        if (isset($_POST['user_login']) && isset($_POST['_wpnonce'])) {
            $nonce = !empty($_POST['_wpnonce']) ? sanitize_key(wp_unslash($_POST['_wpnonce'])) : '';

            if (!empty($nonce) && !wp_verify_nonce($nonce, 'wpfm_lost_password')) {
                return;
            }

            if ($this->retrieve_password()) {
                $url = add_query_arg(
                    [
                        'action' => 'lostpassword',
                        'checkemail' => 'confirm',
                    ], Frontend_Form::login_url()
                );
                wp_redirect($url);
                exit;
            }
        }

        // process reset password form
        if (isset($_POST['pass1']) && isset($_POST['pass2']) && isset($_POST['key']) && isset($_POST['login']) && isset($_POST['_wpnonce'])) {
            $pass1 = sanitize_text_field(wp_unslash($_POST['pass1']));
            $pass2 = sanitize_text_field(wp_unslash($_POST['pass2']));
            $key = sanitize_text_field(wp_unslash($_POST['key']));
            $login = sanitize_text_field(wp_unslash($_POST['login']));
            $nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));

            // verify reset key again
            $user = check_password_reset_key($key, $login);

            if ($user instanceof WP_User) {

                // save these values into the form again in case of errors
                $args['key'] = $key;
                $args['login'] = $login;

                if (empty($pass1) || empty($pass2)) {
                    set_flash_notice(esc_html__('Please enter your password.', 'frontend-manager'), 'error', 'lostpassword');

                    return;
                }

                if ($pass1 !== $pass2) {
                    set_flash_notice(esc_html__('Passwords do not match.', 'frontend-manager'), 'error', 'lostpassword');

                    return;
                }

                $this->reset_password($user, $pass1);

                do_action('wpfm_customer_reset_password', $user);

                wp_redirect(add_query_arg('reset', 'true', remove_query_arg(['key', 'login'])));
                exit;
            }
        }
    }

    /**
     * Handles sending password retrieval email to customer.
     * @return bool|void True: when finish. False: on error
     * @uses $wpdb WordPress Database object
     */
    public function retrieve_password()
    {
        global $wpdb, $wp_hasher;

        $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_key(wp_unslash($_REQUEST['_wpnonce'])) : '';

        if (isset($nonce) && !wp_verify_nonce($nonce, 'wpfm_lost_password')) {
            set_flash_notice(esc_html__('Authentication fail'), 'error', 'lostpassword');
            return;
        }

        $user_login = isset($_POST['user_login']) ? sanitize_text_field(wp_unslash($_POST['user_login'])) : '';

        if (empty($user_login)) {
            set_flash_notice(esc_html__('Enter a username or e-mail address.', 'frontend-manager'), 'error', 'lostpassword');

            return;
        } elseif (strpos($user_login, '@') && apply_filters('get_username_from_email', true)) {
            $user_data = get_user_by('email', trim($user_login));

            if (empty($user_data)) {
                set_flash_notice(esc_html__('There is no user registered with that email address.', 'frontend-manager'), 'error', 'lostpassword');

                return;
            }
        } else {
            $login = trim($user_login);

            $user_data = get_user_by('login', $login);
        }

        do_action('lostpassword_post');

        if ($this->login_errors) {
            return false;
        }

        if (!$user_data) {
            set_flash_notice(esc_html__('Invalid username or e-mail.', 'frontend-manager'), 'error', 'lostpassword');

            return false;
        }

        // redefining user_login ensures we return the right case in the email
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;

        do_action('retrieve_password', $user_login);

        $allow = apply_filters('allow_password_reset', true, $user_data->ID);

        if (!$allow) {
            set_flash_notice(esc_html__('Password reset is not allowed for this user', 'frontend-manager'), 'error', 'lostpassword');

            return false;
        } elseif (is_wp_error($allow)) {
            set_flash_notice($allow->get_error_message(), 'error', 'lostpassword');

            return false;
        }

        $key = get_password_reset_key($user_data);

        if (is_wp_error($key)) {
            set_flash_notice(esc_html__('Reset password fail.', 'frontend-manager'), 'error', 'lostpassword');
            return;
        }

        // Send email notification
        $this->email_reset_pass($user_login, $user_email, $key);

        return true;
    }

    /**
     * Email reset password link
     * @param string $user_login
     * @param string $user_email
     * @param string $key
     */
    public function email_reset_pass($user_login, $user_email, $key)
    {
        $reset_url = add_query_arg(
            [
                'action' => 'rp',
                'key' => $key,
                'login' => urlencode($user_login),
            ], Frontend_Form::login_url()
        );

        $message = esc_html__('Someone requested that the password be reset for the following account:', 'frontend-manager') . "\r\n\r\n";
        $message .= network_home_url('/') . "\r\n\r\n";
        /* translators: %s: username */
        $message .= sprintf(esc_html__('Username: %s', 'frontend-manager'), $user_login) . "\r\n\r\n";
        $message .= esc_html__('If this was a mistake, just ignore this email and nothing will happen.', 'frontend-manager') . "\r\n\r\n";
        $message .= esc_html__('To reset your password, visit the following address:', 'frontend-manager') . "\r\n\r\n";
        $message .= ' ' . $reset_url . " \r\n";

        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        if (is_multisite()) {
            $blogname = $GLOBALS['current_site']->site_name;
        }

        $user_data = get_user_by('login', $user_login);
        /* translators: %s: blogname */
        $title = sprintf(esc_html__('[%s] Password Reset', 'frontend-manager'), $blogname);
        $title = apply_filters('retrieve_password_title', $title);

        $message = apply_filters('retrieve_password_message', $message, $key, $user_login, $user_data);

        if ($message && !wp_mail($user_email, wp_specialchars_decode($title), $message)) {
            wp_die(esc_html(esc_html__('The e-mail could not be sent.', 'frontend-manager')) . "<br />\n" . esc_html(esc_html__('Possible reason: your host may have disabled the mail() function.', 'frontend-manager')));
        }
    }

    /**
     * Handles resetting the user's password.
     * @param object $user The user
     * @param string $new_pass New password for the user in plaintext
     * @return void
     */
    public function reset_password($user, $new_pass)
    {
        do_action('password_reset', $user, $new_pass);

        wp_set_password($new_pass, $user->ID);

        wp_password_change_notification($user);
    }


    /**
     * reCaptcha Validation
     * @param $no_captcha
     * @param $invisible
     * @return void
     */
    public function wpfm_validate_re_captcha($no_captcha = '', $invisible = '')
    {
        $nonce = isset($_REQUEST['wpfm-login-nonce']) ? sanitize_key(wp_unslash($_REQUEST['wpfm-login-nonce'])) : '';

        if (isset($nonce) && !wp_verify_nonce($nonce, 'wpfm_login_action')) {
            return;
        }
        // need to check if invisible reCaptcha need library or we can do it here.
        // ref: https://shareurcodes.com/blog/google%20invisible%20recaptcha%20integration%20with%20php
        $site_key = get_settings_value('recaptcha_site_key');
        $private_key = get_settings_value('recaptcha_secret_key');
        $rremote_addr = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
        $g_recaptcha_response = isset($_POST['g-recaptcha-response']) ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) : '';

        if ($no_captcha == 1 && 0 == $invisible) {
            if (!class_exists('WPFM_ReCaptcha')) {
                require_once WPFM_INCLUDES . '/Lib/class-noCaptcha.php';
            }

            $response = null;
            $reCaptcha = new \WPFM_ReCaptcha($private_key);

            $resp = $reCaptcha->verifyResponse(
                $rremote_addr,
                $g_recaptcha_response
            );

            if (!$resp->success) {
                set_flash_notice(esc_html__('noCaptcha reCAPTCHA validation failed.', 'frontend-manager'), 'error', 'login');
                return;
            }
            return $resp->success;
        } elseif ($no_captcha == 0 && 0 == $invisible) {
            $recap_challenge = isset($_POST['recaptcha_challenge_field']) ? sanitize_text_field(wp_unslash($_POST['recaptcha_challenge_field'])) : '';
            $recap_response = isset($_POST['recaptcha_response_field']) ? sanitize_text_field(wp_unslash($_POST['recaptcha_response_field'])) : '';

            $resp = wpfm_recaptcha_check_answer($private_key, $rremote_addr, $recap_challenge, $recap_response);

            if (!$resp->is_valid) {
                set_flash_notice(esc_html__('reCAPTCHA validation failed.', 'frontend-manager'), 'error', 'login');
                return;
            }
            return $resp->is_valid;
        } elseif ($no_captcha == 0 && 1 == $invisible) {
            $response = null;
            $recaptcha = isset($_POST['g-recaptcha-response']) ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) : '';
            $object = new \WPFM_Invisible_Recaptcha($site_key, $private_key);

            $response = $object->verifyResponse($recaptcha);

            if (isset($response['success']) and $response['success'] != true) {
                set_flash_notice(esc_html__('Invisible reCAPTCHA validation failed.', 'frontend-manager'), 'error', 'login');
                return;
            }
            return $response->success;
        }
    }
}
