<?php
/**
 * The template for displaying the footer
 */
?>
</main><!-- #main-content -->

<footer id="about" class="site-footer">
    <div class="container">
        <!-- Footer Widgets -->
        <?php if (is_active_sidebar('footer-1') || is_active_sidebar('footer-2') || is_active_sidebar('footer-3') || is_active_sidebar('footer-4')) : ?>
        <div class="footer-widgets">
            <?php if (is_active_sidebar('footer-1')) : ?>
                <div class="footer-widget-area">
                    <?php dynamic_sidebar('footer-1'); ?>
                </div>
            <?php endif; ?>
            
            <?php if (is_active_sidebar('footer-2')) : ?>
                <div class="footer-widget-area">
                    <?php dynamic_sidebar('footer-2'); ?>
                </div>
            <?php endif; ?>
            
            <?php if (is_active_sidebar('footer-3')) : ?>
                <div class="footer-widget-area">
                    <?php dynamic_sidebar('footer-3'); ?>
                </div>
            <?php endif; ?>
            
            <?php if (is_active_sidebar('footer-4')) : ?>
                <div class="footer-widget-area">
                    <?php dynamic_sidebar('footer-4'); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Footer Bottom -->
        <div class="footer-inner">
            <div>
                <?php 
                printf(
                    esc_html__('© %1$s %2$s — %3$s', 'yea-theme'),
                    esc_html(date('Y')),
                    esc_html(get_bloginfo('name')),
                    esc_html(get_bloginfo('description'))
                ); 
                ?>
            </div>
            
            <div>
                <?php 
                $footer_text = get_theme_mod('yea_footer_text', __('Built for community, trust, and visible impact.', 'yea-theme'));
                echo esc_html($footer_text);
                ?>
            </div>
        </div>
        
        <!-- Footer Navigation (optional) -->
        <?php if (has_nav_menu('footer')) : ?>
        <div class="footer-nav" style="text-align: center; padding: 20px 0;">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'footer',
                'menu_class'     => 'footer-menu',
                'container'      => false,
                'depth'          => 1,
            ));
            ?>
        </div>
        <?php endif; ?>
    </div>
</footer>

<?php wp_footer(); ?>

</body>
</html>
