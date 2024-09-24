<?php
/**
 * @var WPFM\Frontend\Frontend_Account $sections
 * @var WPFM\Frontend\Frontend_Account $activeSection
 */
?>
<div class="wpfm-dashboard-container">
    <nav class="wpfm-dashboard-navigation">
        <ul>
            <?php
            $sn = 1;
            foreach ($sections as $key => $section) {
                $activeClass = (($activeSection == $key) || !$activeSection && $sn == 1) ? ' active' : ''
                ?>
                <li class="wpfm-menu-item <?= $key . $activeClass ?>">
                    <a href="<?= get_permalink() . "?section=$key" ?>"><?= $section ?></a>
                </li>
                <?php $sn++;
            } ?>
        </ul>
    </nav>
    <div class="wpfm-dashboard-content <?= $activeSection ?: 'post' ?>">
        <?php
        if ( !empty( $activeSection ) && is_user_logged_in() ) {
            do_action( "wpfm_content_{$activeSection}", $sections, $activeSection );
        }
        ?>
    </div>
</div>
