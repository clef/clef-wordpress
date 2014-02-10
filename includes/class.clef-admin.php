<?php
require_once(CLEF_PATH . 'includes/class.clef-settings.php');
require_once(CLEF_PATH . 'includes/class.clef-invite-code.php');

class ClefAdmin {
    const FORM_ID = "clef";
    const CLASS_NAME = "ClefAdmin";

    private static $instance = null;

    private $settings;

    private function __construct($settings) {
        $this->settings = $settings;
        $this->initialize_hooks();
    }

    public function initialize_hooks() {
        add_action('admin_init', array($this, "other_install"));
        add_action('admin_init', array($this, "setup_plugin"));
        add_action('admin_init', array($this, "settings_form"));
        add_action('admin_init', array($this, "multisite_settings_edit"));
        add_action('admin_init', array($this, "connect_clef_account"));
        add_action('admin_init', array($this, "invite_users"));

        add_action('clef_hook_admin_menu', array($this, "hook_admin_menu"));

        add_action('admin_enqueue_scripts', array($this, "admin_enqueue_scripts"));
        add_action('admin_enqueue_styles', array($this, "admin_enqueue_styles"));

        add_action('admin_notices', array($this, 'display_messages') );

        add_action('show_user_profile', array($this, "show_user_profile"));
        add_action('edit_user_profile', array($this, "show_user_profile"));

        add_action('edit_user_profile_update', array($this, 'edit_user_profile_update'));
        add_action('personal_options_update', array($this, 'edit_user_profile_update'));

        add_action('options_edit_clef_multisite', array($this, "multisite_settings_edit"), 10, 0);

        require_once(CLEF_PATH . "/includes/lib/ajax-settings/ajax-settings.php");
        new AjaxSettings(array( 
            "options_name" => CLEF_OPTIONS_NAME, 
            "initialize" => false, 
            "base_url" => CLEF_URL . "/includes/lib/ajax-settings/",
            "formSelector" => "#clef-form"
        ));

        // Display the badge message, if appropriate
        do_action('clef_hook_onboarding');
    }

