<?php
/**
 * The header for our theme
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Ambient Background -->
<div class="ambient-bg"></div>

<header class="site-header" id="header">
    <div class="container header-inner">
        <!-- Brand / Logo -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="brand" rel="home">
            <?php if (has_custom_logo()) : ?>
                <div class="brand-logo">
                    <?php the_custom_logo(); ?>
                </div>
            <?php else : ?>
                <div class="brand-mark">
                    <?php 
                    $site_title = get_bloginfo('name');
                    $initials = '';
                    $words = explode(' ', $site_title);
                    foreach ($words as $word) {
                        $initials .= strtoupper(substr($word, 0, 1));
                        if (strlen($initials) >= 3) break;
                    }
                    echo esc_html(substr($initials, 0, 3));
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="brand-copy">
                <?php bloginfo('name'); ?>
                <?php 
                $tagline = get_theme_mod('yea_site_tagline', get_bloginfo('description'));
                if ($tagline) : 
                ?>
                    <small><?php echo esc_html($tagline); ?></small>
                <?php endif; ?>
            </div>
        </a>

        <!-- Primary Navigation -->
        <nav class="nav" id="primary-nav">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'menu_id'        => 'primary-menu',
                'menu_class'     => '',
                'container'      => false,
                'fallback_cb'    => false,
                'depth'          => 2,
            ));
            ?>
        </nav>

        <!-- Header Actions -->
        <div class="header-actions">
            <?php 
            // Check if Donors menu exists and has items
            if (has_nav_menu('donors')) : 
                wp_nav_menu(array(
                    'theme_location' => 'donors',
                    'menu_class'     => 'donors-menu',
                    'container'      => false,
                    'items_wrap'     => '<a class="btn btn-secondary" href="%2$s">%3$s</a>',
                    'fallback_cb'    => false,
                ));
            else : 
            ?>
                <a class="btn btn-secondary" href="#transparency">
                    <?php esc_html_e('For Donors', 'yea-theme'); ?>
                </a>
            <?php endif; ?>
            
            <a class="btn btn-primary" href="#join">
                <?php esc_html_e('Join YEA', 'yea-theme'); ?>
            </a>
            
            <!-- Mobile Menu Toggle -->
            <button class="menu-toggle" id="menu-toggle" aria-label="<?php esc_attr_e('Toggle Menu', 'yea-theme'); ?>">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

<main id="main-content">
