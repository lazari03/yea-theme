<?php
/**
 * The main template file
 */

get_header();
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-content">
            <?php 
            $hero_eyebrow = get_theme_mod('yea_hero_eyebrow', __('Young entrepreneurs across Albania', 'yea-theme'));
            if ($hero_eyebrow) : 
            ?>
                <div class="eyebrow"><?php echo esc_html($hero_eyebrow); ?></div>
            <?php endif; ?>
            
            <h1>
                <?php echo esc_html(get_theme_mod('yea_hero_title', __('Belong to something ambitious.', 'yea-theme'))); ?>
            </h1>
            
            <p>
                <?php echo esc_html(get_theme_mod('yea_hero_description', __('YEA is where students, founders, schools, and partners come together to build ideas, grow skills, launch initiatives, and create visible impact across Albania.', 'yea-theme'))); ?>
            </p>
            
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?php echo esc_url(get_theme_mod('yea_hero_button1_url', '#join')); ?>">
                    <?php echo esc_html(get_theme_mod('yea_hero_button1_text', __('Join the Community', 'yea-theme'))); ?>
                </a>
                <a class="btn btn-secondary" href="<?php echo esc_url(get_theme_mod('yea_hero_button2_url', '#impact')); ?>">
                    <?php echo esc_html(get_theme_mod('yea_hero_button2_text', __('See Our Impact', 'yea-theme'))); ?>
                </a>
            </div>

            <div class="mini-proof">
                <div><strong class="counter" data-target="700">0</strong><?php esc_html_e('young people engaged', 'yea-theme'); ?></div>
                <div><strong class="counter" data-target="25">0</strong><?php esc_html_e('schools & partners', 'yea-theme'); ?></div>
                <div><strong><?php esc_html_e('EU-backed', 'yea-theme'); ?></strong><?php esc_html_e('programs & visibility', 'yea-theme'); ?></div>
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-badge">
                <strong><?php echo esc_html(get_theme_mod('yea_hero_badge_title', __('Community first', 'yea-theme'))); ?></strong>
                <span><?php echo esc_html(get_theme_mod('yea_hero_badge_text', __('Warm enough for students. Serious enough for institutions.', 'yea-theme'))); ?></span>
            </div>
            
            <?php 
            $hero_image_lg = get_theme_mod('yea_hero_image_lg');
            $hero_image_sm = get_theme_mod('yea_hero_image_sm');
            ?>
            
            <div class="hero-card-lg" <?php if ($hero_image_lg) echo 'style="background-image: linear-gradient(180deg, rgba(16,24,40,0.1), rgba(16,24,40,0.2)), url(' . esc_url(wp_get_attachment_image_url($hero_image_lg, 'yea-hero-lg')) . ');"'; ?>></div>
            
            <div class="hero-card-sm" <?php if ($hero_image_sm) echo 'style="background-image: linear-gradient(180deg, rgba(16,24,40,0.05), rgba(16,24,40,0.2)), url(' . esc_url(wp_get_attachment_image_url($hero_image_sm, 'yea-hero-sm')) . ');"'; ?>></div>
            
            <div class="floating-chip"><?php esc_html_e('Trusted by schools, partners, and donors', 'yea-theme'); ?></div>
        </div>
    </div>
    
    <div class="scroll-indicator" onclick="document.getElementById('impact').scrollIntoView({behavior: 'smooth'})">
        <?php esc_html_e('Scroll to explore', 'yea-theme'); ?>
    </div>
</section>

