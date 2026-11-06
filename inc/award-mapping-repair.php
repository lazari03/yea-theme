<?php
/**
 * Temporary rollout repair: restore the exact pre-layout integration snapshot.
 * Runs only against the empty settings produced by the CF7 add-on save hook.
 * Remove after the restored mapping is verified.
 */
add_action('init', function () {
    if ('wpcf7_contact_form' !== get_post_type(4138)) {
        return;
    }
    $current = get_post_meta(4138, '_wpc_notion', true);
    if (!is_array($current) || !empty($current['enable_database']) || !empty($current['database_selected'])) {
        return;
    }
    $snapshot = json_decode('{"enable_database":true,"database_selected":"2be1ed72-7340-80fa-ad5a-c29575bbff99","mapping":{"category":"q%5Cae","project-title":"title","project-description":"YiIa","coordinator-name":"AenB","coordinator-email":"UcNg","coordinator-phone":"NioK","school-name":"Z~z%7C","school-list":"_T%5DE","members-contacts":"r%7CX%5B","mentor-name":"%7CKHL","mentor-contact":"%7CKHL"}}', true);
    update_post_meta(4138, '_wpc_notion', $snapshot);
}, 100);

