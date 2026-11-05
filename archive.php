<?php
/**
 * The template for displaying archive pages
 */

get_header();
?>

<section class="section" style="padding-top: 60px;">
    <div class="container">
        
        <header class="page-header" style="margin-bottom: 50px; text-align: center;">
            <?php
            the_archive_title('<h1 class="page-title" style="font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.1; letter-spacing: -0.05em; margin-bottom: 20px;">', '</h1>');
            the_archive_description('<div class="archive-description" style="color: var(--muted); max-width: 600px; margin: 0 auto;">', '</div>');
            ?>
        </header>
        
        <?php if (have_posts()) : ?>
            
            <div class="posts-grid">
                <?php while (have_posts()) : the_post(); ?>
                    
                    <article id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?>>
                        <?php if (has_post_thumbnail()) : ?>
                            <a href="<?php the_permalink(); ?>" class="post-card-image" style="background-image: url('<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'yea-post-card')); ?>');"></a>
                        <?php else : ?>
                            <a href="<?php the_permalink(); ?>" class="post-card-image" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark));"></a>
                        <?php endif; ?>
                        
                        <div class="post-card-content">
                            <?php 
                            $categories = get_the_category();
                            if (!empty($categories)) : 
                            ?>
                                <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>" class="category">
                                    <?php echo esc_html($categories[0]->name); ?>
                                </a>
                            <?php endif; ?>
                            
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            
                            <div class="excerpt"><?php echo wp_kses_post(get_the_excerpt()); ?></div>
                            
                            <div class="meta">
                                <span><?php echo esc_html(get_the_date()); ?></span>
                                <span><?php echo esc_html(get_the_author()); ?></span>
                            </div>
                        </div>
                    </article>
                    
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <div class="pagination" style="margin-top: 60px;">
                <?php
                echo paginate_links(array(
                    'prev_text' => __('← Previous', 'yea-theme'),
                    'next_text' => __('Next →', 'yea-theme'),
                ));
                ?>
            </div>
            
        <?php else : ?>
            
            <div class="no-posts" style="text-align: center; padding: 80px 0;">
                <p style="color: var(--muted); font-size: 1.2rem;"><?php esc_html_e('No posts found.', 'yea-theme'); ?></p>
            </div>
            
        <?php endif; ?>
        
    </div>
</section>

<?php
get_footer();
