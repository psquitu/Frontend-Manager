<?php

namespace WPFM\Frontend;

use WP_User;

class Registration
{
    public function __construct()
    {
        add_action('init', [$this, 'user_registration']);
    }

    /**
     * Shows the registration form
     * @param $atts
     * @return string
     * @throws \Random\RandomException
     */
    public function registration_form($atts): string
    {
        $atts = shortcode_atts(['role' => '',], $atts);
        $userrole = $atts['role'];
        $user_nonce = base64_encode(random_bytes(Registration::get_encryption_nonce_length()));
        $roleencoded = wpfm_encryption($userrole, $user_nonce);
        $reg_page = Frontend_Form::registration_url();

        if (false === $reg_page) {
            return '';
        }
        ob_start();
        if (is_user_logged_in()) {
            wpfm_load_template(
                'logged-in.php', [
                    'user' => wp_get_current_user(),
                ]
            );
        } else {
            if (get_option('users_can_register')) {
                $queries = wp_unslash($_GET);
                array_walk(
                    $queries, function (&$a) {
                    $a = sanitize_text_field($a);
                }
                );
                $args = [
                    'action_url' => add_query_arg($queries, $reg_page),
                    'userrole' => $roleencoded,
                    'user_nonce' => $user_nonce,
                    'eye_icon_src' => file_exists(WPFM_ROOT . '/assets/images/eye.svg') ? WPFM_ASSET_URI . '/images/eye.svg' : '',
                    'eye_close_icon_src' => WPFM_ASSET_URI . '/images/eye-close.svg'
                ];
                wpfm_load_template('registration-form.php', $args);
            }else{
                echo '<div class="wpfm-message">' . sprintf(__('This page is restricted. Please contact to admin.', 'wp-user-frontend')) . '</div>';
            }
        }

        return ob_get_clean();
    }

    /**
     * Get the Advanced Encryption Standard we are using
     * @return string
     */
    public static function get_encryption_method(): string
    {
        return 'AES-256-CBC';
    }

    /**
     * Get the nonce length for the encryption
     * Returns 24 If PHP version is 7.2 or above.
     * For PHP version below 7.2 it will send the length as per the encryption method.
     * @return false|int
     */
    public static function get_encryption_nonce_length(): bool|int
    {
        return function_exists('sodium_crypto_secretbox')
            ? SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
            : openssl_cipher_iv_length(self::get_encryption_method());
    }

    /**
     * Get the encryption key length. Defaults to 32
     * @return int
     */
    public static function get_encryption_key_length(): int
    {
        return function_exists('sodium_crypto_secretbox') ? SODIUM_CRYPTO_SECRETBOX_KEYBYTES : 32;
    }

    /**
     * Get the base64 encoded auth keys
     * @return array
     * @throws \Random\RandomException
     */
    public static function get_encryption_auth_keys(): array
    {
        $defaults = [
            'auth_key' => '',
            'auth_salt' => '',
        ];
        $auth_keys = get_option('wpfm_auth_keys', $defaults);
        if (empty($auth_keys['auth_key']) || empty($auth_keys['auth_salt'])) {
            $key = random_bytes(self::get_encryption_key_length());
            $auth_keys['auth_key'] = base64_encode($key);    // phpcs:ignore
            $nonce = random_bytes(self::get_encryption_nonce_length());
            $auth_keys['auth_salt'] = base64_encode($nonce);    // phpcs:ignore
            update_option('wpfm_auth_keys', $auth_keys);
        }

        return [
            'auth_key' => base64_decode($auth_keys['auth_key']),    // phpcs:ignore
            'auth_salt' => base64_decode($auth_keys['auth_salt']),    // phpcs:ignore
        ];
    }

    /**
     * Get a posted value for showing in the form field
     * @param $key
     * @return string
     */
    public static function get_posted_value($key): string
    {
        if (isset($_REQUEST[$key])) {
            return sanitize_text_field(wp_unslash($_REQUEST[$key]));
        }
        return '';
    }

    /**
     * Get actions links for displaying in forms
     * @param $args
     * @return string
     */
    public static function get_action_links($args = [])
    {
        $defaults = [
            'login' => true,
            'register' => true,
            'lostpassword' => true,
        ];

        $args = wp_parse_args($args, $defaults);
        $links = [];

        if ($args['login']) {
            $links[] = sprintf('<a href="%s">%s</a>', (new self())->get_action_url(), __('Log In', 'frontend-manager'));
        }

        if ($args['register'] && get_option('users_can_register')) {
            $links[] = sprintf('<a href="%s">%s</a>', (new self())->get_action_url('register'), __('Register', 'frontend-manager'));
        }

        if ($args['lostpassword']) {
            $links[] = sprintf('<a href="%s">%s</a>', (new self())->get_action_url('lostpassword'), __('Lost Password', 'frontend-manager'));
        }

        return implode(' | ', $links);
    }

    /**
     * Get action url based on action type
     * @param $action
     * @param $redirect_to url to redirect to
     * @return string
     */
    public function get_action_url($action = 'login', $redirect_to = '')
    {
        $root_url = Frontend_Form::login_url();

        switch ($action) {
            case 'resetpass':
                return add_query_arg(['action' => 'resetpass'], $root_url);
            case 'lostpassword':
                return add_query_arg(['action' => 'lostpassword'], $root_url);
            case 'register':
                return Frontend_Form::registration_url();
            case 'logout':
                return wp_nonce_url(add_query_arg(['action' => 'logout'], $root_url), 'log-out');
            default:
                if (empty($redirect_to)) {
                    return $root_url;
                }
                return add_query_arg(['redirect_to' => urlencode($redirect_to)], $root_url);
        }
    }

