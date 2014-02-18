<?php
require_once(CLEF_PATH . 'includes/class.clef-settings.php');
require_once(CLEF_PATH . 'includes/class.clef-invite-code.php');

class ClefAdmin {
    const FORM_ID = "clef";
    const CONNECT_CLEF_OAUTH_ACTION = "connect_clef_account_oauth_code";
    const CONNECT_CLEF_ID_ACTION = "connect_clef_account_clef_id";
    const INVITE_USERS_ACTION = "clef_invite_users";
    const DISMISS_WALTZ_ACTION = "clef_dismiss_waltz";
    const DISCONNECT_CLEF_ACTION = "disconnect_clef_account";

    const CLEF_WALTZ_LOGIN_COUNT = 3;
    const DASHBOARD_WALTZ_LOGIN_COUNT = 15;

    const HIDE_WALTZ_BADGE = 'clef_hide_waltz_badge';
    const HIDE_WALTZ_PROMPT = 'clef_hide_waltz_prompt';

    private static $instance = null;

    protected $settings;

    protected function __construct($settings) {
        $this->settings = $settings;
        $this->initialize_hooks();

        require_once(CLEF_PATH . "/includes/lib/ajax-settings/ajax-settings.php");
        $this->ajax_settings = AjaxSettings::start(array( 
            "options_name" => CLEF_OPTIONS_NAME, 
            "initialize" => false, 
            "base_url" => CLEF_URL . "includes/lib/ajax-settings/",
            "formSelector" => "#clef-form"
        ));
    }

    public function initialize_hooks() {
        add_action('admin_init', array($this, "other_install"));
        add_action('admin_init', array($this, "setup_plugin"));
        add_action('admin_init', array($this, "settings_form"));
        add_action('admin_init', array($this, "multisite_settings_edit"));

        add_action('clef_hook_admin_menu', array($this, "hook_admin_menu"));

        add_action('admin_enqueue_scripts', array($this, "admin_enqueue_scripts"));

        add_action('admin_notices', array($this, 'display_messages') );
        add_action('admin_notices', array($this, 'display_clef_waltz_prompt'));
        add_action('admin_notices', array($this, 'display_dashboard_waltz_prompt'));

        global $clef_ajax;
        $clef_ajax->add_action(self::CONNECT_CLEF_ID_ACTION, array($this, 'ajax_connect_clef_account_with_clef_id'));
        $clef_ajax->add_action(
            self::CONNECT_CLEF_OAUTH_ACTION, 
            array($this, 'ajax_connect_clef_account_with_oauth_code'),
            array('capability' => 'read')
        );
        $clef_ajax->add_action(self::INVITE_USERS_ACTION, array($this, 'ajax_invite_users'));
        $clef_ajax->add_action(
            self::DISMISS_WALTZ_ACTION, 
            array($this, 'ajax_dismiss_waltz_notification'),
            array('capability' => 'read')
        );
        $clef_ajax->add_action(
            self::DISCONNECT_CLEF_ACTION,
            array($this, 'ajax_disconnect_clef_account'),
            array('capability' => 'read')
        );

        // Display the badge message, if appropriate
        do_action('clef_hook_onboarding');
    }

    private function render_waltz_prompt() {
        echo '<div class="waltz-notification">';
            echo '<div class="waltz setup">';
            echo ClefUtils::render_template('admin/waltz-prompt.tpl', array(
                'next_href' => '#',
                'next_text' => __('Hide this message', 'clef')
            ));
            echo '</div>';
        echo '</div>';
    }

    public function display_dashboard_waltz_prompt() {
        $onboarding = ClefOnboarding::start($this->settings);

        $login_count = $onboarding->get_login_count_for_current_user();
        $is_settings_page = ClefUtils::isset_GET('page') == $this->settings->settings_path;

        $hide_waltz_prompt = get_user_meta(get_current_user_id(), self::HIDE_WALTZ_PROMPT, true);

        // If the user has access to the dashboard and they haven't already 
        // dismissed the prompt, then display it.
        $should_display_for_user = !$hide_waltz_prompt && current_user_can('read');

        if ($login_count < self::DASHBOARD_WALTZ_LOGIN_COUNT || !$should_display_for_user || $is_settings_page) return;

        $this->render_waltz_prompt();

        // Make sure the notification doesn't ever show again for this user
        update_user_meta(get_current_user_id(), self::HIDE_WALTZ_PROMPT, true);
    }

