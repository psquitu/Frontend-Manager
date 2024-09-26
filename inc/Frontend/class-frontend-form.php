<?php

namespace WPFM\Frontend;

use WPFM\Admin\Admin;

class Frontend_Form
{

    private $form_fields = [];

    public function __construct()
    {
        add_action("init", [$this, 'init']);
        add_action('wp', [$this, 'handle_post_submission']);
    }

    /**
     * Edit post shortcode
     * @param $atts
     * @return false|string|void
     */
    public function edit_post_shortcode($atts)
    {
        global $userdata;
        extract($atts);
        ob_start();
        if (!is_user_logged_in()) {
            echo wp_kses_post('<div class="wpfm-message">' . esc_html__('You are not logged in.', 'frontend-manager') . '</div>'),

            do_shortcode('[wpfm-login]');

            return;
        }

        $post_id = isset($_GET['pid']) ? intval(wp_unslash($_GET['pid'])) : 0;

        if (!$post_id) {
            return '<div class="wpfm-info">' . esc_html__('Invalid post.', 'frontend-manager') . '</div>';
        }

        if (get_settings_value('enable_post_edit', 'wpfm_settings', 'yes') !== 'yes') {
            return '<div class="wpfm-info">' . esc_html__('Post Editing is disabled.', 'frontend-manager') . '</div>';
        }

        $post = get_post($post_id);

        if (!current_user_can('delete_others_posts') && ($userdata->ID !== (int)$post->post_author)) {
            return '<div class="wpfm-info">' . esc_html__('You are not allowed to edit.', 'frontend-manager') . '</div>';
        }

        echo '<div class="wpfm-form-wrap">';
        echo self::display_notice();
        $this->render_form($post);
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Add post shortcode
     * @param $atts
     * @return false|string|void
     */
    public function add_post_shortcode($atts)
    {
        extract($atts);
        ob_start();
        if (!is_user_logged_in()) {
            echo wp_kses_post('<div class="wpfm-message">' . esc_html__('You are not logged in.', 'frontend-manager') . '</div>'),

            do_shortcode('[wpfm-login]');
            return;
        }

        if (!current_user_can('delete_others_posts')) {
            return '<div class="wpfm-info">' . esc_html__('You are not allowed to add.', 'frontend-manager') . '</div>';
        }

        echo '<div class="wpfm-form-wrap">';
        echo self::display_notice();
        $this->render_form();
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Rander form
     * @param $post
     * @return void
     */
    public function render_form($post = null)
    {
        $post_id = $post ? $post->ID : null;
        ?>
        <form id="wpfm-form" action="" method="post" enctype="multipart/form-data">
            <?php
            foreach ($this->form_fields as $key => $field) {
                $marginClass = $key > 0 ? ' mt-2' : '';
                if ($post) {
                    $field['default'] = $post->{$field['name']};
                }
                if (!in_array($field['name'], ['recaptcha'])) {
                    $featureImgClass = $field['name'] == 'featured_image' ? ' featured-image' : '';
                    echo "<div class='wpfm-form-field{$marginClass}{$featureImgClass}'>";
                    echo "<div class='wpfm-form-field-inner'>";
                    echo sprintf('<div class="form-label">%s</div>', $field['label']);
                    echo Admin::generate_html($field, $post_id);
                    echo '</div>';
                    if ($field['name'] == 'featured_image') {
                        $image_url = '';
                        if ($post_id) {
                            if (has_post_thumbnail($post_id)) {
                                $image_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
                            }
                        }
                        $display = $image_url ? 'block' : 'none';
                        echo "<img src='{$image_url}' id='preview-img' style='display: {$display}' width='80px' alt='' />";
                    }
                    echo '</div>';
                }
                if ('recaptcha' == $field['name'] && $field['enable']) {
                    //get_captcha_html(get_settings_value('recaptcha_site_key'), $marginClass);
                    echo "<div class='wpfm-captcha-field{$marginClass}'>";
                    echo wp_kses(wpfm_recaptcha_get_html(get_settings_value('recaptcha_site_key'), true, null, is_ssl()), [
                        'div' => [
                            'class' => [],
                            'data-sitekey' => [],
                        ],

                        'script' => [
                            'src' => []
                        ],

                        'noscript' => [],

                        'iframe' => [
                            'src' => [],
                            'height' => [],
                            'width' => [],
                            'frameborder' => [],
                        ],
                        'br' => [],
                        'textarea' => [
                            'name' => [],
                            'rows' => [],
                            'cols' => [],
                        ],
                        'input' => [
                            'type' => [],
                            'value' => [],
                            'name' => [],
                        ]
                    ]);
                    echo '</div>';
                }
            }
            echo '<div class="wpfm-form-field mt-3">';
            echo $this->submit_button($post);
            echo '</div>';
            ?>
        </form>
        <?php
    }

    /**
     * Submit Button
     * @param $post
     * @return false|string
     */
    public function submit_button($post = null)
    {
        ob_start();
        ?>
        <div class='wpfm-button-wrap'>
            <?php wp_nonce_field('wpfm_post_add', 'wpfm_post_add_nonce'); ?>
            <input type="hidden" name="action" value="wpfm_submit_post">
            <?php
            if ($post) {
                ?>
                <input type="hidden" name="post_id" value="<?php echo $post->ID ? $post->ID : '0'; ?>">
                <input type="hidden" name="post_date" value="<?php echo esc_attr($post->post_date); ?>">
                <input type="hidden" name="comment_status" value="<?php echo esc_attr($post->comment_status); ?>">
                <input type="hidden" name="post_author" value="<?php echo esc_attr($post->post_author); ?>">
                <input type="submit" class="wpfm-submit-button" name="submit" value="<?php echo esc_attr(get_settings_value('update_text', 'wpfm_settings', esc_html__('Update', 'frontend-manager'))); ?>"/>
                <?php
            } else {
                ?>
                <input type="submit" class="wpfm-submit-button" name="submit" value="<?php echo esc_attr(get_settings_value('submit_text', 'wpfm_settings', esc_html__('Submit', 'frontend-manager'))); ?>"/>
                <?php
            }
            do_action('wpfm_submit_btn');
            ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle Submit post
     * @return void
     */
    public function handle_post_submission(): void
    {
        if (isset($_POST['action']) && 'wpfm_submit_post' == $_POST['action'] && wp_verify_nonce($_POST['wpfm_post_add_nonce'], 'wpfm_post_add')) {
            $data = $_POST['post'];
            if (isset($_POST['post_id']) && !empty($_POST['post_id'])) {
                $data['post_id'] = $_POST['post_id'];
            }
            $files = null;
            if (isset($_FILES['post']) && !empty($_FILES['post'])) {
                foreach ($_FILES['post'] as $key => $file) {
                    $files[$key] = reset($file);
                }
            }
            $this->save_post($data, $files);
        }
    }

    /**
     * Save posts
     * @param $data
     * @param $files
     * @return void
     */
    public function save_post($data, $files = null): void
    {
        if (!empty($data['post_title']) && !empty($data['post_content'])) {
            $post_author = get_settings_value('default_post_owner') ?: get_current_user_id();

            if (isset($_POST['g-recaptcha-response'])) {
                if (empty($_POST['g-recaptcha-response'])) {
                    set_flash_notice(esc_html__('Empty reCaptcha Field.', 'frontend-manager'), 'error');
                    return;
                } else {
                    $no_captcha = 1;
                    $invisible_captcha = 0;
                    if (!$this->wpfm_validate_re_captcha($no_captcha, $invisible_captcha)) {
                        return;
                    }
                }
            }

            // Prepare post data
            $post_data = array(
                'post_title' => sanitize_text_field($data['post_title']),
                'post_content' => wp_kses_post($data['post_content']),
                'post_status' => get_settings_value('post_status') ?: 'pending',  // Post will be pending review
                'post_author' => $post_author,
                'post_category' => (array)get_settings_value('default_category'), // Category ID
            );

            // Insert the post into the database
            if (isset($data['post_id']) && !empty($data['post_id'])) {
                $post_data['ID'] = $data['post_id'];
                $post_id = wp_update_post($post_data);
            } else {
                $post_id = wp_insert_post($post_data);
            }

            update_post_meta($post_id, '_is_fronted_post', 1);

            if ($post_id) {
                // Handle featured image upload if provided
                if (!empty($files)) {
                    $attachment_id = $this->handle_featured_image_upload($files, $post_id);

                    if ($attachment_id) {
                        // Set the uploaded image as the post's featured image
                        set_post_thumbnail($post_id, $attachment_id);
                    }
                }

                if (!isset($data['post_id'])) {
                    $this->send_email($post_id, $post_author);
                }

                set_flash_notice(esc_html__('Post saved successfully!'));
            } else {
                set_flash_notice(esc_html__('Failed to submit the post. Please try again.'), 'error');
            }
        } else {
            set_flash_notice(esc_html__('Please fill in all required fields.'), 'error');
        }
    }

    /**
     * Send notification according to settings
     * @param $post_id
     * @param $post_author
     * @return void
     */
    public function send_email($post_id, $post_author): void
    {
        if (get_settings_value('enable_notification') === 'on') {
            $mail_body = $this->prepare_mail_body(get_settings_value('notification_body'), $post_author, $post_id);
            $to = $this->prepare_mail_body(get_settings_value('notification_to'), $post_author, $post_id);
            $subject = $this->prepare_mail_body(get_settings_value('notification_subject'), $post_author, $post_id);
            $subject = wp_strip_all_tags($subject);
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            wp_mail($to, $subject, $mail_body, $headers);
        }
    }

    /**
     * Function to handle image upload and attachment
     * @param $files
     * @param $post_id
     * @return false|int|\WP_Error
     */
    public function handle_featured_image_upload($files, $post_id)
    {
        // Check if a file is uploaded
        if (isset($files) && !$files['error']) {

            if (!function_exists('wp_handle_upload')) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }
            // Handle the file upload
            $uploaded_file = wp_handle_upload($files, array('test_form' => false));

            if (isset($uploaded_file['file'])) {
                // File upload was successful, prepare an array for image attachment
                $file_name = basename($uploaded_file['file']);
                $file_type = wp_check_filetype($uploaded_file['file']);
                $wp_upload_dir = wp_upload_dir();

                // Create the attachment data
                $attachment_data = array(
                    'guid' => $wp_upload_dir['url'] . '/' . $file_name,
                    'post_mime_type' => $uploaded_file['type'],
                    'post_title' => sanitize_file_name($file_name),
                    'post_content' => '',
                    'post_status' => 'inherit',
                );

                // Insert the attachment into the WordPress media library
                $attachment_id = wp_insert_attachment($attachment_data, $uploaded_file['file'], $post_id);

                // Generate attachment metadata and update it in the database
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $uploaded_file['file']);
                wp_update_attachment_metadata($attachment_id, $attachment_metadata);

                return $attachment_id;
            }
        }

        return false;
    }


    /**
     * Prepare mail body
     * @param $content
     * @param $user_id
     * @param $post_id
     * @return array|string|string[]
     */
    public function prepare_mail_body($content, $user_id, $post_id): array|string
    {
        $user = get_user_by('id', $user_id);
        $post = get_post($post_id);
        $post_field_search = [
            '%post_title%',
            '%post_content%',
            '%post_excerpt%',
            '%tags%',
            '%category%',
            '%author%',
            '%author_email%',
            '%author_bio%',
            '%sitename%',
            '%siteurl%',
            '%permalink%',
            '%editlink%',
        ];
        $home_url = sprintf('<a href="%s">%s</a>', home_url(), home_url());
        $post_url = sprintf('<a href="%s">%s</a>', get_permalink($post_id), get_permalink($post_id));
        $post_edit_link = sprintf('<a href="%s">%s</a>', admin_url('post.php?action=edit&post=' . $post_id), admin_url('post.php?action=edit&post=' . $post_id));
        $post_field_replace = [
            $post->post_title,
            $post->post_content,
            $post->post_excerpt,
            get_the_term_list($post_id, 'post_tag', '', ', '),
            get_the_term_list($post_id, 'category', '', ', '),
            $user->display_name,
            $user->user_email,
            ($user->description) ? $user->description : 'not available',
            get_bloginfo('name'),
            $home_url,
            $post_url,
            $post_edit_link,
        ];
        if (class_exists('WooCommerce')) {
            $post_field_search[] = '%product_cat%';
            $post_field_replace[] = get_the_term_list($post_id, 'product_cat', '', ', ');
        }
        $content = str_replace($post_field_search, $post_field_replace, $content);
        preg_match_all('/%custom_([\w-]*)\b%/', $content, $matches);
        [$search, $replace] = $matches;
        if ($replace) {
            foreach ($replace as $index => $meta_key) {
                $value = get_post_meta($post_id, $meta_key, false);

                if (isset($value[0]) && is_array($value[0])) {
                    $new_value = implode('; ', $value[0]);
                } else {
                    $new_value = implode('; ', $value);
                }

                $original_value = '';
                $meta_val = '';

                if (count($value) > 1) {
                    $is_first = true;

                    foreach ($value as $val) {
                        if ($is_first) {
                            if (get_post_mime_type((int)$val)) {
                                $meta_val = wp_get_attachment_url($val);
                            } else {
                                $meta_val = $val;
                            }
                            $is_first = false;
                        } else {
                            if (get_post_mime_type((int)$val)) {
                                $meta_val = $meta_val . ', ' . wp_get_attachment_url($val);
                            } else {
                                $meta_val = $meta_val . ', ' . $val;
                            }
                        }

                        if (get_post_mime_type((int)$val)) {
                            $meta_val = $meta_val . ',' . wp_get_attachment_url($val);
                        } else {
                            $meta_val = $meta_val . ',' . $val;
                        }
                    }
                    $original_value = $original_value . $meta_val;
                } else {
                    if ('address_field' === $meta_key) {
                        $value = get_post_meta($post_id, $meta_key, true);
                        $new_value = implode(', ', $value);
                    }

                    if (get_post_mime_type((int)$new_value)) {
                        $original_value = wp_get_attachment_url($new_value);
                    } else {
                        $original_value = $new_value;
                    }
                }
                $content = str_replace($search[$index], $original_value, $content);
            }
        }
        return $content;
    }

    /**
     * reCaptcha Validation
     * @return void
     */
    public function wpfm_validate_re_captcha($no_captcha = '', $invisible = '')
    {
        $site_key = get_settings_value('recaptcha_site_key');
        $private_key = get_settings_value('recaptcha_secret_key');
        $remote_addr = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
        $g_recaptcha_response = isset($_POST['g-recaptcha-response']) ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) : '';

        if ($no_captcha == 1 && 0 == $invisible) {
            if (!class_exists('WPFM_ReCaptcha')) {
                require_once WPFM_INCLUDES . '/Lib/class-noCaptcha.php';
            }

            $response = null;
            $reCaptcha = new \WPFM_ReCaptcha($private_key);

            $resp = $reCaptcha->verifyResponse(
                $remote_addr,
                $g_recaptcha_response
            );

            if (!$resp->success) {
                set_flash_notice(esc_html__('noCaptcha reCAPTCHA validation failed.', 'frontend-manager'), 'error');
                return;
            }
            return $resp->success;
        } elseif ($no_captcha == 0 && 0 == $invisible) {
            $recap_challenge = isset($_POST['recaptcha_challenge_field']) ? sanitize_text_field(wp_unslash($_POST['recaptcha_challenge_field'])) : '';
            $recap_response = isset($_POST['recaptcha_response_field']) ? sanitize_text_field(wp_unslash($_POST['recaptcha_response_field'])) : '';

            $resp = wpfm_recaptcha_check_answer($private_key, $remote_addr, $recap_challenge, $recap_response);

            if (!$resp->is_valid) {
                set_flash_notice(esc_html__('reCAPTCHA validation failed.', 'frontend-manager'), 'error');
                return;
            }
            return $resp->is_valid;
        } elseif ($no_captcha == 0 && 1 == $invisible) {
            $response = null;
            $recaptcha = isset($_POST['g-recaptcha-response']) ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) : '';
            $object = new \WPFM_Invisible_Recaptcha($site_key, $private_key);

            $response = $object->verifyResponse($recaptcha);

            if (isset($response['success']) and $response['success'] != true) {
                set_flash_notice(esc_html__('Invisible reCAPTCHA validation failed.', 'frontend-manager'), 'error');
                return;
            }
            return $response->success;
        }
    }

    /**
     * Post form fields
     * @return void
     */
    public function init()
    {
        $this->form_fields = apply_filters("wpfm_form_fields", [
            [
                'label' => esc_html__('Post Title *', 'frontend-manager'),
                'name' => 'post_title',
                'type' => 'text',
                'default' => '',
                'required' => true,
                'description' => '',
                'group' => 'post',
            ],
            [
                'label' => esc_html__('Post Content *', 'frontend-manager'),
                'name' => 'post_content',
                'type' => 'editor',
                'default' => '',
                'required' => true,
                'description' => '',
                'size' => '100%',
                'group' => 'post',
            ],
            [
                'label' => esc_html__('Featured Image', 'frontend-manager'),
                'button_label' => esc_html__('Featured Image', 'frontend-manager'),
                'name' => 'featured_image',
                'type' => 'file',
                'default' => '',
                'required' => false,
                'description' => esc_html__('Maximum file size is 256KB.', 'frontend-manager'),
                'group' => 'post',
            ],
            [
                'label' => '',
                'name' => 'recaptcha',
                'type' => 'recaptcha',
                'default' => '',
                'required' => true,
                'enable' => get_settings_value('enable_recaptcha') == 'on' ? true : false,
                'group' => 'post',
            ]
        ]);
    }

    /**
     * Login Url
     * @return mixed|null
     */
    public static function login_url()
    {
        $login_url = wp_login_url();
        if (get_option('wpfm_login')) {
            $login_url = get_permalink(get_option('wpfm_login'));
        }
        if (get_settings_value('login_page')) {
            $login_url = get_permalink(get_settings_value('login_page'));
        }
        return apply_filters('wpfm_login_url', $login_url);
    }

    /**
     * Register Url
     * @return mixed|null
     */
    public static function registration_url()
    {
        $register_url = site_url('wp-login.php?action=register', 'login');
        if (get_option('wpfm_register')) {
            $register_url = get_permalink(get_option('wpfm_register'));
        }
        if (get_settings_value('register_page')) {
            $register_url = get_permalink(get_settings_value('register_page'));
        }
        return apply_filters('wpfm_register_url', $register_url);
    }

    /**
     * Login Link with anchor tag
     * @return mixed|null
     */
    public static function login_link()
    {
        $login = sprintf('<a href="%1$s">%2$s</a>', Frontend_Form::login_url(), esc_html__('Login', 'frontend-manager'));
        return apply_filters('wpfm_login_link', $login);
    }

    /**
     * Register Link with anchor tag
     * @return mixed|null
     */
    public static function register_link()
    {
        $register = sprintf('<a href="%1$s">%2$s</a>', Frontend_Form::registration_url(), esc_html__('Register', 'frontend-manager'));
        return apply_filters('wpfm_register_link', $register);
    }

    /**
     * Display the transient value and destroy
     * @param $transient
     * @return mixed|null
     */
    public static function display_notice($transient = 'wp_flash_notice')
    {
        ob_start();
        $notice = get_transient($transient);
        if ($notice) {
            $class[] = 'wpfm-flash-message';
            $class[] = $notice['type'] == 'error' ? 'flash-error' : 'flash-success';
            $class = implode(' ', apply_filters('wpfm_display_notice_class', $class));
            $messages = implode('<br />', apply_filters('wpfm_display_notice_messages', (array)$notice['message']));
            ?>
            <div class="<?php echo esc_attr($class); ?>">
                <p><?php echo $messages; ?></p>
            </div>
            <?php
            // Clear the transient after displaying it
            delete_transient($transient);
        }
        $noticeHtml = ob_get_clean();
        return apply_filters('wpfm_display_notice_html', $noticeHtml);
    }
}
