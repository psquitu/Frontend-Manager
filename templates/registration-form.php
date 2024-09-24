<?php
/**
 * @var WPFM\Frontend\Registration::registration_form $action_url
 * @var WPFM\Frontend\Registration::registration_form $userrole
 * @var WPFM\Frontend\Registration::registration_form $user_nonce
 * @var WPFM\Frontend\Registration::registration_form $eye_icon_src
 * @var WPFM\Frontend\Registration::registration_form $eye_close_icon_src
 */

use WPFM\Frontend\Frontend_Form;
use WPFM\Frontend\Registration;

?>
<div class="registration" id="wpfm-registration-form">

    <?php
    $message = apply_filters('registration_message', '');

    if (!empty($message)) {
        echo $message . "\n";
    }

    $success = isset($_GET['success']) ? sanitize_text_field(wp_unslash($_GET['success'])) : '';

    if ('yes' == $success) {
        echo wp_kses_post("<div class='wpfm-flash-message flash-success' style='text-align:center'>" . __('Registration has been successful!', 'frontend-manager') . '</div>');
    }
    echo Frontend_Form::display_notice('register');
    ?>
    <form id="registration-form" class="wpfm-registration-form" action="<?php echo esc_attr($action_url); ?>" method="post">
        <div class="wpfm-form form-label-above">
            <div class="form-row">
                <div class="wpfm-label"><?php esc_html_e('Name', 'frontend-manager'); ?> <span class="required">*</span></div>
                <div class="wpfm-form-group">
                    <div class="wpfm-name-field-wrap format-first-last">
                        <div class="form-row wpfm-field-first-name">
                            <input type="text" name="first_name" id="wpfm-user_fname" class="input" value="<?php echo esc_attr(wpfm()->frontend->registration_form->get_posted_value('reg_fname')); ?>"
                                   size=""/>
                            <label class="wpfm-form-sub-label"><?php esc_html_e('First', 'frontend-manager'); ?></label>
                        </div>

                        <div class="form-row wpfm-field-last-name">
                            <input type="text" name="last_name" id="wpfm-user_lname" class="input" value="<?php echo esc_attr(wpfm()->frontend->registration_form->get_posted_value('reg_lname')); ?>"
                                   size="16"/>
                            <label class="wpfm-form-sub-label"><?php esc_html_e('Last', 'frontend-manager'); ?></label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="wpfm-label"><?php esc_html_e('Email', 'frontend-manager'); ?> <span class="required">*</span></div>
                <div class="wpfm-fields">
                    <input type="email" name="email" id="wpfm-user_email" class="input" value="<?php echo esc_attr(wpfm()->frontend->registration_form->get_posted_value('reg_email')); ?>"
                           size="40">
                </div>
            </div>

            <div class="form-row">
                <div class="wpfm-label"><?php esc_html_e('Username', 'frontend-manager'); ?> <span class="required">*</span></div>
                <div class="wpfm-fields">
                    <input type="text" name="username" id="wpfm-user_login" class="input" value="<?php echo esc_attr(wpfm()->frontend->registration_form->get_posted_value('log')); ?>" size="40"/>
                </div>
            </div>

            <div class="form-row">
                <div class="wpfm-label"><?php esc_html_e('Password', 'frontend-manager'); ?> <span class="required">*</span></div>
                <div class="wpfm-fields-inline" style="position: relative; width: fit-content">
                    <input type="password" name="pwd1" id="wpfm-user_pass1" class="input" value="" size="40"/>
                    <img class="wpfm-eye" src="<?php echo esc_url($eye_icon_src); ?>" data-eye="<?php echo esc_url($eye_icon_src); ?>" data-close_eye="<?= esc_url($eye_close_icon_src) ?>" alt="">
                </div>
            </div>

            <div class="form-row">
                <div class="wpfm-label"><?php esc_html_e('Confirm Password', 'frontend-manager'); ?> <span class="required">*</span></div>
                <div class="wpfm-fields-inline" style="position: relative; width: fit-content">
                    <input type="password" name="pwd2" id="wpfm-user_pass2" class="input" value="" size="40"/>
                    <img class="wpfm-eye" src="<?php echo esc_url($eye_icon_src); ?>" data-eye="<?php echo esc_url($eye_icon_src); ?>" data-close_eye="<?= esc_url($eye_close_icon_src) ?>" alt="">
                </div>
            </div>

            <div class="wpfm-submit mb-1 mt-1">
                <input type="hidden" name="ur" value="<?php echo esc_attr($userrole); ?>"/>
                <input type="hidden" name="user_nonce" value="<?php echo esc_attr($user_nonce); ?>"/>
                <input type="hidden" name="redirect_to" value="<?php echo esc_attr(wpfm()->frontend->registration_form->get_posted_value('redirect_to')); ?>"/>
                <input type="hidden" name="action" value="registration"/>
                <?php wp_nonce_field('wpfm_registration_action'); ?>
                <input type="submit" name="wp-submit" id="wp-submit" value="<?php echo esc_html_e('Register', 'frontend-manager'); ?>"/>
            </div>

            <div class="wpfm-links-wrap wpfm-fields-inline">
                <?php echo wp_kses_post(Registration::get_action_links(['register' => false])); ?>
            </div>

        </div>
    </form>
</div>
