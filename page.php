<?php
/**
 * The template for displaying all pages
 */

get_header();
?>

<section class="section" style="padding-top: 60px;">
    <div class="container">
        <?php while (have_posts()) : the_post(); ?>
            
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                
                <?php if (has_post_thumbnail()) : ?>
                    <div class="page-featured-image" style="margin-bottom: 40px; border-radius: 28px; overflow: hidden;">
                        <?php the_post_thumbnail('large', array('style' => 'width: 100%; height: auto;')); ?>
                    </div>
                <?php endif; ?>
                
                <header class="entry-header" style="margin-bottom: 40px;">
                    <h1 class="entry-title" style="font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.1; letter-spacing: -0.05em;">
                        <?php the_title(); ?>
                    </h1>
                </header>
                
                <div class="entry-content">
                    <?php the_content(); ?>
                    
                    <?php
                    wp_link_pages(array(
                        'before' => '<div class="page-links">' . esc_html__('Pages:', 'yea-theme'),
                        'after'  => '</div>',
                    ));
                    ?>
                </div>
                
                <?php if (comments_open() || get_comments_number()) : ?>
                    <div style="margin-top: 60px;">
                        <?php comments_template(); ?>
                    </div>
                <?php endif; ?>
                
            </article>
            
        <?php endwhile; ?>
    </div>
</section>

<?php
get_footer();
