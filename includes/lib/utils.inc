<?php
    if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
    }

    function plugin_symlink_fix( $url, $path, $plugin ) {
        // Do it only for this plugin
        if ( strstr( $plugin, basename(__FILE__) ) )
            return str_replace( dirname(__FILE__), '/' . basename( dirname( $plugin ) ), $url );

        return $url;
    }
    add_filter( 'plugins_url', 'plugin_symlink_fix', 10, 3 );
?>