<?php
/**
 * @var $posts
 * @var $paged
 */

use WPFM\Frontend\Frontend_Form;

if ($posts->have_posts()) { ?>
    <div class='items-table-container'>
        <?= Frontend_Form::display_notice('posts') ?>
        <table class="items-table post" cellpadding="0" cellspacing="0">
            <thead>
            <tr class="items-list-header">
                <th><?php esc_html_e('Title', 'frontend-manager') ?></th>
                <th><?php esc_html_e('Status', 'frontend-manager'); ?></th>
                <th><?php esc_html_e('Options', 'frontend-manager'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php while ($posts->have_posts()) : $posts->the_post(); ?>
                <tr>
                    <td><?php the_title() ?></td>
                    <td>
                        <?php
                        $current_post_status = get_post_status();
                        if ('publish' === $current_post_status) {
                            $link_text = esc_html__('View', 'frontend-manager');
                            $the_link = get_permalink();
                        } else {
                            $link_text = esc_html__('Preview', 'frontend-manager');
                            $the_link = get_preview_post_link();
                        }
                        show_post_status($current_post_status);
                        echo '&nbsp;|&nbsp;';
                        printf(
                            '<a href="%s" target="_blank">%s</a>',
                            $the_link,
                            $link_text
                        );
                        ?>
                    </td>
                    <td data-label="<?php esc_attr_e('Options: ', 'wp-user-frontend'); ?>" class="data-column">
                        <?php
                        $post = get_post(get_the_ID());
                        if (is_post_editable($post)) {
                            $edit_page = (int)get_settings_value('edit_page_id');
                            $url = add_query_arg(['pid' => $post->ID], get_permalink($edit_page));
                            ?>
                            <a class="wpfm-posts-options wpfm-posts-edit" href="<?php echo esc_url(wp_nonce_url($url, 'wpfm_edit')); ?>">
                                <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M12.2175 0.232507L14.0736 2.08857C14.3836 2.39858 14.3836 2.90335 14.0736 3.21336L12.6189 4.66802L9.63808 1.68716L11.0927 0.232507C11.4027 -0.0775022 11.9075 -0.0775022 12.2175 0.232507ZM0 14.3061V11.3253L8.7955 2.52974L11.7764 5.5106L2.98086 14.3061H0Z"
                                          fill="#B7C4E7"/>
                                </svg>
                            </a>
                            <?php
                        }
                        if (get_settings_value('enable_post_del', 'wpfm_settings', 'yes') == 'yes' && is_post_editable($post)) {
                            $del_url = add_query_arg(['action' => 'del', 'pid' => $post->ID]);
                            $message = __('Are you sure to delete?', 'frontend-manager'); ?>
                            <a class="wpfm-posts-options wpfm-posts-delete" style="color: red;" href="<?php echo esc_url_raw(wp_nonce_url($del_url, 'wpfm_delete')); ?>"
                               onclick="return confirm('<?php echo esc_attr($message); ?>');">
                                <svg width="15" height="15" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M11.8082 1.9102H7.98776C7.73445 1.9102 7.49152 1.80958 7.3124 1.63046C7.13328 1.45134 7.03266 1.20841 7.03266 0.955102C7.03266 0.701793 7.13328 0.458859 7.3124 0.279743C7.49152 0.100626 7.73445 0 7.98776 0H11.8082C12.0615 0 12.3044 0.100626 12.4835 0.279743C12.6626 0.458859 12.7633 0.701793 12.7633 0.955102C12.7633 1.20841 12.6626 1.45134 12.4835 1.63046C12.3044 1.80958 12.0615 1.9102 11.8082 1.9102ZM1.30203 2.86529H18.4939C18.7472 2.86529 18.9901 2.96591 19.1692 3.14503C19.3483 3.32415 19.449 3.56708 19.449 3.82039C19.449 4.0737 19.3483 4.31663 19.1692 4.49575C18.9901 4.67486 18.7472 4.77549 18.4939 4.77549H16.5837V16.2367C16.5835 16.9966 16.2815 17.7253 15.7442 18.2626C15.2069 18.7999 14.4782 19.1018 13.7184 19.102H6.07754C5.31768 19.1018 4.58901 18.7998 4.05171 18.2625C3.51441 17.7252 3.21246 16.9966 3.21223 16.2367V4.77549H1.30203C1.04872 4.77549 0.805783 4.67486 0.626667 4.49575C0.44755 4.31663 0.346924 4.0737 0.346924 3.82039C0.346924 3.56708 0.44755 3.32415 0.626667 3.14503C0.805783 2.96591 1.04872 2.86529 1.30203 2.86529ZM8.6631 14.0468C8.84222 13.8677 8.94284 13.6247 8.94284 13.3714V8.5959C8.94284 8.34259 8.84222 8.09966 8.6631 7.92054C8.48398 7.74142 8.24105 7.6408 7.98774 7.6408C7.73443 7.6408 7.4915 7.74142 7.31238 7.92054C7.13327 8.09966 7.03264 8.34259 7.03264 8.5959V13.3714C7.03264 13.6247 7.13327 13.8677 7.31238 14.0468C7.4915 14.2259 7.73443 14.3265 7.98774 14.3265C8.24105 14.3265 8.48398 14.2259 8.6631 14.0468ZM12.4835 14.0468C12.6626 13.8677 12.7633 13.6247 12.7633 13.3714V8.5959C12.7633 8.34259 12.6626 8.09966 12.4835 7.92054C12.3044 7.74142 12.0615 7.6408 11.8081 7.6408C11.5548 7.6408 11.3119 7.74142 11.1328 7.92054C10.9537 8.09966 10.853 8.34259 10.853 8.5959V13.3714C10.853 13.6247 10.9537 13.8677 11.1328 14.0468C11.3119 14.2259 11.5548 14.3265 11.8081 14.3265C12.0615 14.3265 12.3044 14.2259 12.4835 14.0468Z"
                                          fill="#B7C4E7"/>
                                </svg>
                            </a>
                        <?php } ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <div class='wpfm-account-pagination mt-2'>
            <?php echo showPagination($posts->max_num_pages, $paged); ?>
        </div>
    </div>
    <?php
    wp_reset_postdata();
}
