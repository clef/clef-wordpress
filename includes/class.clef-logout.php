<?php

class ClefLogout {
    private static $instance = null;

    private $settings;

    private function __construct($settings) {
        $this->settings = $settings;
        $this->initialize_hooks();
    }

    public function initialize_hooks() {
        add_filter( 'heartbeat_received',  array($this, "hook_heartbeat"), 10, 3);
        add_filter( 'init', array($this, 'logout_hook_handler'));
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            add_filter('init', array($this, "logged_out_check_with_redirect"));
        }
        
        /**
        * Accommodate WP Engine's restriction on the scope of the Heartbeat API (i.e., restricted to post/page editing pages only)
        * by expanding the scope to all pages in WP Dashboard; this scope is required for Clef to display the WP logout modal upon Clef-enabled logouts.
        */
        if ( class_exists('WPE_Heartbeat_Throttle') ) {
            add_filter( 'wpe_heartbeat_allowed_pages', array( $this, 'increase_heartbeat_scope_for_wpe') );
        }
    }

    /**
     * Handle a Clef logout hook handshake, if the parameters exist.
     */
    public function logout_hook_handler() {
        if(isset($_POST) && isset($_POST['logout_token'])) {

            $args = array(
                'logout_token' => $_REQUEST['logout_token'],
                'app_id' => $this->settings->get( 'clef_settings_app_id' ),
                'app_secret' => $this->settings->get( 'clef_settings_app_secret' ),
            );

            $response = wp_remote_post( CLEF_API_BASE . 'logout', array( 'method' => 'POST',
                'timeout' => 45, 'body' => $args ) );

            if (is_wp_error($response)) {
                $return = array(
                    "error" => $response->get_error_message(),
                    "success" => false
                );
            } else {
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

    public function logged_out_check_with_redirect() {
        $this->logged_out_check(array("redirect" => true));
    }

    public function logged_out_check($opts = array("redirect" => false)) {
        $logged_out = false;
        // if the user is logged into WP but logged out with Clef, sign them out of Wordpress
        if (is_user_logged_in()) {
            $session = ClefSession::start();
            if ($session->get('logged_in_at') && $session->get('logged_in_at') < get_user_meta(wp_get_current_user()->ID, "logged_out_at", true)) {
                wp_logout();
                $logged_out = true;
            } else {
                $logged_out = false;
            }
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
    
    /***
     * Ported from Jeremy Pry (http://jeremypry.com/); Original: https://gist.github.com/JPry/b1f6c55a5d5337557f97
     * Remove WP Engine's reduced scope for the Heartbeat API (i.e., editing-only pages) so that the WP logout modal will appear
     * when the Clef logout hook is received.
     */
    public function increase_heartbeat_scope_for_wpe( $heartbeat_allowed_pages ) {
        $heartbeat_allowed_pages[] = 'admin.php';
        $heartbeat_allowed_pages[] = 'customize.php';
        $heartbeat_allowed_pages[] = 'edit.php';
        $heartbeat_allowed_pages[] = 'edit-comments.php';
        $heartbeat_allowed_pages[] = 'edit-tags.php';
        $heartbeat_allowed_pages[] = 'export.php';
        $heartbeat_allowed_pages[] = 'import.php';
        $heartbeat_allowed_pages[] = 'index.php';
        $heartbeat_allowed_pages[] = 'media-new.php';
        $heartbeat_allowed_pages[] = 'nav-menus.php';
        $heartbeat_allowed_pages[] = 'options-discussion.php';
        $heartbeat_allowed_pages[] = 'options-general.php';
        $heartbeat_allowed_pages[] = 'options-media.php';
        $heartbeat_allowed_pages[] = 'options-permalink.php';
        $heartbeat_allowed_pages[] = 'options-reading.php';
        $heartbeat_allowed_pages[] = 'options-writing.php';
        $heartbeat_allowed_pages[] = 'plugin-editor.php';
        $heartbeat_allowed_pages[] = 'plugin-install.php';
        $heartbeat_allowed_pages[] = 'plugins.php';
        $heartbeat_allowed_pages[] = 'post-new.php';
        $heartbeat_allowed_pages[] = 'profile.php';
        $heartbeat_allowed_pages[] = 'theme-editor.php';
        $heartbeat_allowed_pages[] = 'themes.php';
        $heartbeat_allowed_pages[] = 'tools.php';
        $heartbeat_allowed_pages[] = 'update-core.php';
        $heartbeat_allowed_pages[] = 'upload.php';
        $heartbeat_allowed_pages[] = 'users.php';
        $heartbeat_allowed_pages[] = 'user-new.php';
        $heartbeat_allowed_pages[] = 'widgets.php';
        
        return $heartbeat_allowed_pages;
    }
}
?>
