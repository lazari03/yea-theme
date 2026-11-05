<?php
/**
 * Title: YEA Blog Feed
 * Slug: yea-theme/blog-feed
 * Categories: yea
 * Inserter: yes
 * Description: Latest posts section using the YEA blog card layout and a native Gutenberg Query Loop.
 */
?>
<!-- wp:group {"tagName":"section","className":"section soft","layout":{"type":"default"}} -->
<section class="wp-block-group section soft">
  <!-- wp:group {"className":"container","layout":{"type":"default"}} -->
  <div class="wp-block-group container">
    <!-- wp:group {"className":"section-head","layout":{"type":"default"}} -->
    <div class="wp-block-group section-head">
      <!-- wp:group {"layout":{"type":"default"}} -->
      <div class="wp-block-group">
        <!-- wp:paragraph {"className":"yea-component-kicker"} -->
        <p class="yea-component-kicker">YEA Blog Feed</p>
        <!-- /wp:paragraph -->

        <!-- wp:heading {"level":2} -->
        <h2 class="wp-block-heading">Latest news, stories, and program updates.</h2>
        <!-- /wp:heading -->
      </div>
      <!-- /wp:group -->

      <!-- wp:paragraph -->
      <p>Use this section on the homepage, About page, donor pages, or landing pages to surface the newest posts automatically.</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->

    <!-- wp:query {"query":{"perPage":6,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false},"displayLayout":{"type":"grid","columnCount":3},"className":"archive-query yea-blog-feed-query"} -->
    <div class="wp-block-query archive-query yea-blog-feed-query">
      <!-- wp:post-template {"className":"posts-grid"} -->
        <!-- wp:group {"className":"post-card","layout":{"type":"default"}} -->
        <div class="wp-block-group post-card">
          <!-- wp:post-featured-image {"isLink":true,"className":"post-card-image"} /-->

          <!-- wp:group {"className":"post-card-content","layout":{"type":"default"}} -->
          <div class="wp-block-group post-card-content">
            <!-- wp:post-terms {"term":"category","className":"category"} /-->
            <!-- wp:post-title {"level":3,"isLink":true} /-->
            <!-- wp:post-excerpt {"moreText":"","className":"excerpt"} /-->

            <!-- wp:group {"className":"meta","layout":{"type":"flex","justifyContent":"left","verticalAlignment":"center","flexWrap":"wrap"}} -->
            <div class="wp-block-group meta">
              <!-- wp:post-date /-->
              <!-- wp:post-author-name /-->
            </div>
            <!-- /wp:group -->
          </div>
          <!-- /wp:group -->
        </div>
        <!-- /wp:group -->
      <!-- /wp:post-template -->

    </div>
    <!-- /wp:query -->

    <!-- wp:buttons {"style":{"spacing":{"margin":{"top":"28px"}}}} -->
    <div class="wp-block-buttons" style="margin-top:28px">
      <!-- wp:button {"className":"is-style-outline"} -->
      <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/blog">View all posts</a></div>
      <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
  </div>
  <!-- /wp:group -->
</section>
<!-- /wp:group -->
