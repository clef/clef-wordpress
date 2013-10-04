<?php

function print_api_descript() {
    echo '<p>To manage the Clef application that syncs with your plugin, please visit <a href="https://developer.getclef.com">the Clef developer site</a>.</p>';
}

class ClefAdmin extends ClefBase {

    const FORM_ID = "clef";
    const CLASS_NAME = "ClefAdmin";

    public static function init() {
        add_action('admin_menu', array(__CLASS__, "admin_menu"));
        add_action('admin_init', array(__CLASS__, "setup_plugin"));
        add_action('admin_init', array(__CLASS__, "settings_form"));
        add_action('admin_init', array(__CLASS__, "multisite_settings_edit"));
        add_action('admin_enqueue_scripts', array(__CLASS__, "admin_enqueue_scripts"));
        add_action('admin_enqueue_styles', array(__CLASS__, "admin_enqueue_styles"));
        add_action('show_user_profile', array(__CLASS__, "show_user_profile"));
        add_action('edit_user_profile', array(__CLASS__, "show_user_profile"));
        add_action('edit_user_profile_update', array(__CLASS__, 'edit_user_profile_update'));
        add_action('personal_options_update', array(__CLASS__, 'edit_user_profile_update'));
        add_action('admin_notices', array(__CLASS__, 'edit_profile_errors'));

        add_action('options_edit_clef_multisite', array(__CLASS__, "multisite_settings_edit"), 10, 0);
    }


    public static function admin_enqueue_scripts($hook) {

        $exploded_path = explode('/', $hook);
        $settings_page_name = array_shift($exploded_path);

        // only register clef logout if user is a clef user
        if (get_user_meta(wp_get_current_user()->ID, 'clef_id')) {
            wp_register_script('wpclef_logout', CLEF_URL .'assets/js/clef_heartbeat.js', array('jquery'), '1.0', TRUE);
            wp_enqueue_script('wpclef_logout');
        }
        
        if(preg_match("/clef/", $settings_page_name)) {
            wp_register_style('wpclef_styles', CLEF_URL . 'assets/css/wpclef.css', FALSE, '1.0.0');
            wp_enqueue_style('wpclef_styles');

            wp_register_script('wpclef_keys', CLEF_URL . 'assets/js/keys.js', array('jquery'), '1.0.0', TRUE );
            wp_enqueue_script('wpclef_keys');
        } 
    }

    public static function show_user_profile() {
        $connected = !!get_user_meta(wp_get_current_user()->ID, "clef_id", true);
        $app_id = self::setting( 'clef_settings_app_id' );
        $redirect_url = trailingslashit( home_url() ) . "?clef_callback=clef_callback&connecting=true";
        $redirect_url .=  ("&state=" . wp_create_nonce("connect_clef"));
        include CLEF_TEMPLATE_PATH."user_profile.tpl.php";
    }

    public static function edit_user_profile_update($user_id) {
        if (isset($_POST['remove_clef']) && $_POST['remove_clef']) {
            delete_user_meta($user_id, "clef_id");
        }
    }

    public static function edit_profile_errors($errors) {
        if (isset($_SESSION['Clef_Messages']) && !empty($_SESSION['Clef_Messages'])) {
            $_SESSION['Clef_Messages'] = array_unique( $_SESSION['Clef_Messages'] );
            echo '<div id="login_error">';
            foreach ( $_SESSION['Clef_Messages'] as $message ) {
                echo '<p><strong>ERROR</strong>: '. $message . ' </p>';
            }
            echo '</div>';
            $_SESSION['Clef_Messages'] = array();
        }
    }

    public static function admin_menu() {
        add_menu_page("Clef", "Clef", "manage_options", 'clef', array(__CLASS__, 'general_settings'));
        if (get_site_option(self::MS_ENABLED_OPTION) && self::individual_settings()) {
            add_submenu_page('clef','Settings','Settings','manage_options','clef', array(__CLASS__, 'general_settings'));
            add_submenu_page("clef", "Multisite Options", "Enable Multisite", "manage_options", 'clef_multisite', array(__CLASS__, 'multisite_settings'));
        }
    }

    public static function general_settings() {
        if (self::individual_settings()) {
            $form = ClefSettings::forID(self::FORM_ID, CLEF_OPTIONS_NAME);
            $form->renderBasicForm('Clef Settings', Settings_API_Util::ICON_SETTINGS);   
        } else {
            include CLEF_TEMPLATE_PATH . "admin/multsite-enabled.tpl.php";
        }
    }

    public static function multisite_settings() {
        include CLEF_TEMPLATE_PATH . "admin/multisite-disabled.tpl.php";
    }

    public static function settings_form() {
        $form = ClefSettings::forID(self::FORM_ID, CLEF_OPTIONS_NAME);

        $settings = $form->addSection('clef_settings', 'API Settings', 'print_api_descript');
        $values = $settings->settings->values;
        if(!isset($values['clef_settings_app_id']) ||
            !isset($values['clef_settings_app_secret']) || 
            $values['clef_settings_app_id'] == "" ||
            $values['clef_settings_app_secret'] == "") {
            $site_name = urlencode(get_option('blogname'));
            $site_domain = urlencode(get_option('siteurl'));
            ob_start();
            include CLEF_TEMPLATE_PATH."keys_generation.tpl.php";
            $form->introHTML = ob_get_clean();
        } 

        $settings->addField('app_id', 'App ID', Settings_API_Util_Field::TYPE_TEXTFIELD);
        $settings->addField('app_secret', 'App Secret', Settings_API_Util_Field::TYPE_TEXTFIELD);

        $pw_settings = $form->addSection('clef_password_settings', 'Password Settings', '');
        $pw_settings->addField('disable_passwords', 'Disable passwords for Clef users.', Settings_API_Util_Field::TYPE_CHECKBOX);
        $pw_settings->addField('force', 'Disable passwords for all users, and hide the password login form.', Settings_API_Util_Field::TYPE_CHECKBOX);
        
        $key = Clef::setting( 'clef_password_settings_override_key' );
        $override_msg = '<a href="javascript:void(0);" onclick="document.getElementById(\'wpclef[clef_password_settings_override_key]\').value=\''. md5(uniqid(mt_rand(), true)) .'\'">Set an override key</a> to enable passwords via ';
        if (!empty($key)) {
            $url = wp_login_url() .'?override=' .$key;
            $override_msg .= "<strong><a href='" .$url ."' target='new'>this secret URL</a></strong>.";
        } else {
            $override_msg .= 'a secret URL.';
        }
        
        $pw_settings->addField('override_key', $override_msg, Settings_API_Util_Field::TYPE_TEXTFIELD); 

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

            wp_redirect(admin_url('/options.php?page=clef'));
            exit();
        }
    }
}

?>