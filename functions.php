<?php
/**
 * YEA Theme Functions
 * Young Entrepreneurs Albania WordPress Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define theme version
define('YEA_VERSION', '1.3.70');

require_once get_template_directory() . '/inc/award-form-config.php';
require_once get_template_directory() . '/inc/award-delivery.php';

/**
 * Theme Setup
 */
function yea_theme_setup() {
    // Add theme support
    add_theme_support('automatic-feed-links');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    add_theme_support('customize-selective-refresh-widgets');
    add_theme_support('responsive-embeds');
    add_theme_support('wp-block-styles');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_editor_style(array('style.css', 'editor-style.css', 'assets/css/components/partner-logos.css', 'assets/css/components/navigation.css', 'assets/css/components/content.css', 'assets/css/components/project-directory.css', 'assets/css/components/forms.css', 'assets/css/components/award-portal.css'));
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    add_theme_support('custom-header', array(
        'default-image' => '',
        'width'         => 1920,
        'height'        => 600,
        'flex-height'   => true,
        'flex-width'    => true,
    ));
    
    // Register navigation menus
    register_nav_menus(array(
        'primary'   => __('Primary Menu', 'yea-theme'),
        'footer'    => __('Footer Menu', 'yea-theme'),
        'donors'    => __('Donors Menu', 'yea-theme'),
    ));
    
    // Load text domain
    load_theme_textdomain('yea-theme', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'yea_theme_setup');

/**
 * Enqueue Scripts and Styles
 */
function yea_enqueue_scripts() {
    // Google Fonts
    wp_enqueue_style('yea-google-fonts', 'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap', array(), null);
    
    // Main stylesheet
    wp_enqueue_style('yea-style', get_stylesheet_uri(), array(), YEA_VERSION);
    
    wp_enqueue_style('yea-partner-logos', get_template_directory_uri() . '/assets/css/components/partner-logos.css', array('yea-style'), YEA_VERSION);
    wp_enqueue_style('yea-navigation', get_template_directory_uri() . '/assets/css/components/navigation.css', array('yea-style'), YEA_VERSION);

    wp_enqueue_style('yea-project-directory', get_template_directory_uri() . '/assets/css/components/project-directory.css', array('yea-style'), YEA_VERSION);
    if (is_page_template(array('page-project-directory', 'page-project-directory.html'))) {
        wp_enqueue_script('yea-project-directory', get_template_directory_uri() . '/js/project-directory.js', array(), YEA_VERSION, true);
    }
    wp_enqueue_style('yea-content', get_template_directory_uri() . '/assets/css/components/content.css', array('yea-style'), YEA_VERSION);
    wp_enqueue_style('yea-responsive', get_template_directory_uri() . '/assets/css/components/responsive.css', array('yea-navigation'), YEA_VERSION);
    wp_enqueue_style('yea-forms', get_template_directory_uri() . '/assets/css/components/forms.css', array('yea-style'), YEA_VERSION);

    if (is_page_template(array('page-award-portal', 'page-award-portal.html'))) {
        wp_enqueue_style('yea-award-portal', get_template_directory_uri() . '/assets/css/components/award-portal.css', array('yea-forms'), YEA_VERSION);
        wp_enqueue_script('yea-award-portal', get_template_directory_uri() . '/js/award-portal.js', array('yea-theme'), YEA_VERSION, true);
        wp_localize_script('yea-award-portal', 'yeaAward', array('deliveryRoot' => esc_url_raw(rest_url('yea/v1/award-delivery/'))));
    }

    // Theme scripts
    wp_enqueue_script('yea-editorial', get_template_directory_uri() . '/js/editorial.js', array(), YEA_VERSION, true);
    wp_enqueue_script('yea-theme', get_template_directory_uri() . '/js/theme.js', array(), YEA_VERSION, true);
    
    // Pass theme options to JavaScript
    wp_localize_script('yea-theme', 'yeaOptions', array(
        'openMenu' => __('Open menu', 'yea-theme'),
        'closeMenu' => __('Close menu', 'yea-theme'),
        'menuLabel' => __('Navigation', 'yea-theme'),
        'toggleSubmenu' => __('Toggle submenu:', 'yea-theme'),
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('yea_nonce'),
    ));
    
    // Comment reply script
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'yea_enqueue_scripts');

/**
 * Register Widget Areas
 */
function yea_widgets_init() {
    register_sidebar(array(
        'name'          => __('Footer Widget Area 1', 'yea-theme'),
        'id'            => 'footer-1',
        'description'   => __('Add widgets here to appear in footer column 1.', 'yea-theme'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));
    
    register_sidebar(array(
        'name'          => __('Footer Widget Area 2', 'yea-theme'),
        'id'            => 'footer-2',
        'description'   => __('Add widgets here to appear in footer column 2.', 'yea-theme'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));
    
    register_sidebar(array(
        'name'          => __('Footer Widget Area 3', 'yea-theme'),
        'id'            => 'footer-3',
        'description'   => __('Add widgets here to appear in footer column 3.', 'yea-theme'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));
    
    register_sidebar(array(
        'name'          => __('Footer Widget Area 4', 'yea-theme'),
        'id'            => 'footer-4',
        'description'   => __('Add widgets here to appear in footer column 4.', 'yea-theme'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'yea_widgets_init');

/**
 * Sanitize checkbox values.
 *
 * @param mixed $checked Raw value.
 * @return bool
 */
function yea_sanitize_checkbox($checked) {
    return (bool) $checked;
}

/**
 * Register Customizer settings for theme-level options.
 *
 * Content still belongs in Gutenberg; these controls are for theme options only.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @return void
 */
function yea_customize_register($wp_customize) {
    $wp_customize->add_section(
        'yea_theme_options',
        array(
            'title'       => __('YEA Theme Options', 'yea-theme'),
            'priority'    => 35,
            'description' => __('Use Gutenberg for page content. Use these options for theme-wide presentation settings.', 'yea-theme'),
        )
    );

    $wp_customize->add_setting(
        'yea_show_brand_text',
        array(
            'default'           => true,
            'sanitize_callback' => 'yea_sanitize_checkbox',
            'transport'         => 'refresh',
        )
    );

    $wp_customize->add_control(
        'yea_show_brand_text',
        array(
            'label'   => __('Show logo text', 'yea-theme'),
            'section' => 'yea_theme_options',
            'type'    => 'checkbox',
        )
    );

    if (class_exists('WP_Customize_Color_Control')) {
        $wp_customize->add_setting(
            'yea_primary_color',
            array(
                'default'           => '#358c9d',
                'sanitize_callback' => 'sanitize_hex_color',
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'yea_primary_color',
                array(
                    'label'   => __('Primary accent color', 'yea-theme'),
                    'section' => 'yea_theme_options',
                )
            )
        );
    }

    $wp_customize->add_section(
        'yea_header_buttons',
        array(
            'title'       => __('YEA Header Buttons', 'yea-theme'),
            'priority'    => 36,
            'description' => __('Control the header CTA button shown next to navigation.', 'yea-theme'),
        )
    );

    $wp_customize->add_setting(
        'yea_header_join_text',
        array(
            'default'           => __('Join YEA', 'yea-theme'),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        )
    );

    $wp_customize->add_control(
        'yea_header_join_text',
        array(
            'label'   => __('Join button text', 'yea-theme'),
            'section' => 'yea_header_buttons',
            'type'    => 'text',
        )
    );

    $wp_customize->add_setting(
        'yea_header_join_url',
        array(
            'default'           => '#join',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
        )
    );

    $wp_customize->add_control(
        'yea_header_join_url',
        array(
            'label'   => __('Join button link', 'yea-theme'),
            'section' => 'yea_header_buttons',
            'type'    => 'url',
        )
    );

    $wp_customize->add_section(
        'yea_footer_follow',
        array(
            'title'       => __('YEA Footer Follow', 'yea-theme'),
            'priority'    => 37,
            'description' => __('Only social links with a URL will be displayed in the footer.', 'yea-theme'),
        )
    );

    $wp_customize->add_section(
        'yea_footer_options',
        array(
            'title'       => __('YEA Footer', 'yea-theme'),
            'priority'    => 38,
            'description' => __('Control the footer closing text shown above the footer navigation.', 'yea-theme'),
        )
    );

    $wp_customize->add_setting(
        'yea_footer_text',
        array(
            'default'           => __('Built for community, trust, and visible impact.', 'yea-theme'),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        )
    );

    $wp_customize->add_control(
        'yea_footer_text',
        array(
            'label'   => __('Footer closing text', 'yea-theme'),
            'section' => 'yea_footer_options',
            'type'    => 'text',
        )
    );

    $wp_customize->add_setting(
        'yea_footer_follow_title',
        array(
            'default'           => __('Follow', 'yea-theme'),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        )
    );

    $wp_customize->add_control(
        'yea_footer_follow_title',
        array(
            'label'   => __('Follow title', 'yea-theme'),
            'section' => 'yea_footer_follow',
            'type'    => 'text',
        )
    );

    $wp_customize->add_setting(
        'yea_footer_follow_description',
        array(
            'default'           => __('Use this area for social links, newsletter signup, or donor contact information.', 'yea-theme'),
            'sanitize_callback' => 'sanitize_textarea_field',
            'transport'         => 'refresh',
        )
    );

    $wp_customize->add_control(
        'yea_footer_follow_description',
        array(
            'label'   => __('Follow description', 'yea-theme'),
            'section' => 'yea_footer_follow',
            'type'    => 'textarea',
        )
    );

    foreach (array(
        'instagram' => __('Instagram URL', 'yea-theme'),
        'facebook'  => __('Facebook URL', 'yea-theme'),
        'tiktok'    => __('TikTok URL', 'yea-theme'),
        'linkedin'  => __('LinkedIn URL', 'yea-theme'),
        'youtube'   => __('YouTube URL', 'yea-theme'),
    ) as $network => $label) {
        $wp_customize->add_setting(
            'yea_social_' . $network,
            array(
                'default'           => '',
                'sanitize_callback' => 'esc_url_raw',
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'yea_social_' . $network,
            array(
                'label'   => $label,
                'section' => 'yea_footer_follow',
                'type'    => 'url',
            )
        );
    }
}
add_action('customize_register', 'yea_customize_register');

/**
 * Check whether the Appearance menu already contains a submenu slug.
 *
 * @param string $slug Menu slug.
 * @return bool
 */
function yea_has_themes_submenu($slug) {
    global $submenu;

    if (empty($submenu['themes.php'])) {
        return false;
    }

    foreach ($submenu['themes.php'] as $item) {
        if (isset($item[2]) && $slug === $item[2]) {
            return true;
        }
    }

    return false;
}

/**
 * Ensure both the Customizer and the Theme Editor are visible under Appearance.
 */
function yea_add_theme_management_menus() {
    if (current_theme_supports('block-templates') && !yea_has_themes_submenu('site-editor.php')) {
        add_submenu_page(
            'themes.php',
            __('Theme Editor', 'yea-theme'),
            __('Theme Editor', 'yea-theme'),
            'edit_theme_options',
            'site-editor.php'
        );
    }

    if (!yea_has_themes_submenu('customize.php')) {
        add_submenu_page(
            'themes.php',
            __('Customizer', 'yea-theme'),
            __('Customizer', 'yea-theme'),
            'customize',
            'customize.php'
        );
    }
}
add_action('admin_menu', 'yea_add_theme_management_menus', 999);

/**
 * Build shared theme CSS variables.
 */
function yea_get_theme_css_variables($selector = ':root') {
    $primary_color = get_theme_mod('yea_primary_color', '#358c9d');
    $primary_color = sanitize_hex_color($primary_color) ?: '#358c9d';
    $brand_display = get_theme_mod('yea_show_brand_text', true) ? 'grid' : 'none';
    
    // Calculate darker and lighter variants
    $primary_dark = yea_adjust_brightness($primary_color, -30);
    $primary_light = yea_adjust_brightness($primary_color, 40);

    return sprintf(
        '%1$s { --primary: %2$s; --primary-dark: %3$s; --primary-light: %4$s; --brand-copy-display: %5$s; }',
        $selector,
        esc_attr($primary_color),
        esc_attr($primary_dark),
        esc_attr($primary_light),
        esc_attr($brand_display)
    );
}

/**
 * Output shared theme CSS variables.
 */
function yea_customizer_css() {
    ?>
    <style type="text/css">
        <?php echo yea_get_theme_css_variables(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </style>
    <?php
}
add_action('wp_head', 'yea_customizer_css');

/**
 * Load theme variables into the block editor.
 */
function yea_block_editor_assets() {
    wp_add_inline_style('wp-block-library', yea_get_theme_css_variables('.editor-styles-wrapper'));
}
add_action('enqueue_block_editor_assets', 'yea_block_editor_assets');

/**
 * Helper function to adjust color brightness
 */
function yea_adjust_brightness($hex, $steps) {
    $hex = ltrim($hex, '#');
    
    $r = max(0, min(255, hexdec(substr($hex, 0, 2)) + $steps));
    $g = max(0, min(255, hexdec(substr($hex, 2, 2)) + $steps));
    $b = max(0, min(255, hexdec(substr($hex, 4, 2)) + $steps));
    
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

/**
 * Custom excerpt length
 */
function yea_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'yea_excerpt_length');

/**
 * Custom excerpt more
 */
function yea_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'yea_excerpt_more');

/**
 * Add custom image sizes
 */
function yea_add_image_sizes() {
    add_image_size('yea-hero-lg', 800, 600, true);
    add_image_size('yea-hero-sm', 400, 300, true);
    add_image_size('yea-program', 600, 800, true);
    add_image_size('yea-community', 400, 400, true);
    add_image_size('yea-post-card', 600, 400, true);
}
add_action('after_setup_theme', 'yea_add_image_sizes');

/**
 * Add custom body classes
 */
function yea_body_classes($classes) {
    if (is_front_page()) {
        $classes[] = 'home-page';
    }
    
    if (is_singular()) {
        $classes[] = 'singular';
    }
    
    return $classes;
}
add_filter('body_class', 'yea_body_classes');

/**
 * Register block patterns
 */
function yea_register_block_patterns() {
    if (function_exists('register_block_pattern_category')) {
        register_block_pattern_category('yea', array(
            'label' => __('YEA Components', 'yea-theme'),
        ));

        register_block_pattern_category('yea-pages', array(
            'label' => __('YEA Page Starters', 'yea-theme'),
        ));
    }
}
add_action('init', 'yea_register_block_patterns');

/**
 * Register block styles used by YEA components.
 */
function yea_register_block_styles() {
    if (!function_exists('register_block_style')) {
        return;
    }

    register_block_style(
        'core/details',
        array(
            'name'  => 'yea-accordion',
            'label' => __('YEA Accordion', 'yea-theme'),
        )
    );

    register_block_style(
        'core/quote',
        array(
            'name'  => 'yea-testimonial',
            'label' => __('YEA Testimonial', 'yea-theme'),
        )
    );
}
add_action('init', 'yea_register_block_styles');

/**
 * Get registered pattern content by slug.
 *
 * @param string $slug Pattern slug.
 * @return string
 */
function yea_get_pattern_content($slug) {
    if (!class_exists('WP_Block_Patterns_Registry')) {
        return '';
    }

    $pattern = WP_Block_Patterns_Registry::get_instance()->get_registered($slug);

    return is_array($pattern) && !empty($pattern['content']) ? $pattern['content'] : '';
}

/**
 * Seed new pages with a starter pattern so the editor is never empty.
 *
 * @param string  $content Default content.
 * @param WP_Post $post    Current post object.
 * @return string
 */
function yea_default_page_content($content, $post) {
    if (!$post instanceof WP_Post || 'page' !== $post->post_type || '' !== trim($content)) {
        return $content;
    }

    $starter = yea_get_pattern_content('yea-theme/page-starter');

    return $starter ?: $content;
}
add_filter('default_content', 'yea_default_page_content', 10, 2);

/**
 * Move starter layouts into real page content so block templates stay editable.
 */
function yea_maybe_seed_editable_page_content() {
    if (!is_admin() || !current_user_can('edit_pages') || wp_doing_ajax()) {
        return;
    }

    $targets = array();

    $front_page_id = (int) get_option('page_on_front');
    if ($front_page_id > 0) {
        $targets[$front_page_id] = 'yea-theme/homepage-full';
    }

    $special_pages = get_posts(
        array(
            'post_type'      => 'page',
            'post_status'    => array('publish', 'draft', 'pending', 'private', 'future'),
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => '_wp_page_template',
                    'value'   => array('page-builder', 'page-builder.html', 'page-contact', 'page-contact.html'),
                    'compare' => 'IN',
                ),
            ),
        )
    );

    foreach ($special_pages as $page_id) {
        $template_slug = (string) get_post_meta($page_id, '_wp_page_template', true);

        if (isset($targets[$page_id])) {
            continue;
        }

        if (in_array($template_slug, array('page-builder', 'page-builder.html'), true)) {
            $targets[$page_id] = 'yea-theme/page-starter';
            continue;
        }

        if (in_array($template_slug, array('page-contact', 'page-contact.html'), true)) {
            $targets[$page_id] = 'yea-theme/contact-details';
        }
    }

    foreach ($targets as $page_id => $pattern_slug) {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || 'page' !== $page->post_type) {
            continue;
        }

        $existing_content = trim((string) $page->post_content);
        $page_starter     = trim(yea_get_pattern_content('yea-theme/page-starter'));

        if ('' !== $existing_content) {
            $can_replace_generic_starter = '' !== $page_starter
                && $existing_content === $page_starter
                && 'yea-theme/page-starter' !== $pattern_slug;

            if (!$can_replace_generic_starter) {
                continue;
            }
        }

        $pattern_content = yea_get_pattern_content($pattern_slug);
        if ('' === $pattern_content) {
            continue;
        }

        wp_update_post(
            array(
                'ID'           => $page_id,
                'post_content' => $pattern_content,
            )
        );
    }
}
add_action('admin_init', 'yea_maybe_seed_editable_page_content');

/**
 * Build breadcrumb items for the current request.
 */
function yea_get_breadcrumb_items() {
    $items = array(
        array(
            'label' => __('Home', 'yea-theme'),
            'url'   => home_url('/'),
        ),
    );

    if (is_front_page()) {
        return $items;
    }

    if (is_home()) {
        $posts_page_id = (int) get_option('page_for_posts');

        if ($posts_page_id) {
            $items[] = array(
                'label' => get_the_title($posts_page_id),
                'url'   => '',
            );
        } else {
            $items[] = array(
                'label' => __('Blog', 'yea-theme'),
                'url'   => '',
            );
        }

        return $items;
    }

    if (is_singular('post')) {
        $posts_page_id = (int) get_option('page_for_posts');

        if ($posts_page_id) {
            $items[] = array(
                'label' => get_the_title($posts_page_id),
                'url'   => get_permalink($posts_page_id),
            );
        }

        $categories = get_the_category();
        if (!empty($categories)) {
            $primary_category = $categories[0];
            $ancestors        = array_reverse(get_ancestors($primary_category->term_id, 'category'));

            foreach ($ancestors as $ancestor_id) {
                $ancestor = get_term($ancestor_id, 'category');
                if ($ancestor && !is_wp_error($ancestor)) {
                    $items[] = array(
                        'label' => $ancestor->name,
                        'url'   => get_term_link($ancestor),
                    );
                }
            }

            $items[] = array(
                'label' => $primary_category->name,
                'url'   => get_term_link($primary_category),
            );
        }

        $items[] = array(
            'label' => get_the_title(),
            'url'   => '',
        );

        return $items;
    }

    if (is_page()) {
        global $post;

        if ($post instanceof WP_Post) {
            $ancestors = array_reverse(get_post_ancestors($post));

            foreach ($ancestors as $ancestor_id) {
                $items[] = array(
                    'label' => get_the_title($ancestor_id),
                    'url'   => get_permalink($ancestor_id),
                );
            }

            $items[] = array(
                'label' => get_the_title($post),
                'url'   => '',
            );
        }

        return $items;
    }

    if (is_category() || is_tag() || is_tax()) {
        $posts_page_id = (int) get_option('page_for_posts');
        if ($posts_page_id) {
            $items[] = array(
                'label' => get_the_title($posts_page_id),
                'url'   => get_permalink($posts_page_id),
            );
        }

        $term = get_queried_object();
        if ($term instanceof WP_Term) {
            $ancestors = array_reverse(get_ancestors($term->term_id, $term->taxonomy));

            foreach ($ancestors as $ancestor_id) {
                $ancestor = get_term($ancestor_id, $term->taxonomy);
                if ($ancestor && !is_wp_error($ancestor)) {
                    $items[] = array(
                        'label' => $ancestor->name,
                        'url'   => get_term_link($ancestor),
                    );
                }
            }

            $items[] = array(
                'label' => single_term_title('', false),
                'url'   => '',
            );
        }

        return $items;
    }

    if (is_search()) {
        $items[] = array(
            'label' => __('Search results', 'yea-theme'),
            'url'   => '',
        );

        return $items;
    }

    if (is_post_type_archive()) {
        $post_type_name = get_query_var('post_type');
        if (is_array($post_type_name)) {
            $post_type_name = reset($post_type_name);
        }

        $post_type = $post_type_name ? get_post_type_object($post_type_name) : null;

        if ($post_type) {
            $items[] = array(
                'label' => $post_type->labels->name,
                'url'   => '',
            );
        }

        return $items;
    }

    if (is_archive()) {
        $items[] = array(
            'label' => wp_strip_all_tags(get_the_archive_title()),
            'url'   => '',
        );

        return $items;
    }

    if (is_404()) {
        $items[] = array(
            'label' => __('Not found', 'yea-theme'),
            'url'   => '',
        );
    }

    return $items;
}

/**
 * Render breadcrumbs for templates and content.
 */
function yea_breadcrumbs_shortcode() {
    $items = array();

    // Keep the more specific destination when adjacent levels share a label
    // (for example, a Blog posts page followed by a Blog category).
    foreach (yea_get_breadcrumb_items() as $item) {
        $last_index = count($items) - 1;
        if ($last_index >= 0 && 0 === strcasecmp(
            trim(wp_strip_all_tags($items[$last_index]['label'])),
            trim(wp_strip_all_tags($item['label']))
        )) {
            $items[$last_index] = $item;
        } else {
            $items[] = $item;
        }
    }

    if (count($items) < 2) {
        return '';
    }

    $output = '<nav class="yea-breadcrumbs" aria-label="' . esc_attr__('Breadcrumb', 'yea-theme') . '"><ol class="breadcrumb-trail">';

    foreach ($items as $index => $item) {
        $is_last = ($index === count($items) - 1);
        $output .= '<li class="breadcrumb-item' . ($is_last ? ' is-current' : '') . '">';

        if (!$is_last && !empty($item['url'])) {
            $output .= '<a href="' . esc_url($item['url']) . '">' . esc_html($item['label']) . '</a>';
        } else {
            $output .= '<span>' . esc_html($item['label']) . '</span>';
        }

        $output .= '</li>';
    }

    $output .= '</ol></nav>';

    return $output;
}
add_shortcode('yea_breadcrumbs', 'yea_breadcrumbs_shortcode');

/** Resolve header fragments against the homepage; preserve configured URLs. */
function yea_resolve_header_url($url) {
    $url = trim((string) $url);
    return 0 === strpos($url, '#') ? home_url('/' . $url) : $url;
}

/**
 * Get header CTA button markup for desktop and mobile contexts.
 *
 * @param string $context Render context.
 * @return string
 */
function yea_get_header_ctas_markup($context = 'desktop') {
    $buttons = array(
        array(
            'text'  => trim((string) get_theme_mod('yea_header_join_text', __('Join YEA', 'yea-theme'))),
            'url'   => trim((string) get_theme_mod('yea_header_join_url', '#join')),
            'class' => '',
        ),
    );

    $items = '';

    foreach ($buttons as $button) {
        if ('' === $button['text'] || '' === $button['url']) {
            continue;
        }

        $button_class = 'wp-block-button';
        if ('' !== $button['class']) {
            $button_class .= ' ' . $button['class'];
        }

        $items .= sprintf(
            '<div class="%1$s"><a class="wp-block-button__link wp-element-button" href="%2$s">%3$s</a></div>',
            esc_attr($button_class),
            esc_url(yea_resolve_header_url($button['url'])),
            esc_html($button['text'])
        );
    }

    if ('' === $items) {
        return '';
    }

    $context_class = 'desktop';
    if ('mobile' === $context) {
        $context_class = 'mobile';
    }

    return sprintf(
        '<div class="wp-block-buttons header-actions header-actions--%1$s">%2$s</div>',
        esc_attr($context_class),
        $items
    );
}

/**
 * Render header CTA buttons from Customizer settings.
 *
 * @param array<string, string> $atts Shortcode attributes.
 * @return string
 */
function yea_header_ctas_shortcode($atts = array()) {
    $atts = shortcode_atts(
        array(
            'context' => 'desktop',
        ),
        $atts,
        'yea_header_ctas'
    );

    return yea_get_header_ctas_markup((string) $atts['context']);
}
add_shortcode('yea_header_ctas', 'yea_header_ctas_shortcode');

/**
 * Get configured footer social networks.
 *
 * @return array<string, array<string, string>>
 */
function yea_get_footer_social_networks() {
    return array(
        'instagram' => array(
            'label' => __('Instagram', 'yea-theme'),
            'mark'  => 'IG',
        ),
        'facebook' => array(
            'label' => __('Facebook', 'yea-theme'),
            'mark'  => 'FB',
        ),
        'tiktok' => array(
            'label' => __('TikTok', 'yea-theme'),
            'mark'  => 'TT',
        ),
        'linkedin' => array(
            'label' => __('LinkedIn', 'yea-theme'),
            'mark'  => 'IN',
        ),
        'youtube' => array(
            'label' => __('YouTube', 'yea-theme'),
            'mark'  => 'YT',
        ),
    );
}

/**
 * Render conditional footer follow links from Customizer settings.
 *
 * @return string
 */
function yea_footer_follow_shortcode() {
    $title       = trim((string) get_theme_mod('yea_footer_follow_title', __('Follow', 'yea-theme')));
    $description = trim((string) get_theme_mod('yea_footer_follow_description', __('Use this area for social links, newsletter signup, or donor contact information.', 'yea-theme')));
    $socials     = '';

    foreach (yea_get_footer_social_networks() as $network => $config) {
        $url = trim((string) get_theme_mod('yea_social_' . $network, ''));

        if ('' === $url) {
            continue;
        }

        $socials .= sprintf(
            '<li><a class="footer-social-link footer-social-link--%1$s" href="%2$s" target="_blank" rel="noopener noreferrer"><span class="footer-social-mark">%3$s</span><span class="footer-social-label">%4$s</span></a></li>',
            esc_attr($network),
            esc_url($url),
            esc_html($config['mark']),
            esc_html($config['label'])
        );
    }

    $output = '<div class="wp-block-group footer-widget footer-follow">';

    if ('' !== $title) {
        $output .= '<h4 class="wp-block-heading">' . esc_html($title) . '</h4>';
    }

    if ('' !== $description) {
        $output .= '<p>' . esc_html($description) . '</p>';
    }

    if ('' !== $socials) {
        $output .= '<ul class="footer-socials">' . $socials . '</ul>';
    }

    $output .= '</div>';

    return $output;
}
add_shortcode('yea_footer_follow', 'yea_footer_follow_shortcode');

/**
 * Determine whether a Query Loop belongs to the YEA blog feed component.
 */
function yea_is_limited_blog_query_block($attrs, $query_vars = array()) {
    if (!is_array($attrs)) {
        return false;
    }

    $class_name = isset($attrs['className']) ? (string) $attrs['className'] : '';
    $query = isset($attrs['query']) && is_array($attrs['query']) ? $attrs['query'] : array();
    $post_type = isset($query['postType']) ? $query['postType'] : 'post';

    if (!empty($query_vars['post_type'])) {
        $post_type = $query_vars['post_type'];
    }

    $is_post_query = 'post' === $post_type || (is_array($post_type) && in_array('post', $post_type, true));
    $is_legacy_homepage_blog_feed = is_front_page() && false !== strpos($class_name, 'archive-query');
    $is_yea_blog_query = false !== strpos($class_name, 'yea-blog-feed-query')
        || false !== strpos($class_name, 'yea-blog-page-query')
        || $is_legacy_homepage_blog_feed;

    if (!$is_post_query || !$is_yea_blog_query) {
        return false;
    }

    if (!empty($query['inherit'])) {
        return false;
    }

    return true;
}

/**
 * Get the desired post count for the YEA blog feed.
 *
 * The component stays dynamic: Gutenberg controls the count, while `6` remains
 * the safe default when no explicit perPage value is stored yet.
 */
function yea_get_blog_query_posts_per_page($attrs, $query_vars = array()) {
    $requested_per_page = 0;

    if (is_array($attrs) && isset($attrs['query']['perPage'])) {
        $requested_per_page = absint($attrs['query']['perPage']);
    } elseif (!empty($query_vars['posts_per_page'])) {
        $requested_per_page = absint($query_vars['posts_per_page']);
    }

    return $requested_per_page > 0 ? $requested_per_page : 6;
}

/**
 * Keep YEA blog query loops aligned with the saved Gutenberg post count.
 */
function yea_limit_blog_query_loops($parsed_block, $source_block, $parent_block) {
    if (!is_array($parsed_block) || empty($parsed_block['blockName']) || 'core/query' !== $parsed_block['blockName']) {
        return $parsed_block;
    }

    if (empty($parsed_block['attrs']['query']) || !is_array($parsed_block['attrs']['query'])) {
        return $parsed_block;
    }

    if (!yea_is_limited_blog_query_block($parsed_block['attrs'])) {
        return $parsed_block;
    }

    $parsed_block['attrs']['query']['perPage'] = yea_get_blog_query_posts_per_page($parsed_block['attrs']);

    return $parsed_block;
}
add_filter('render_block_data', 'yea_limit_blog_query_loops', 10, 3);

/**
 * Enforce the actual frontend query count for the YEA Blog Feed component.
 *
 * Some saved Query Loop instances can keep older attributes on the live site
 * even when the editor preview reflects the intended count. This filter makes
 * the real posts_per_page value match the saved Gutenberg setting.
 */
function yea_limit_blog_query_loop_vars($query_vars, $block, $page) {
    $parsed_block = array();

    if (is_object($block) && isset($block->parsed_block) && is_array($block->parsed_block)) {
        $parsed_block = $block->parsed_block;
    }

    $attrs = isset($parsed_block['attrs']) && is_array($parsed_block['attrs']) ? $parsed_block['attrs'] : array();
    if (!yea_is_limited_blog_query_block($attrs, $query_vars)) {
        return $query_vars;
    }

    $query_vars['posts_per_page'] = yea_get_blog_query_posts_per_page($attrs, $query_vars);
    $query_vars['nopaging'] = false;

    if (isset($query_vars['posts_per_archive_page'])) {
        $query_vars['posts_per_archive_page'] = $query_vars['posts_per_page'];
    }

    return $query_vars;
}
add_filter('query_loop_block_query_vars', 'yea_limit_blog_query_loop_vars', 20, 3);

/**
 * As a final safeguard, trim rendered YEA blog feed markup to the saved count.
 *
 * This handles installs where the Query Loop frontend render path still
 * outputs older saved results even after query vars are filtered.
 */
function yea_limit_rendered_blog_query_block($block_content, $block) {
    if (!is_string($block_content) || '' === $block_content) {
        return $block_content;
    }

    if (!is_array($block) || empty($block['blockName']) || 'core/query' !== $block['blockName']) {
        return $block_content;
    }

    $attrs = isset($block['attrs']) && is_array($block['attrs']) ? $block['attrs'] : array();

    if (!yea_is_limited_blog_query_block($attrs)) {
        return $block_content;
    }

    if (false === strpos($block_content, 'wp-block-post-template') || !class_exists('DOMDocument')) {
        return $block_content;
    }

    $limit = yea_get_blog_query_posts_per_page($attrs);

    $dom = new DOMDocument();
    $previous_state = libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML(
        '<?xml encoding="utf-8" ?><div id="yea-query-root">' . $block_content . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous_state);

    if (!$loaded) {
        return $block_content;
    }

    $xpath = new DOMXPath($dom);
    $list_nodes = $xpath->query('//ul[contains(concat(" ", normalize-space(@class), " "), " wp-block-post-template ")]');

    if (!$list_nodes || 0 === $list_nodes->length) {
        return $block_content;
    }

    foreach ($list_nodes as $list_node) {
        $post_items = array();

        foreach ($list_node->childNodes as $child_node) {
            if (XML_ELEMENT_NODE === $child_node->nodeType && 'li' === strtolower($child_node->nodeName)) {
                $post_items[] = $child_node;
            }
        }

        if (count($post_items) <= $limit) {
            continue;
        }

        foreach (array_slice($post_items, $limit) as $extra_item) {
            $list_node->removeChild($extra_item);
        }
    }

    $root = $dom->getElementById('yea-query-root');

    if (!$root) {
        return $block_content;
    }

    $trimmed_html = '';

    foreach ($root->childNodes as $child) {
        $trimmed_html .= $dom->saveHTML($child);
    }

    return '' !== $trimmed_html ? $trimmed_html : $block_content;
}
add_filter('render_block', 'yea_limit_rendered_blog_query_block', 20, 2);

/**
 * Sync saved header template-part CTA markup with current Customizer settings.
 */
function yea_remove_header_donor_button($block_content, $block) {
    if (!is_string($block_content) || '' === $block_content) {
        return $block_content;
    }

    if (!is_array($block) || empty($block['blockName']) || 'core/template-part' !== $block['blockName']) {
        return $block_content;
    }

    $attrs = isset($block['attrs']) && is_array($block['attrs']) ? $block['attrs'] : array();
    if (empty($attrs['slug']) || 'header' !== $attrs['slug']) {
        return $block_content;
    }

    $cta_markup = yea_get_header_ctas_markup();

    if (false !== strpos($block_content, '[yea_header_ctas]')) {
        $block_content = preg_replace('/<p>\s*\[yea_header_ctas\]\s*<\/p>/i', $cta_markup, $block_content);
        $block_content = str_replace('[yea_header_ctas]', $cta_markup, $block_content);
    }

    if (false === strpos($block_content, 'header-actions') || !class_exists('DOMDocument')) {
        return $block_content;
    }

    $donor_text = trim((string) get_theme_mod('yea_header_donors_text', __('For Donors', 'yea-theme')));
    $donor_url = trim((string) get_theme_mod('yea_header_donors_url', '#transparency'));
    $join_text = trim((string) get_theme_mod('yea_header_join_text', __('Join YEA', 'yea-theme')));
    $join_url = yea_resolve_header_url(get_theme_mod('yea_header_join_url', '#join'));

    $dom = new DOMDocument();
    $previous_state = libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML(
        '<?xml encoding="utf-8" ?><div id="yea-header-root">' . $block_content . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous_state);

    if (!$loaded) {
        return $block_content;
    }

    $xpath = new DOMXPath($dom);
    $button_links = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " header-actions ")]//*[contains(concat(" ", normalize-space(@class), " "), " wp-block-button__link ")]');

    if ($button_links) {
        foreach ($button_links as $button_link) {
            $link_text = trim((string) $button_link->textContent);
            $href = trim((string) $button_link->getAttribute('href'));
            $matches_donor = ('' !== $donor_text && $link_text === $donor_text) || ('' !== $donor_url && $href === $donor_url);

            if (!$matches_donor) {
                continue;
            }

            $button_node = $button_link->parentNode;

            while ($button_node && XML_ELEMENT_NODE === $button_node->nodeType) {
                $class_attribute = '';

                if ($button_node->attributes instanceof DOMNamedNodeMap) {
                    $class_node = $button_node->attributes->getNamedItem('class');
                    if ($class_node) {
                        $class_attribute = (string) $class_node->nodeValue;
                    }
                }

                $class_name = ' ' . trim($class_attribute) . ' ';

                if (false !== strpos($class_name, ' wp-block-button ')) {
                    break;
                }

                $button_node = $button_node->parentNode;
            }

            if ($button_node && $button_node->parentNode) {
                $button_node->parentNode->removeChild($button_node);
            }
        }
    }

    $action_groups = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " header-actions ")]');

    if ($action_groups) {
        foreach ($action_groups as $action_group) {
            $button_nodes = array();
            $button_query = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " wp-block-button ")]', $action_group);

            if ($button_query) {
                foreach ($button_query as $button_node) {
                    $button_nodes[] = $button_node;
                }
            }

            if ('' === $join_text || '' === $join_url) {
                foreach ($button_nodes as $button_node) {
                    if ($button_node->parentNode) {
                        $button_node->parentNode->removeChild($button_node);
                    }
                }
                continue;
            }

            $primary_button = null;

            if (!empty($button_nodes)) {
                $primary_button = array_shift($button_nodes);
            }

            foreach ($button_nodes as $extra_button) {
                if ($extra_button->parentNode) {
                    $extra_button->parentNode->removeChild($extra_button);
                }
            }

            if (!$primary_button) {
                $primary_button = $dom->createElement('div');
                $primary_button->setAttribute('class', 'wp-block-button');
                $action_group->appendChild($primary_button);
            } else {
                $primary_button->setAttribute('class', 'wp-block-button');
            }

            $button_link = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " wp-block-button__link ")]', $primary_button)->item(0);

            if (!$button_link) {
                $button_link = $dom->createElement('a');
                $button_link->setAttribute('class', 'wp-block-button__link wp-element-button');
                $primary_button->appendChild($button_link);
            }

            while ($button_link->firstChild) {
                $button_link->removeChild($button_link->firstChild);
            }

            $button_link->setAttribute('href', $join_url);
            $button_link->appendChild($dom->createTextNode($join_text));
        }
    }

    $root = $dom->getElementById('yea-header-root');

    if (!$root) {
        return $block_content;
    }

    $clean_html = '';

    foreach ($root->childNodes as $child) {
        $clean_html .= $dom->saveHTML($child);
    }

    return '' !== $clean_html ? $clean_html : $block_content;
}
add_filter('render_block', 'yea_remove_header_donor_button', 30, 2);