<!-- Impact Section -->
<section id="impact" class="section soft">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <h2><?php echo esc_html(get_theme_mod('yea_impact_title', __('Visible impact, not vague claims.', 'yea-theme'))); ?></h2>
            </div>
            <p><?php echo esc_html(get_theme_mod('yea_impact_description', __('This section is where donors and institutional partners get confidence fast. Use real numbers here, not generic NGO language.', 'yea-theme'))); ?></p>
        </div>

        <div class="impact-grid">
            <div class="impact-card reveal-scale" style="transition-delay: 0.1s;">
                <strong class="counter" data-target="<?php echo esc_attr(get_theme_mod('yea_stat_1_number', '780')); ?>">0</strong>
                <span><?php echo esc_html(get_theme_mod('yea_stat_1_label', __('students trained and involved in programs', 'yea-theme'))); ?></span>
            </div>
            <div class="impact-card reveal-scale" style="transition-delay: 0.2s;">
                <strong class="counter" data-target="<?php echo esc_attr(get_theme_mod('yea_stat_2_number', '25')); ?>">0</strong>
                <span><?php echo esc_html(get_theme_mod('yea_stat_2_label', __('schools and ecosystem partners engaged', 'yea-theme'))); ?></span>
            </div>
            <div class="impact-card reveal-scale" style="transition-delay: 0.3s;">
                <strong class="counter" data-target="<?php echo esc_attr(get_theme_mod('yea_stat_3_number', '100')); ?>">0</strong>
                <span><?php echo esc_html(get_theme_mod('yea_stat_3_label', __('projects, competitions, and startup ideas activated', 'yea-theme'))); ?></span>
            </div>
            <div class="impact-card reveal-scale" style="transition-delay: 0.4s;">
                <strong><?php echo esc_html(get_theme_mod('yea_stat_4_number', 'EU')); ?></strong>
                <span><?php echo esc_html(get_theme_mod('yea_stat_4_label', __('supported opportunities, exchanges, and visibility', 'yea-theme'))); ?></span>
            </div>
        </div>
    </div>
</section>

<!-- Programs Section -->
<section id="programs" class="section">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <h2><?php echo esc_html(get_theme_mod('yea_programs_title', __('Programs people actually want to join.', 'yea-theme'))); ?></h2>
            </div>
            <p><?php echo esc_html(get_theme_mod('yea_programs_description', __('These cards should drive action. Keep them simple, visual, and outcome-based.', 'yea-theme'))); ?></p>
        </div>

        <div class="program-grid">
            <?php 
            $program_1_image = get_theme_mod('yea_program_1_image');
            $program_2_image = get_theme_mod('yea_program_2_image');
            $program_3_image = get_theme_mod('yea_program_3_image');
            ?>
            
            <article class="program-card reveal-left" style="transition-delay: 0.1s; <?php if ($program_1_image) echo 'background-image: url(' . esc_url(wp_get_attachment_image_url($program_1_image, 'yea-program')) . ');'; ?>">
                <span class="tag"><?php echo esc_html(get_theme_mod('yea_program_1_tag', __('Startup mindset', 'yea-theme'))); ?></span>
                <h3><?php echo esc_html(get_theme_mod('yea_program_1_title', __('Build and launch ideas', 'yea-theme'))); ?></h3>
                <p><?php echo esc_html(get_theme_mod('yea_program_1_description', __('Workshops, entrepreneurial thinking, and real project execution for ambitious young people.', 'yea-theme'))); ?></p>
            </article>
            
            <article class="program-card reveal-scale" style="transition-delay: 0.2s; <?php if ($program_2_image) echo 'background-image: url(' . esc_url(wp_get_attachment_image_url($program_2_image, 'yea-program')) . ');'; ?>">
                <span class="tag"><?php echo esc_html(get_theme_mod('yea_program_2_tag', __('European exposure', 'yea-theme'))); ?></span>
                <h3><?php echo esc_html(get_theme_mod('yea_program_2_title', __('Erasmus+ and exchanges', 'yea-theme'))); ?></h3>
                <p><?php echo esc_html(get_theme_mod('yea_program_2_description', __('Open doors beyond Albania through mobility, collaboration, and cross-border learning.', 'yea-theme'))); ?></p>
            </article>
            
            <article class="program-card reveal-right" style="transition-delay: 0.3s; <?php if ($program_3_image) echo 'background-image: url(' . esc_url(wp_get_attachment_image_url($program_3_image, 'yea-program')) . ');'; ?>">
                <span class="tag"><?php echo esc_html(get_theme_mod('yea_program_3_tag', __('Recognition', 'yea-theme'))); ?></span>
                <h3><?php echo esc_html(get_theme_mod('yea_program_3_title', __('Challenges and awards', 'yea-theme'))); ?></h3>
                <p><?php echo esc_html(get_theme_mod('yea_program_3_description', __('Competitions and public-stage moments that turn participation into momentum.', 'yea-theme'))); ?></p>
            </article>
        </div>
    </div>
</section>

