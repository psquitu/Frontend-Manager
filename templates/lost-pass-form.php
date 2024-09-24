<?php

use WPFM\Frontend\Frontend_Form;
use WPFM\Frontend\Registration;

?>
<div class="login" id="wpfm-login-form">

    <?= Frontend_Form::display_notice('lostpassword') ?>

    <form name="lostpasswordform" id="lostpasswordform" action="" method="post">
        <p>
            <label for="wpfm-user_login"><?php esc_html_e('Username or E-mail:', 'frontend-manager'); ?></label>
            <input type="text" name="user_login" id="wpfm-user_login" class="input" value="" size="20"/>
        </p>

        <?php do_action('lostpassword_form'); ?>

        <p class="submit">
            <input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e('Get New Password', 'frontend-manager'); ?>"/>
            <input type="hidden" name="redirect_to" value="<?php echo esc_attr(Registration::get_posted_value('redirect_to')); ?>"/>
            <input type="hidden" name="action" value="lostpassword"/>
            <?php wp_nonce_field('wpfm_lost_password'); ?>
        </p>
    </form>

    <div class="wpfm-links-wrap wpfm-fields-inline">
        <?php echo wp_kses_post(Registration::get_action_links(['lostpassword' => false])); ?>
    </div>
</div>