    public function display_clef_waltz_prompt() {
        $is_google_chrome = strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false;
        $is_settings_page = ClefUtils::isset_GET('page') == $this->settings->settings_path;
        $should_hide = get_user_meta(get_current_user_id(), self::HIDE_WALTZ_PROMPT, true);

        $onboarding = ClefOnboarding::start($this->settings);
        $login_count = $onboarding->get_login_count();

        if (!$is_google_chrome || !$is_settings_page || $should_hide || $login_count < self::CLEF_WALTZ_LOGIN_COUNT) return;
        
        $this->render_waltz_prompt();
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

        $ident = ClefUtils::register_script('waltz_notification', array('jquery'));
        wp_enqueue_script($ident);
        
        $ident = ClefUtils::register_style('admin');
        wp_enqueue_style($ident);
        
        if(preg_match("/".$this->settings->settings_path."/", $settings_page_name)) {
            $ident = ClefUtils::register_script(
                'settings', 
                array('jquery', 'backbone', 'underscore', $this->ajax_settings->identifier())
            );
            wp_enqueue_script($ident);
        } 
    }

    /**
     * @return array Users filtered by >= $role
     */
    protected function filter_users_by_role($users, $role) {
        $filtered_users = array();
        if ($role === 'everyone')  {
            $filtered_users = $users;
        }
        else {
            foreach ($users as $user) {
                if (ClefUtils::user_fulfills_role($user, $role)) {
                    $filtered_users[] = $user;
                }
            }
        }
        return $filtered_users;
    }

    protected function send_invite_email($user, $invite_code) {
        $invite_link = $invite_code->get_link();
        $to = $user->user_email;
        $subject = __('Set up Clef for your account', "clef");
        $message = ClefUtils::render_template('invite_email.tpl', array("invite_link" =>  $invite_link));

        add_filter('wp_mail_content_type', array('ClefUtils', 'set_html_content_type'));
        error_log($to . '\n' . $subject . '\n' . $message . '\n');
        wp_mail($to, $subject, $message);
        remove_filter('wp_mail_content_type', array('ClefUtils', 'set_html_content_type'));
    }

    public function render_connect_clef_account() {
        echo ClefUtils::render_template(
            'admin/connect.tpl', 
            array( 
                "options" => array(
                    "connected" => ClefUtils::user_has_clef(),
                    "appID" => $this->settings->get( 'clef_settings_app_id' ),
                    "redirectURL" => add_query_arg(array( 'clef' => 'true'), wp_login_url()),
                    "clefJSURL" => CLEF_JS_URL,
                    "nonces" => array(
                        "connectClef" => wp_create_nonce(self::CONNECT_CLEF_OAUTH_ACTION),
                        "disconnectClef" => wp_create_nonce(self::DISCONNECT_CLEF_ACTION)
                    )
                )
            )
        );
    }

    public function admin_menu() {
        if ($this->settings->multisite_disallow_settings_override()) {
            // if the single site override of settings is not allowed
            // let's add a menu page that only lets a user connect
            // their clef account
            add_menu_page(
                __("Clef", 'clef'),
                __("Clef", 'clef'),
                "read",
                $this->settings->settings_path,
                array($this, 'render_connect_clef_account')
            );
            return;
        }

        if ($this->bruteprotect_active() && get_site_option("bruteprotect_installed_clef")) {
            $menu_name = 'bruteprotect-config';
            add_submenu_page(
                $menu_name, 
                __("Clef", "clef"), 
                __("Clef", "clef"), 
                "manage_options", 
                $this->settings->settings_path, 
                array($this, 'general_settings')
            );
        } else {
            $clef_menu_title = $this->get_clef_menu_title();
            $menu_name = $this->settings->settings_path;
            add_menu_page(
                __("Clef", 'clef'), 
                $clef_menu_title,
                "manage_options", 
                $menu_name, 
                array($this, 'general_settings')
            );
        }

        if (ClefUtils::user_has_clef()) $name = __('Disconnect Clef account', 'clef');
        else $name = __('Connect Clef account', 'clef');
        add_submenu_page(
            $menu_name,
            $name, 
            $name,
            'read', 
            'connect_clef_account', 
            array($this, 'render_connect_clef_account')
        );

        if (!$this->bruteprotect_active() && !is_multisite())  {
            add_submenu_page(
                $menu_name, 
                __('Add Additional Security', 'clef'), 
                __('Additional Security', 'clef'), 
                'manage_options', 
                'clef_other_install', 
                array($this, 'other_install_settings')
            );
        }
    }


