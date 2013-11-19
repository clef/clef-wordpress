<?php

function plugin_symlink_fix( $url, $path, $plugin ) {
    // Do it only for this plugin
    
    if ( strstr( $plugin,  'wpclef' ) ) {
        return str_replace( dirname( $plugin ), '/' . basename( dirname( $plugin ) ), $url );
    }

    return $url;
}
add_filter( 'plugins_url', 'plugin_symlink_fix', 10, 3 );