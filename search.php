<?php
/**
 * The template for displaying search results pages
 */

get_header();
?>

<section class="section" style="padding-top: 60px;">
    <div class="container">
        
        <header class="page-header" style="margin-bottom: 50px;">
            <h1 class="page-title" style="font-size: clamp(2rem, 4vw, 3.5rem); line-height: 1.1; letter-spacing: -0.05em; margin-bottom: 20px;">
                <?php
                printf(
                    esc_html__('Search Results for: %s', 'yea-theme'),
                    '<span style="color: var(--primary);">' . get_search_query() . '</span>'
                );
                ?>
            </h1>
            
            <p style="color: var(--muted);">
                <?php
                printf(
                    esc_html(_n('Found %s result', 'Found %s results', (int) $wp_query->found_posts, 'yea-theme')),
                    number_format_i18n($wp_query->found_posts)
                );
                ?>
            </p>
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
                            $post_type = get_post_type_object(get_post_type());
                            if ($post_type) : 
                            ?>
                                <span class="category"><?php echo esc_html($post_type->label); ?></span>
                            <?php endif; ?>
                            
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            
                            <div class="excerpt">
                                <?php 
                                $excerpt = get_the_excerpt();
                                if ($excerpt) {
                                    echo wp_kses_post($excerpt);
                                } else {
                                    echo wp_kses_post(wp_trim_words(get_the_content(), 20));
                                }
                                ?>
                            </div>
                            
                            <div class="meta">
                                <span><?php echo esc_html(get_the_date()); ?></span>
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
            
            <div class="no-results" style="text-align: center; padding: 80px 0;">
                <p style="color: var(--muted); font-size: 1.2rem; margin-bottom: 30px;">
                    <?php esc_html_e('No results found. Try a different search term.', 'yea-theme'); ?>
                </p>
                
                <div style="max-width: 500px; margin: 0 auto; padding: 40px; background: var(--bg); border-radius: 28px;">
                    <?php get_search_form(); ?>
                </div>
            </div>
            
        <?php endif; ?>
        
    </div>
</section>

<?php
get_footer();
