<?php
/*
Plugin Name: Frontend Manager
Description: Registration forms and Login form, frontend profile, captcha options. User post manages from frontend.
Author: Prakash Sunuwar (Praron)
Version: 1.0.0
Author URI: https://praron.com/
License: GPL2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: frontend-manager
Domain Path: /languages
*/


use WPFM\Admin\Admin;
use WPFM\Admin\Installer;
use WPFM\Frontend\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

define('WPFM_VERSION', '1.0.0');
define('WPFM_FILE', __FILE__);
define('WPFM_ROOT', __DIR__);
define('WPFM_ROOT_URI', plugins_url('', __FILE__));
define('WPFM_ASSET_URI', WPFM_ROOT_URI . '/assets');
define('WPFM_INCLUDES', WPFM_ROOT . '/inc');

final class WPFM_Frontend_Post
{
    /**
     * Singleton instance
     *
     * @var self
     */
    private static $instance;
    public Frontend $frontend;
    public Admin $admin;

    /**
     * Fire up the plugin
     */
    public function __construct()
    {
        $this->includes();
        $this->init_hooks();
        add_action('plugins_loaded', [$this, 'instantiate']);
    }

    /**
     * Initialize the hooks
     * @return void
     */
    public function init_hooks()
    {
        add_action('init', [$this, 'load_textdomain']);
        register_activation_hook(__FILE__, [$this, 'pluginActivationHooks']);
        register_deactivation_hook(__FILE__, [$this, 'pluginDeactivationHooks']);
    }


    /**
     * Include the required files
     * @return void
     */
    public function includes()
    {
        require_once WPFM_INCLUDES . '/functions.php';
        require_once WPFM_INCLUDES . '/Admin/class-admin.php';
        require_once WPFM_INCLUDES . '/Admin/class-installer.php';
        require_once WPFM_INCLUDES . '/Frontend/class-frontend-form.php';
        require_once WPFM_INCLUDES . '/Frontend/class-frontend-account.php';
        require_once WPFM_INCLUDES . '/Frontend/class-shortcodes.php';
        require_once WPFM_INCLUDES . '/Frontend/class-frontend.php';
        require_once WPFM_INCLUDES . '/Frontend/class-frontend-dashboard.php';
        require_once WPFM_INCLUDES . '/Frontend/class-login.php';
        require_once WPFM_INCLUDES . '/Frontend/class-registration.php';
        require_once WPFM_INCLUDES . '/Lib/class-invisible-recaptcha.php';
        require_once WPFM_INCLUDES . '/Lib/recaptchalib.php';
    }

    /**
     * Load the translation file for current language.
     * @author Prakash Sunuwar
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('frontend-manager', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function pluginActivationHooks()
    {
        foreach (Installer::pages() as $key => $page) {
            $page_id = Installer::create_page($page['args']);
            update_option("wpfm_" . $key, $page_id);
            if(isset($page['template']) && $page['template']){
                $template = apply_filters('wpfm_page_template', $page['template']);
                update_post_meta($page_id, "_wp_page_template", $template);
            }
        }
    }

    public function pluginDeactivationHooks()
    {
        foreach (Installer::pages() as $key => $page) {
            $page_id = get_option("wpfm_" . $key);
            wp_delete_post($page_id, true);
            delete_option("wpfm_" . $key);
        }
    }

    /**
     * Instantiate the classes
     *
     * @return void
     */
    public function instantiate()
    {
        if (is_admin()) {
            $this->admin = new Admin();
        } else {
            $this->frontend = new Frontend();
        }
    }

    /**
     * Get the singleton instance
     *
     * @return self
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

function wpfm()
{
    return WPFM_Frontend_Post::instance();
}

// kickoff
wpfm();