    public function hook_admin_menu() {
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    public function display_messages() {
        settings_errors( CLEF_OPTIONS_NAME );
    }

    public function admin_enqueue_scripts($hook) {
        $exploded_path = explode('/', $hook);
        $settings_page_name = array_shift($exploded_path);

        // only register clef logout if user is a clef user
        if (get_user_meta(wp_get_current_user()->ID, 'clef_id')) {
            ClefUtils::register_script('clef_heartbeat');
            wp_enqueue_script('wpclef_logout');
        }
        
        if(preg_match("/".$this->settings->settings_path."/", $settings_page_name)) {
            ClefUtils::register_styles();
            $ident = ClefUtils::register_script('settings', array('jquery', 'backbone', 'underscore'));
            wp_enqueue_script($ident);
        } 
    }

    public function show_user_profile($user) {
        if (!$user) {
            $user = wp_get_current_user();
        }
        $connected = !!get_user_meta($user->ID, "clef_id", true);
        if (!$connected) {
            $app_id = $this->settings->get( 'clef_settings_app_id' );
            $redirect_url = add_query_arg(
                array(
                    'state' => wp_create_nonce("connect_clef"),
                    'clef' => true,
                    'connecting' => true
                ), get_edit_profile_url(wp_get_current_user()->ID)
            );
        }
        echo ClefUtils::render_template('user_profile.tpl', array(
            "connected" => $connected,
            "app_id" => $app_id,
            "redirect_url" => $redirect_url
        ));
    }

    public function edit_user_profile_update($user_id) {
        if (isset($_POST['remove_clef']) && $_POST['remove_clef']) {
            ClefUtils::dissociate_clef_id($user_id);
        }
    }

    public function invite_users() {
        if (isset($_REQUEST['invite_users']) && $_REQUEST['invite_users']) {
            $other_users = get_users(array('exclude' => array(get_current_user_id())));
            $invite_codes = array();
            foreach ($other_users as $user) {
                $invite_code = new InviteCode($user);
                update_user_meta($user->ID, 'clef_invite_code', $invite_code);

                $invite_link = $invite_code->get_link();
                $to = $user->user_email;
                $subject = 'Set up Clef for your account';
                $message = ClefUtils::render_template('invite_email.tpl', array("invite_link" =>  $invite_link));

                add_filter('wp_mail_content_type', array('ClefUtils', 'set_html_content_type'));
                wp_mail($to, $subject, $message);
                remove_filter('wp_mail_content_type', array('ClefUtils', 'set_html_content_type'));
            }
        }
    }

    public function connect_clef_account() {
        if (isset($_REQUEST['clef']) && isset($_REQUEST['connecting']) &&
        isset($_REQUEST['code'])) {

            // do state CSRF check
            if (!isset($_GET['state']) || !wp_verify_nonce($_GET['state'], 'connect_clef')) {
                die();
            }

            try {
                $info = ClefUtils::exchange_oauth_code_for_info($_REQUEST['code'], $this->settings);
            } catch (LoginException $e) {
                add_settings_error(
                    CLEF_OPTIONS_NAME,
                    esc_attr("settings_updated"),
                    __("Error while connecting your Clef account: ", "clef") . $e->getMessage(),
                    "updated"
                );
                return;
            }

            ClefUtils::associate_clef_id($info->id);
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

    public function admin_menu() {
        // if the single site override of settings is not allowed
        // let's not add anything to the menu
        if ($this->settings->multisite_disallow_settings_override()) return;

        if ($this->bruteprotect_active() && get_site_option("bruteprotect_installed_clef")) {
            add_submenu_page("bruteprotect-config", "Clef", "Clef", "manage_options", $this->settings->settings_path, array($this, 'general_settings'));
            if ($this->settings->is_multisite_enabled() && $this->settings->use_individual_settings) {
                add_submenu_page("bruteprotect-config", __("Clef Multisite Options", 'clef'), __("Clef Enable Multisite", 'clef'), "manage_options", 'clef_multisite', array($this, 'multisite_settings'));
            }
        } else {
            add_menu_page(__("Clef", 'clef'), __("Clef", 'clef'), "manage_options", $this->settings->settings_path, array($this, 'general_settings'));
            if ($this->settings->is_multisite_enabled() && $this->settings->user_individual_settings) {
                add_submenu_page('clef', __('Settings', 'clef'), __('Settings', 'clef'),'manage_options', $this->settings->settings_path, array($this, 'general_settings'));
                add_submenu_page("clef", __("Multisite Options", 'clef'), __("Enable Multisite", 'clef'), "manage_options", 'clef_multisite', array($this, 'multisite_settings'));
            } 

            if (!$this->bruteprotect_active() && !is_multisite())  {
                add_submenu_page('clef', __('Add Additional Security', 'clef'), __('Additional Security', 'clef'), 'manage_options', 'clef_other_install', array($this, 'other_install_settings'));
            }
        } 
        
    }

    public function general_settings() {
        if ($this->settings->use_individual_settings) {
            $form = ClefSettings::forID(self::FORM_ID, CLEF_OPTIONS_NAME, $this->settings);

            $options = $this->settings->get_site_option();
            $setup = array();
            $setup['siteName'] = get_option('blogname');
            $setup['siteDomain'] = get_option('siteurl');
            $setup['source'] = "wordpress";
            if (get_site_option("bruteprotect_installed_clef")) {
                $setup['source'] = "bruteprotect";
            }
            $options['setup'] = $setup;
            $options['configured'] = $this->settings->is_configured();
            $options['clefBase'] = CLEF_BASE;
            $options['options_name'] = CLEF_OPTIONS_NAME;

            echo ClefUtils::render_template('admin/settings.tpl', array(
                "form" => $form,
                "options" => $options,
            ));
        } else {
            echo ClefUtils::render_template('admin/multisite-enabled.tpl');
        }
    }

    public function multisite_settings() {
        echo ClefUtils::render_template('admin/multisite-disabled.tpl');
    }

    public function other_install_settings() {
        require_once 'lib/plugin-installer/installer.php';

        $installer = new PluginInstaller( array( "name" => "BruteProtect", "slug" => "bruteprotect" ) );
        // pass in current URL as base URL
        $url = $installer->url();

        echo ClefUtils::render_template('admin/other-install.tpl', array(
            "url" => $url
        ));
    }

    public function other_install() {
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

    public function settings_form() {
        $form = ClefSettings::forID(self::FORM_ID, CLEF_OPTIONS_NAME, $this->settings);

        $this->add_api_settings($form);

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

        $invite_users_settings = $form->addSection('invite_users', __('Invite Users', "clef"), array(__CLASS__, 'print_invite_users_descript'));
        return $form;
    }

    public function multisite_settings_edit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] == 'clef_multisite') {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'clef_multisite')) {
                die("Security check; nonce failed.");
            }

            $override = get_option(self::MS_OVERRIDE_OPTION);

            if (!add_option(self::MS_OVERRIDE_OPTION, !$override)) {
                update_option(self::MS_OVERRIDE_OPTION, !$override);
            }

            wp_redirect(add_query_arg(array('page' => $this->settings->settings_path, 'updated' => 'true'), admin_url('admin.php')));
            exit();
        }
    }

    public function setup_plugin() {
        if (is_admin() && get_option("Clef_Activated")) {
            delete_option("Clef_Activated");

            if ($this->bruteprotect_active()) {
                wp_redirect(add_query_arg(array('page' => $this->settings->settings_path), admin_url('admin.php')));
            } else {
                wp_redirect(add_query_arg(array('page' => $this->settings->settings_path), admin_url('options.php')));
            }
            exit();
        }
    }

    public function add_api_settings($form) {
        $settings = $form->addSection('clef_settings', __('API Settings'), array(__CLASS__, 'print_api_descript'));
        $settings->addField('app_id', __('Application ID', "clef"), Settings_API_Util_Field::TYPE_TEXTFIELD);
        $settings->addField('app_secret', __('Application Secret', "clef"), Settings_API_Util_Field::TYPE_TEXTFIELD);
        if (!$this->settings->is_configured()) {
            $settings->addField('oauth_code', '', Settings_API_Util_Field::TYPE_HIDDEN, '');
        }
        return $settings;
    }

    public function bruteprotect_active() {
        return in_array( 'bruteprotect/bruteprotect.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
    }

    public static function print_invite_users_descript() {
        $url = add_query_arg(array('page' => 'clef', 'invite_users' => 'true'), admin_url('admin.php'));
        _e('<p>Invite users of your site here.</p>', 'clef');
        _e("<a href='$url'>Invite all users</a>", 'clef');
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

    public static function start($settings) {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self($settings);
        }
        return self::$instance;
    }
}
