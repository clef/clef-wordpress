<?php

    class ClefLogout extends ClefBase {

        public static function init() {

            add_filter( 'heartbeat_received',  array(__CLASS__, "hook_heartbeat"), 10, 3);

            if (isset($_POST)) {
                self::logout_hook_handler();
            }

            self::logged_out_check();
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

                if (isset($body->success) && isset($body->clef_id)) {
                    self::set_user_logged_out_at($body->clef_id);
                }
            }
        }

        private static function set_user_logged_out_at($clef_id) {
            $user = get_users(array('meta_key' => 'clef_id', 'meta_value' => $clef_id));
            if (!empty($user)) {
                $user = $user[0];

                // upon success, log user out
                update_user_meta($user->ID, 'logged_out_at', time());
            }
        }

        public static function logged_out_check() {
            // if the user is logged into WP but logged out with Clef, sign them out of Wordpress
            if (is_user_logged_in() && isset($_SESSION['logged_in_at']) && $_SESSION['logged_in_at'] < get_user_meta(wp_get_current_user()->ID, "logged_out_at", true)) {
                wp_logout();
                return true;
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