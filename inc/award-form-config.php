<?php
/** Administrator-only portal configuration; submissions remain owned by CF7. */
if (!defined('ABSPATH')) { exit; }

add_action('rest_api_init', function () {
    register_rest_route('yea/v1', '/award-form/(?P<id>\d+)', array(
        'methods' => array('GET', 'POST'),
        'permission_callback' => function () { return current_user_can('manage_options'); },
        'args' => array(
            'id' => array('type' => 'integer', 'required' => true),
            'form' => array('type' => 'string'),
            'file_field' => array('type' => 'string'),
            'file_property' => array('type' => 'string'),
            'field_properties' => array('type' => 'object'),
            'diagnostic_test' => array('type' => 'boolean'),
        ),
        'callback' => 'yea_award_form_config',
    ));
});

function yea_award_form_config($request) {
    if (!function_exists('wpcf7_contact_form') || !function_exists('WPC_WPCF7_NTN\\Helpers\\get_notion_databases_columns')) {
        return new WP_Error('yea_missing_integration', 'Contact Form 7 and the Notion add-on are required.', array('status' => 503));
    }
    $form = wpcf7_contact_form((int) $request['id']);
    if (!$form) { return new WP_Error('yea_form_missing', 'Form not found.', array('status' => 404)); }
    $integration = $form->prop('wpc_notion');
    if (empty($integration['enable_database']) || empty($integration['database_selected'])) {
        return new WP_Error('yea_mapping_missing', 'An enabled Notion database must already be configured.', array('status' => 409));
    }
    $database = $integration['database_selected'];
    $columns = \WPC_WPCF7_NTN\Helpers\get_notion_databases_columns($database, true);
    if (!$columns) { return new WP_Error('yea_schema_unavailable', 'Cannot read the configured Notion database schema.', array('status' => 502)); }
    if ('POST' === $request->get_method() && $request->get_param('diagnostic_test')) {
        return yea_award_notion_delivery_check($form, $database, $columns);
    }
    if ('POST' === $request->get_method()) {
        $markup = $request->get_param('form');
        if (!is_string($markup) || '' === trim($markup)) {
            return new WP_Error('yea_form_required', 'Nonempty form markup is required.', array('status' => 400));
        }
        $form->set_properties(array('form' => $markup));
        $specs = (array) $request->get_param('field_properties');
        if ($request->get_param('file_field') || $request->get_param('file_property')) {
            $specs[(string) $request->get_param('file_field')] = array('name' => $request->get_param('file_property'), 'type' => 'files');
        }
        $tags = array();
        foreach ($form->scan_form_tags() as $tag) { if ($tag->name) { $tags[$tag->name] = $tag; } }
        $allowed = array('file' => array('files'), 'select' => array('select', 'multi_select', 'rich_text'), 'number' => array('number', 'rich_text'), 'text' => array('rich_text', 'title'), 'textarea' => array('rich_text'), 'email' => array('email', 'rich_text'), 'tel' => array('phone_number', 'rich_text'));
        // Validate all requested mappings before any schema mutation.
        foreach ($specs as $field => $spec) {
            if (!is_array($spec) || empty($spec['name']) || empty($spec['type']) || !isset($tags[$field]) || !in_array($spec['type'], $allowed[$tags[$field]->basetype] ?? array(), true)) {
                return new WP_Error('yea_mapping_invalid', 'A requested property is incompatible with its form field.', array('status' => 400));
            }
            foreach ($columns as $candidate) {
                if ($candidate->name === $spec['name'] && $candidate->type !== $spec['type']) {
                    return new WP_Error('yea_property_type', 'An existing property has a different type; it will not be changed.', array('status' => 409));
                }
            }
        }
        foreach ($specs as $field => $spec) {
            $column = null;
            foreach ($columns as $candidate) {
                if ($candidate->name === $spec['name']) { $column = $candidate; break; }
            }
            if (!$column) {
                $service = \WPC_WPCF7_NTN\WPCF7_Notion_Service::get_instance();
                $api = new class($service->get_api_key()) extends \WPC_WPCF7_NTN\API_Notion {
                    public function add_property($database_id, $name, $type) {
                        return $this->request('databases/' . $database_id, array('properties' => array($name => array($type => (object) array()))), 'PATCH');
                    }
                };
                $result = $api->add_property($database, $spec['name'], $spec['type']);
                if (is_wp_error($result)) {
                    return new WP_Error('yea_property_failed', 'Notion could not add a property. Existing form settings are unchanged.', array('status' => 502));
                }
                $columns = \WPC_WPCF7_NTN\Helpers\get_notion_databases_columns($database, true);
                foreach ($columns as $candidate) {
                    if ($candidate->name === $spec['name'] && $candidate->type === $spec['type']) { $column = $candidate; break; }
                }
                if (!$column) { return new WP_Error('yea_property_unconfirmed', 'The added property could not be confirmed. Check the schema before retrying.', array('status' => 502)); }
            }
            $integration['mapping'][$field] = $column->id;
        }
        // Save properties directly. The add-on's legacy editor save hook reads
        // $_POST and clears mappings during JSON REST saves; do not invoke it.
        $form->set_properties(array('wpc_notion' => $integration));
        if (!$form->save()) { return new WP_Error('yea_save_failed', 'The form could not be saved.', array('status' => 500)); }
    }
    return array('id' => $form->id(), 'database_id' => $database, 'columns' => array_values($columns), 'mapping' => $integration['mapping'], 'last_delivery_error' => get_transient('yea_award_notion_last_error') ?: null);
}

