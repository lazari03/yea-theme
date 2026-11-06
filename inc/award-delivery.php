<?php
/** Registration receipts and a separate code-authorized delivery portal. */
if (!defined('ABSPATH')) { exit; }

function yea_award_registration_form_id() { return (int) get_option('yea_award_registration_form_id', 0); }
function yea_award_delivery_open() { return (bool) get_option('yea_award_delivery_open', false); }
function yea_award_new_code() { return 'YEA-' . implode('-', str_split(strtoupper(bin2hex(random_bytes(12))), 4)); }
function yea_award_normalize_code($value) {
    $value = strtoupper(preg_replace('/[\s-]+/', '', trim((string) $value)));
    return preg_match('/^YEA[A-F0-9]{24}$/D', $value) ? 'YEA-' . implode('-', str_split(substr($value, 3), 4)) : '';
}
function yea_award_delivery_url() {
    $page = (int) get_option('yea_award_delivery_page_id', 0);
    return $page ? get_permalink($page) : home_url('/');
}

// Never trust a code supplied by a browser during registration.
add_filter('wpcf7_posted_data', function ($data) {
    $submission = WPCF7_Submission::get_instance();
    if ($submission && yea_award_registration_form_id() === (int) $submission->get_contact_form()->id()) {
        $data['registration-code'] = yea_award_new_code();
        $GLOBALS['yea_award_receipt'] = array('code' => $data['registration-code'], 'project' => (string) ($data['project-title'] ?? ''));
    }
    return $data;
});
add_action('add-on-cf7-for-notion/notion-api/response', function ($route, $pretty, $response) {
    if ('pages' === $route && !empty($GLOBALS['yea_award_receipt']) && !is_wp_error($response) && !empty($pretty->id)) {
        $GLOBALS['yea_award_receipt']['page_id'] = $pretty->id;
    }
}, 20, 3);
add_action('wpcf7_before_send_mail', function ($form, &$abort, $submission) {
    if (yea_award_registration_form_id() === (int) $form->id() && empty($GLOBALS['yea_award_receipt']['page_id'])) {
        $abort = true; $submission->set_status('aborted');
        $submission->set_response('Regjistrimi nuk u konfirmua. Provoni përsëri ose kontaktoni YEA!.');
    }
}, 20, 3);
add_filter('wpcf7_feedback_response', function ($response) {
    $receipt = $GLOBALS['yea_award_receipt'] ?? array();
    if (empty($receipt['page_id']) || empty($receipt['code'])) { return $response; }
    // Notion is the registration source of truth. A mail failure must not make
    // the applicant lose a saved code or create another registration by retrying.
    $mail_failed = 'mail_failed' === ($response['status'] ?? '');
    $email_sent = !$mail_failed;
    if ($mail_failed && class_exists('WPCF7_Mail')) {
        $form = wpcf7_contact_form(yea_award_registration_form_id());
        $confirmation = $form ? $form->prop('mail_2') : array();
        if (!empty($confirmation['active'])) { $email_sent = WPCF7_Mail::send($confirmation, 'mail_2'); }
    }
    if (!in_array($response['status'] ?? '', array('mail_sent', 'mail_failed'), true)) { return $response; }
    $response['status'] = 'mail_sent';
    $response['registration_code'] = $receipt['code'];
    $response['project_name'] = $receipt['project'];
    $response['delivery_url'] = yea_award_delivery_url();
    $response['email_confirmation'] = (bool) $email_sent;
    $response['message'] = 'Regjistrimi u ruajt. Kodi juaj: ' . $receipt['code'] . '. Ruajeni këtë kod për dorëzimin e projektit.';
    if (!$email_sent) { $response['message'] .= ' Emaili nuk u dërgua; ruajeni kodin nga kjo faqe.'; }
    return $response;
});

