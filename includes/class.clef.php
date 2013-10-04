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
    }
    
    public static function disable_lost_password_form() {
        if (!empty($_POST['user_login'])) {
            $user = get_user_by( 'login', $_POST['user_login'] );
            
            if ( (self::setting( 'clef_password_settings_disable_passwords' ) && get_user_meta($user->ID, 'clef_id')) || (self::setting( 'clef_password_settings_force' ) == 1)) {
                $_SESSION['Clef_Messages'][] = "Lost password resets have been disabled.";
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

    public static function create_table($name) {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $tablename = $wpdb->prefix . $name;
        $sql = "CREATE TABLE $tablename " . self::$TABLES[$name];
        dbDelta($sql);
    }

    public static function activate_plugin($network) {
        add_site_option("Clef_Activated", true);
    }

    public static function deactivate_plugin($network) {

    }
    
    public static function uninstall_plugin() {
        delete_site_option(CLEF_OPTIONS_NAME);
        if (current_user_can( 'delete_plugins' )) { 
            global $wpdb;
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE meta_key = %s", 'clef_id' ) );
            delete_site_option(CLEF_OPTIONS_NAME);
        }
    }

}

?>