<!-- Community Section -->
<section id="community" class="section soft">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <h2><?php echo esc_html(get_theme_mod('yea_community_title', __('A community that feels real.', 'yea-theme'))); ?></h2>
            </div>
            <p><?php echo esc_html(get_theme_mod('yea_community_description', __('This is the emotional center of the website. Replace stock images with your own event photos.', 'yea-theme'))); ?></p>
        </div>

        <div class="community-grid">
            <div class="mosaic">
                <?php for ($i = 1; $i <= 6; $i++) : 
                    $image_id = get_theme_mod('yea_community_image_' . $i);
                    $style = $image_id ? 'style="background-image: url(' . esc_url(wp_get_attachment_image_url($image_id, 'yea-community')) . ');"' : '';
                ?>
                    <div <?php echo $style; ?>></div>
                <?php endfor; ?>
            </div>

            <div class="community-copy reveal-right">
                <div>
                    <h3 style="margin:0;font-size:2.2rem;line-height:1.02;letter-spacing:-0.05em;">
                        <?php echo esc_html(get_theme_mod('yea_community_copy_title', __('Belonging is the product.', 'yea-theme'))); ?>
                    </h3>
                    <p style="color:var(--muted);margin:16px 0 0;font-size:1.05rem;line-height:1.7;">
                        <?php echo esc_html(get_theme_mod('yea_community_copy_text', __('People join because they want skills, but they stay because they find peers, mentors, visibility, and momentum.', 'yea-theme'))); ?>
                    </p>

                    <div class="community-points">
                        <div class="community-point"><?php echo esc_html(get_theme_mod('yea_community_point_1', __('Students find ambitious people like themselves', 'yea-theme'))); ?></div>
                        <div class="community-point"><?php echo esc_html(get_theme_mod('yea_community_point_2', __('Schools see structured, youth-centered impact', 'yea-theme'))); ?></div>
                        <div class="community-point"><?php echo esc_html(get_theme_mod('yea_community_point_3', __('Partners see credible execution and reach', 'yea-theme'))); ?></div>
                    </div>
                </div>

                <div class="quote-card">
                    <p><?php echo esc_html(get_theme_mod('yea_community_quote', __('"YEA gave me the confidence to stop waiting and actually build something."', 'yea-theme'))); ?></p>
                    <strong><?php echo esc_html(get_theme_mod('yea_community_quote_author', __('— Featured member story', 'yea-theme'))); ?></strong>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Partners Section -->
<section class="section">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <h2><?php echo esc_html(get_theme_mod('yea_partners_title', __('Partners and institutional trust.', 'yea-theme'))); ?></h2>
            </div>
            <p><?php echo esc_html(get_theme_mod('yea_partners_description', __('Keep this clean. Donors scan logos fast. They do not want visual chaos here.', 'yea-theme'))); ?></p>
        </div>

        <div class="logos">
            <?php 
            // Partner logos can be added via customizer or use default text
            $partners = array(
                __('European Union', 'yea-theme'),
                __('Schools', 'yea-theme'),
                __('Municipalities', 'yea-theme'),
                __('Sponsors', 'yea-theme'),
                __('Media Partners', 'yea-theme'),
            );
            
            foreach ($partners as $index => $partner) : 
                $delay = ($index + 1) * 0.05;
            ?>
                <div class="logo-box reveal-scale" style="transition-delay: <?php echo esc_attr($delay); ?>s;">
                    <?php echo esc_html($partner); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Transparency Section -->
