<?php

use WPFM\Frontend\Registration;

/**
 * Check if current post is editable
 * @param $post
 * @return bool
 */
function is_post_editable($post)
{
    $show_edit = false;

    if (get_settings_value('enable_post_edit', 'wpfm_settings', 'yes') == 'yes') {
        $disable_pending_edit = get_settings_value('disable_pending_edit', 'wpfm_settings', 'on');
        $disable_publish_edit = get_settings_value('disable_publish_edit', 'wpfm_settings', 'off');

        $show_edit = true;
        if (('pending' === $post->post_status && 'on' === $disable_pending_edit) || ('publish' === $post->post_status && 'off' !== $disable_publish_edit)) {
            $show_edit = false;
        }

        if (($post->post_status == 'draft' || $post->post_status == 'pending') && (!empty($payment_status) && $payment_status != 'completed')) {
            $show_edit = false;
        }
    }

    return $show_edit;
}

function show_post_status($status)
{
    if ('publish' === $status) {
        $title = __('Live', 'frontend-manager');
        $fontcolor = '#33CC33';
    } elseif ('draft' === $status) {
        $title = __('Offline', 'frontend-manager');
        $fontcolor = '#bbbbbb';
    } elseif ('pending' === $status) {
        $title = __('Awaiting Approval', 'frontend-manager');
        $fontcolor = '#C00202';
    } elseif ('future' === $status) {
        $title = __('Scheduled', 'frontend-manager');
        $fontcolor = '#bbbbbb';
    } elseif ('private' === $status) {
        $title = __('Private', 'frontend-manager');
        $fontcolor = '#bbbbbb';
    }

    $show_status = '<span style="color:' . $fontcolor . ';">' . $title . '</span>';
    echo wp_kses_post(apply_filters('wpfm_show_post_status', $show_status, $status));
}

/**
 * Edit post link for frontend
 *
 * @param string $url url of the original post edit link
 * @param int $post_id
 * @return string url of the current edit post page
 *
 */
function override_admin_edit_link($url, $post_id)
{
    if (is_admin()) {
        return $url;
    }

    $override = wpfm_get_option('override_editlink', 'wpfm_general', 'no');

    if ($override === 'yes') {
        $url = '';

        if ('yes' === get_settings_value('enable_post_edit', null, 'yes')) {
            $edit_page = (int)get_settings_value('edit_page_id');
            $url = get_permalink($edit_page);

            $url = wp_nonce_url($url . '?pid=' . $post_id, 'ht_edit');
        }
    }

    return apply_filters('ht_front_post_edit_link', $url);
}

/**
 * Show helper texts to understand the type of page in admin page listing
 * @param array $state
 * @param WP_Post $post
 * @return array
 */
function _admin_page_states($state, $post)
{
    if ('page' !== $post->post_type) {
        return $state;
    }

    $pattern = '/\[(wpfm[\w\-\_]+).+\]/';

    preg_match_all($pattern, $post->post_content, $matches);
    $matches = array_unique($matches[0]);

    if (!empty($matches)) {
        $page = '';
        $shortcode = $matches[0];

        if ('[wpfm-account]' === $shortcode) {
            $page = 'WPFM Account Page';
        } elseif ('[wpfm-edit]' === $shortcode) {
            $page = 'WPFM Post Edit Page';
        } elseif ('[wpfm-login]' === $shortcode) {
            $page = 'WPFM Login Page';
        } elseif ('[wpfm-submit-post]' === $shortcode) {
            $page = 'WPFM Submit Post';
        } elseif ('[wpfm-editprofile]' === $shortcode) {
            $page = 'WPFM Profile Edit Page';
        } elseif (stristr($shortcode, '[wpfm-dashboard')) {
            $page = 'WPFM Dashboard Page';
        } elseif (stristr($shortcode, '[wpfm-profile type="registration"')) {
            $page = 'WPFM Registration Page';
        } elseif (stristr($shortcode, '[wpfm_profile type="profile"')) {
            $page = 'WPFM Profile Edit Page';
        } elseif (stristr($shortcode, '[wpfm_form')) {
            $page = 'WPFM Form Page';
        }/**/

        if (!empty($page)) {
            $state['frontend-manager'] = $page;
        }
    }

    return $state;
}

add_filter('display_post_states', '_admin_page_states', 10, 2);


