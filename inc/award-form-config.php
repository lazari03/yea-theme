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
    if ('POST' === $request->get_method()) {
        $markup = $request->get_param('form');
        if (!is_string($markup) || '' === trim($markup)) {
            return new WP_Error('yea_form_required', 'Nonempty form markup is required.', array('status' => 400));
        }
        // Validate the file tag before making any remote schema change.
        $field = sanitize_key((string) $request->get_param('file_field'));
        $property = sanitize_text_field((string) $request->get_param('file_property'));
        $form->set_properties(array('form' => $markup));
        if ($field || $property) {
            $matching = array_filter($form->scan_form_tags(), function ($tag) use ($field) {
                return $field === $tag->name && 'file' === $tag->basetype;
            });
            if (!$field || !$property || !$matching) {
                return new WP_Error('yea_file_tag_required', 'A matching CF7 file field and Notion property name are required.', array('status' => 400));
            }
            $column = null;
            foreach ($columns as $candidate) {
                if ($candidate->name === $property) { $column = $candidate; break; }
            }
            if ($column && 'files' !== $column->type) {
                return new WP_Error('yea_property_type', 'The existing property is not a Files property; it will not be changed.', array('status' => 409));
            }
            if (!$column) {
                $service = \WPC_WPCF7_NTN\WPCF7_Notion_Service::get_instance();
                $api = new class($service->get_api_key()) extends \WPC_WPCF7_NTN\API_Notion {
                    public function add_files_property($database_id, $name) {
                        return $this->request('databases/' . $database_id, array('properties' => array($name => array('files' => (object) array()))), 'PATCH');
                    }
                };
                $result = $api->add_files_property($database, $property);
                if (is_wp_error($result)) {
                    return new WP_Error('yea_property_failed', 'Notion could not add the file property. Existing form settings are unchanged.', array('status' => 502));
                }
                $columns = \WPC_WPCF7_NTN\Helpers\get_notion_databases_columns($database, true);
                foreach ($columns as $candidate) {
                    if ($candidate->name === $property && 'files' === $candidate->type) { $column = $candidate; break; }
                }
                if (!$column) { return new WP_Error('yea_property_unconfirmed', 'The added property could not be confirmed. Retry after checking the database schema.', array('status' => 502)); }
            }
            $integration['mapping'][$field] = $column->id;
        }
        // Save properties directly. The add-on's legacy editor save hook reads
        // $_POST and clears mappings during JSON REST saves; do not invoke it.
        $form->set_properties(array('wpc_notion' => $integration));
        if (!$form->save()) { return new WP_Error('yea_save_failed', 'The form could not be saved.', array('status' => 500)); }
    }
    return array('id' => $form->id(), 'database_id' => $database, 'columns' => array_values($columns), 'mapping' => $integration['mapping']);
}
