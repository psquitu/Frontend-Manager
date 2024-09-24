<?php

use WPFM\Frontend\Frontend_Form;

$registerLink = '';
if (get_option('users_can_register')) {
    $registerLink = '/ ' . Frontend_Form::register_link();
}
$msg = '<div class="wpfm-message">' . sprintf(__('This page is restricted. Please %1$s %2$s to view this page.', 'wp-user-frontend'), Frontend_Form::login_link(), $registerLink) . '</div>';
echo wp_kses_post($msg);
