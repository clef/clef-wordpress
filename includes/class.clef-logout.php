<?php

class ClefLogout {
    private static $instance = null;

    private $settings;

    private function __construct($settings) {
        $this->settings = $settings;
        $this->session = ClefSession::start();
        $this->initialize_hooks();

        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            $this->logged_out_check();
        }
    }

    public function initialize_hooks() {
        add_filter( 'heartbeat_received',  array($this, "hook_heartbeat"), 10, 3);
        $this->register_logout_hook_handler();
    }

    /**
     * Handle a Clef logout hook handshake, if the parameters exist.
     */
    public function register_logout_hook_handler() {
        if(isset($_POST) && isset($_POST['logout_token'])) {

            $args = array(
                'logout_token' => $_REQUEST['logout_token'],
                'app_id' => $this->settings->get( 'clef_settings_app_id' ),
                'app_secret' => $this->settings->get( 'clef_settings_app_secret' ),
            );

            $response = wp_remote_post( CLEF_API_BASE . 'logout', array( 'method' => 'POST',
                'timeout' => 45, 'body' => $args ) ); 

            $body = json_decode( $response['body'] );

            if (isset($body->success) && isset($body->clef_id)) {
                $this->set_user_logged_out_at($body->clef_id);
                $return = array(
                    "success" => true
                );
            } else {
                $return = array(
                    "success" => false,
                    "error" => $body->error
                );
            }
            echo(json_encode($return));
            exit();
        }
    }

    private function set_user_logged_out_at($clef_id) {
        $user = get_users(array('meta_key' => 'clef_id', 'meta_value' => $clef_id, 'blog_id' => false));
        if (!empty($user)) {
            $user = $user[0];

            // upon success, log user out
            update_user_meta($user->ID, 'logged_out_at', time());
        }
    }

    public function logged_out_check($opts = array("redirect" => false)) {
        $logged_out = false;
        // if the user is logged into WP but logged out with Clef, sign them out of Wordpress
        if (is_user_logged_in() && 
            $this->session->get('logged_in_at') && 
            $this->session->get('logged_in_at') < get_user_meta(wp_get_current_user()->ID, "logged_out_at", true)) {
            wp_logout();
            $logged_out = true;
        }
        else if (!is_user_logged_in()) {
            $logged_out = true;
        } else {
            $logged_out = false;
        }

        if ($opts['redirect'] && $logged_out) {
            wp_redirect(wp_login_url());
            exit();
        } else {
            return $logged_out;
        }
    }

    public function hook_heartbeat($response, $data, $screen_id) {
        $logged_out = $this->logged_out_check(array("redirect" => false));
        if ($logged_out) {
            $response['cleflogout'] = true;
        }
        return $response;
    }

    public static function start($settings) {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self($settings);
        }
        return self::$instance;
    }
}
?>
