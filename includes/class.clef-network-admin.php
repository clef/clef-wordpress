<?php

class ClefNetworkAdmin extends ClefAdmin {

    const CLASS_NAME = "ClefNetworkAdmin";

    public static function init() {
        add_action('admin_init', array(__CLASS__, "setup_plugin"));
        add_action('admin_init', array(__CLASS__, "settings_form"));
        add_action('admin_enqueue_scripts', array(__CLASS__, "admin_enqueue_scripts"));
        add_action('admin_enqueue_styles', array(__CLASS__, "admin_enqueue_styles"));
        add_action('show_user_profile', array(__CLASS__, "show_user_profile"));
        add_action('edit_user_profile', array(__CLASS__, "show_user_profile"));
        add_action('edit_user_profile_update', array(__CLASS__, 'edit_user_profile_update'));
        add_action('personal_options_update', array(__CLASS__, 'edit_user_profile_update'));
        add_action('admin_notices', array(__CLASS__, 'edit_profile_errors'));

        // MULTISITE
        if (self::network_active()) {
            add_action('network_admin_menu', array(__CLASS__, "admin_menu"));
            add_action('network_admin_edit_clef', array(__CLASS__, "general_settings_edit"), 10, 0);
            add_action('network_admin_edit_clef_multisite', array(__CLASS__, "multisite_settings_edit"), 10, 0);
        }
    }

    public static function network_active() {
        if ( ! function_exists( 'is_plugin_active_for_network' ) )
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

        return is_plugin_active_for_network('wpclef/wpclef.php');
    }

    public static function admin_menu() {
        add_menu_page("Clef", "Clef", 0, 'clef', array(__CLASS__, 'general_settings'));
        add_submenu_page('clef','Settings','Settings','clef','clef', array(__CLASS__, 'general_settings'));
        add_submenu_page("clef", "Multisite Options", "Multisite Options", 0, 'clef_multisite', array(__CLASS__, 'multisite_settings'));
    }

    public static function general_settings() {
        $form = ClefSettings::forID(self::FORM_ID, CLEF_OPTIONS_NAME);
        $form->renderBasicForm('Clef Settings', Settings_API_Util::ICON_SETTINGS);  
    }

    public static function general_settings_edit() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'clef-options')) {
            die("Security check; nonce failed.");
        }

        $form = ClefSettings::forID(self::FORM_ID, CLEF_OPTIONS_NAME);

        $input = $form->validate($_POST['wpclef']);

        foreach ($input as $key => $value) {
            self::setting($key, $value);
        }

        wp_redirect(add_query_arg(array('page' => 'clef', 'updated' => 'true'), network_admin_url('admin.php')));
        exit();
    }

    public static function multisite_settings() {
        $form_url = "edit.php?action=clef_multisite";
        if (get_site_option(self::MS_ENABLED_OPTION)) {
            include CLEF_TEMPLATE_PATH . 'network_admin/multisite-settings-enabled.tpl.php';
        } else {
            include CLEF_TEMPLATE_PATH . 'network_admin/multisite-settings-disabled.tpl.php';
        }
    }

    public static function multisite_settings_edit() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'clef_multisite')) {
            die("Security check; nonce failed.");
        }

        $enabled = get_site_option(self::MS_ENABLED_OPTION);

        if ($enabled) {
            // TODO: actions for when network wide multisite is disabled
            error_log("DISABLED!");
        } else {
            // TODO: actions for when network wide multiside is enabled
            error_log("ENABLED");
        }

        if (!add_site_option(self::MS_ENABLED_OPTION, !$enabled)) {
            update_site_option(self::MS_ENABLED_OPTION, !$enabled);
        }

        wp_redirect(add_query_arg(array('page' => 'clef_multisite', 'updated' => 'true'), network_admin_url('admin.php')));
        exit();
    }

    public static function setup_plugin() {
        if (is_admin() && get_site_option("Clef_Activated")) {
            delete_site_option("Clef_Activated");

            wp_redirect(network_admin_url('admin.php?page=clef'));

            exit();
        }
    }

    public static function print_api_descript() {
        echo '<p>To manage the Clef application that syncs with your plugin, please visit <a href="https://developer.getclef.com">the Clef developer site</a>.</p>';
    }
}

?>