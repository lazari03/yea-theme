<?php
/**
 * Template for displaying search forms
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>" style="display: flex; gap: 12px;">
    <label style="flex: 1;">
        <span class="screen-reader-text"><?php echo _x('Search for:', 'label', 'yea-theme'); ?></span>
        <input 
            type="search" 
            class="search-field" 
            placeholder="<?php echo esc_attr_x('Search...', 'placeholder', 'yea-theme'); ?>" 
            value="<?php echo get_search_query(); ?>" 
            name="s"
            style="
                width: 100%;
                padding: 16px 24px;
                border: 1px solid var(--line);
                border-radius: 999px;
                font-size: 1rem;
                font-family: inherit;
                background: #fff;
                transition: all 0.3s;
            "
        />
    </label>
    <button 
        type="submit" 
        class="search-submit btn btn-primary"
        style="padding: 16px 32px;"
    >
        <?php echo esc_html_x('Search', 'submit button', 'yea-theme'); ?>
    </button>
</form>
