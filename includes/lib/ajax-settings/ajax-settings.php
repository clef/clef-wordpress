<?php

class AjaxSettings {
    const VERSION = '0.0.1';

    function __construct( $opts=array() ) {
        $this->options = $opts;

        $this->enqueue_scripts();
        $this->enqueue_styles();

        add_action(
            'wp_ajax_ajax_settings_save_' . $this->name(), 
            array($this, 'handle_settings_save')
        );
    }

    function enqueue_scripts() {
        $ident = $this->name() . 'ajax-settings';
        wp_register_script(
            $ident,
            $this->options['base_url']  . "js/ajax-settings.min.js",
            array('backbone'),
            $this::VERSION,
            TRUE
        );
        wp_localize_script($ident, 'ajaxSetOpt', $this->options);
        wp_enqueue_script($ident);
    }

    function enqueue_styles() {
        $ident = $this->name() . 'ajax-settings';
        wp_register_style(
            $ident,
            $this->options['base_url'] . 'css/ajax-settings.min.css',
            false,
            $this::VERSION
        );
        wp_enqueue_style($ident);
    }

    function handle_settings_save() {
        $settings = json_decode(file_get_contents( "php://input" ), true);
        $option_page = $settings['option_page'];

        if (!wp_verify_nonce($settings['_wpnonce'], $option_page . "-options")) {
            wp_die(__('Cheatin&#8217; uh?'));
        }

        $capability = 'manage_options';
        $capability = apply_filters( "option_page_capability_{$option_page}", $capability );
        if ( !current_user_can( $capability ) )
            wp_die(__('Cheatin&#8217; uh?'));

        $whitelist_options = apply_filters( 'whitelist_options', array() );
        $options = $whitelist_options[$settings['option_page']];
        if (empty($options[0]) || $options[0] != $this->name()) {
            wp_die("You can't do that!");
        }

        $to_be_saved = array();
        foreach ($settings as $key => &$value) {
            $match = preg_match('/(.+)\[(.+)\]$/', $key, $output);
            if ($match) {
                $nester_key = $output[1];
                if ($nester_key == $this->name()) {
                    $nested_key = $output[2];
                    $to_be_saved[$nested_key] = $value;
                }
            } 
        }

        update_option($this->name(), $to_be_saved);

        echo json_encode(array( "success" => true ));
        die();
    }

    function name() {
        return $this->options['options_name'];
    }
}