// Legacy entry points may point to one canonical application, without duplicate forms.
add_action('init', function () {
    register_post_meta('page', 'yea_award_portal_target', array(
        'type' => 'integer', 'single' => true, 'show_in_rest' => true,
        'sanitize_callback' => 'absint',
        'auth_callback' => function ($allowed, $key, $post_id) { return current_user_can('edit_post', $post_id); },
    ));
});
add_action('template_redirect', function () {
    if (!is_page() || is_preview()) { return; }
    $source = get_queried_object_id();
    $target = (int) get_post_meta($source, 'yea_award_portal_target', true);
    if ($target && $source !== $target && 'page' === get_post_type($target) && 'publish' === get_post_status($target) && !(int) get_post_meta($target, 'yea_award_portal_target', true)) {
        wp_safe_redirect(get_permalink($target), 302);
        exit;
    }
});

/** Keep only API error details; never store tokens, request payloads or uploaded files. */
add_action('add-on-cf7-for-notion/notion-api/response', function ($route, $pretty, $response) {
    if ('pages' === $route && is_wp_error($response)) {
        set_transient('yea_award_notion_last_error', array(
            'time' => gmdate('c'), 'code' => isset($pretty->code) ? $pretty->code : $response->get_error_code(),
            'message' => isset($pretty->message) ? $pretty->message : $response->get_error_message(),
        ), DAY_IN_SECONDS);
    }
}, 10, 3);

/** Administrator-only delivery check: synthetic values, no CF7 submit or email. */
function yea_award_notion_delivery_check($form, $database, $columns) {
    $mapper = \WPC_WPCF7_NTN\WPCF7_Field_Mapper::get_instance();
    $mapped = \WPC_WPCF7_NTN\Helpers\get_mapped_tags_from_contact_form($form, $columns);
    $mapped = $mapper->filter_mapped_tags($mapped);
    $tags = array();
    foreach ($form->scan_form_tags() as $tag) { $tags[$tag->name] = $tag; }
    $values = array();
    foreach ($mapped as $name => $field) {
        $value = 'Temporary integration check';
        if ('title' === $field['notion_field_type']) { $value = 'YEA delivery check ' . gmdate('Y-m-d H:i:s') . ' (temporary)'; }
        if ('email' === $field['type']) { $value = 'integration-check@example.test'; }
        if ('tel' === $field['type']) { $value = '+355000000000'; }
        if ('select' === $field['type']) { $value = array($tags[$name]->values[0]); }
        if ('file' === $field['type']) {
            $value = array(home_url('/wp-content/uploads/2024/11/YEA-Awards-2025-Vol.-9_Info.pdf'));
        }
        $values[$field['notion_field_id']] = (object) array(
            'field_value' => $mapper->get_formatted_field_value($field['type'], $field['notion_field_type'], $value),
            'field_type' => $field['notion_field_type'], 'column_id' => $field['notion_field_id'],
        );
    }
    $fields = \WPC_WPCF7_NTN\Helpers\prepare_fields_for_notion($values, $database);
    $api = \WPC_WPCF7_NTN\wpconnect_wpcf7_notion_get_api();
    $result = $api->add_database_row($database, $fields);
    if (is_wp_error($result)) {
        return new WP_Error('yea_delivery_failed', 'Notion rejected the delivery check.', array('status' => 502, 'notion_error' => get_transient('yea_award_notion_last_error')));
    }
    $service = \WPC_WPCF7_NTN\WPCF7_Notion_Service::get_instance();
    $cleanup = new class($service->get_api_key()) extends \WPC_WPCF7_NTN\API_Notion {
        public function archive_check($id) { return $this->request('pages/' . $id, array('archived' => true), 'PATCH'); }
    };
    $archived = $cleanup->archive_check($result->id);
    return array('delivered' => true, 'test_record_id' => $result->id, 'archived' => !is_wp_error($archived), 'property_count' => count($fields));
}
