<?php
/**
 * The template for displaying all single posts
 */

get_header();
?>

<section class="section" style="padding-top: 60px;">
    <div class="container">
        <?php while (have_posts()) : the_post(); ?>
            
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                
                <?php if (has_post_thumbnail()) : ?>
                    <div class="post-featured-image" style="margin-bottom: 40px; border-radius: 28px; overflow: hidden;">
                        <?php the_post_thumbnail('large', array('style' => 'width: 100%; height: auto;')); ?>
                    </div>
                <?php endif; ?>
                
                <header class="entry-header" style="margin-bottom: 40px;">
                    <?php 
                    $categories = get_the_category();
                    if (!empty($categories)) : 
                    ?>
                        <div style="margin-bottom: 16px;">
                            <span class="category" style="display: inline-block; background: var(--soft); color: var(--primary-dark); padding: 8px 16px; border-radius: 999px; font-size: 0.85rem; font-weight: 700;">
                                <?php echo esc_html($categories[0]->name); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <h1 class="entry-title" style="font-size: clamp(2rem, 4vw, 3.5rem); line-height: 1.1; letter-spacing: -0.05em; margin-bottom: 20px;">
                        <?php the_title(); ?>
                    </h1>
                    
                    <div class="entry-meta" style="color: var(--muted); font-weight: 600; display: flex; gap: 24px; flex-wrap: wrap;">
                        <span><?php echo esc_html(get_the_date()); ?></span>
                        <span><?php echo esc_html(get_the_author()); ?></span>
                        <?php if (comments_open()) : ?>
                            <span><?php comments_number('0 comments', '1 comment', '% comments'); ?></span>
                        <?php endif; ?>
                    </div>
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
                
                <footer class="entry-footer" style="margin-top: 60px; padding-top: 30px; border-top: 1px solid var(--line);">
                    <?php 
                    $tags = get_the_tags();
                    if ($tags) : 
                    ?>
                        <div class="tags" style="margin-bottom: 20px;">
                            <strong style="display: block; margin-bottom: 12px;"><?php esc_html_e('Tags:', 'yea-theme'); ?></strong>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <?php foreach ($tags as $tag) : ?>
                                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" style="display: inline-block; background: var(--bg); padding: 8px 16px; border-radius: 999px; font-size: 0.85rem; font-weight: 600; transition: all 0.3s;">
                                        <?php echo esc_html($tag->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Post Navigation -->
                    <nav class="post-navigation" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 40px;">
                        <?php
                        $prev_post = get_previous_post();
                        $next_post = get_next_post();
                        ?>
                        
                        <?php if ($prev_post) : ?>
                            <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>" style="background: var(--bg); padding: 24px; border-radius: 20px; transition: all 0.3s;">
                                <span style="display: block; color: var(--muted); font-size: 0.85rem; margin-bottom: 8px;"><?php esc_html_e('← Previous Post', 'yea-theme'); ?></span>
                                <strong style="color: var(--ink);"><?php echo esc_html(get_the_title($prev_post->ID)); ?></strong>
                            </a>
                        <?php else : ?>
                            <div></div>
                        <?php endif; ?>
                        
                        <?php if ($next_post) : ?>
                            <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>" style="background: var(--bg); padding: 24px; border-radius: 20px; text-align: right; transition: all 0.3s;">
                                <span style="display: block; color: var(--muted); font-size: 0.85rem; margin-bottom: 8px;"><?php esc_html_e('Next Post →', 'yea-theme'); ?></span>
                                <strong style="color: var(--ink);"><?php echo esc_html(get_the_title($next_post->ID)); ?></strong>
                            </a>
                        <?php endif; ?>
                    </nav>
                </footer>
                
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
