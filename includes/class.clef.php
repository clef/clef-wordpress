<?php

class Clef extends ClefBase {

    private static $TABLES = array();

    public static function init() {

        if ( !session_id() ) {
            session_start();
        }

        if ( !isset( $_SESSION['Clef_Messages'] ) ) {
            $_SESSION['Clef_Messages'] = array();
        }

        add_action('lost_password', array( 'Clef', 'disable_lost_password_form' ) );
        add_action('lostpassword_post', array( 'Clef', 'disable_lost_password_form' ) );
        add_filter('wp_authenticate_user', array('Clef', 'clear_logout_hook'));

        if (is_network_admin()) {
            ClefNetworkAdmin::init();
        } else if (is_admin()) {
            ClefAdmin::init();
        }

        ClefLogin::init();
        ClefLogout::init();

        ClefBadge::hook_display();
 }
    
    public static function disable_lost_password_form() {
        if (!empty($_POST['user_login'])) {
            $user = get_user_by( 'login', $_POST['user_login'] );
            
            if ( (self::setting( 'clef_password_settings_disable_passwords' ) && get_user_meta($user->ID, 'clef_id')) || (self::setting( 'clef_password_settings_force' ) == 1)) {
                $_SESSION['Clef_Messages'][] = __("Lost password resets have been disabled.", 'clef');
                header("Location: " . wp_login_url());
                exit();
            }
        }
    }

    public static function clear_logout_hook($user) {
        if (isset($_SESSION['logged_in_at'])) {
            unset($_SESSION['logged_in_at']);
        }
        return $user;
    }

    public static function register_styles() {
        wp_register_style('wpclef', CLEF_URL . 'assets/css/wpclef.min.css', FALSE, '1.0.0');
        wp_enqueue_style('wpclef');
    }

    public static function create_table($name) {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $tablename = self::table_name($name);
        $sql = "CREATE TABLE $tablename " . self::$TABLES[$name];
        dbDelta($sql);
    }

    public static function drop_table($name) {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $tablename = self::table_name($name);
        $sql = "DROP TABLE IF EXISTS $tablename";
        $wpdb->query($sql);
    }

    public static function activate_plugin($network) {
        add_site_option("Clef_Activated", true);
    }

    public static function deactivate_plugin($network) {
        return;
    }
    
    public static function uninstall_plugin() {
        if (current_user_can( 'delete_plugins' )) { 
            global $wpdb;
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE meta_key = %s", 'clef_id' ) );
        }

        if (is_multisite() && is_network_admin()) {
            self::_multisite_uninstall();
        } else {
            delete_option(CLEF_OPTIONS_NAME);
        }
    }

    public static function _multisite_uninstall() {
        delete_site_option(CLEF_OPTIONS_NAME);
        delete_site_option(self::MS_OVERRIDE_OPTION);
        delete_site_option(self::MS_ENABLED_OPTION);
    }

    public static function update($version, $previous_version){
        $settings_changes = false;

        if ($previous_version) {
            if (version_compare($previous_version, "1.9.1.1", '<')) {
                ClefBadge::hide_prompt();
            }

            if (version_compare($previous_version, "1.9", '<')) {
               if (!$previous_version) {
                    $previous_version = $version;
               }
               self::setting('installed_at', $previous_version);
            }

            if (version_compare($previous_version, "1.8.0", '<')) {
                $settings_changes = array(
                    "clef_password_settings_override_key" => "clef_override_settings_key"
                );
            }
        } else {
            self::setting('installed_at', $version);
        }
        
        

        if ($settings_changes) {
            foreach ($settings_changes as $old_name => $new_name) {
                $value = self::setting($old_name);
                if ($value) {
                    self::setting($new_name, $value);
                    self::delete_setting($old_name);
                }
            }
        }

        self::setting("version", $version);
    }

}

?>
