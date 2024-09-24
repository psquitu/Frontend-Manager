<?php
/**
 * @var array $formFields
 */

use WPFM\Admin\Admin;

?>
<div class="wpfm-wrap">
    <form method="POST" action="">
        <div class="form-wrap">
            <h2 class="nav-tab-wrap">
                <?php
                foreach ($formFields as $sectionKey => $fields) {
                    $sectionLabel = ucwords(implode(' ', explode('-', $sectionKey)));
                    ?>
                    <a href="#wpfm-<?= $sectionKey ?>" class="nav-tab">
                        <?php esc_html_e($sectionLabel, 'frontend-manager'); ?>
                    </a>
                    <?php
                    $tabContentHtml[] = '<div id="wpfm-' . $sectionKey . '" class="wpfm-content-group">';
                    $tabContentHtml[] = '<table class="form-table">';
                    $tabContentHtml[] = '<tbody>';
                    foreach ($fields as $field) {
                        $tabContentHtml[] = '<tr>';
                        $tabContentHtml[] = '<th>' . $field['label'] . '</th>';
                        $tabContentHtml[] = '<td>';
                        $tabContentHtml[] = Admin::generate_html($field);
                        $tabContentHtml[] = '</td>';
                        $tabContentHtml[] = '</tr>';
                    }
                    $tabContentHtml[] = '</tbody>';
                    $tabContentHtml[] = '</table>';
                    $tabContentHtml[] = '</div>';
                }
                ?>
            </h2>
            <div class="tab-contents">
                <?php
                echo implode('', $tabContentHtml);
                wp_nonce_field('wpfm_settings_nonce', 'wpfm_settings_nonce_field');
                submit_button();
                ?>
            </div>
        </div>
    </form>
</div>
