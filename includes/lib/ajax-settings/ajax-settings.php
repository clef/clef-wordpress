<?php

class AjaxSettings {
    const VERSION = '1.0.0';
    static $DEFAULTS =  array(
        "network_wide" => false
    );

    private static $instance = null;

    private function __construct( $opts ) {
        $this->options = array_merge(self::$DEFAULTS, $opts);


        add_action('admin_enqueue_scripts', array($this, "enqueue_scripts"));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));

        add_action(
            'wp_ajax_ajax_settings_save_' . $this->name(),
            array($this, 'handle_settings_save')
        );
    }

    function enqueue_scripts() {
        $ident = $this->identifier();
        wp_register_script(
            $ident,
            $this->options['base_url']  . "js/ajax-settings.v" . self::VERSION . ".min.js",
            array('backbone'),
            self::VERSION,
            TRUE
        );
        wp_localize_script($ident, 'ajaxSetOpt', $this->options);
        wp_enqueue_script($ident);
    }

    function enqueue_styles() {
        $ident = $this->identifier();
        wp_register_style(
            $ident,
            $this->options['base_url'] . 'css/ajax-settings.v'  . self::VERSION . 'min.css',
            false,
            self::VERSION
        );
        wp_enqueue_style($ident);
    }

    function handle_settings_save() {
        if (!isset($_REQUEST[$this->name()])) wp_die(__('Settings could not be parsed — this may be caused by a plugin conflict.'));

        $to_be_saved = $_REQUEST[$this->name()];
        $option_page = $_REQUEST['option_page'];
        $is_network_wide = isset($_REQUEST['network_wide']) && $_REQUEST['network_wide'];

        if (!wp_verify_nonce($_REQUEST['_wpnonce'], $option_page . "-options")) {
            wp_die(__('Cheatin&#8217; uh?'));
        }

        // if it's network request, we want to check that the current user is
        // a network admin
        if ($is_network_wide && !is_super_admin()) wp_die(__('Cheatin&#8217; uh?'));

        // verify that the user has the permissions to edit the clef page
        $capability = 'manage_options';
        $capability = apply_filters( "option_page_capability_{$option_page}", $capability );
        if ( !current_user_can( $capability ) )
            wp_die(__('Cheatin&#8217; uh?'));

        $whitelist_options = apply_filters( 'whitelist_options', array() );
        $options = $whitelist_options[$option_page];
        if (empty($options[0]) || $options[0] != $this->name()) {
            wp_die("You can't do that!");
        }

        $to_be_saved = apply_filters('ajax_settings_pre_save', $to_be_saved);

        if ($is_network_wide) {
            update_site_option($this->name(), $to_be_saved);
        } else {
            update_option($this->name(), $to_be_saved);
        }

        $errors = get_settings_errors();
        $response = array();

        if (!empty($errors)) {
            $error_messages = array();
            foreach ($errors as &$error) {
                $error_messages[$error['code']] = $error['message'];
            }
            $response['errors'] = $error_messages;
            header('HTTP/1.0 400');
            wp_send_json_error($response);
        } else {
            $response['success'] = true;
            wp_send_json($response);
        }
    }

    function identifier() {
        return $this->name() . '-ajax-settings';
    }

    function name() {
        return $this->options['options_name'];
    }

    function update_options($options) {
        $this->options = array_merge($this->options, $options);
    }

    public static function start($options = array()) {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self($options);
        } else {
            self::$instance->update_options($options);
        }
        return self::$instance;
    }
}