    /**
     * Determines whether to badge the Clef menu icon.
     *
     * @return string The title of the menu with or without a badge
     */
    public function get_clef_menu_title() {
        $clef_menu_title = __('Clef', 'clef');

        $onboarding = ClefOnboarding::start($this->settings);

        $user_is_admin = current_user_can('manage_options');
        $login_count = $onboarding->get_login_count();
        $hide_waltz_badge = get_user_meta(get_current_user_id(), self::HIDE_WALTZ_BADGE, true);
        $is_google_chrome = strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false;

        $badge_menu_title = $user_is_admin && 
                            $login_count >= self::CLEF_WALTZ_LOGIN_COUNT && 
                            !$hide_waltz_badge && 
                            $is_google_chrome;

        if ($badge_menu_title) {
            $clef_menu_title .= $this->render_badge(1);
        }

        return $clef_menu_title;
    }

    public function render_badge($count) {
        return " <span class='update-plugins count-1'><span class='update-count'>" . $count . "</span></span>";
    }

    public function general_settings($options = false) {
        // Ensure that if the Waltz notification bubble was showing, that it is 
        // never shown again.
        update_user_meta(get_current_user_id(), self::HIDE_WALTZ_BADGE, true);

        $form = ClefSettings::forID(self::FORM_ID, CLEF_OPTIONS_NAME, $this->settings);

        if (!$options) {
            $options = $this->settings->get_site_option();
        }

        $options = array_merge(array(
            'setup' => array(
                'siteName' => get_option('blogname'),
                'siteDomain' => get_option('siteurl'),
                'logoutHook' => wp_login_url(),
                'source' => 'wordpress'
            ),
            'nonces' => array(
                'connectClef' => wp_create_nonce(self::CONNECT_CLEF_ID_ACTION),
                'inviteUsers' => wp_create_nonce(self::INVITE_USERS_ACTION)
            ),
            'configured' => $this->settings->is_configured(),
            'clefBase' => CLEF_BASE,
            'optionsName' => CLEF_OPTIONS_NAME,
            'settingsPath' => $this->settings->settings_path,
            'isMultisite' => is_multisite(),
            'isNetworkSettings' => false,
            'isNetworkSettingsEnabled' => $this->settings->network_settings_enabled(),
            'isSingleSiteSettingsAllowed' => $this->settings->single_site_settings_allowed(),
            'isUsingIndividualSettings' => $this->settings->use_individual_settings
        ), $options);

        if (get_site_option("bruteprotect_installed_clef")) {
            $options['source'] = "bruteprotect";
        }

        echo ClefUtils::render_template('admin/settings.tpl', array(
            "form" => $form,
            "options" => $options
        ));
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


        $settings = $form->addSection('clef_settings', __('API Settings'));
        $settings->addField('app_id', __('Application ID', "clef"), Settings_API_Util_Field::TYPE_TEXTFIELD);
        $settings->addField('app_secret', __('Application Secret', "clef"), Settings_API_Util_Field::TYPE_TEXTFIELD);

        $pw_settings = $form->addSection('clef_password_settings', __('Password Settings'), '');
        $pw_settings->addField('disable_passwords', __('Disable passwords for Clef users', "clef"), Settings_API_Util_Field::TYPE_CHECKBOX);
        $pw_settings->addField(
            'disable_certain_passwords', 
            __('Disable certain passwords', "clef"), 
            Settings_API_Util_Field::TYPE_SELECT,
            "Disabled",
            array( "options" => array("", "Contributor", "Author", "Editor", "Administrator", "Super Administrator" ) )
        );
        $pw_settings->addField('force', __('Disable all passwords', "clef"), Settings_API_Util_Field::TYPE_CHECKBOX);

        $pw_settings->addField(
            'xml_allowed', 
            __('Allow XML'),
            Settings_API_Util_Field::TYPE_CHECKBOX
        );

        $override_settings = $form->addSection('clef_override_settings', __('Override Settings'));
        $override_settings->addField('key', __("Override key", "clef"), Settings_API_Util_Field::TYPE_TEXTFIELD); 

        $support_clef_settings = $form->addSection('support_clef', __('Support Clef', "clef"));
        $support_clef_settings->addField(
            'badge', 
            __("Support Clef by automatically adding a link!", "clef"),
            Settings_API_Util_Field::TYPE_SELECT,
            "disabled",
            array("options" => array(array(__("Badge", "clef"), "badge") , array(__("Link", "clef"), "link"), array(__("Disabled", "clef"), "disabled")))
        );

        $invite_users_settings = $form->addSection('invite_users', __('Invite Users', "clef"));
        return $form;
    }