<section id="transparency" class="section soft">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <h2><?php echo esc_html(get_theme_mod('yea_transparency_title', __('Transparency donors can verify.', 'yea-theme'))); ?></h2>
            </div>
            <p><?php echo esc_html(get_theme_mod('yea_transparency_description', __('Most NGO sites bury this. You should make it visible and easy to scan.', 'yea-theme'))); ?></p>
        </div>

        <div class="transparency-grid">
            <div class="report-card reveal-left">
                <h3 style="margin:0;font-size:1.9rem;letter-spacing:-0.04em;"><?php esc_html_e('Reports and documents', 'yea-theme'); ?></h3>
                <p style="color:var(--muted);margin:14px 0 0;line-height:1.6;">
                    <?php esc_html_e('Add your actual PDFs, annual reports, program overviews, and donor-facing summaries here.', 'yea-theme'); ?>
                </p>

                <div class="report-list">
                    <?php 
                    // You can create a custom post type for reports or use attachments
                    $reports = array(
                        __('Annual Report 2025', 'yea-theme'),
                        __('Impact Summary', 'yea-theme'),
                        __('Programs Overview', 'yea-theme'),
                        __('Partnership Deck', 'yea-theme'),
                    );
                    
                    foreach ($reports as $report) : 
                    ?>
                        <div class="report-item">
                            <span><?php echo esc_html($report); ?></span>
                            <span>PDF</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="stats-panel reveal-right">
                <h3 style="margin:0;font-size:1.9rem;letter-spacing:-0.04em;"><?php esc_html_e('Why this layout works', 'yea-theme'); ?></h3>
                <p style="margin:14px 0 0;color:rgba(255,255,255,0.8);line-height:1.6;">
                    <?php esc_html_e('It balances youth energy with institutional clarity. That is the whole job.', 'yea-theme'); ?>
                </p>

                <div class="stats-stack">
                    <div class="stats-row"><span><?php esc_html_e('Community feeling', 'yea-theme'); ?></span><strong><?php esc_html_e('High', 'yea-theme'); ?></strong></div>
                    <div class="stats-row"><span><?php esc_html_e('Donor confidence', 'yea-theme'); ?></span><strong><?php esc_html_e('High', 'yea-theme'); ?></strong></div>
                    <div class="stats-row"><span><?php esc_html_e('Content clarity', 'yea-theme'); ?></span><strong><?php esc_html_e('Strong', 'yea-theme'); ?></strong></div>
                    <div class="stats-row"><span><?php esc_html_e('WordPress fit', 'yea-theme'); ?></span><strong><?php esc_html_e('Gutenberg', 'yea-theme'); ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section id="join" class="section">
    <div class="container">
        <div class="cta-band reveal-scale">
            <div>
                <h2><?php echo esc_html(get_theme_mod('yea_cta_title', __('Ready to join or partner with YEA?', 'yea-theme'))); ?></h2>
                <p><?php echo esc_html(get_theme_mod('yea_cta_description', __('One clear community-facing action. One clear donor-facing action. No clutter.', 'yea-theme'))); ?></p>
            </div>
            <div style="display:flex;gap:14px;flex-wrap:wrap;">
                <a class="btn btn-primary" href="<?php echo esc_url(get_theme_mod('yea_cta_button1_url', '#')); ?>">
                    <?php echo esc_html(get_theme_mod('yea_cta_button1_text', __('Join the Community', 'yea-theme'))); ?>
                </a>
                <a class="btn btn-secondary" href="<?php echo esc_url(get_theme_mod('yea_cta_button2_url', '#')); ?>" style="background:rgba(255,255,255,0.15);border-color:rgba(255,255,255,0.25);color:#fff;">
                    <?php echo esc_html(get_theme_mod('yea_cta_button2_text', __('Partner With Us', 'yea-theme'))); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Recent Posts Section (if blog posts exist) -->
<?php 
$recent_posts = new WP_Query(array(
    'posts_per_page' => 3,
    'post_status'    => 'publish',
));

if ($recent_posts->have_posts()) : 
?>
<section class="section soft">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <h2><?php esc_html_e('Latest News & Updates', 'yea-theme'); ?></h2>
            </div>
            <p><?php esc_html_e('Stay informed about our programs, events, and community stories.', 'yea-theme'); ?></p>
        </div>

        <div class="posts-grid">
            <?php while ($recent_posts->have_posts()) : $recent_posts->the_post(); ?>
                <article class="post-card reveal-scale">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="post-card-image" style="background-image: url('<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'yea-post-card')); ?>');"></div>
                    <?php else : ?>
                        <div class="post-card-image" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark));"></div>
                    <?php endif; ?>
                    
                    <div class="post-card-content">
                        <?php 
                        $categories = get_the_category();
                        if (!empty($categories)) : 
                        ?>
                            <span class="category"><?php echo esc_html($categories[0]->name); ?></span>
                        <?php endif; ?>
                        
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        
                        <div class="excerpt"><?php echo wp_kses_post(get_the_excerpt()); ?></div>
                        
                        <div class="meta">
                            <span><?php echo esc_html(get_the_date()); ?></span>
                            <span><?php echo esc_html(get_the_author()); ?></span>
                        </div>
                    </div>
                </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="btn btn-primary">
                <?php esc_html_e('View All Posts', 'yea-theme'); ?>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
get_footer();
