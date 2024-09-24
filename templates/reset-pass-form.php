<?php
use WPFM\Frontend\Frontend_Form;
use WPFM\Frontend\Registration;

/**
 * @var WPFM\Frontend\Login::login_form $eye_icon_src
 * @var WPFM\Frontend\Login::login_form $eye_close_icon_src
 */
?>
<div class="login" id="wpfm-login-form">

    <?php
    Frontend_Form::display_notice('resetpassword');
    ?>

    <form id="resetpasswordform" action="" method="post">
        <p>
            <label for="wpfm-pass1"><?php esc_html_e('New password', 'frontend-manager'); ?></label>
        <div class="wpfm-fields-inline" style="position: relative; width: fit-content">
            <input name="pass1" id="wpfm-pass1" class="input" size="40" value="" type="password" autocomplete="off"/>
            <img class="wpfm-eye" src="<?php echo esc_url($eye_icon_src); ?>" data-eye="<?php echo esc_url($eye_icon_src); ?>" data-close_eye="<?= esc_url($eye_close_icon_src) ?>" alt="">
        </div>
        </p>

        <p>
            <label for="wpfm-pass2"><?php esc_html_e('Confirm new password', 'frontend-manager'); ?></label>
        <div class="wpfm-fields-inline" style="position: relative; width: fit-content">
            <input name="pass2" id="wpfm-pass2" class="input" size="40" value="" type="password" autocomplete="off"/>
            <img class="wpfm-eye" src="<?php echo esc_url($eye_icon_src); ?>" data-eye="<?php echo esc_url($eye_icon_src); ?>" data-close_eye="<?= esc_url($eye_close_icon_src) ?>" alt="">
        </div>
        </p>

        <?php do_action('resetpassword_form'); ?>

        <p class="submit">
            <input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e('Reset Password', 'frontend-manager'); ?>"/>
            <input type="hidden" name="key" value="<?php echo esc_attr(Registration::get_posted_value('key')); ?>"/>
            <input type="hidden" name="login" id="user_login" value="<?php echo esc_attr(Registration::get_posted_value('login')); ?>"/>
            <input type="hidden" name="wpfm_reset_password" value="true"/>
        </p>

        <?php wp_nonce_field('wpfm_reset_pass'); ?>
    </form>
</div>
