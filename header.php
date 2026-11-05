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

<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e('Skip to content', 'yea-theme'); ?></a>

<!-- Ambient Background -->
<div class="ambient-bg"></div>

<header class="site-header" id="header">
    <div class="container header-inner">
        <!-- Brand / Logo -->
        <div class="brand">
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
            
            <a class="brand-copy" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                <?php bloginfo('name'); ?>
                <?php 
                $tagline = get_theme_mod('yea_site_tagline', get_bloginfo('description'));
                if ($tagline) : 
                ?>
                    <small><?php echo esc_html($tagline); ?></small>
                <?php endif; ?>
            </a>
        </div>

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

        <!-- Mobile Menu Toggle -->
        <button class="menu-toggle" id="menu-toggle" type="button" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e('Toggle Menu', 'yea-theme'); ?>">
                <span></span>
                <span></span>
                <span></span>
        </button>
    </div>
</header>

<main id="main-content">
