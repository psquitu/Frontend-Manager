<?php

use WPFM\Frontend\Frontend_Form;
use WPFM\Frontend\Registration;

/**
 * @var WPFM\Frontend\Login::login_form $action_url
 * @var WPFM\Frontend\Login::login_form $redirect_to
 */
?>
<div class="login" id="wpfm-login-form">

    <?php

    $message = apply_filters('login_message', '');

    if (!empty($message)) {
        echo $message . "\n";
    }
    echo Frontend_Form::display_notice('login');
    ?>

    <form name="loginform" class="wpfm-login-form" id="loginform" action="<?php echo esc_attr($action_url); ?>" method="post">
        <p>
            <label for="wpfm-user_login"><?php esc_html_e('Username or Email', 'frontend-manager'); ?></label>
            <input type="text" name="username" id="wpfm-user_login" class="input" value="" size="20"/>
        </p>
        <p>
            <label for="wpfm-user_pass"><?php esc_html_e('Password', 'frontend-manager'); ?></label>
            <input type="password" name="password" id="wpfm-user_pass" class="input" value="" size="20"/>
        </p>

        <?php $recaptcha = get_settings_value('enable_login_form_recaptcha', 'wpfm_settings', 'off'); ?>
        <?php if ($recaptcha == 'on') { ?>
            <p>
            <div class="wpfm-fields">
                <?php echo wp_kses(wpfm_recaptcha_get_html(get_settings_value('recaptcha_site_key'), true, null, is_ssl()), [
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
                ]); ?>
            </div>
            </p>
        <?php } ?>

        <p class="forgetmenot">
            <input name="rememberme" type="checkbox" id="wpfm-rememberme" value="forever"/>
            <label for="wpfm-rememberme"><?php esc_html_e('Remember Me', 'tfp'); ?></label>
        </p>

        <p class="submit">
            <input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_html_e('Log In', 'frontend-manager'); ?>"/>
            <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>"/>
            <input type="hidden" name="action" value="login"/>
            <?php wp_nonce_field('wpfm_login_action', 'wpfm-login-nonce'); ?>
        </p>
        <?php do_action('wpfm_login_form_bottom'); ?>
    </form>

    <div class="wpfm-links-wrap wpfm-fields-inline">
        <?php echo wp_kses_post(Registration::get_action_links(['login' => false])); ?>
    </div>
</div>
