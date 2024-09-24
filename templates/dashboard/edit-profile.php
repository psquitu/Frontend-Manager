<?php
global $current_user;

use WPFM\Frontend\Frontend_Form;

/**
 * @var WPFM\Frontend\Frontend::edit_profile $eye_icon_src;
 * @var WPFM\Frontend\Frontend::edit_profile $eye_close_icon_src;
 */
?>
<div class="wpfm-profile-form wpfm-form">
    <?= Frontend_Form::display_notice('update_profile') ?>
    <form id="wpfm-update-profile-form" action="" method="post">
        <div class="form-label-above">
            <div class="form-row">
                <div class="wpfm-label">
                    <label for="first_name"><?php esc_html_e('First Name ', 'tfp'); ?><span class="required">*</span></label>
                </div>
                <div class="wpfm-fields">
                    <input type="text" class="input-text" name="first_name" id="first_name" value="<?php echo esc_attr($current_user->first_name); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="wpfm-label">
                    <label for="last_name"><?php esc_html_e('Last Name ', 'frontend-manager'); ?><span class="required">*</span></label>
                </div>
                <div class="wpfm-fields">
                    <input type="text" class="input-text" name="last_name" id="last_name" value="<?php echo esc_attr($current_user->last_name); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="wpfm-label">
                    <label for="email"><?php esc_html_e('Email Address ', 'frontend-manager'); ?><span class="required">*</span></label>
                </div>
                <div class="wpfm-fields">
                    <input type="email" class="input-text" name="email" id="email" value="<?php echo esc_attr($current_user->user_email); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="wpfm-label">
                    <label for="current_password"><?php esc_html_e('Current Password', 'frontend-manager'); ?></label>
                </div>
                <div class="wpfm-fields">
                    <div class="wpfm-fields-inline">
                        <input type="password" class="input-text" name="current_password" id="current_password" size="16" value="" autocomplete="off"/>
                        <img class="wpfm-eye" src="<?php echo esc_url($eye_icon_src); ?>" data-eye="<?php echo esc_url($eye_icon_src); ?>" data-close_eye="<?= esc_url($eye_close_icon_src) ?>" alt="">
                    </div>
                </div>
                <span class="wpfm-help"><?php esc_html_e('Leave this field empty to keep your password unchanged.', 'frontend-manager'); ?></span>
            </div>

            <div class="form-row">
                <div class="wpfm-label">
                    <label for="pass1"><?php esc_html_e('New Password', 'frontend-manager'); ?></label>
                </div>
                <div class="wpfm-fields">
                    <div class="wpfm-fields-inline">
                        <input type="password" class="input-text" name="pass1" id="pass1" size="16" value="" autocomplete="off"/>
                        <img class="wpfm-eye" src="<?php echo esc_url($eye_icon_src); ?>" data-eye="<?php echo esc_url($eye_icon_src); ?>" data-close_eye="<?= esc_url($eye_close_icon_src) ?>" alt="">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="wpfm-label">
                    <label for="pass2"><?php esc_html_e('Confirm New Password', 'frontend-manager'); ?></label>
                </div>
                <div class="wpfm-fields">
                    <div class="wpfm-fields-inline">
                        <input type="password" class="input-text" name="pass2" id="pass2" size="16" value="" autocomplete="off"/>
                        <img class="wpfm-eye" src="<?php echo esc_url($eye_icon_src); ?>" data-eye="<?php echo esc_url($eye_icon_src); ?>" data-close_eye="<?= esc_url($eye_close_icon_src) ?>" alt="">
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="pass-strength-result" id="pass-strength-result"><?php esc_html_e('Strength indicator', 'frontend-manager'); ?></div>
                <div class="wpfm-submit mt-3">
                    <?php wp_nonce_field('wpfm-update-profile'); ?>
                    <input type="hidden" name="action" value="wpfm_update_profile">
                    <button type="submit" name="update_profile" id="wpfm-account-update-profile"><?php esc_html_e('Update Profile', 'frontend-manager'); ?></button>
                </div>
            </div>
        </div>
    </form>
</div>
