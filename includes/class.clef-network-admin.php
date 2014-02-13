<?php
require_once(CLEF_PATH . 'includes/class.clef-settings.php');

class ClefNetworkAdmin extends ClefAdmin {
    private static $instance = null;

    protected function __construct($settings) {
        $this->settings = $settings;
        $this->initialize_hooks();

        require_once(CLEF_PATH . "/includes/lib/ajax-settings/ajax-settings.php");
        new AjaxSettings(array( 
            "options_name" => CLEF_OPTIONS_NAME, 
            "initialize" => false, 
            "base_url" => CLEF_URL . "/includes/lib/ajax-settings/",
            "formSelector" => "#clef-form",
            "network_wide" => true
        ));
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
            add_action('wp_ajax_clef_multisite_options', array($this, 'ajax_multisite_options'));
            add_action('network_admin_edit_clef', array($this, "general_settings_edit"), 10, 0);
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
        add_submenu_page(
            "clef", 
            "Multisite Options", 
            "Multisite Options", 
            "manage_options", 
            'clef_multisite', 
            array($this, 'multisite_settings'));
    }

    public function general_settings() {
        $network_settings_enabled = get_site_option(ClefInternalSettings::MS_ENABLED_OPTION);
        $allow_single_site_settings = get_site_option(ClefInternalSettings::MS_ALLOW_OVERRIDE_OPTION);

        $form = ClefSettings::forID(self::FORM_ID, CLEF_OPTIONS_NAME, $this->settings);

        $options = $this->settings->get_site_option();
        $setup = array();
        $setup['siteName'] = get_option('blogname');
        $setup['siteDomain'] = get_option('siteurl');
        $setup['source'] = "wordpress";
        if (get_site_option("bruteprotect_installed_clef")) {
            $setup['source'] = "bruteprotect";
        }
        $setup['_wp_nonce_connect_clef'] = wp_create_nonce(self::CONNECT_CLEF_NONCE_NAME);
        $setup['_wp_nonce_invite_users'] = wp_create_nonce(self::INVITE_USERS_NONCE_NAME);
        $options['setup'] = $setup;
        $options['configured'] = $this->settings->is_configured();
        $options['clefBase'] = CLEF_BASE;
        $options['settings_path'] = $this->settings->settings_path;
        $options['options_name'] = CLEF_OPTIONS_NAME;
        $options['is_network_settings'] = true;
        $options['overridden_by_network_settings'] = false;
        $options['is_multisite'] = is_multisite();
        $options['network_wide'] = $options['is_network_settings'];
        $options['network_settings_enabled'] = $network_settings_enabled;
        $options['allow_single_site_settings'] = $allow_single_site_settings;


        echo ClefUtils::render_template('admin/settings.tpl', array(
            "form" => $form,
            "options" => $options
        ));
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

        wp_redirect(add_query_arg(array('page' => $this->settings->settings_path, 'updated' => 'true'), network_admin_url('admin.php')));
        exit();
    }

    public function multisite_settings() {
        $form_url = "edit.php?action=clef_multisite";
        if (get_site_option(ClefInternalSettings::MS_ENABLED_OPTION)) {
            $allow_override = get_site_option(ClefInternalSettings::MS_ALLOW_OVERRIDE_OPTION);
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

    public function ajax_multisite_options() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'clef_multisite')) {
            wp_send_json(array("error" => "invalid nonce"));
        }


        if (isset($_POST['allow_override_form'])) {
            $value = isset($_POST['allow_override']);
            update_site_option(ClefInternalSettings::MS_ALLOW_OVERRIDE_OPTION, $value);
        }

        $enabled = get_site_option(ClefInternalSettings::MS_ENABLED_OPTION);
        
        if (isset($_POST['disable']) || isset($_POST['enable'])) {
            update_site_option(ClefInternalSettings::MS_ENABLED_OPTION, !$enabled);
        }

        wp_send_json(array("success" => true));
    }
    public function multisite_settings_edit() {
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
