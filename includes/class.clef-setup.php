<?php

/**
 * Handles activation, deactivation, and uninstall when Clef is the base 
 * plugin.
 *
 * @package Clef
 * @since 2.0
 */
class ClefSetup {
    public static $meta_keys = array(
        'clef_id', 
        'clef_invite_code',
        'logged_out_at',
        'clef_logins',
        'clef_hide_waltz_badge',
        'clef_hide_waltz_prompt'
    );

    public static function activate_plugin($network) {
        if (is_network_admin()) {
            add_site_option("Clef_Activated", true);
        } else {
            add_option("Clef_Activated", true);
        }
    }

    public static function deactivate_plugin($network) { }
    
    /**
     * Clean up Clef metadata and site options.
     */
    public static function uninstall_plugin() {
        if (current_user_can( 'delete_plugins' )) { 
            global $wpdb;
            foreach (self::$meta_keys as $meta_key) {
                $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE meta_key = %s", $meta_key) );
            }
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
