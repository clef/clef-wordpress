<?php
    class ClefBase {

        const MS_ENABLED_OPTION = "clef_multisite_enabled";
        const MS_OVERRIDE_OPTION = 'clef_multisite_override';
        const MS_USER_SITE_TABLE_NAME = "clef_user_sites";

        private static $_individual_settings = null;

        public static function setting( $name , $value=false ) {

            if (self::individual_settings()) {
                $getter = 'get_option';
                $setter = 'update_option';
            } else {
                $getter = 'get_site_option';
                $setter = 'update_site_option';
            }

            static $clef_settings = NULL;
            if ( $clef_settings === NULL ) {
                $clef_settings = $getter( CLEF_OPTIONS_NAME );
            }
            
            if ($value) {
                $clef_settings[$name] = $value;
                $setter(CLEF_OPTIONS_NAME, $clef_settings);
                return $value;
            } else {
                if ( isset( $clef_settings[$name] ) ) {
                    return $clef_settings[$name];
                }
            }

            // Fall-through
            return FALSE;
        }


        /**
        * Function to check whether site's settings are controlled by 
        * multisite or not.
        **/
        public static function individual_settings() {
            $multisite_enabled = get_site_option(self::MS_ENABLED_OPTION);
            if (!$multisite_enabled) return true;

            if (is_null(self::$_individual_settings)) {
                // we cache this check, this solves an issue on the first update

                $override = get_option(self::MS_OVERRIDE_OPTION, 'undefined');

                // check to see whether the override is set (it would not be set
                // if the blog had previously been used without multisite 
                // enabled). sets it if it is null.
                if ($override == "undefined") {
                    $override = !!get_option(CLEF_OPTIONS_NAME);
                    add_option(self::MS_OVERRIDE_OPTION, $override);
                }

                self::$_individual_settings = $override && !is_network_admin();
            } 
            return self::$_individual_settings;

        }

        public static function delete_setting($name) {
            static $clef_settings = NULL;
            if ( $clef_settings === NULL ) {
                $clef_settings = get_site_option( CLEF_OPTIONS_NAME );
            }
            if (isset($clef_settings[$name])) {
                $value = $clef_settings[$name];
                unset($clef_settings[$name]);
                update_site_option(CLEF_OPTIONS_NAME, $clef_settings);
                return $value;
            }
            
            return FALSE;
        }

        public static function redirect_error() {
            if (!is_user_logged_in()) {
                header( 'Location: ' . wp_login_url() );
            } else {
                header( 'Location: ' . get_edit_profile_url(wp_get_current_user()->ID));
            }
            exit();
        }

        protected static function associate_clef_id($clef_id, $user_id=false) {
            if (!$user_id) {
                $user_id = wp_get_current_user()->ID;
            }

            if (is_multisite()) {
                $site_id = get_current_site()->id;

                global $wpdb;

                $tablename = self::table_name(self::MS_USER_SITE_TABLE_NAME);

                $sql = $wpdb->prepare("INSERT IGNORE INTO $tablename (clef_id, site_id) VALUES (%s, %d);", $clef_id, $site_id);
                $res = $wpdb->query($sql);

                if(is_wp_error($res)) {
                    $_SESSION['Clef_Messages'][] = "An error occurred when creating your new account: " . $res->get_error_message();
                    self::redirect_error();
                }
            }

            update_user_meta($user_id, 'clef_id', $clef_id);
        }

        protected static function dissociate_clef_id($user_id=false) {

            if (is_multisite()) {
                $site_id = get_current_site()->id;
                $clef_id = get_user_meta($user_id, 'clef_id', true);

                global $wpdb;

                $tablename = self::table_name(self::MS_USER_SITE_TABLE_NAME);

                $sql = $wpdb->prepare("DELETE FROM $tablename WHERE clef_id = %s AND site_id = %d;", $clef_id, $site_id);

                $res = $wpdb->query($sql);

                if(is_wp_error($res)) {
                    $_SESSION['Clef_Messages'][] = "An error occurred when creating your new account: " . $res->get_error_message();
                    self::redirect_error();
                }
            }

            delete_user_meta($user_id, "clef_id");
        }

        public static function table_name($tablename) {
            global $wpdb;
            return $wpdb->prefix . $tablename;
        }
    }
?>