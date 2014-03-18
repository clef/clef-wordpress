<?php

class ClefPro  {
    const GET_PRO_SERVICES_ACTION = "clef_get_pro_services";

    private static $instance = null;

    private function __construct($settings) {
        $this->settings = $settings;

        $this->initialize_hooks();
    }

    public function initialize_hooks() {
        global $clef_ajax;

        $clef_ajax->add_action(self::GET_PRO_SERVICES_ACTION, array($this, 'ajax_get_pro_services'));
    }

    public function add_settings($form) {
        $customization = $form->addSection('customization', __('Customization', 'clef'));
        $customization->addField(
            'message', 
            __('Message for login page'),
            Settings_API_Util_Field::TYPE_TEXTAREA
        );
        $customization->addField(
            'logo', 
            __('Logo for login page'),
            Settings_API_Util_Field::TYPE_HIDDEN
        );
    }

    public function ajax_get_pro_services() {
        $args = array(
            'app_id' => $this->settings->get('clef_settings_app_id'),
            'app_secret' => $this->settings->get('clef_settings_app_secret')
        );

        $response = wp_remote_post(
            CLEF_API_BASE . 'app/info',
            array( 'method' => 'POST', 'body' => $args, 'timeout' => 20 )
        );

        if ( is_wp_error($response) ) {
            return $response;
        } else {
            return json_decode($response['body'])->services;
        }
    }

    public static function start($settings= array()) {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self($settings);
        }
        return self::$instance;
    }
}