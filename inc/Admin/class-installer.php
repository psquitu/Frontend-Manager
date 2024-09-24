<?php
namespace WPFM\Admin;
class Installer {

    public function __construct() {
    }

    public static function pages(){
        return [
            'dashboard' => array(
                'args' => array(
                    'post_title' => 'Dashboard',
                    'post_status' => 'publish',
                    'post_date' => date('Y-m-d H:i:s'),
                    'post_type' => 'page',
                    'comment_status' => 'closed',
                    'post_content' => "[wpfm-dashboard]"
                ),
            ),
            'account' => array(
                'args' => array(
                    'post_title' => 'Account',
                    'post_status' => 'publish',
                    'post_date' => date('Y-m-d H:i:s'),
                    'post_type' => 'page',
                    'comment_status' => 'closed',
                    'post_content' => "[wpfm-account]"
                ),
                //'template' => "test.php"
            ),
            'submit-post' => array(
                'args' => array(
                    'post_title' => 'Submit Post',
                    'post_status' => 'publish',
                    'post_date' => date('Y-m-d H:i:s'),
                    'post_type' => 'page',
                    'comment_status' => 'closed',
                    'post_content' => "[wpfm-submit-post]"
                ),
            ),
            'edit' => array(
                'args' => array(
                    'post_title' => 'Edit',
                    'post_status' => 'publish',
                    'post_date' => date('Y-m-d H:i:s'),
                    'post_type' => 'page',
                    'comment_status' => 'closed',
                    'post_content' => "[wpfm-edit]"
                ),
            ),
            'login' => array(
                'args' => array(
                    'post_title' => 'Login',
                    'post_status' => 'publish',
                    'post_date' => date('Y-m-d H:i:s'),
                    'post_type' => 'page',
                    'comment_status' => 'closed',
                    'post_content' => "[wpfm-login]"
                ),
            ),
            'register' => array(
                'args' => array(
                    'post_title' => 'Register',
                    'post_status' => 'publish',
                    'post_date' => date('Y-m-d H:i:s'),
                    'post_type' => 'page',
                    'comment_status' => 'closed',
                    'post_content' => "[wpfm-register]"
                ),
            ),
        ];
    }


    /**
     * Create a page with title and content
     * @param string $page_title
     * @param string $post_content
     * @return false|int|\WP_Error
     */
    public static function create_page( $args ) {
        $page_id = wp_insert_post( $args );

        if ( $page_id && !is_wp_error( $page_id ) ) {
            return $page_id;
        }

        return false;
    }
}
