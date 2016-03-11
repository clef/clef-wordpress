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
        $action = $_REQUEST['action'];
        $hook_info = $this->hook_map[$action];
        $options = $hook_info['options'];

        $data = $_REQUEST;
        $send_non_200_error = true;

        if ($options['nonce'] && (!isset($data['_wpnonce']) || !wp_verify_nonce($data['_wpnonce'], $action))) {
            $this->send_json_error(
                array("error" => __("invalid nonce", "wpclef")),
                $send_non_200_error
            );
        }

        if (!current_user_can($options['capability'])) {
            $this->send_json_error(
                array("error" => __("user does not have correct capabilities", "wpclef")),
                $send_non_200_error
            );
        }

        $response = call_user_func($hook_info['function']);
        if (is_wp_error($response)) {
            $this->send_json_error(
                array("error" => $response->get_error_message()),
                $send_non_200_error
            );
        } else {
            wp_send_json($response);
        }
    }

    public static function send_json_error($data, $send_non_200) {
        if ($send_non_200) header('HTTP/1.0 400');
        wp_send_json_error($data);
    }

    public static function start($settings) {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self($settings);
        }
        return self::$instance;
    }
}