/**
 * Sync footer closing text with the Customizer value.
 */
function yea_sync_footer_text($block_content, $block) {
    if (!is_string($block_content) || '' === $block_content) {
        return $block_content;
    }

    if (!is_array($block) || empty($block['blockName']) || 'core/template-part' !== $block['blockName']) {
        return $block_content;
    }

    $attrs = isset($block['attrs']) && is_array($block['attrs']) ? $block['attrs'] : array();
    if (empty($attrs['slug']) || 'footer' !== $attrs['slug']) {
        return $block_content;
    }

    if (false === strpos($block_content, 'footer-inner') || !class_exists('DOMDocument')) {
        return $block_content;
    }

    $footer_text = trim((string) get_theme_mod('yea_footer_text', __('Built for community, trust, and visible impact.', 'yea-theme')));

    $dom = new DOMDocument();
    $previous_state = libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML(
        '<?xml encoding="utf-8" ?><div id="yea-footer-root">' . $block_content . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous_state);

    if (!$loaded) {
        return $block_content;
    }

    $xpath = new DOMXPath($dom);
    $footer_inner = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " footer-inner ")]')->item(0);

    if (!$footer_inner) {
        return $block_content;
    }

    $footer_paragraph = $xpath->query('.//p[1]', $footer_inner)->item(0);

    if (!$footer_paragraph) {
        $footer_paragraph = $dom->createElement('p');
        $footer_inner->insertBefore($footer_paragraph, $footer_inner->firstChild);
    }

    while ($footer_paragraph->firstChild) {
        $footer_paragraph->removeChild($footer_paragraph->firstChild);
    }

    $footer_paragraph->appendChild($dom->createTextNode($footer_text));

    $root = $dom->getElementById('yea-footer-root');

    if (!$root) {
        return $block_content;
    }

    $clean_html = '';

    foreach ($root->childNodes as $child) {
        $clean_html .= $dom->saveHTML($child);
    }

    return '' !== $clean_html ? $clean_html : $block_content;
}
add_filter('render_block', 'yea_sync_footer_text', 40, 2);

