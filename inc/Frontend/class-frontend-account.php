<?php

namespace WPFM\Frontend;

class Frontend_Account
{
    private $sections = '';
    private $postType = 'post';

    /**
     * Link constructor.
     */
    public function __construct()
    {
        $this->postType = get_settings_value('post_type') ?: $this->postType;
        $this->sections = get_account_sections();
    }

    /**
     * Render shortcode
     *
     * @return string
     */
    public function _account_shortcode_callback($atts)
    {
        extract(shortcode_atts([], $atts));

        ob_start();
        if (is_user_logged_in()) {
            $activeSection = $_REQUEST['section'] ?? 'post';
            wpfm_load_template('account.php', [
                'sections' => $this->sections,
                'activeSection' => $activeSection,
            ]);
        } else {
            wpfm_load_template('unauthorized.php', [
                'sections' => $this->sections,
            ]);
        }
        return ob_get_clean();
    }
}
