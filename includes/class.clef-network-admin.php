<?php
require_once(CLEF_PATH . 'includes/class.clef-settings.php');

class ClefNetworkAdmin extends ClefAdmin {
    private static $instance = null;
    const MULTISITE_SETTINGS_ACTION = "clef_multisite_settings";

    protected function __construct($settings) {
        $this->settings = $settings;

        if (is_network_admin()) {
            $this->initialize_hooks();

            require_once(CLEF_PATH . "/includes/lib/ajax-settings/ajax-settings.php");
            $this->ajax_settings = AjaxSettings::start();
        }

        global $clef_ajax;
        $clef_ajax->add_action(
            self::MULTISITE_SETTINGS_ACTION,
            array($this, 'ajax_multisite_options'),
            array( 'capability' => 'manage_network_options')
        );
    }

    public function initialize_hooks() {
        add_action('admin_init', array($this, "setup_plugin"));
        add_action('admin_init', array($this, "settings_form"));
        add_action('admin_enqueue_scripts', array($this, "admin_enqueue_scripts"));
        add_action('clef_hook_admin_menu', array($this, 'hook_admin_menu'));

        // MULTISITE
        if ($this->network_active()) {
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
            array($this, 'general_settings'),
            CLEF_URL . 'assets/dist/img/gradient_icon_16.png'
        );
    }

    public function general_settings($options=array()) {
        $options['isNetworkSettings'] = true;
        $options['isUsingIndividualSettings'] = false;
        $options['network_wide'] = true;
        parent::general_settings($options);
    }

    public function ajax_multisite_options() {
        if (isset($_REQUEST['allow_override'])) {
            update_site_option(ClefInternalSettings::MS_ALLOW_OVERRIDE_OPTION, (bool) $_REQUEST['allow_override']);
        }

        return array("success" => true);
    }

    public function multisite_settings_edit() {
        if (!is_super_admin()) die(__('Cheatin&#8217; uh?'));

        if (!wp_verify_nonce($_POST['_wpnonce'], 'clef_multisite')) {
            die(__("Security check; nonce failed.", "wpclef"));
        }

        if (isset($_POST['allow_override_form'])) {
            update_site_option(ClefInternalSettings::MS_ALLOW_OVERRIDE_OPTION, isset($_POST['allow_override']));
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

            if (!$this->settings->is_configured()) {
                wp_redirect(add_query_arg(array('page' => $this->settings->settings_path), network_admin_url('admin.php')));
                exit();
            }
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