function get_settings_value($name, $section = 'wpfm_settings', $default = '')
{
    $settings = get_option($section);
    if ($settings && isset($settings[$name])) {
        return $settings[$name];
    }
    return $default;
}

/**
 * Include a template file
 *
 * Looks up first on the theme directory, if not found
 * lods from plugins folder
 *
 * @param string $file file name or path to file
 */
function wpfm_load_template($file, $args = [])
{
    //phpcs:ignore
    if ($args && is_array($args)) {
        extract($args);
    }

    $child_theme_dir = get_stylesheet_directory() . '/wpfm/';
    $parent_theme_dir = get_template_directory() . '/wpfm/';
    $wpfm_dir = WPFM_ROOT . '/templates/';

    if (file_exists($child_theme_dir . $file)) {
        include $child_theme_dir . $file;
    } elseif (file_exists($parent_theme_dir . $file)) {
        include $parent_theme_dir . $file;
    } elseif (file_exists($wpfm_dir . $file)) {
        include $wpfm_dir . $file;
    }
}

/**
 * Function to set flash notice
 * @param $message
 * @param $type
 * @return void
 */
function set_flash_notice($message, $type = 'success', $transient = 'wp_flash_notice')
{
    set_transient($transient, array('message' => $message, 'type' => $type), 60); // Store for 1 minute
}


function get_captcha_html($site_key, $marginClass = '')
{
    ?>
    <div class='wpfm-captcha-field<?php echo $marginClass ?>'>
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
        <script>
            var verifyCallback = function (response) {
                document.getElementById('htf-g-recaptcha').classList.remove('invalid-captcha');
                document.querySelector('.wpfm-submit-button').setAttribute('type', 'submit');
            };
            var onloadCallback = function () {
                grecaptcha.render('htf-g-recaptcha', {
                    'sitekey': '<?php echo $site_key?>',
                    'callback': verifyCallback,
                });
            };
            jQuery(document).ready(function ($) {
                $(document).on('click', 'input[type=button].wpfm-submit-button', function () {
                    document.getElementById('htf-g-recaptcha').classList.add('invalid-captcha');
                })
            });
        </script>
        <div class="g-recaptcha" id="htf-g-recaptcha" data-sitekey="<?php echo get_settings_value('recaptcha_site_key') ?>"></div>
    </div>
    <?php
}

function get_account_sections()
{
    $postKey = get_settings_value('post_type') ?: 'post';
    $sections = [
        'dashboard' => esc_html__('Dashboard', 'frontend-manager'),
        $postKey => esc_html__('My Posts', 'frontend-manager'),
        'edit-profile' => esc_html__('Edit Profile', 'frontend-manager'),
        'submit-post' => esc_html__('Submit Post', 'frontend-manager'),
    ];
    return apply_filters('wpfm_account_sections', $sections);
}

/**
 * Encryption function for various usage
 * @param $id
 * @param $nonce
 * @return false|string encoded string or false if encryption failed
 * @throws Exception
 */
function wpfm_encryption( $id, $nonce = null ) {
    $auth_keys  = Registration::get_encryption_auth_keys();
    $secret_key = $auth_keys['auth_key'];
    $secret_iv  = ! empty( $nonce ) ? base64_decode( $nonce ) : $auth_keys['auth_salt'];

    if ( function_exists( 'sodium_crypto_secretbox' ) ) {
        try {
            return base64_encode( sodium_crypto_secretbox( $id, $secret_iv, $secret_key ) );
        } catch ( Exception $e ) {
            delete_option( 'wpfm_auth_keys' );
            return false;
        }
    }

    $ciphertext_raw = openssl_encrypt( $id, Registration::get_encryption_method(), $secret_key, OPENSSL_RAW_DATA, $secret_iv );
    $hmac           = hash_hmac( 'sha256', $ciphertext_raw, $secret_key, true );

    return base64_encode( $secret_iv.$hmac.$ciphertext_raw );
}

/**
 * Decryption function for various usage
 * @param $id
 * @param $nonce
 * @return bool|string ecrypted string or false if decryption failed
 * @throws Exception
 */
