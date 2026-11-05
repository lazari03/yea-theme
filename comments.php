<?php
/**
 * The template for displaying comments
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Return if password protected
if (post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area">

    <?php if (have_comments()) : ?>
        
        <h2 class="comments-title" style="font-size: 1.8rem; margin-bottom: 30px;">
            <?php
            $comment_count = get_comments_number();
            if ('1' === $comment_count) {
                printf(
                    esc_html__('One thought on &ldquo;%1$s&rdquo;', 'yea-theme'),
                    '<span>' . get_the_title() . '</span>'
                );
            } else {
                printf(
                    esc_html(_nx('%1$s thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', $comment_count, 'comments title', 'yea-theme')),
                    number_format_i18n($comment_count),
                    '<span>' . get_the_title() . '</span>'
                );
            }
            ?>
        </h2>

        <?php the_comments_navigation(); ?>

        <ol class="comment-list" style="list-style: none; padding: 0; margin: 0;">
            <?php
            wp_list_comments(array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 56,
                'callback'    => 'yea_comment_callback',
            ));
            ?>
        </ol>

        <?php the_comments_navigation(); ?>

    <?php endif; ?>

    <?php
    // If comments are closed and there are comments, let's leave a little note
    if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) :
    ?>
        <p class="no-comments" style="color: var(--muted); font-style: italic;">
            <?php esc_html_e('Comments are closed.', 'yea-theme'); ?>
        </p>
    <?php endif; ?>

    <?php
    comment_form(array(
        'title_reply'          => __('Leave a Reply', 'yea-theme'),
        'title_reply_to'       => __('Leave a Reply to %s', 'yea-theme'),
        'title_reply_before'   => '<h3 id="reply-title" class="comment-reply-title" style="font-size: 1.5rem; margin-bottom: 20px;">',
        'title_reply_after'    => '</h3>',
        'comment_notes_before' => '<p class="comment-notes" style="color: var(--muted); margin-bottom: 20px;">' . __('Your email address will not be published.', 'yea-theme') . '</p>',
        'class_submit'         => 'submit btn btn-primary',
        'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
        'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . _x('Comment', 'noun', 'yea-theme') . '</label><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true" required="required" style="width: 100%; padding: 16px; border: 1px solid var(--line); border-radius: 16px; font-family: inherit; font-size: 1rem; resize: vertical;"></textarea></p>',
    ));
    ?>

</div>

<?php
/**
 * Custom comment callback
 */
function yea_comment_callback($comment, $args, $depth) {
    $tag = ('div' === $args['style']) ? 'div' : 'li';
    ?>
    <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class(empty($args['has_children']) ? '' : 'parent', $comment); ?> style="margin-bottom: 30px; padding: 24px; background: var(--bg); border-radius: 20px;">
        
        <article id="div-comment-<?php comment_ID(); ?>" class="comment-body" style="display: flex; gap: 20px;">
            
            <div class="comment-author-avatar" style="flex-shrink: 0;">
                <?php if (0 != $args['avatar_size']) echo get_avatar($comment, $args['avatar_size'], '', '', array('style' => 'border-radius: 50%;')); ?>
            </div>
            
            <div class="comment-content" style="flex: 1;">
                
                <footer class="comment-meta" style="margin-bottom: 12px;">
                    <div class="comment-author" style="font-weight: 700; color: var(--ink);">
                        <?php printf(__('%s', 'yea-theme'), sprintf('<b class="fn">%s</b>', get_comment_author_link($comment))); ?>
                    </div>
                    <div class="comment-metadata" style="font-size: 0.85rem; color: var(--muted);">
                        <a href="<?php echo esc_url(get_comment_link($comment, $args)); ?>" style="color: inherit;">
                            <time datetime="<?php comment_time('c'); ?>">
                                <?php
                                printf(
                                    _x('%1$s at %2$s', '1: date, 2: time', 'yea-theme'),
                                    get_comment_date('', $comment),
                                    get_comment_time()
                                );
                                ?>
                            </time>
                        </a>
                        <?php edit_comment_link(__('Edit', 'yea-theme'), '<span class="edit-link" style="margin-left: 10px;">', '</span>'); ?>
                    </div>
                </footer>
                
                <?php if ('0' == $comment->comment_approved) : ?>
                    <p class="comment-awaiting-moderation" style="color: var(--primary); font-style: italic;"><?php _e('Your comment is awaiting moderation.', 'yea-theme'); ?></p>
                <?php endif; ?>
                
                <div class="comment-text" style="line-height: 1.7;">
                    <?php comment_text(); ?>
                </div>
                
                <?php
                comment_reply_link(array_merge($args, array(
                    'add_below' => 'div-comment',
                    'depth'     => $depth,
                    'max_depth' => $args['max_depth'],
                    'before'    => '<div class="reply" style="margin-top: 12px;">',
                    'after'     => '</div>',
                )));
                ?>
                
            </div>
        </article>
    </<?php echo $tag; ?>>
    <?php
}
