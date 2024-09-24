<?php

namespace WPFM\Admin;

class Admin
{
    public string $parent_slug = 'frontend-manager';
    private $pages = [];
    private $postTypes = [];
    private $statuses = [];
    private $categories = [];
    private $users = [];
    private $default_notification_body = '';
    public Installer $installer;
    /**
     * @var mixed|null
     */
    public mixed $capability;

    public function __construct()
    {
        $this->installer = new Installer();
        $this->capability = apply_filters('wpfm_admin_role', 'manage_options');;
        add_action('admin_enqueue_scripts', [$this, '_enqueue_admin_style']);
        add_action("admin_init", [$this, 'init']);
        add_action('admin_menu', [$this, 'wpfm_menu']);
    }

    function wpfm_menu() {
        add_menu_page(__('Frontend Manager', 'frontend-manager'), __('Frontend Manager', 'frontend-manager'), $this->capability, $this->parent_slug, [$this, '_wpfm_page'], 'dashicons-welcome-widgets-menus', '82.1');
    }

    public function form_fields()
    {
        $redirectLoginPages = $this->pages;
        $redirectLoginPages['previous_page'] = esc_html__('Previous Page', 'frontend-manager');
        $fields = [
            'general-settings' => apply_filters('wpfm_general_settings', [
                [
                    'name' => 'edit_page_id',
                    'label' => esc_html__('Edit Page', 'frontend-manager'),
                    'desc' => 'Select the page where [wpfm-edit] is located',
                    'type' => 'select',
                    'default' => get_option('wpfm_edit')?:'',
                    'options' => $this->pages,
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'enable_post_edit',
                    'label' => esc_html__('Users can edit post?', 'frontend-manager'),
                    'desc' => esc_html__('Users will be able to edit their own posts', 'frontend-manager'),
                    'type' => 'select',
                    'default' => 'yes',
                    'options' => [
                        'yes' => esc_html__('Yes', 'frontend-manager'),
                        'no' => esc_html__('No', 'frontend-manager'),
                    ],
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'enable_post_del',
                    'label' => esc_html__('User can delete post?', 'frontend-manager'),
                    'desc' => esc_html__('Users will be able to delete their own posts', 'frontend-manager'),
                    'type' => 'select',
                    'default' => 'yes',
                    'options' => [
                        'yes' => esc_html__('Yes', 'frontend-manager'),
                        'no' => esc_html__('No', 'frontend-manager'),
                    ],
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'disable_pending_edit',
                    'label' => esc_html__('Pending Post Edit', 'frontend-manager'),
                    'desc' => esc_html__('Disable post editing while post in "pending" status', 'frontend-manager'),
                    'type' => 'checkbox',
                    'default' => 'on',
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'disable_publish_edit',
                    'label' => esc_html__('Editing Published Post', 'frontend-manager'),
                    'desc' => esc_html__('Disable post editing while post in "publish" status', 'frontend-manager'),
                    'type' => 'checkbox',
                    'default' => 'off',
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'per_page',
                    'label' => esc_html__('Posts per page', 'frontend-manager'),
                    'desc' => esc_html__('How many posts will be listed in a page', 'frontend-manager'),
                    'type' => 'text',
                    'default' => '10',
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'recaptcha_site_key',
                    'label' => esc_html__('reCAPTCHA Site Key', 'frontend-manager'),
                    'desc' => '',
                    'type' => 'text',
                    'default' => '',
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'recaptcha_secret_key',
                    'label' => esc_html__('reCAPTCHA Secret Key', 'frontend-manager'),
                    'desc' => sprintf(esc_html__('%1$s Register here %2$s to get reCaptcha Site and Secret keys.', 'frontend-manager'), '<a target="_blank" href="https://www.google.com/recaptcha/">', '</a>'),
                    'type' => 'text',
                    'default' => '',
                    'group' => 'wpfm_settings',
                ],
            ]),
            'login-registration' => apply_filters('wpfm_login_registration', [
                [
                    'name' => 'autologin_after_registration',
                    'label' => esc_html__('Autologin After Register', 'frontend-manager'),
                    'desc' => '',
                    'type' => 'checkbox',
                    'default' => 'off',
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'redirect_after_login_page',
                    'label' => esc_html__('Redirect After Login Page', 'frontend-manager'),
                    'desc' => '',
                    'type' => 'select',
                    'default' => get_option('wpfm_account') ?: '',
                    'options' => $redirectLoginPages,
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'register_page',
                    'label' => esc_html__('Registration Page', 'frontend-manager'),
                    'desc' => 'To access the register page enable the {Anyone can register} option from >Settings>General Settings',
                    'type' => 'select',
                    'default' => get_option('wpfm_register') ?: '',
                    'options' => $this->pages,
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'login_page',
                    'label' => esc_html__('Login Page', 'frontend-manager'),
                    'desc' => '',
                    'type' => 'select',
                    'default' => get_option('wpfm_login') ?: '',
                    'options' => $this->pages,
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'enable_login_form_recaptcha',
                    'label' => esc_html__('Enable reCAPTCHA in Login Form', 'frontend-manager'),
                    'desc' => 'If enabled, users have to verify reCAPTCHA in login page. Also, make sure that reCAPTCHA is configured properly from General Options',
                    'type' => 'checkbox',
                    'default' => '',
                    'group' => 'wpfm_settings',
                ],
            ]),
            'post-settings' => apply_filters('wpfm_post_settings', array_merge([
                [
                    'name' => 'post_type',
                    'label' => esc_html__('Post Type', 'frontend-manager'),
                    'desc' => '',
                    'type' => 'select',
                    'default' => 'post',
                    'options' => $this->postTypes,
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'post_status',
                    'label' => esc_html__('Post Status', 'frontend-manager'),
                    'desc' => '',
                    'type' => 'select',
                    'default' => 'publish',
                    'options' => $this->statuses,
                    'group' => 'wpfm_settings',
                ]], $this->categories, [
                [
                    'name' => 'default_post_owner',
                    'label' => esc_html__('Default Post Owner', 'frontend-manager'),
                    'desc' => '',
                    'type' => 'select',
                    'default' => get_current_user_id(),
                    'options' => $this->users,
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'submit_text',
                    'label' => esc_html__('Submit Button Text', 'frontend-manager'),
                    'desc' => '',
                    'type' => 'text',
                    'default' => esc_html__('Submit', 'frontend-manager'),
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'update_text',
                    'label' => esc_html__('Update Button Text', 'frontend-manager'),
                    'desc' => '',
                    'type' => 'text',
                    'default' => esc_html__('Update', 'frontend-manager'),
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'update_message',
                    'label' => esc_html__('Update profile message', 'frontend-manager'),
                    'desc' => '',
                    'type' => 'textarea',
                    'default' => esc_html__('Post successfully updated.', 'frontend-manager'),
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'enable_recaptcha',
                    'label' => esc_html__('Enable reCAPTCHA', 'frontend-manager'),
                    'desc' => 'Check if you want to enable recaptcha on add/edit form',
                    'type' => 'checkbox',
                    'default' => 'off',
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'enable_post_comment_recaptcha',
                    'label' => esc_html__('Enable Recaptcha On Post Comment', 'frontend-manager'),
                    'desc' => '',
                    'type' => 'checkbox',
                    'default' => 'off',
                    'group' => 'wpfm_settings',
                ],
            ])),
            'notification-settings' => apply_filters('wpfm_notification_settings', [
                [
                    'name' => 'enable_notification',
                    'label' => esc_html__('Enable post notification', 'frontend-manager'),
                    'desc' => '',
                    'type' => 'checkbox',
                    'default' => 'on',
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'notification_to',
                    'label' => esc_html__('To', 'frontend-manager'),
                    'desc' => '',
                    'type' => 'text',
                    'default' => get_option('admin_email'),
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'notification_subject',
                    'label' => esc_html__('Subject', 'frontend-manager'),
                    'desc' => '',
                    'type' => 'text',
                    'default' => esc_html__('New post created', 'frontend-manager'),
                    'group' => 'wpfm_settings',
                ],
                [
                    'name' => 'notification_body',
                    'label' => esc_html__('Message', 'frontend-manager'),
                    'desc' => '',
                    'type' => 'textarea',
                    'default' => $this->default_notification_body,
                    'group' => 'wpfm_settings',
                ],
            ])
        ];
        return apply_filters('wpfm_setting_fields', $fields);
    }

