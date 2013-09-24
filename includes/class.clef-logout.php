<?php

    class ClefLogout {

        public static function init() {

            add_action('init', array(__CLASS__, 'logout_hook_handler'));
            add_action('init', array(__CLASS__, 'logged_out_check'));
            add_filter( 'heartbeat_received',  array("Clef", "hook_heartbeat"), 10, 3);

        }

        public static function logout_hook_handler() {

            if(isset($_POST['logout_token'])) {

                $args = array(
                    'logout_token' => $_REQUEST['logout_token'],
                    'app_id' => self::setting( 'clef_settings_app_id' ),
                    'app_secret' => self::setting( 'clef_settings_app_secret' ),
                );

                $response = wp_remote_post( CLEF_API_BASE . 'logout', array( 'method' => 'POST',
                    'timeout' => 45, 'body' => $args ) ); 
                $body = json_decode( $response['body'] );

                if (isset($body->success) && $body->success == 1 && isset($body->clef_id)) {
                    $user = get_users(array('meta_key' => 'clef_id', 'meta_value' => $body->clef_id));
                    $user = $user[0];

                    // upon success, log user out
                    update_user_meta($user->ID, 'logged_out_at', time());
                }
            }
        }

        public static function logged_out_check($redirect=true) {
            // if the user is logged into WP but logged out with Clef, sign them out of Wordpress
            if (is_user_logged_in() && isset($_SESSION['logged_in_at']) && $_SESSION['logged_in_at'] < get_user_meta(wp_get_current_user()->ID, "logged_out_at", true)) {
                wp_logout();
                if ($redirect) {
                    self::redirect_error();
                } else {
                    return true;
                }
            }
            return false;
        }

        public static function hook_heartbeat($response, $data, $screen_id) {
            $logged_out = self::logged_out_check(false);
            if ($logged_out) {
                $response['cleflogout'] = true;
            }
            return $response;
        }
    }
?>