    public function user_registration()
    {
        if (isset($_POST['action']) && 'registration' == $_POST['action'] && isset($_POST['_wpnonce']) && wp_unslash($_POST['_wpnonce']) &&
            wp_verify_nonce(wp_unslash($_POST['_wpnonce']), 'wpfm_registration_action')) {
            $first_name = isset($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '';
            $last_name = isset($_POST['last_name']) ? sanitize_text_field(wp_unslash($_POST['last_name'])) : '';
            $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
            $pwd1 = isset($_POST['pwd1']) ? sanitize_text_field(wp_unslash($_POST['pwd1'])) : '';
            $pwd2 = isset($_POST['pwd2']) ? sanitize_text_field(wp_unslash($_POST['pwd2'])) : '';
            $username = isset($_POST['username']) ? sanitize_text_field(wp_unslash($_POST['username'])) : '';
            $ur = isset($_POST['ur']) ? sanitize_text_field(wp_unslash($_POST['ur'])) : '';
            $user_nonce = isset($_POST['user_nonce']) ? sanitize_text_field(
                wp_unslash($_POST['user_nonce'])
            ) : '';
            $errors = [];
            if (!$first_name) {
                $errors['first_name'] = esc_html__('First name is required.', 'frontend-manager');
            }
            if (!$last_name) {
                $errors['last_name'] = esc_html__('Last name is required.', 'frontend-manager');
            }
            if (!$email) {
                $errors['email'] = esc_html__('Email is required.', 'frontend-manager');
            }
            if (!$username) {
                $errors['username'] = esc_html__('Username is required.', 'frontend-manager');
            }
            if (!$pwd1) {
                $errors['pass1'] = esc_html__('Password is required.', 'frontend-manager');
            }
            if (!$pwd2) {
                $errors['pass2'] = esc_html__('Confirm Password is required.', 'frontend-manager');
            }
            if ($pwd1 !== $pwd2) {
                $errors['pass2'] = esc_html__('Passwords are not same.', 'frontend-manager');
            }
            if (get_user_by('login', $username) == $username) {
                $errors['username'] = esc_html__('A user with same username already exists.', 'frontend-manager');
            }
            $user = get_user_by('email', $email);
            if (is_email($username)) {
                $user = get_user_by('email', $username);
            }
            if ($user) {
                $errors['email'] = esc_html__('Email already exists.', 'frontend-manager');
            }

            $errors = apply_filters('wpfm_registration_errors', $errors);

            if (empty($errors)) {
                $userdata = [];
                $userdata['user_login'] = sanitize_user($username);
                $dec_role = wpfm_decryption($ur, $user_nonce);
                $userdata['first_name'] = $first_name;
                $userdata['last_name'] = $last_name;
                $userdata['user_email'] = $email;
                $userdata['user_pass'] = $pwd1;
                if (get_role($dec_role)) {
                    $userdata['role'] = empty($dec_role) || 'administrator' === $dec_role ? get_option(
                        'default_role'
                    ) : $dec_role;
                }
                $user = wp_insert_user($userdata);
            }

            if (is_wp_error($user)) {
                $errors['fail'] = $user->get_error_messages();
            }

            if (empty($errors)) {
                $user = new WP_User($user);
                $user_login = stripslashes($user->user_login);
                $user_email = stripslashes($user->user_email);
                $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
                $message = sprintf(
                        esc_html__('New user registration on your site %s:', 'frontend-manager'),
                        get_option('blogname')
                    ) . "\r\n\r\n";
                $message .= sprintf(esc_html__('Username: %s', 'frontend-manager'), $user_login) . "\r\n\r\n";
                $message .= sprintf(esc_html__('E-mail: %s', 'frontend-manager'), $user_email) . "\r\n";
                $subject = 'New User Registration';
                $subject = apply_filters('wpfm_default_admin_mail_subject', $subject);
                $message = apply_filters('wpfm_default_admin_mail_body', $message);

                wp_mail(
                    get_option('admin_email'),
                    sprintf(esc_html__('[%1$s] %2$s', 'frontend-manager'), $blogname, $subject), $message
                );

                $message = sprintf(esc_html__('Hi, %s', 'frontend-manager'), $user_login) . "\r\n";
                $message .= 'Congrats! You are Successfully registered to ' . $blogname . "\r\n\r\n";
                $message .= 'Thanks';
                $subject = 'Thank you for registering';
                $subject = apply_filters('wpfm_default_mail_subject', $subject);
                $message = apply_filters('wpfm_default_mail_body', $message);

                wp_mail(
                    $user_email, sprintf(esc_html__('[%1$s] %2$s', 'frontend-manager'), $blogname, $subject),
                    $message
                );

                $autologin_after_registration = get_settings_value('autologin_after_registration', 'wpfm_settings', 'off');
                if ($autologin_after_registration === 'on') {
                    wp_clear_auth_cookie();
                    wp_set_current_user($user);
                    wp_set_auth_cookie($user);
                }

                if (!empty($_POST['redirect_to'])) {
                    $redirect = sanitize_text_field(wp_unslash($_POST['redirect_to']));
                } else {
                    $redirect = Frontend_Form::registration_url() . '?success=yes';
                }
                wp_redirect(apply_filters('wpfm_registration_redirect', $redirect, $user));
                exit;
            } else {
                set_flash_notice($errors, 'error', 'register');
            }
        }
    }
}
