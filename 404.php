<?php
/**
 * The template for displaying 404 pages (not found)
 */

get_header();
?>

<section class="section" style="padding-top: 100px; padding-bottom: 150px; text-align: center;">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto;">
            <h1 style="font-size: clamp(4rem, 10vw, 8rem); line-height: 1; letter-spacing: -0.05em; color: var(--primary); margin-bottom: 20px;">
                404
            </h1>
            
            <h2 style="font-size: clamp(1.5rem, 3vw, 2.5rem); margin-bottom: 20px;">
                <?php esc_html_e('Page Not Found', 'yea-theme'); ?>
            </h2>
            
            <p style="color: var(--muted); font-size: 1.1rem; margin-bottom: 40px;">
                <?php esc_html_e('The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.', 'yea-theme'); ?>
            </p>
            
            <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">
                    <?php esc_html_e('Go Home', 'yea-theme'); ?>
                </a>
                
                <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="btn btn-secondary">
                    <?php esc_html_e('View Blog', 'yea-theme'); ?>
                </a>
            </div>
            
            <!-- Search Form -->
            <div style="margin-top: 60px; padding: 40px; background: var(--bg); border-radius: 28px;">
                <h3 style="margin-bottom: 20px;"><?php esc_html_e('Search for what you need', 'yea-theme'); ?></h3>
                <?php get_search_form(); ?>
            </div>
        </div>
    </div>
</section>

<?php
get_footer();
