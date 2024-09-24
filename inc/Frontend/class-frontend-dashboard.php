<?php

namespace WPFM\Frontend;

class Frontend_Dashboard
{
    /**
     * Handle's user dashboard functionality
     * Insert shortcode [wpfm-dashboard] in a page to
     * show the user dashboard
     * @param $atts
     * @return false|string
     */
    public function dashboard_shortcode($atts)
    {
        extract(shortcode_atts([], $atts));
        ob_start();
        if (is_user_logged_in()) {
            wpfm_load_template('dashboard/dashboard.php');
        } else {
            wpfm_load_template('unauthorized.php');
        }
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}
