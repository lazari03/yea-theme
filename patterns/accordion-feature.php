<?php
/**
 * Title: YEA Accordion
 * Slug: yea-theme/accordion-feature
 * Categories: yea
 * Inserter: yes
 * Description: Two-column accordion section with editable image, copy, and expandable items.
 */
?>
<!-- wp:group {"tagName":"section","className":"section soft","layout":{"type":"default"}} -->
<section class="wp-block-group section soft">
  <!-- wp:group {"className":"container","layout":{"type":"default"}} -->
  <div class="wp-block-group container">
    <!-- wp:group {"className":"yea-accordion-feature","layout":{"type":"default"}} -->
    <div class="wp-block-group yea-accordion-feature">
      <!-- wp:group {"className":"yea-accordion-copy","layout":{"type":"default"}} -->
      <div class="wp-block-group yea-accordion-copy">
        <!-- wp:paragraph {"className":"yea-component-kicker"} -->
        <p class="yea-component-kicker">YEA Accordion</p>
        <!-- /wp:paragraph -->

        <!-- wp:heading {"level":2} -->
        <h2 class="wp-block-heading">Answer questions without making the page feel heavy.</h2>
        <!-- /wp:heading -->

        <!-- wp:paragraph -->
        <p>Use this block when you need structured explanations for programs, memberships, donor processes, or application steps. Replace the image, the copy, and each accordion item directly in Gutenberg.</p>
        <!-- /wp:paragraph -->

        <!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
        <figure class="wp-block-image size-large"><img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&amp;fit=crop&amp;w=1200&amp;q=80" alt="Team workshop session"/></figure>
        <!-- /wp:image -->
      </div>
      <!-- /wp:group -->

      <!-- wp:group {"className":"yea-accordion-stack","layout":{"type":"default"}} -->
      <div class="wp-block-group yea-accordion-stack">
        <!-- wp:details {"open":true,"className":"is-style-yea-accordion"} -->
        <details class="wp-block-details is-style-yea-accordion" open><summary>Who is this program designed for?</summary><!-- wp:paragraph -->
        <p>Ideal for ambitious students, early founders, and youth communities that want practical experience, mentorship, and structured opportunities.</p>
        <!-- /wp:paragraph --></details>
        <!-- /wp:details -->

        <!-- wp:details {"className":"is-style-yea-accordion"} -->
        <details class="wp-block-details is-style-yea-accordion"><summary>What kind of outcomes should people expect?</summary><!-- wp:paragraph -->
        <p>Use this item to explain the real outcome: skills gained, networks opened, public visibility, or concrete projects launched.</p>
        <!-- /wp:paragraph --></details>
        <!-- /wp:details -->

        <!-- wp:details {"className":"is-style-yea-accordion"} -->
        <details class="wp-block-details is-style-yea-accordion"><summary>How can partners or donors get involved?</summary><!-- wp:paragraph -->
        <p>Replace this answer with your partnership model, sponsorship offer, grant process, or application timeline.</p>
        <!-- /wp:paragraph --></details>
        <!-- /wp:details -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:group -->
  </div>
  <!-- /wp:group -->
</section>
<!-- /wp:group -->
