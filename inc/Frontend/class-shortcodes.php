<?php

namespace WPFM\Frontend;

class Shortcode {
    public function __construct() {
        add_action( 'init', [ $this, 'init_shortcode' ] );
    }

    /**
     * initialize WPFM shortcodes
     * @return void
     */
    public function init_shortcode() {
        add_shortcode( 'wpfm-account', [ wpfm()->frontend->wpfm_account, '_account_shortcode_callback' ] );
        add_shortcode( 'wpfm-edit', [ wpfm()->frontend->frontend_form, 'edit_post_shortcode' ] );
        add_shortcode( 'wpfm-submit-post', [ wpfm()->frontend->frontend_form, 'add_post_shortcode' ] );
        add_shortcode( 'wpfm-login', [ wpfm()->frontend->login, 'login_form' ] );
        add_shortcode( 'wpfm-dashboard', [ wpfm()->frontend->frontend_dashboard, 'dashboard_shortcode' ] );
        add_shortcode( 'wpfm-register', [ wpfm()->frontend->registration_form, 'registration_form' ] );
    }
}