    public function init()
    {
        $default_notification_body = "Hi Admin,\r\n";
        $default_notification_body .= "A new post has been created in your site %sitename% (%siteurl%).\r\n\r\n";
//        $edit_mail_body = "Hi Admin,\r\n";
//        $edit_mail_body .= "The post \"%post_title%\" has been updated.\r\n\r\n";
        $default_notification_body .= "Here is the details:\r\n";
        $default_notification_body .= "Post Title: %post_title%\r\n";
        $default_notification_body .= "Content: %post_content%\r\n";
        $default_notification_body .= "Author: %author%\r\n";
        $default_notification_body .= "Post URL: %permalink%\r\n";
        $default_notification_body .= 'Edit URL: %editlink%';

        $this->default_notification_body = $default_notification_body;
        $post_types = get_post_types(array('public' => true), 'names', 'and');
        unset($post_types['attachment']);
        unset($post_types['revision']);
        unset($post_types['nav_menu_item']);
        unset($post_types['custom_css']);
        unset($post_types['customize_changeset']);
        unset($post_types['oembed_cache']);
        $this->postTypes = $post_types;

        $this->statuses = get_post_statuses();

        $post_type_selected = get_settings_value('post_type') ?: 'post';
        $taxonomies = get_object_taxonomies($post_type_selected, 'objects');
        unset($taxonomies['edition']);
        $taxonomies = array_map(function ($tax) use ($post_type_selected) {
            $name = 'default_' . $tax->name;
            $default_category = get_settings_value($name);
            if (!is_array($default_category)) {
                $default_category = (array)$default_category;
            }
            if ($tax->hierarchical) {
                $args = ['hide_empty' => false,
                    'hierarchical' => true,
                    'selected' => $default_category,
                    'taxonomy' => $tax->name,];
                $categories = get_terms($args);
                $default = [];
                $options = [];
                foreach ($categories as $category) {
                    if (in_array($category->term_id, $default_category)) {
                        $default[] = $category->term_id;
                    }
                    $options[$category->term_id] = $category->name;
                }
                return [
                    'name' => $name,
                    'label' => esc_html__('Default', 'frontend-manager') . ' ' . $post_type_selected . ' ' . $tax->name,
                    'desc' => '',
                    'type' => 'multiselect',
                    'default' => $default,
                    'options' => $options,
                    'group' => 'wpfm_settings',
                ];
            }
            return [];
        }, $taxonomies);
        $taxonomies = array_filter($taxonomies);
        $this->categories = $taxonomies;

        $pages = get_posts(['numberposts' => -1, 'post_type' => 'page']);
        $this->pages = array_column($pages, 'post_title', 'ID');

        $this->users = $this->_list_users();

        $this->_save_settings();
    }

