<?php
/** Install the fixed, versioned language plugin bundled with this theme. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
add_action( 'rest_api_init', function () {
    register_rest_route( 'yea/v1', '/i18n/install', array(
        'methods' => 'POST',
        'permission_callback' => function () { return current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' ); },
        'callback' => function () {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            $source = get_template_directory() . '/bundled-plugins/yea-i18n';
            if ( ! is_file( $source . '/yea-i18n.php' ) ) { return new WP_Error( 'missing_bundle', 'Bundled plugin unavailable.', array( 'status' => 500 ) ); }
            if ( ! WP_Filesystem() ) { return new WP_Error( 'filesystem', 'WordPress filesystem unavailable.', array( 'status' => 500 ) ); }
            $destination = WP_PLUGIN_DIR . '/yea-i18n';
            global $wp_filesystem;
            if ( ! $wp_filesystem->is_dir( $destination ) && ! $wp_filesystem->mkdir( $destination ) ) { return new WP_Error( 'filesystem', 'Cannot create plugin directory.', array( 'status' => 500 ) ); }
            $copied = copy_dir( $source, $destination );
            if ( is_wp_error( $copied ) ) { return $copied; }
            $result = activate_plugin( 'yea-i18n/yea-i18n.php' );
            if ( is_wp_error( $result ) ) { return $result; }
            return array( 'installed' => true, 'active' => is_plugin_active( 'yea-i18n/yea-i18n.php' ), 'version' => get_plugin_data( $destination . '/yea-i18n.php' )['Version'] );
        },
    ) );
} );
