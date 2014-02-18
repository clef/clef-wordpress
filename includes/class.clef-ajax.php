<?php

class ClefAjax {

    private $settings;
    private static $instance = null;
    private $hook_map = array();
    private $defaults = array(
        "nonce" => true,
        "capability" => "manage_options",
        "priority" => 10,
        "accepted_args" => 1
    );

    private function __construct($settings) {
        $this->settings = $settings;
    }

    public function add_action($action, $function_to_add, $opts = array()) {
        $opts = array_merge($this->defaults, $opts);

        $this->hook_map[$action] = array( 
            "function" => $function_to_add,
            "options" => $opts
        );

        add_action(
            'wp_ajax_' . $action, 
            array($this, "handle_ajax_request"),
            $opts['priority'], 
            $opts['accepted_args']
        );
    }

    public function handle_ajax_request() {
        $data = json_decode(file_get_contents( "php://input" ), true);
        if (empty($data)) {
            $data = $_REQUEST;
        }

        $action = $_REQUEST['action'];
        $hook_info = $this->hook_map[$action];
        $options = $hook_info['options'];

        if ($options['nonce'] && (!isset($data['_wpnonce']) || !wp_verify_nonce($data['_wpnonce'], $action))) {
            wp_send_json(array("error" => __("invalid nonce", "clef")));
        }

        if (!current_user_can($options['capability'])) {
            wp_send_json(array("error" => __("user does not have correct capabilities", "clef")));
        }

        call_user_func($hook_info['function']);
    }

    public static function start($settings) {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self($settings);
        }
        return self::$instance;
    }
}