add_action('admin_init', function () {
    foreach (array('yea_award_registration_form_id', 'yea_award_delivery_page_id') as $name) {
        register_setting('yea_award_portal', $name, array('type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 0));
    }
    register_setting('yea_award_portal', 'yea_award_delivery_open', array('type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false));
});
add_action('admin_menu', function () {
    add_options_page('YEA Award Portal', 'YEA Award Portal', 'manage_options', 'yea-award-portal', 'yea_award_settings_screen');
});
function yea_award_settings_screen() {
    if (!current_user_can('manage_options')) { return; }
    echo '<div class="wrap"><h1>YEA Award Portal</h1><form method="post" action="options.php">';
    settings_fields('yea_award_portal');
    echo '<table class="form-table"><tr><th scope="row">Project delivery</th><td><input type="hidden" name="yea_award_delivery_open" value="0"><label><input type="checkbox" name="yea_award_delivery_open" value="1" ' . checked(yea_award_delivery_open(), true, false) . '> Open project delivery</label><p class="description">When closed, codes cannot unlock uploads. Registration remains available.</p></td></tr>';
    echo '<tr><th><label for="award-form-id">Registration form</label></th><td><input id="award-form-id" type="number" min="1" name="yea_award_registration_form_id" value="' . esc_attr(yea_award_registration_form_id()) . '"><p class="description">Contact Form 7 form ID.</p></td></tr>';
    echo '<tr><th><label for="award-page-id">Delivery page</label></th><td><input id="award-page-id" type="number" min="1" name="yea_award_delivery_page_id" value="' . esc_attr(get_option('yea_award_delivery_page_id', 0)) . '"></td></tr></table>';
    submit_button('Save portal settings'); echo '</form></div>';
}

add_action('rest_api_init', function () {
    register_rest_route('yea/v1', '/award-settings', array(
        'methods' => array('GET', 'POST'), 'permission_callback' => function () { return current_user_can('manage_options'); },
        'args' => array('form_id' => array('type' => 'integer'), 'delivery_page_id' => array('type' => 'integer'), 'open' => array('type' => 'boolean')),
        'callback' => function ($request) {
            if ('POST' === $request->get_method()) {
                foreach (array('form_id' => 'yea_award_registration_form_id', 'delivery_page_id' => 'yea_award_delivery_page_id', 'open' => 'yea_award_delivery_open') as $key => $option) {
                    if ($request->has_param($key)) { update_option($option, $request->get_param($key), false); }
                }
            }
            return array('form_id' => yea_award_registration_form_id(), 'delivery_page_id' => (int) get_option('yea_award_delivery_page_id'), 'open' => yea_award_delivery_open(), 'last_delivery_error' => get_transient('yea_award_delivery_api_error') ?: null);
        },
    ));
    register_rest_route('yea/v1', '/award-delivery/status', array('methods' => 'GET', 'permission_callback' => '__return_true', 'callback' => function () { return yea_award_no_cache(array('open' => yea_award_delivery_open())); }));
    register_rest_route('yea/v1', '/award-delivery/unlock', array('methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => 'yea_award_unlock', 'args' => array('code' => array('type' => 'string', 'required' => true))));
    register_rest_route('yea/v1', '/award-delivery/files', array('methods' => 'POST', 'permission_callback' => 'yea_award_delivery_permission', 'callback' => 'yea_award_receive_files'));
});
function yea_award_no_cache($data) { $response = new WP_REST_Response($data); $response->header('Cache-Control', 'no-store, private'); return $response; }
function yea_award_notion_request($path, $method = 'GET', $body = null, $content_type = 'application/json') {
    if (!class_exists('WPC_WPCF7_NTN\\WPCF7_Notion_Service')) { return new WP_Error('yea_notion_unavailable', 'Lidhja me regjistrimet nuk është e disponueshme.', array('status' => 503)); }
    $service = \WPC_WPCF7_NTN\WPCF7_Notion_Service::get_instance();
    $key = $service->get_api_key();
    if (!is_string($key) || '' === trim($key)) { return new WP_Error('yea_notion_unavailable', 'Lidhja me regjistrimet nuk është e disponueshme.', array('status' => 503)); }
    $args = array('method' => $method, 'timeout' => 60, 'headers' => array('Authorization' => 0 === strpos($key, 'Bearer ') ? $key : 'Bearer ' . $key, 'Notion-Version' => '2022-06-28', 'Content-Type' => $content_type));
    if (null !== $body) { $args['body'] = 'application/json' === $content_type ? wp_json_encode($body) : $body; }
    $response = wp_remote_request('https://api.notion.com/v1/' . $path, $args);
    if (is_wp_error($response)) { return new WP_Error('yea_notion_transport', 'Lidhja u ndërpre. Provoni përsëri.', array('status' => 502)); }
    $data = json_decode(wp_remote_retrieve_body($response), true);
    $status = (int) wp_remote_retrieve_response_code($response);
    if ($status < 200 || $status >= 300 || !is_array($data)) {
        set_transient('yea_award_delivery_api_error', array('time' => gmdate('c'), 'status' => $status, 'code' => $data['code'] ?? 'invalid_response', 'message' => sanitize_text_field($data['message'] ?? 'Invalid response')), HOUR_IN_SECONDS);
        return new WP_Error('yea_notion_rejected', 'Dorëzimi nuk u konfirmua. Provoni përsëri.', array('status' => 502));
    }
    return $data;
}
function yea_award_find_registration($code) {
    if (!$code || !function_exists('wpcf7_contact_form')) { return new WP_Error('yea_code_invalid', 'Kodi nuk u gjet. Kontrollojeni dhe provoni përsëri.', array('status' => 401)); }
    $form = wpcf7_contact_form(yea_award_registration_form_id());
    $mapping = $form ? $form->prop('wpc_notion') : array();
    if (empty($mapping['enable_database']) || empty($mapping['database_selected']) || empty($mapping['mapping']['registration-code'])) { return new WP_Error('yea_code_not_configured', 'Portali nuk është ende gati. Kontaktoni YEA!.', array('status' => 503)); }
    $result = yea_award_notion_request('databases/' . $mapping['database_selected'] . '/query', 'POST', array('filter' => array('property' => $mapping['mapping']['registration-code'], 'rich_text' => array('equals' => $code)), 'page_size' => 2));
    if (is_wp_error($result)) { return $result; }
    if (1 !== count($result['results'] ?? array())) { return new WP_Error('yea_code_invalid', 'Kodi nuk u gjet. Kontrollojeni dhe provoni përsëri.', array('status' => 401)); }
    $page = $result['results'][0]; $project = '';
    foreach ($page['properties'] as $property) {
        if ('title' === $property['type']) { foreach ($property['title'] as $text) { $project .= $text['plain_text'] ?? $text['text']['content'] ?? ''; } }
    }
    return array('page_id' => $page['id'], 'project' => $project);
}
function yea_award_unlock($request) {
    if (!yea_award_delivery_open()) { return new WP_Error('yea_delivery_closed', 'Dorëzimi i projekteve nuk është hapur ende. Ruajeni kodin tuaj.', array('status' => 423)); }
    $rate_key = 'yea_award_unlock_' . hash_hmac('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown', wp_salt('auth'));
    $attempts = (int) get_transient($rate_key);
    if ($attempts >= 30) { return new WP_Error('yea_rate_limited', 'Shumë përpjekje. Provoni pas një minute.', array('status' => 429)); }
    set_transient($rate_key, $attempts + 1, MINUTE_IN_SECONDS);
    $record = yea_award_find_registration(yea_award_normalize_code($request->get_param('code')));
    if (is_wp_error($record)) { return $record; }
    $token = bin2hex(random_bytes(32));
    $session = array('page_id' => $record['page_id'], 'project' => $record['project'], 'receipt' => 'YEA-D-' . strtoupper(bin2hex(random_bytes(8))), 'complete' => false);
    set_transient('yea_award_session_' . hash('sha256', $token), $session, 30 * MINUTE_IN_SECONDS);
    return yea_award_no_cache(array('token' => $token, 'project' => $record['project'], 'expires_in' => 1800));
}
function yea_award_session_key($request) {
    $token = $request->get_header('x-yea-delivery-token');
    return preg_match('/^[a-f0-9]{64}$/D', $token) ? 'yea_award_session_' . hash('sha256', $token) : '';
}
function yea_award_delivery_permission($request) {
    if (!yea_award_delivery_open()) { return new WP_Error('yea_delivery_closed', 'Dorëzimi nuk është i hapur.', array('status' => 423)); }
    $key = yea_award_session_key($request);
    return $key && get_transient($key) ? true : new WP_Error('yea_session_expired', 'Sesioni ka përfunduar. Vendosni përsëri kodin.', array('status' => 401));
}
function yea_award_validate_delivery_files($files) {
    $items = array();
    if (!isset($files['documents']['name']) || !is_array($files['documents']['name']) || count($files['documents']['name']) < 1 || count($files['documents']['name']) > 2) {
        return new WP_Error('yea_files_required', 'Zgjidhni një ose dy dokumente PowerPoint / PDF.', array('status' => 400));
    }
    $mimes = array('pdf' => 'application/pdf', 'ppt' => 'application/vnd.ms-powerpoint', 'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
    foreach ($files['documents']['name'] as $i => $name) {
        $tmp = $files['documents']['tmp_name'][$i] ?? '';
        if (UPLOAD_ERR_OK !== ($files['documents']['error'][$i] ?? -1) || !is_uploaded_file($tmp)) { return new WP_Error('yea_upload_failed', 'Ngarkimi nuk përfundoi. Zgjidhni përsëri dokumentin.', array('status' => 400)); }
        if (filesize($tmp) < 1 || filesize($tmp) > 20 * 1024 * 1024) { return new WP_Error('yea_file_size', 'Çdo dokument duhet të jetë deri në 20 MB.', array('status' => 400)); }
        $checked = wp_check_filetype_and_ext($tmp, sanitize_file_name($name), $mimes);
        if (empty($checked['ext']) || !isset($mimes[$checked['ext']])) { return new WP_Error('yea_file_type', 'Lejohen vetëm PDF, PPT dhe PPTX.', array('status' => 400)); }
        if ('pptx' === $checked['ext'] && class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if (true !== $zip->open($tmp)) { return new WP_Error('yea_file_type', 'Dokumenti PowerPoint nuk është i vlefshëm.', array('status' => 400)); }
            $valid = false !== $zip->locateName('ppt/presentation.xml'); $zip->close();
            if (!$valid) { return new WP_Error('yea_file_type', 'Dokumenti PowerPoint nuk është i vlefshëm.', array('status' => 400)); }
        }
        $items[] = array('name' => sanitize_file_name($name), 'path' => $tmp, 'mime' => $mimes[$checked['ext']], 'hash' => hash_file('sha256', $tmp));
    }
    return $items;
}
function yea_award_upload_to_notion($file, &$session, $key) {
    $fingerprint = hash('sha256', $file['name'] . $file['hash']);
    $stage = $session['uploads'][$fingerprint] ?? array();
    if (empty($stage['id'])) {
        $created = yea_award_notion_request('file_uploads', 'POST', array('mode' => 'single_part', 'filename' => $file['name'], 'content_type' => $file['mime']));
        if (is_wp_error($created)) { return $created; }
        $stage = array('id' => $created['id'], 'name' => $file['name'], 'uploaded' => false);
        $session['uploads'][$fingerprint] = $stage; set_transient($key, $session, 30 * MINUTE_IN_SECONDS);
    }
    if (!$stage['uploaded']) {
        $status = yea_award_notion_request('file_uploads/' . $stage['id']);
        if (!is_wp_error($status) && 'uploaded' === ($status['status'] ?? '')) { $stage['uploaded'] = true; }
        else {
            $bytes = file_get_contents($file['path']);
            if (false === $bytes) { return new WP_Error('yea_file_read_failed', 'Dokumenti nuk u lexua. Zgjidheni përsëri.', array('status' => 400)); }
            $boundary = 'yea-' . bin2hex(random_bytes(16));
            $body = '--' . $boundary . "\r\nContent-Disposition: form-data; name=\"file\"; filename=\"" . $file['name'] . "\"\r\nContent-Type: " . $file['mime'] . "\r\n\r\n" . $bytes . "\r\n--" . $boundary . "--\r\n";
            $sent = yea_award_notion_request('file_uploads/' . $stage['id'] . '/send', 'POST', $body, 'multipart/form-data; boundary=' . $boundary);
            if (is_wp_error($sent)) { return $sent; }
            if ('uploaded' !== ($sent['status'] ?? '')) { return new WP_Error('yea_upload_pending', 'Ngarkimi nuk u konfirmua ende. Provoni përsëri.', array('status' => 502)); }
            $stage['uploaded'] = true;
        }
        $session['uploads'][$fingerprint] = $stage; set_transient($key, $session, 30 * MINUTE_IN_SECONDS);
    }
    return $stage;
}
function yea_award_has_receipt($page_id, $receipt) {
    $cursor = '';
    for ($i = 0; $i < 20; $i++) {
        $result = yea_award_notion_request('blocks/' . $page_id . '/children?page_size=100' . ($cursor ? '&start_cursor=' . rawurlencode($cursor) : ''));
        if (is_wp_error($result)) { return $result; }
        foreach ($result['results'] ?? array() as $block) {
            if ('toggle' === $block['type']) { foreach ($block['toggle']['rich_text'] ?? array() as $text) { if (false !== strpos($text['plain_text'] ?? $text['text']['content'] ?? '', $receipt)) { return true; } } }
        }
        if (empty($result['has_more'])) { return false; } $cursor = $result['next_cursor'];
    }
    return new WP_Error('yea_receipt_unconfirmed', 'Dorëzimi po verifikohet. Provoni më vonë.', array('status' => 502));
}
function yea_award_receive_files($request) {
    $key = yea_award_session_key($request); $session = get_transient($key);
    if (!$session) { return new WP_Error('yea_session_expired', 'Vendosni përsëri kodin.', array('status' => 401)); }
    if (!empty($session['complete'])) { return yea_award_no_cache(array('received' => true, 'receipt' => $session['receipt'])); }
    $lock = 'yea_award_lock_' . hash('sha256', $session['page_id']);
    if (get_option($lock) && (int) get_option($lock) < time() - 300) { delete_option($lock); }
    if (!add_option($lock, time(), '', false)) { return new WP_Error('yea_upload_busy', 'Një dorëzim po përpunohet. Prisni pak.', array('status' => 409)); }
    try {
        $files = yea_award_validate_delivery_files($request->get_file_params());
        if (is_wp_error($files)) { return $files; }
        $stages = array();
        foreach ($files as $file) { $stage = yea_award_upload_to_notion($file, $session, $key); if (is_wp_error($stage)) { return $stage; } $stages[] = $stage; }
        return yea_award_attach_delivery($session, $key, $stages);
    } finally { delete_option($lock); }
}

function yea_award_attach_delivery(&$session, $key, $stages) {
        $exists = !empty($session['attach_attempted']) ? yea_award_has_receipt($session['page_id'], $session['receipt']) : false;
        if (is_wp_error($exists)) { return $exists; }
        if (!$exists) {
            $children = array();
            foreach ($stages as $stage) { $children[] = array('object' => 'block', 'type' => 'file', 'file' => array('type' => 'file_upload', 'file_upload' => array('id' => $stage['id']), 'caption' => array(array('type' => 'text', 'text' => array('content' => $stage['name']))))); }
            $session['attach_attempted'] = true; set_transient($key, $session, 30 * MINUTE_IN_SECONDS);
            $attached = yea_award_notion_request('blocks/' . $session['page_id'] . '/children', 'PATCH', array('children' => array(array('object' => 'block', 'type' => 'toggle', 'toggle' => array('rich_text' => array(array('type' => 'text', 'text' => array('content' => 'Dorëzim projekti · ' . gmdate('Y-m-d H:i') . ' UTC · ' . $session['receipt']))), 'children' => $children)))));
            if (is_wp_error($attached)) { return $attached; }
        }
        $session['complete'] = true; set_transient($key, $session, 30 * MINUTE_IN_SECONDS);
        return yea_award_no_cache(array('received' => true, 'receipt' => $session['receipt']));
}

add_shortcode('yea_award_delivery', function () {
    return '<section class="award-delivery" aria-label="Dorëzimi i projektit"><p class="award-delivery-status" role="status">Po kontrollohet nëse dorëzimi është i hapur…</p><form class="award-code-form" hidden><label for="award-code">Kodi i regjistrimit</label><input id="award-code" name="code" autocomplete="off" autocapitalize="characters" spellcheck="false" required placeholder="YEA-XXXX-XXXX-XXXX-XXXX-XXXX-XXXX"><button type="submit" class="award-next">Hap dorëzimin →</button></form><section class="award-unlocked" hidden><h3 class="award-project-welcome" tabindex="-1"></h3><form class="award-delivery-files"><label for="award-documents">Dokumentet e projektit</label><div class="award-file-drop"><input id="award-documents" name="documents[]" type="file" accept=".pdf,.ppt,.pptx" multiple required><p class="award-help">Deri në dy dokumente PowerPoint (PPT/PPTX) ose PDF, deri në 20 MB secili.</p><ul class="award-selected-files"></ul></div><button type="submit" class="award-next">Dërgo dokumentet</button><p class="award-help">Prisni konfirmimin pa e mbyllur këtë faqe.</p></form></section><p class="award-delivery-error" role="alert"></p><section class="award-delivery-success" hidden tabindex="-1"><h3>Dokumentet u dorëzuan.</h3><p>U ruajtën bashkë me regjistrimin e projektit tuaj.</p><p class="award-delivery-receipt"></p></section><noscript>Aktivizoni JavaScript për të verifikuar kodin dhe ngarkuar dokumentet.</noscript></section>';
});

/** Bounded administrator test: a temporary Notion row and fixtures, never CF7 emails. */
function yea_award_workflow_check($form, $database, $columns) {
    $code = yea_award_new_code(); $page_id = null; $paths = array();
    $key = 'yea_award_workflow_test_' . bin2hex(random_bytes(12));
    $outcome = null; $archived = false;
    try {
        $mapper = \WPC_WPCF7_NTN\WPCF7_Field_Mapper::get_instance();
        $mapped = $mapper->filter_mapped_tags(\WPC_WPCF7_NTN\Helpers\get_mapped_tags_from_contact_form($form, $columns));
        $tags = array(); foreach ($form->scan_form_tags() as $tag) { $tags[$tag->name] = $tag; }
        $values = array();
        foreach ($mapped as $name => $field) {
            $value = 'Temporary workflow check';
            if ('title' === $field['notion_field_type']) { $value = 'YEA registration / delivery check (temporary) ' . gmdate('c'); }
            if ('email' === $field['type']) { $value = 'workflow-check@example.test'; }
            if ('tel' === $field['type']) { $value = '+355000000000'; }
            if ('select' === $field['type']) { $value = array($tags[$name]->values[0]); }
            if ('registration-code' === $name) { $value = $code; }
            $values[$field['notion_field_id']] = (object) array('field_value' => $mapper->get_formatted_field_value($field['type'], $field['notion_field_type'], $value), 'field_type' => $field['notion_field_type'], 'column_id' => $field['notion_field_id']);
        }
        if (!isset($mapped['registration-code'])) { return new WP_Error('yea_code_mapping_missing', 'Configure the registration-code mapping before testing.', array('status' => 409)); }
        $api = wpconnect_wpcf7_notion_get_api();
        $created = $api->add_database_row($database, \WPC_WPCF7_NTN\Helpers\prepare_fields_for_notion($values, $database));
        if (is_wp_error($created)) { return $created; } $page_id = $created->id;
        $record = yea_award_find_registration($code);
        if (is_wp_error($record) || $record['page_id'] !== $page_id) { throw new RuntimeException('The saved registration code did not resolve to its project.'); }
        $session = array('page_id' => $page_id, 'project' => $record['project'], 'receipt' => 'YEA-TEST-' . strtoupper(bin2hex(random_bytes(8))), 'complete' => false);
        $fixtures = array(
            array('name' => 'Workflow-check.pdf', 'mime' => 'application/pdf', 'body' => "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF\n"),
            array('name' => 'Workflow-check.pptx', 'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'body' => base64_decode('UEsDBBQAAAAIANu9Zl07YV80yAAAAFgBAAATAAAAW0NvbnRlbnRfVHlwZXNdLnhtbJWQvU7EMAzHXyXKii4uDAyo7Q0HKzDwAlbqXiPyYSW+0/H2uD2QgI3R/n/8LPf7S4rmTLWFkgd76zq7H/u3D6ZmVMltsIsIPwA0v1DC5gpTVmUuNaHoWI/A6N/xSHDXdffgSxbKspO1w479I814imKeLrq+UirFZs3halxZg0XmGDyK6nDO0x/K7ovgNLl52hK43ajBwti/6Pk1TGRescozJq0DZgGu1BSwBdzq/ReyzHPwNBV/ShpxP8tS/DW6hCF/HwPb78ZPUEsDBBQAAAAIANu9Zl07zL8VlgAAAAoBAAALAAAAX3JlbHMvLnJlbHONjz0OwjAMha8S+QB1y8CAmnZhYUVcIErdH9EklmMQ3J6IiSIGRr/36Xty2z/Cau4keUnRQlPV0HftmVanJcjzwtkUImYLsyofELOfKbhcJaZYmjFJcFpOmZCdv7qJcFfXe5RPB2yd5jRYkNPQgLk8mf5xp3FcPB2TvwWK+mPiiyhmJxOpBWZFFsolfNNVMQN2LW6+7F5QSwMEFAAAAAgA271mXXlaRllsAAAAiAAAABQAAABwcHQvcHJlc2VudGF0aW9uLnhtbFWNwQ6DMAxDfwX1A0jhsENVyhmJn6hGNpCaNGqiaZ+/7rYdbT/bcX1TGV7Y9Kq8uGn0bk1RgjRUZMvW7aEjrEEWd5pJAND7iZR1rILcs0dtlK3L9oTfHhWYvb8B5Yvdd1TLsR27GqQI/xfpA1BLAQIUAxQAAAAIANu9Zl07YV80yAAAAFgBAAATAAAAAAAAAAAAAACAAQAAAABbQ29udGVudF9UeXBlc10ueG1sUEsBAhQDFAAAAAgA271mXTvMvxWWAAAACgEAAAsAAAAAAAAAAAAAAIAB+QAAAF9yZWxzLy5yZWxzUEsBAhQDFAAAAAgA271mXXlaRllsAAAAiAAAABQAAAAAAAAAAAAAAIABuAEAAHBwdC9wcmVzZW50YXRpb24ueG1sUEsFBgAAAAADAAMAvAAAAFYCAAAAAA==')),
        );
        $stages = array();
        foreach ($fixtures as $fixture) {
            $path = tempnam(get_temp_dir(), 'yea_workflow_'); if (!$path) { throw new RuntimeException('Cannot create workflow fixture.'); }
            $paths[] = $path; file_put_contents($path, $fixture['body']);
            $stage = yea_award_upload_to_notion(array('name' => $fixture['name'], 'mime' => $fixture['mime'], 'path' => $path, 'hash' => hash_file('sha256', $path)), $session, $key);
            if (is_wp_error($stage)) { throw new RuntimeException($stage->get_error_message()); } $stages[] = $stage;
        }
        $attached = yea_award_attach_delivery($session, $key, $stages);
        if (is_wp_error($attached)) { throw new RuntimeException($attached->get_error_message()); }
        $confirmed = yea_award_has_receipt($page_id, $session['receipt']);
        if (true !== $confirmed) { throw new RuntimeException('The native delivery attachments were not confirmed.'); }
        $outcome = array('registration_saved' => true, 'code_resolves_project' => true, 'native_files_attached' => count($stages), 'receipt_confirmed' => true, 'emails_sent' => false, 'test_record_id' => $page_id);
    } catch (Throwable $error) {
        $outcome = new WP_Error('yea_workflow_failed', $error->getMessage(), array('status' => 502));
    } finally {
        foreach ($paths as $path) { wp_delete_file($path); } delete_transient($key);
        if ($page_id) {
            $cleanup = yea_award_notion_request('pages/' . $page_id, 'PATCH', array('archived' => true));
            $archived = !is_wp_error($cleanup);
        }
    }
    if (is_wp_error($outcome)) { $outcome->add_data(array_merge($outcome->get_error_data(), array('test_record_id' => $page_id, 'archived' => $archived, 'notion_error' => get_transient('yea_award_delivery_api_error') ?: null))); return $outcome; }
    $outcome['archived'] = $archived; return $outcome;
}