/**
 * Admin notice for required plugins (optional)
 */
function yea_admin_notice() {
    // Add any admin notices here if needed
}
add_action('admin_notices', 'yea_admin_notice');


/**
 * Remove old GeneratePress assets that conflict with this block theme.
 */
function yea_dequeue_conflicting_theme_assets() {
    $style_handles = array(
        'generate-blog-columns',
        'generate-sticky',
        'generate-offside',
        'gp-premium-icons',
        'generate-navigation-branding',
    );

    foreach ($style_handles as $handle) {
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }

    $script_handles = array(
        'generate-offside',
        'generate-sticky',
    );

    foreach ($script_handles as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
}
add_action('wp_enqueue_scripts', 'yea_dequeue_conflicting_theme_assets', 100);

/**
 * Register a small reset screen for stale Site Editor overrides.
 */
function yea_register_reset_theme_page() {
    add_theme_page(
        __('YEA Reset', 'yea-theme'),
        __('YEA Reset', 'yea-theme'),
        'edit_theme_options',
        'yea-reset',
        'yea_render_reset_theme_page'
    );
}
add_action('admin_menu', 'yea_register_reset_theme_page');

/**
 * Render the reset page.
 */
function yea_render_reset_theme_page() {
    if (!current_user_can('edit_theme_options')) {
        return;
    }

    $reset_count = isset($_GET['yea_reset_count']) ? absint($_GET['yea_reset_count']) : 0;
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Reset YEA Site Editor Overrides', 'yea-theme'); ?></h1>
        <?php if (isset($_GET['yea_reset']) && '1' === $_GET['yea_reset']) : ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    printf(
                        esc_html__('Removed %d saved template override(s). Header, footer, and templates now use the theme defaults again.', 'yea-theme'),
                        $reset_count
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>
        <p><?php esc_html_e('Use this if the site is still showing an old header, old navigation behavior, or stale block templates after updating the theme.', 'yea-theme'); ?></p>
        <p><?php esc_html_e('This removes saved Site Editor template and template-part overrides for the current theme. It does not delete pages, posts, menus, or Customizer settings.', 'yea-theme'); ?></p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('yea_reset_site_editor'); ?>
            <input type="hidden" name="action" value="yea_reset_site_editor" />
            <?php submit_button(__('Reset Saved Theme Overrides', 'yea-theme'), 'delete'); ?>
        </form>
    </div>
    <?php
}

/**
 * Delete saved block-template overrides for the current theme.
 */
function yea_handle_reset_site_editor() {
    if (!current_user_can('edit_theme_options')) {
        wp_die(esc_html__('You are not allowed to do that.', 'yea-theme'));
    }

    check_admin_referer('yea_reset_site_editor');

    $post_ids = get_posts(
        array(
            'post_type'      => array('wp_template', 'wp_template_part'),
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'wp_theme',
                    'field'    => 'slug',
                    'terms'    => get_stylesheet(),
                ),
            ),
        )
    );

    $deleted = 0;

    foreach ($post_ids as $post_id) {
        if (wp_delete_post($post_id, true)) {
            $deleted++;
        }
    }

    $redirect_url = add_query_arg(
        array(
            'page'            => 'yea-reset',
            'yea_reset'       => 1,
            'yea_reset_count' => $deleted,
        ),
        admin_url('themes.php')
    );

    wp_safe_redirect($redirect_url);
    exit;
}
add_action('admin_post_yea_reset_site_editor', 'yea_handle_reset_site_editor');


