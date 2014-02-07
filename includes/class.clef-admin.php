<?php

class ClefAdmin extends ClefBase {

    const FORM_ID = "clef";
    const CLASS_NAME = "ClefAdmin";

    public static function init() {
        add_action('admin_init', array(__CLASS__, "other_install"));
        add_action('admin_init', array(__CLASS__, "setup_plugin"));
        add_action('admin_init', array(__CLASS__, "settings_form"));
        add_action('admin_init', array(__CLASS__, "multisite_settings_edit"));
        add_action('admin_init', array(__CLASS__, "connect_clef_account"));

        add_action('admin_menu', array(__CLASS__, "admin_menu"));

        add_action('admin_enqueue_scripts', array(__CLASS__, "admin_enqueue_scripts"));
        add_action('admin_enqueue_styles', array(__CLASS__, "admin_enqueue_styles"));

        add_action('admin_notices', array(__CLASS__, 'display_messages') );

        add_action('show_user_profile', array(__CLASS__, "show_user_profile"));
        add_action('edit_user_profile', array(__CLASS__, "show_user_profile"));

        add_action('edit_user_profile_update', array(__CLASS__, 'edit_user_profile_update'));
        add_action('personal_options_update', array(__CLASS__, 'edit_user_profile_update'));

        add_action('options_edit_clef_multisite', array(__CLASS__, "multisite_settings_edit"), 10, 0);

        require_once(CLEF_PATH . "/includes/lib/ajax-settings/ajax-settings.php");
        new AjaxSettings(array( 
            "options_name" => CLEF_OPTIONS_NAME, 
            "initialize" => false, 
            "base_url" => CLEF_URL . "/includes/lib/ajax-settings/"
        ));

        ClefBadge::hook_onboarding();
    }

    public static function display_messages() {
        settings_errors( CLEF_OPTIONS_NAME );
    }

    public static function admin_enqueue_scripts($hook) {
        $exploded_path = explode('/', $hook);
        $settings_page_name = array_shift($exploded_path);

        // only register clef logout if user is a clef user
        if (get_user_meta(wp_get_current_user()->ID, 'clef_id')) {
            self::register_script('clef_heartbeat');
            wp_enqueue_script('wpclef_logout');
        }
        
        if(preg_match("/clef/", $settings_page_name)) {
            Clef::register_styles();

            $ident = self::register_script('settings', array('jquery', 'backbone', 'underscore'));
            wp_enqueue_script($ident);
        } 
    }

    public static function show_user_profile() {
        $connected = !!get_user_meta(wp_get_current_user()->ID, "clef_id", true);
        if (!$connected) {
            $app_id = self::setting( 'clef_settings_app_id' );
            $redirect_url = add_query_arg(
                array(
                    'state' => wp_create_nonce("connect_clef"),
                    'clef' => true,
                    'connecting' => true
                ), get_edit_profile_url(wp_get_current_user()->ID)
            );
        }
        include CLEF_TEMPLATE_PATH."user_profile.tpl.php";
    }

    public static function edit_user_profile_update($user_id) {
        if (isset($_POST['remove_clef']) && $_POST['remove_clef']) {
            self::dissociate_clef_id($user_id);
        }
    }

    public static function connect_clef_account() {
        if (isset($_REQUEST['clef']) && isset($_REQUEST['connecting']) &&
        isset($_REQUEST['code'])) {

            // do state CSRF check
            if (!isset($_GET['state']) || !wp_verify_nonce($_GET['state'], 'connect_clef')) {
                die();
            }

            try {
                $info = self::exchange_oauth_code_for_info($_REQUEST['code']);
            } catch (LoginException $e) {
                add_settings_error(
                    CLEF_OPTIONS_NAME,
                    esc_attr("settings_updated"),
                    __("Error while connecting your Clef account: ", "clef") . $e->getMessage(),
                    "updated"
                );
                return;
            }

            self::associate_clef_id($info->id);
            // Log in the user
            $_SESSION['logged_in_at'] = time();

            add_settings_error(
                CLEF_OPTIONS_NAME,
                esc_attr("settings_updated"),
                __("Successfully connected your Clef account.", "clef"),
                "updated"
            );
        }
    }

    public static function admin_menu() {
        // if the single site override of settings is not allowed
        // let's not add anything to the menu
        if (self::multisite_disallow_settings_override()) return;

        if (self::bruteprotect_active() && get_site_option("bruteprotect_installed_clef")) {
            add_submenu_page("bruteprotect-config", "Clef", "Clef", "manage_options", 'clef', array(__CLASS__, 'general_settings'));
            if (self::is_multisite_enabled() && self::individual_settings()) {
                add_submenu_page("bruteprotect-config", __("Clef Multisite Options", 'clef'), __("Clef Enable Multisite", 'clef'), "manage_options", 'clef_multisite', array(__CLASS__, 'multisite_settings'));
            }
        } else {
            add_menu_page(__("Clef", 'clef'), __("Clef", 'clef'), "manage_options", 'clef', array(__CLASS__, 'general_settings'));
            if (self::is_multisite_enabled() && self::individual_settings()) {
                add_submenu_page('clef', __('Settings', 'clef'), __('Settings', 'clef'),'manage_options','clef', array(__CLASS__, 'general_settings'));
                add_submenu_page("clef", __("Multisite Options", 'clef'), __("Enable Multisite", 'clef'), "manage_options", 'clef_multisite', array(__CLASS__, 'multisite_settings'));
            } 

            if (!self::bruteprotect_active() && !is_multisite())  {
                add_submenu_page('clef', __('Add Additional Security', 'clef'), __('Additional Security', 'clef'), 'manage_options', 'clef_other_install', array(__CLASS__, 'other_install_settings'));
            }
        } 
        
    }