function wpfm_decryption( $id, $nonce = null ) {
    // get auth keys
    $auth_keys = Registration::get_encryption_auth_keys();
    if ( empty( $auth_keys ) ) {
        return false;
    }

    $secret_key = $auth_keys['auth_key'];
    $secret_iv  = ! empty( $nonce ) ? base64_decode( $nonce ) : $auth_keys['auth_salt'];

    // should we use sodium_crypto_secretbox_open
    if ( function_exists( 'sodium_crypto_secretbox_open') ) {
        try {
            return sodium_crypto_secretbox_open( base64_decode( $id ), $secret_iv, $secret_key );
        } catch ( Exception $e ) {
            delete_option( 'wpfm_auth_keys' );
            return false;
        }
    }

    $c              = base64_decode( $id );
    $ivlen          = Registration::get_encryption_nonce_length();
    $secret_iv      = substr( $c, 0, $ivlen );
    $hmac           = substr( $c, $ivlen, 32 );
    $ciphertext_raw = substr( $c, $ivlen + 32 );
    $original_text  = openssl_decrypt( $ciphertext_raw, Registration::get_encryption_method(), $secret_key, OPENSSL_RAW_DATA, $secret_iv );
    $calcmac        = hash_hmac( 'sha256', $ciphertext_raw, $secret_key, true );

    // timing attack safe comparison
    if ( hash_equals( $hmac, $calcmac ) ) {
        return $original_text;
    }

    return false;
}

function showPagination(int $totalPages, int $currentPage, int $range = 4, $method = '')
{
    if ($totalPages <= 1) {
        return '';
    }

    $parsedUrl = wp_parse_url(get_pagenum_link());
    $query = (isset($parsedUrl['query']) && $parsedUrl['query']) ? ('?' . $parsedUrl['query']) : '';
    $query = ($query && isset($parsedUrl['fragment'])) ? $query . $parsedUrl['fragment'] : $query;
    $url = esc_url($parsedUrl['host'] . $parsedUrl['path']);

    ob_start();
    $min = 1;
    /** minimum page number to be shown */
    $max = $totalPages;
    /** max page number to be shown */
    if ($totalPages > $range) {
        if ($currentPage <= ceil($range / 2)) {
            $min = 1;
            $max = $range;
        } else {
            $min = ($currentPage - ceil($range / 2)) + 1;
            if ($range % 2 === 0) {
                $max = $currentPage + ceil($range / 2);
            } else {
                $max = ($currentPage + ceil($range / 2)) - 1;
            }

            if ($currentPage > ($totalPages - ceil($range / 2))) {
                $min = $totalPages - ($range - 1);
                $max = $totalPages;
            }
        }
    }
    $pageMethod = '/page/';
    if (strtolower($method) == 'get') {
        $pageMethod = '/page=';
    }
    ?>
    <section class="pagination-section">
        <div class="container">
            <ul class="pagination">
                <?php
                if (!empty($min) && !empty($max)) {
                    if ($min > 1) {
                        echo '<li><a href="' . trailingslashit($url) . '/' . $query . '" class="first"> First</a></li>';
                    }
                    if ($currentPage > 1) {
                        echo '<li><a href="' . untrailingslashit($url) . '/page/' . ($currentPage - 1) . '/' . $query . '" class="prev">&lt; Prev</a></li>';
                    }
                    if ($min > 1) {
                        echo '<li><a href="' . trailingslashit($url) . '/' . $query . '"> 1</a></li>';
                        if ($min > 2) {
                            echo '<li><span class="dot">.....</span></li>';
                        }
                    }

                    for ($i = $min; $i <= $max; $i++) {
                        echo '<li class="' . (($currentPage == $i) ? 'active' : '') . '"><a href="' . untrailingslashit($url) . '/page/' . $i . '/' . $query . '">' . $i . '</a></li>';
                    }

                    if ($max < $totalPages) {
                        if ($max != $totalPages - 1) {
                            echo '<li><span class="dot">.....</span></li>';
                        }
                        echo '<li><a href="' . untrailingslashit($url) . '/page/' . $totalPages . '/' . $query . '">' . $totalPages . '</a></li>';
                    }
                    if ($currentPage < $totalPages) {
                        echo '<li><a href="' . untrailingslashit($url) . '/page/' . ($currentPage + 1) . '/' . $query . '" class="next">Next &gt;</a></li>';
                    }
                    if ($max < $totalPages) {
                        echo '<li><a href="' . untrailingslashit($url) . '/page/' . $totalPages . '/' . $query . '" class="last">Last</a></li>';
                    }
                }
                ?>

            </ul>
        </div>
    </section>
    <!-- end pagination -->
    <?php

    return ob_get_clean();
}
