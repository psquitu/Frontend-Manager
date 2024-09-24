<p>
    <?php
    global $current_user;
    printf(
        wp_kses_post(__('Hello %1$s, (not %1$s? <a href="%2$s">Sign out</a>)', 'frontend-manager')),
        '<strong>' . esc_html($current_user->display_name) . '</strong>',
        esc_url(wp_logout_url(get_permalink()))
    );
    ?>
</p>
<p>
    <?php
    $links = '';
    foreach (get_account_sections() as $section => $label) {
        // backward compatibility
        if (is_array($label)) {
            $section = $label['slug'];
            $label = $label['label'];
        }

        $links .= '<a href="' . esc_url(add_query_arg(['section' => $section], get_permalink())) . '">' . $label . '</a>, ';
    }

    printf(
        wp_kses_post(esc_html__('From your account dashboard you can view your dashboard, manage your %s', 'frontend-manager')),
        wp_kses($links, ['a' => ['href' => []]])
    );
    ?>
</p>
