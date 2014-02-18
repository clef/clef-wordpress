<?php

/**
 * Handles activation, deactivation, and uninstall when Clef is the base 
 * plugin.
 *
 * @package Clef
 * @since 2.0
 */
class ClefSetup {
    public static function activate_plugin($network) {
        add_site_option("Clef_Activated", true);
    }

    public static function deactivate_plugin($network) { }
    
    /**
     * Clean up Clef metadata and site options.
     */
    public static function uninstall_plugin() {
        if (current_user_can( 'delete_plugins' )) { 
            global $wpdb;
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE meta_key = %s", 'clef_id' ) );
        }

        if (is_multisite() && is_network_admin()) {
            self::multisite_uninstall();
        } else {
            delete_option(CLEF_OPTIONS_NAME);
        }
    }

    private static function multisite_uninstall() {
        delete_site_option(CLEF_OPTIONS_NAME);
        delete_site_option(ClefInternalSettings::MS_OVERRIDE_OPTION);
        delete_site_option(ClefInternalSettings::MS_ENABLED_OPTION);
    }

    public static function register_plugin_hooks() {
        register_activation_hook(CLEF_PATH . 'wpclef.php', array('ClefSetup', 'activate_plugin'));
        register_deactivation_hook(CLEF_PATH . 'wpclef.php', array('ClefSetup', 'deactivate_plugin'));
        register_uninstall_hook(CLEF_PATH . 'wpclef.php', array('ClefSetup', 'uninstall_plugin'));
    }
}

?>
