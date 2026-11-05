<?php
/**
 * Title: Header Shell
 * Slug: yea-theme/header-shell
 * Categories: header
 * Block Types: core/template-part/header
 * Inserter: yes
 * Description: Editable header layout with logo, navigation, and a single CTA button.
 */
?>
<!-- wp:group {"className":"container header-inner","layout":{"type":"default"}} -->
<div class="wp-block-group container header-inner">
  <!-- wp:group {"className":"brand","layout":{"type":"flex","orientation":"horizontal","verticalAlignment":"center","flexWrap":"nowrap"}} -->
  <div class="wp-block-group brand">
    <!-- wp:site-logo {"width":140,"className":"brand-logo"} /-->

    <!-- wp:group {"metadata":{"name":"Brand Text"},"className":"brand-copy","layout":{"type":"constrained"}} -->
    <div class="wp-block-group brand-copy">
      <!-- wp:heading {"level":2,"className":"brand-title","fontSize":"medium"} -->
      <h2 class="wp-block-heading brand-title has-medium-font-size">YEA!</h2>
      <!-- /wp:heading -->

      <!-- wp:paragraph {"className":"brand-subtitle"} -->
      <p class="brand-subtitle">Young Entrepreneurs Albania</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->
  </div>
  <!-- /wp:group -->

  <!-- wp:navigation {"overlayMenu":"mobile","icon":"menu","className":"nav","layout":{"type":"flex","justifyContent":"center","orientation":"horizontal","flexWrap":"nowrap"}} /-->

  <!-- wp:buttons {"metadata":{"name":"Header Actions"},"className":"header-actions","layout":{"type":"flex","justifyContent":"right","flexWrap":"nowrap"}} -->
  <div class="wp-block-buttons header-actions">
    <!-- wp:button -->
    <div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#join">Join YEA</a></div>
    <!-- /wp:button -->
  </div>
  <!-- /wp:buttons -->
</div>
<!-- /wp:group -->
