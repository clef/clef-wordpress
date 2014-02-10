<?php
require_once(CLEF_PATH . 'includes/class.clef-settings.php');

class ClefNetworkAdmin extends ClefAdmin {
    private static $instance = null;

    private $settings;

    private function __construct($settings) {
        $this->settings = $settings;
        $this->initialize_hooks();
    }

    public function initialize_hooks() {
        add_action('admin_init', array($this, "setup_plugin"));
        add_action('admin_init', array($this, "settings_form"));
        add_action('admin_enqueue_scripts', array($this, "admin_enqueue_scripts"));
        add_action('admin_enqueue_styles', array($this, "admin_enqueue_styles"));
        add_action('show_user_profile', array($this, "show_user_profile"));
        add_action('edit_user_profile', array($this, "show_user_profile"));
        add_action('edit_user_profile_update', array($this, 'edit_user_profile_update'));
        add_action('personal_options_update', array($this, 'edit_user_profile_update'));
        add_action('admin_notices', array($this, 'edit_profile_errors'));
        add_action('clef_hook_admin_menu', array($this, 'hook_admin_menu'));

        // MULTISITE
        if ($this->network_active()) {
            add_action('network_admin_edit_clef', array($this, "general_settings_edit"), 10, 0);
            add_action('network_admin_edit_clef_multisite', array($this, "multisite_settings_edit"), 10, 0);
        }
    }

    public function hook_admin_menu() {
        if ($this->network_active()) {
            add_action('network_admin_menu', array($this, "admin_menu"));
        }
    }

    public function network_active() {
        if ( ! function_exists( 'is_plugin_active_for_network' ) )
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

        return is_plugin_active_for_network('wpclef/wpclef.php');
    }

    public function admin_menu() {
        add_menu_page("Clef", "Clef", "manage_options", 'clef', array($this, 'general_settings'));
        add_submenu_page('clef','Settings','Settings', "manage_options",'clef', array($this, 'general_settings'));
        add_submenu_page("clef", "Multisite Options", "Multisite Options", "manage_options", 'clef_multisite', array($this, 'multisite_settings'));
    }

    public function general_settings() {
        $form = ClefSettings::forID(self::FORM_ID, CLEF_OPTIONS_NAME, $this->settings);
        $form->renderBasicForm('Clef Settings', Settings_API_Util::ICON_SETTINGS);  
    }

    public function general_settings_edit() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'clef-options')) {
            die("Security check; nonce failed.");
        }

        $form = ClefSettings::forID(self::FORM_ID, CLEF_OPTIONS_NAME, $this->settings);

        $input = $form->validate($_POST['wpclef']);

        foreach ($input as $key => $value) {
            $this->settings->set($key, $value);
        }

        wp_redirect(add_query_arg(array('page' => 'clef', 'updated' => 'true'), network_admin_url('admin.php')));
        exit();
    }

    public function multisite_settings() {
        $form_url = "edit.php?action=clef_multisite";
        if (get_site_option(self::MS_ENABLED_OPTION)) {
            $allow_override = get_site_option(self::MS_ALLOW_OVERRIDE_OPTION);
            echo ClefUtils::render_template('network_admin/multisite-settings-enabled.tpl', array(
                "form_url" => $form_url,
                "allow_override" => $allow_override
            ));
        } else {
            echo ClefUtils::render_template('network_admin/multisite-settings-disabled.tpl', array(
                "form_url" => $form_url
            ));
        }
    }

    public function multisite_settings_edit() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'clef_multisite')) {
            die("Security check; nonce failed.");
        }


        if (isset($_POST['allow_override_form'])) {
            $value = isset($_POST['allow_override']);
            update_site_option(self::MS_ALLOW_OVERRIDE_OPTION, $value);
        }

        $enabled = get_site_option(self::MS_ENABLED_OPTION);
        
        if (isset($_POST['disable']) || isset($_POST['enable'])) {
            update_site_option(self::MS_ENABLED_OPTION, !$enabled);
        }

        if ($enabled) {
            // TODO: actions for when network wide multisite is disabled
        } else {
            // TODO: actions for when network wide multiside is enabled
        }

        wp_redirect(add_query_arg(array('page' => 'clef_multisite', 'updated' => 'true'), network_admin_url('admin.php')));
        exit();
    }

    public function setup_plugin() {
        if (is_network_admin() && get_site_option("Clef_Activated")) {
            delete_site_option("Clef_Activated");

            if (!add_site_option(self::MS_ENABLED_OPTION, true)) {
                update_site_option(self::MS_ENABLED_OPTION, true);
            }

            wp_redirect(add_query_arg(array('page' => $this->settings->settings_path), network_admin_url('admin.php')));

            exit();
        }
    }

    public static function start($settings) {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self($settings);
        }
        return self::$instance;
    }
}

?>
