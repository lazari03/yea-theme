<?php
/** Run only on an isolated WordPress database. See README. */
$root = getenv('WP_TEST_ROOT');
if (!$root || getenv('WP_TEST_ISOLATED') !== '1') {
    fwrite(STDERR, "Set WP_TEST_ROOT and WP_TEST_ISOLATED=1 for an isolated installation.\n");
    exit(1);
}
require rtrim($root, '/') . '/wp-load.php';
function check_theme($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
}
$terms = array();
$posts = array();
try {
    foreach (array('alpha', 'beta', 'outside') as $label) {
        $result = wp_insert_term('Fixture ' . $label . ' ' . uniqid(), 'category');
        check_theme(!is_wp_error($result), 'Cannot create fixture category');
        $terms[] = $result['term_id'];
    }
    for ($i = 0; $i < 23; $i++) {
        $posts[] = wp_insert_post(array('post_title' => 'Query fixture ' . $i, 'post_status' => 'publish', 'post_category' => array($terms[$i < 21 ? $i % 2 : 2])), true);
    }
    $query = array('perPage' => 10, 'pages' => 0, 'offset' => 0, 'postType' => 'post', 'order' => 'desc', 'orderBy' => 'date', 'inherit' => false, 'taxQuery' => array('category' => array($terms[0], $terms[1])));
    $seen = array();
    foreach (array(1, 2, 3) as $page) {
        $block = new WP_Block(array('blockName' => 'core/post-template', 'attrs' => array()), array('query' => $query));
        $result = new WP_Query(build_query_vars_from_query_block($block, $page));
        check_theme((int) $result->found_posts === 21, 'Query must include both selected terms');
        check_theme((int) $result->max_num_pages === 3, 'Matching posts must paginate');
        $seen = array_merge($seen, wp_list_pluck($result->posts, 'ID'));
    }
    check_theme(count(array_unique($seen)) === 21, 'Pagination duplicated or omitted posts');
    $query['taxQuery']['category'] = array($terms[2]);
    $block = new WP_Block(array('blockName' => 'core/post-template', 'attrs' => array()), array('query' => $query));
    $changed = new WP_Query(build_query_vars_from_query_block($block, 1));
    check_theme((int) $changed->found_posts === 2, 'Changing editor-selected terms must update the feed');
    $original = '<div><img class="wp-image-999999" src="example.png" alt="Original"></div>';
    check_theme(yea_render_partner_logos($original, array('attrs' => array('className' => 'article'))) === $original, 'Logo sizing must never affect article images');
    echo "Passed isolated WordPress taxonomy, pagination and image isolation checks.\n";
} finally {
    foreach ($posts as $post) { if (!is_wp_error($post)) { wp_delete_post($post, true); } }
    foreach ($terms as $term) { wp_delete_term($term, 'category'); }
}