    public static function general_settings() {
        if (self::individual_settings()) {
            $form = ClefSettings::forID(self::FORM_ID, CLEF_OPTIONS_NAME);

            $options = self::get_settings();
            $setup = array();
            $setup['siteName'] = get_option('blogname');
            $setup['siteDomain'] = get_option('siteurl');
            $setup['source'] = "wordpress";
            if (get_site_option("bruteprotect_installed_clef")) {
                $setup['source'] = "bruteprotect";
            }
            $options['setup'] = $setup;
            $options['configured'] = self::is_configured();
            $options['clefBase'] = CLEF_BASE;

            include CLEF_TEMPLATE_PATH . "admin/settings.tpl.php";  
        } else {
            include CLEF_TEMPLATE_PATH . "admin/multsite-enabled.tpl.php";
        }
    }

    public static function multisite_settings() {
        include CLEF_TEMPLATE_PATH . "admin/multisite-disabled.tpl.php";
    }

    public static function other_install_settings() {
        require_once 'lib/plugin-installer/installer.php';

        $installer = new PluginInstaller( array( "name" => "BruteProtect", "slug" => "bruteprotect" ) );

        // pass in current URL as base URL
        $url = $installer->url();

        include CLEF_TEMPLATE_PATH . "admin/other-install.tpl.php";
    }

    public static function other_install() {
        require_once 'lib/plugin-installer/installer.php';

        $installer = new PluginInstaller( array( 
            "name" => "BruteProtect", 
            "slug" => "bruteprotect",
            "redirect" => admin_url( "admin.php?page=bruteprotect-config" )
        ) );

        if ($installer->called()) {
            $installer->install_and_activate();
        }
    }

    public static function settings_form() {
        $form = ClefSettings::forID(self::FORM_ID, CLEF_OPTIONS_NAME);

        self::add_api_settings($form, self::is_configured());

        $pw_settings = $form->addSection('clef_password_settings', __('Password Settings'), '');
        $pw_settings->addField('disable_passwords', __('Disable passwords for Clef users', "clef"), Settings_API_Util_Field::TYPE_CHECKBOX);
        $pw_settings->addField(
            'disable_certain_passwords', 
            __('Disable certain passwords', "clef"), 
            Settings_API_Util_Field::TYPE_SELECT,
            "Disabled",
            array( "options" => array("Disabled", "Editor", "Author", "Administrator", "Super Administrator" ) )
        );
        $pw_settings->addField('force', __('Disable all passwords', "clef"), Settings_API_Util_Field::TYPE_CHECKBOX);

        $pw_settings->addField(
            'xml_allowed', 
            __('Allow XML'),
            Settings_API_Util_Field::TYPE_CHECKBOX
        );

        $override_settings = $form->addSection('clef_override_settings', __('Override Settings'), array(__CLASS__, 'print_override_descript'));
        $override_settings->addField('key', "Override key", Settings_API_Util_Field::TYPE_TEXTFIELD); 

        $support_clef_settings = $form->addSection('support_clef', __('Support Clef', "clef"), array(__CLASS__, 'print_support_clef_descript'));
        $support_clef_settings->addField(
            'badge', 
            __("Support Clef by automatically adding a link!", "clef"),
            Settings_API_Util_Field::TYPE_SELECT,
            "disabled",
            array("options" => array(array("Badge", "badge") , array("Link", "link"), array("Disabled", "disabled")))
        );

        return $form;
    }

    public static function multisite_settings_edit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] == 'clef_multisite') {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'clef_multisite')) {
                die("Security check; nonce failed.");
            }

            $override = get_option(self::MS_OVERRIDE_OPTION);

            if (!add_option(self::MS_OVERRIDE_OPTION, !$override)) {
                update_option(self::MS_OVERRIDE_OPTION, !$override);
            }

            wp_redirect(add_query_arg(array('page' => 'clef', 'updated' => 'true'), admin_url('admin.php')));
            exit();
        }
    }

    public static function setup_plugin() {
        if (is_admin() && get_option("Clef_Activated")) {
            delete_option("Clef_Activated");

            if (self::bruteprotect_active()) {
                wp_redirect(admin_url('/admin.php?page=clef'));
            } else {
                wp_redirect(admin_url('/options.php?page=clef'));
            }
            exit();
        }
    }

    public static function print_api_descript() {
        _e('<p>For more advanced settings, log in to your <a href="https://developer.getclef.com">Clef dashboard</a> or contact <a href="mailto:support@getclef.com">support@getclef.com</a>.</p>', 'clef');
    }

    public static function print_override_descript() {
        _e("<p>If you choose to allow only Clef logins on your site, you can set an 'override' URL. </br> With this URL, you'll be able to log into your site with passwords even if Clef-only mode is enabled.</p>", 'clef');
    }

    public static function print_support_clef_descript() {
        _e("<p>Clef is, and will always be, free for you and your users. We'd really appreciate it if you'd support us (and show visitors they are browsing a secure site) by adding a link to Clef in your site footer!</p>", "clef");
    }

    public static function add_api_settings($form, $configured=false) {
        $settings = $form->addSection('clef_settings', __('API Settings'), array(__CLASS__, 'print_api_descript'));
        $settings->addField('app_id', __('Application ID', "clef"), Settings_API_Util_Field::TYPE_TEXTFIELD);
        $settings->addField('app_secret', __('Application Secret', "clef"), Settings_API_Util_Field::TYPE_TEXTFIELD);
        if (!$configured) {
            $settings->addField('oauth_code', '', Settings_API_Util_Field::TYPE_HIDDEN, '');
        }
        return $settings;
    }
}