    public function multisite_settings_edit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
            ClefUtils::isset_GET('page') == 'clef' &&
            ClefUtils::isset_GET('action') == 'clef_multisite' &&
            !is_network_admin()
        ) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'clef_multisite')) {
                die(__("Security check; nonce failed.", "clef"));
            }

            $override = get_option(ClefInternalSettings::MS_OVERRIDE_OPTION);

            if (!add_option(ClefInternalSettings::MS_OVERRIDE_OPTION, !$override)) {
                update_option(ClefInternalSettings::MS_OVERRIDE_OPTION, !$override);
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

    public function bruteprotect_active() {
        return in_array( 'bruteprotect/bruteprotect.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
    }

    public static function print_invite_users_descript() {
        $url = add_query_arg(array('page' => 'clef', 'invite_users' => 'true'), admin_url('admin.php'));
        _e('<p>Invite users of your site here.</p>', 'clef');
        _e("<a href='$url'>Invite all users</a>", 'clef');
    }

    /**** BEGIN AJAX HANDLERS ******/

    public function ajax_dismiss_waltz_notification() {
        update_user_meta(get_current_user_id(), self::HIDE_WALTZ_PROMPT, true);
        return array('success' => true);
    }

    public function ajax_invite_users() {
        $role = strtolower(ClefUtils::isset_POST('roles'));

        if (!$role) {
            return new WP_Error('invalid_role', __('invalid role', 'clef'));
        }

        $other_users = get_users(array('exclude' => array(get_current_user_id())));
        $filtered_users = $this->filter_users_by_role($other_users, $role);

        if (empty($filtered_users)) {
            return new WP_Error('no_users', __("there are no other users with this role or greater", "clef"));
        }

        foreach ($filtered_users as &$user) {
            $invite_code = new InviteCode($user);
            update_user_meta($user->ID, 'clef_invite_code', $invite_code);
            $this->send_invite_email($user, $invite_code);
        }

        return array("success" => true);
    }

     public function ajax_connect_clef_account_with_clef_id() {
        if (!ClefUtils::isset_POST('identifier')) {
            return new WP_Error("invalid_clef_id", __("invalid Clef ID", "clef"));
        }

        ClefUtils::associate_clef_id($_POST["identifier"]);
        return array("success" => true);
    }

    public function ajax_connect_clef_account_with_oauth_code() {
        if (!ClefUtils::isset_POST('identifier')) {
            return new WP_Error("invalid_oauth_code", __("invalid OAuth Code", "clef"));
        }

        try {
            $info = ClefUtils::exchange_oauth_code_for_info(ClefUtils::isset_POST('identifier'), $this->settings);
        } catch (LoginException $e) {
            return new WP_Error("bad_oauth_exchange", $e->getMessage());
        }

        ClefUtils::associate_clef_id($info->id);
        $_SESSION['logged_in_at'] = time();

        return array("success" => true);
    }

    public function ajax_disconnect_clef_account() {
        ClefUtils::dissociate_clef_id();
        return array("success" => true);
    }

    /**** END AJAX HANDLERS ******/

    public static function start($settings) {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self($settings);
        }
        return self::$instance;
    }
}
