<?php

class ClefUserSettings {
    private static $instance = null;
    private $rendered = false;

    const CONNECT_CLEF_OAUTH_ACTION = "connect_clef_account_oauth_code";
    const DISCONNECT_CLEF_ACTION = "disconnect_clef_account";

    protected function __construct($settings) {
        $this->settings = $settings;
        $this->initialize_hooks();
    }

    public function initialize_hooks() {
        add_action('clef_render_user_settings', array($this, 'render'));
        add_action('wp_footer', array($this, 'print_assets'));

        add_shortcode('clef_user_settings', array($this, 'render'));
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));

        global $clef_ajax;
        $clef_ajax->add_action(
            self::CONNECT_CLEF_OAUTH_ACTION,
            array($this, 'ajax_connect_clef_account_with_oauth_code'),
            array('capability' => 'read')
        );
        $clef_ajax->add_action(
            self::DISCONNECT_CLEF_ACTION,
            array($this, 'ajax_disconnect_clef_account'),
            array('capability' => 'read')
        );
    }

    public function render() {
        $connect_nonce = wp_create_nonce(self::CONNECT_CLEF_OAUTH_ACTION);
        $redirect_url = add_query_arg(
            array('_wpnonce' => $connect_nonce, 'connect' => true),
            admin_url("/admin.php?page=" . $this->settings->settings_path)
        );

        echo ClefUtils::render_template(
            'user_settings.tpl',
            array(
                "options" => array(
                    "connected" => ClefUtils::user_has_clef(),
                    "appID" => $this->settings->get( 'clef_settings_app_id' ),
                    "redirectURL" => $redirect_url,
                    "clefJSURL" => CLEF_JS_URL,
                    "nonces" => array(
                        "connectClef" => $connect_nonce,
                        "disconnectClef" => wp_create_nonce(self::DISCONNECT_CLEF_ACTION)
                    )
                )
            )
        );
        $this->rendered = true;
    }

    public function register_assets() {
        $this->script_identifier = ClefUtils::register_script('connect', array('jquery', 'backbone', 'underscore'));
        $this->style_identifier = ClefUtils::register_style('admin');
        wp_localize_script($this->script_identifier, 'ajaxurl',  admin_url('admin-ajax.php'));
    }

    public function print_assets() {
        if ($this->rendered) {
            if (!ClefUtils::style_has_been_added('admin')) {
                wp_print_styles($this->style_identifier);
                wp_admin_css( 'wp-admin', true );
                wp_admin_css( 'colors-fresh', true );
            }
            if (!ClefUtils::script_has_been_added('settings')) wp_print_scripts($this->script_identifier);
        }
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

        $result = ClefUtils::associate_clef_id($info->id);

        if (is_wp_error($result)) {
            return $result;
        } else {
            $session = ClefSession::start();
            $session->set('logged_in_at', time());

            return array("success" => true);
        }
    }

    public function ajax_disconnect_clef_account() {
        $user = wp_get_current_user();
        $passwords_disabled = $this->settings->passwords_are_disabled_for_user($user);
        if (current_user_can('manage_options') && $passwords_disabled) {
            return new WP_Error('passwords_disabled', __("your passwords are currently disabled. <br/> If you disconnect your Clef account, you won't be able to log in. Please enable passwords for yourself before disconnecting your Clef account", 'clef'));
        }
        ClefUtils::dissociate_clef_id();
        return array("success" => true);
    }

    public static function start($settings) {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self($settings);
        }
        return self::$instance;
    }
}