/**
 * Keep the Gutenberg header navigation in sync with the primary WordPress menu.
 *
 * Site Editor can save a separate wp_navigation post that otherwise diverges
 * from Appearance > Menus. Only the header's "nav" block is synchronized;
 * footer and other Navigation blocks remain independently editable.
 */
function yea_sync_header_navigation_from_primary_menu( $parsed_block ) {
    if ( ! is_array( $parsed_block ) || ( $parsed_block['blockName'] ?? '' ) !== 'core/navigation' ) {
        return $parsed_block;
    }

    $classes = $parsed_block['attrs']['className'] ?? '';
    if ( ! preg_match( '/(?:^|\s)nav(?:\s|$)/', $classes ) ) {
        return $parsed_block;
    }

    $locations = get_nav_menu_locations();
    $menu_id   = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;
    if ( ! $menu_id ) {
        return $parsed_block;
    }

    $items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'publish' ) );
    if ( empty( $items ) || is_wp_error( $items ) ) {
        return $parsed_block;
    }

    $by_parent = array();
    foreach ( $items as $item ) {
        if ( 'publish' !== $item->post_status ) {
            continue;
        }
        $by_parent[ (int) $item->menu_item_parent ][] = $item;
    }

    // Gutenberg Navigation uses its child blocks for both desktop and mobile.
    $build = static function ( $parent, $depth = 0 ) use ( &$build, $by_parent ) {
        if ( $depth > 5 || empty( $by_parent[ $parent ] ) ) {
            return array();
        }
        $result = array();
        foreach ( $by_parent[ $parent ] as $item ) {
            $children = $build( (int) $item->ID, $depth + 1 );
            $label    = html_entity_decode( wp_strip_all_tags( $item->title ), ENT_QUOTES, get_bloginfo( 'charset' ) ?: 'UTF-8' );
            $url      = esc_url_raw( $item->url );
            if ( '' === $label || '' === $url ) {
                continue;
            }
            $submenu  = ! empty( $children );
            $result[] = array(
                'blockName'    => $submenu ? 'core/navigation-submenu' : 'core/navigation-link',
                'attrs'        => array(
                    'label' => $label,
                    'url'   => $url,
                ),
                'innerBlocks'  => $children,
                'innerHTML'    => '',
                'innerContent' => array_fill( 0, count( $children ), null ),
            );
        }
        return $result;
    };

    $children = $build( 0 );
    if ( ! $children ) {
        return $parsed_block;
    }

    unset( $parsed_block['attrs']['ref'] );
    $parsed_block['innerBlocks']  = $children;
    $parsed_block['innerHTML']    = '';
    $parsed_block['innerContent'] = array_fill( 0, count( $children ), null );
    return $parsed_block;
}
add_filter( 'render_block_data', 'yea_sync_header_navigation_from_primary_menu', 20 );
