<?php
require_once(CLEF_PATH . 'includes/class.clef-settings.php');

class ClefNetworkAdmin extends ClefAdmin {
    private static $instance = null;
    const MULTISITE_SETTINGS_NONCE_NAME = "clef_multisite_settings";

    protected function __construct($settings) {
        $this->settings = $settings;

        if (is_network_admin()) {
            $this->initialize_hooks();
        }

        add_action('wp_ajax_clef_multisite_options', array($this, 'ajax_multisite_options'));
        require_once(CLEF_PATH . "/includes/lib/ajax-settings/ajax-settings.php");
        $this->ajax_settings = AjaxSettings::start();
    }

    public function initialize_hooks() {
        add_action('admin_init', array($this, "setup_plugin"));
        add_action('admin_init', array($this, "settings_form"));
        add_action('admin_enqueue_scripts', array($this, "admin_enqueue_scripts"));
        add_action('show_user_profile', array($this, "show_user_profile"));
        add_action('edit_user_profile', array($this, "show_user_profile"));
        add_action('edit_user_profile_update', array($this, 'edit_user_profile_update'));
        add_action('personal_options_update', array($this, 'edit_user_profile_update'));
        add_action('admin_notices', array($this, 'edit_profile_errors'));
        add_action('clef_hook_admin_menu', array($this, 'hook_admin_menu'));

        // MULTISITE
        if ($this->network_active()) {
            // remove_all_actions('network_admin_edit_clef_multisite');
            add_action('network_admin_edit_clef_multisite', array($this, "multisite_settings_edit"), 10, 0);
        }
    }

    public function admin_enqueue_scripts($hook) {
        parent::admin_enqueue_scripts($hook);
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
        add_menu_page(
            "Clef", 
            "Clef", 
            "manage_options", 
            'clef', 
            array($this, 'general_settings'));
        add_submenu_page(
            'clef',
            'Settings',
            'Settings', 
            "manage_options",
            'clef', 
            array($this, 'general_settings'));
    }

    public function general_settings($options=array()) {
        $options['isNetworkSettings'] = true;
        $options['isUsingIndividualSettings'] = false;
        $options['network_wide'] = true;
        parent::general_settings($options);
    }

    public function ajax_multisite_options() {
        if (!is_super_admin()) wp_send_json(array("error" => "invalid user"));

        $settings = json_decode(file_get_contents( "php://input" ), true);

        if (!wp_verify_nonce($settings['_wpnonce'], $this::MULTISITE_SETTINGS_NONCE_NAME)) {
            wp_send_json(array("error" => "invalid nonce"));
        }

        if (isset($settings['allow_override'])) {
            update_site_option(ClefInternalSettings::MS_ALLOW_OVERRIDE_OPTION, $settings['allow_override']);
        }

        wp_send_json(array("success" => true));
    }

    public function multisite_settings_edit() {
        if (!is_super_admin()) die("You're not a super admin!");

        if (!wp_verify_nonce($_POST['_wpnonce'], 'clef_multisite')) {
            die("Security check; nonce failed.");
        }

        if (isset($_POST['allow_override_form'])) {
            $value = isset($_POST['allow_override']);
            update_site_option(ClefInternalSettings::MS_ALLOW_OVERRIDE_OPTION, $value);
        }

        $enabled = get_site_option(ClefInternalSettings::MS_ENABLED_OPTION);
        
        if (isset($_POST['disable']) || isset($_POST['enable'])) {
            update_site_option(ClefInternalSettings::MS_ENABLED_OPTION, !$enabled);
        }

        if ($enabled) {
            // TODO: actions for when network wide multisite is disabled
        } else {
            // TODO: actions for when network wide multiside is enabled
        }

        wp_redirect(add_query_arg(array('page' => $this->settings->settings_path, 'updated' => 'true'), network_admin_url('admin.php')));
        exit();
    }

    public function setup_plugin() {
        if (is_network_admin() && get_site_option("Clef_Activated")) {
            delete_site_option("Clef_Activated");

            if (!add_site_option(ClefInternalSettings::MS_ENABLED_OPTION, true)) {
                update_site_option(ClefInternalSettings::MS_ENABLED_OPTION, true);
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
