<?php

class ClefUserSettings {
    private static $instance = null;
    private $rendered = false;

    const DISCONNECT_CLEF_ACTION = "disconnect_clef_account";

    protected function __construct($settings) {
        $this->settings = $settings;
        $this->initialize_hooks();
        $this->connect_error = null;
    }

    public function initialize_hooks() {
        add_action('init', array($this,'connect_clef_account'));

        add_action('clef_render_user_settings', array($this, 'render'));
        add_action('wp_footer', array($this, 'print_assets'));

        add_shortcode('clef_user_settings', array($this, 'render'));
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));

        global $clef_ajax;
        $clef_ajax->add_action(
            self::DISCONNECT_CLEF_ACTION,
            array($this, 'ajax_disconnect_clef_account'),
            array('capability' => 'read')
        );
    }

    public function render() {
        echo ClefUtils::render_template(
            'user_settings.tpl',
            array(
                "connect_error" => $this->connect_error,
                "options" => array(
                    "connected" => ClefUtils::user_has_clef(),
                    "appID" => $this->settings->get( 'clef_settings_app_id' ),
                    "clefJSURL" => CLEF_JS_URL,
                    "state" => ClefUtils::get_state(),
                    "nonces" => array(
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

    public function connect_clef_account() {
        if (ClefUtils::isset_GET('connect_clef_account') && ClefUtils::isset_get('code')) {
            try {
                $info = ClefUtils::exchange_oauth_code_for_info(ClefUtils::isset_GET('code'), $this->settings);

                $result = ClefUtils::associate_clef_id($info->id);

                if (is_wp_error($result)) {
                    $this->connect_error = $result;
                } else {
                    $session = ClefSession::start();
                    $session->set('logged_in_at', time());
                    return;
                }
            } catch (LoginException $e) {
                $this->connect_error =  new WP_Error("bad_oauth_exchange", $e->getMessage());
            } catch (ClefStateException $e)  {
                $this->connect_error =  new WP_Error("bad_state_parameter", $e->getMessage());
            }
        }
    }

    public function ajax_disconnect_clef_account() {
        $user = wp_get_current_user();
        $passwords_disabled = $this->settings->passwords_are_disabled_for_user($user);
        if (current_user_can('manage_options') && $passwords_disabled) {
            return new WP_Error('passwords_disabled', __("your passwords are currently disabled. <br/> If you disconnect your Clef account, you won't be able to log in. Please enable passwords for yourself before disconnecting your Clef account", 'wpclef'));
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