    /**
     * Get lists of users from database
     * @return array
     */
    public function _list_users()
    {
        global $wpdb;

        $users = $wpdb->get_results("SELECT ID, user_login from $wpdb->users");

        $list = [];

        if ($users) {
            foreach ($users as $user) {
                $list[$user->ID] = $user->user_login;
            }
        }

        return $list;
    }

    public function _enqueue_admin_style()
    {
        wp_enqueue_style('wpfm-admin-style', WPFM_ASSET_URI . '/css/admin.css', false, WPFM_VERSION);
        wp_enqueue_script('wpfm-admin-script', WPFM_ASSET_URI . '/js/admin.js', ['jquery-core'], WPFM_VERSION, true);
    }

    /**
     * The content of the Post Form page.
     *
     * @return void
     */
    public function _wpfm_page()
    {
        $formFields = $this->form_fields();
        require_once WPFM_ROOT . '/views/admin/index.php';
    }

    /**
     * Get field description for display
     *
     * @param array $args settings field args
     */
    public static function get_field_description($args)
    {
        if (!empty($args['desc'])) {
            $desc = sprintf('<p class="description">%s</p>', $args['desc']);
        } else {
            $desc = '';
        }

        return $desc;
    }

    /**
     * @param $field
     * @return string
     */
    public static function generate_html($field, $post_id = null)
    {
        $type = isset($field['type']) ? $field['type'] : 'text';
        $html = '';
        $value = get_settings_value($field['name'], 'wpfm_settings', $field['default']);
        switch ($type) {
            case 'checkbox':
                $disabled = !empty($field['is_pro_preview']) && $field['is_pro_preview'] ? 'disabled' : '';
                $html .= '<div class="field-group checkbox-field">';
                $html .= sprintf('<label for="%1$s[%2$s]">', $field['group'], $field['name']);
                $html .= sprintf('<input type="hidden" name="%1$s[%2$s]" value="off" />', $field['group'], $field['name']);
                $html .= sprintf('<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s %4$s />', $field['group'], $field['name'], checked($value, 'on', false), $disabled);
                $html .= sprintf('<span class="description">%1$s</span></label>', $field['desc']);
                $html .= '</div>';
                break;
            case 'multicheckbox':
                $disabled = !empty($field['is_pro_preview']) && $field['is_pro_preview'] ? 'disabled' : '';
                $html .= '<div class="field-group multicheckbox-field checkbox-field">';
                $html .= sprintf('<input type="hidden" name="%1$s[%2$s]" value="" %3$s />', $field['section'], $field['id'], $disabled);
                foreach ($field['options'] as $key => $label) {
                    $checked = in_array($key, (array)$value) ? $key : '0';
                    $html .= sprintf('<label for="%1$s[%2$s][%3$s]">', $field['group'], $field['id'], $key);
                    $html .= sprintf('<input type="checkbox" class="checkbox" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $field['group'], $field['name'], $key, checked($checked, $key, false));
                    $html .= sprintf('<span class="description">%1$s</span></label>', $label);
                }
                $html .= Admin::get_field_description($field);
                $html .= '</div>';
                break;
            case 'select':
                $html .= '<div class="field-group select-field">';
                $size = isset($field['size']) && !is_null($field['size']) ? $field['size'] : 'regular';
                $disabled = !empty($field['is_pro_preview']) && $field['is_pro_preview'] ? 'disabled' : '';
                $html .= sprintf('<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]" %4$s>', $size, $field['group'], $field['name'], $disabled);
                foreach ($field['options'] as $key => $label) {
                    $html .= sprintf('<option value="%s"%s>%s</option>', $key, selected($value, $key, false), $label);
                }
                $html .= sprintf('</select>');
                $html .= Admin::get_field_description($field);
                $html .= '</div>';
                break;
            case 'multiselect':
                $html .= '<div class="field-group multiselect-field select-field">';
                $size = isset($field['size']) && !is_null($field['size']) ? $field['size'] : 'regular';
                $disabled = !empty($field['is_pro_preview']) && $field['is_pro_preview'] ? 'disabled' : '';
                $html .= sprintf('<select class="%1$s" name="%2$s[%3$s][]" id="%2$s[%3$s]" %4$s multiple>', $size, $field['group'], $field['name'], $disabled);
                foreach ($field['options'] as $key => $label) {
                    $selected = in_array($key, (array)$value) ? ' selected="selected"' : '';
                    $html .= sprintf('<option value="%s"%s>%s</option>', $key, $selected, $label);
                }
                $html .= sprintf('</select>');
                $html .= Admin::get_field_description($field);
                $html .= '</div>';
                break;
            case 'textarea':
                $html .= '<div class="field-group textarea-field">';
                $size = isset($field['size']) && !is_null($field['size']) ? $field['size'] : 'regular';
                $placeholder = empty($field['placeholder']) ? '' : ' placeholder="' . $field['placeholder'] . '"';
                $disabled = !empty($field['is_pro_preview']) && $field['is_pro_preview'] ? 'disabled' : '';
                $html .= sprintf('<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%4$s %5$s>%6$s</textarea>', $size, $field['group'], $field['name'], $placeholder, $disabled, $value);
                $html .= Admin::get_field_description($field);
                $html .= '</div>';
                break;
            case 'radio':
                $disabled = !empty($field['is_pro_preview']) && $field['is_pro_preview'] ? 'disabled' : '';
                $html .= '<div class="field-group radio-field">';
                foreach ($field['options'] as $key => $label) {
                    $html .= sprintf('<label for="wpfm-%1$s[%2$s][%3$s]">', $field['group'], $field['name'], $key);
                    $html .= sprintf('<input type="radio" class="radio" id="wpfm-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s %5$s />', $field['group'], $field['name'], $key, checked($value, $key, false), $disabled);
                    $html .= sprintf('%1$s</label><br>', $label);
                }
                $html .= Admin::get_field_description($field);
                $html .= '</div>';
                break;
            case 'number':
                $html .= '<div class="field-group number-field">';
                $size = isset($field['size']) && !is_null($field['size']) ? $field['size'] : 'regular';
                $type = isset($field['type']) ? $field['type'] : 'number';
                $placeholder = empty($field['placeholder']) ? '' : ' placeholder="' . $field['placeholder'] . '"';
                $min = empty($field['min']) ? '' : ' min="' . $field['min'] . '"';
                $max = empty($field['max']) ? '' : ' max="' . $field['max'] . '"';
                $step = empty($field['max']) ? '' : ' step="' . $field['step'] . '"';
                $disabled = !empty($field['is_pro_preview']) && $field['is_pro_preview'] ? 'disabled' : '';
                $html .= sprintf('<input type="%1$s" class="%2$s-number" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s %10$s/>', $type, $size, $field['group'], $field['name'], $value, $placeholder, $min, $max, $step, $disabled);
                $html .= Admin::get_field_description($field);
                $html .= '</div>';
                break;
            case 'editor':
                $html .= '<div class="field-group editor-field">';
                ob_start();
                $size = isset($field['size']) && !is_null($field['size']) ? $field['size'] : '500px';
                echo '<div style="max-width: ' . $size . ';">';
                $editor_settings = array(
                    'teeny' => true,
                    'textarea_name' => $field['group'] . '[' . $field['name'] . ']',
                    'textarea_rows' => 10,
                    'media_buttons' => false
                );
                if (isset($field['options']) && is_array($field['options'])) {
                    $editor_settings = array_merge($editor_settings, $field['options']);
                }
                wp_editor($value, $field['group'] . '-' . $field['name'], $editor_settings);
                echo '</div>';
                echo Admin::get_field_description($field);
                $html .= ob_get_clean();
                $html .= '</div>';
                break;
            case 'file':
                $html .= '<div class="field-group file-field">';
                $disabled = !empty($field['is_pro_preview']) && $field['is_pro_preview'] ? 'disabled' : '';
                $size = isset($field['size']) && !is_null($field['size']) ? $field['size'] : 'regular';
                $id = $field['group'] . '[' . $field['name'] . ']';
                $label = isset($field['button_label']) ? $field['button_label'] : __('Choose File',
                    'frontend-manager');

                $html .= sprintf('<input type="file" style="display: none" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" onchange="loadFile(event)" %5$s/>', $size, $field['group'], $field['name'], $value, $disabled);
                $html .= '<input type="button" class="button wpfm-browse" value="' . $label . '" />';
                $html .= Admin::get_field_description($field);
                $html .= '</div>';
                break;
            case 'password':
                $html .= '<div class="field-group password-field">';
                $disabled = !empty($field['is_pro_preview']) && $field['is_pro_preview'] ? 'disabled' : '';
                $size = isset($field['size']) && !is_null($field['size']) ? $field['size'] : 'regular';
                $html .= sprintf('<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" %5$s/>', $size, $field['group'], $field['name'], $value, $disabled);
                $html .= Admin::get_field_description($field);
                $html .= '</div>';
                break;
            case 'color':
                $html .= '<div class="field-group color-field">';
                $disabled = !empty($field['is_pro_preview']) && $field['is_pro_preview'] ? 'disabled' : '';
                $size = isset($field['size']) && !is_null($field['size']) ? $field['size'] : 'regular';
                $html .= sprintf('<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" %6$s />', $size, $field['group'], $field['name'], $value, $field['std'], $disabled);
                $html .= Admin::get_field_description($field);
                $html .= '</div>';
                break;
            default:
                $html .= '<div class="field-group default-field">';
                $size = isset($field['size']) && !is_null($field['size']) ? $field['size'] : 'regular';
                $type = isset($field['type']) ? $field['type'] : 'text';
                $placeholder = empty($field['placeholder']) ? '' : ' placeholder="' . $field['placeholder'] . '"';
                $disabled = !empty($field['is_pro_preview']) && $field['is_pro_preview'] ? 'disabled' : '';
                $html .= sprintf('<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s %7$s/>', $type, $size, $field['group'], $field['name'], $value, $placeholder, $disabled);
                $html .= Admin::get_field_description($field);
                $html .= '</div>';
                break;
        }
        return $html;
    }

    public function _save_settings()
    {
        if (isset($_POST['wpfm_settings_nonce_field']) && wp_verify_nonce($_POST['wpfm_settings_nonce_field'], 'wpfm_settings_nonce') && isset($_POST['wpfm_settings']) && $_POST['wpfm_settings']) {
            update_option('wpfm_settings', $_POST['wpfm_settings']);
        }
    }

}
