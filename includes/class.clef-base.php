<?php
    class ClefBase {

        const NS_TABLE_NAME = "clef_network_settings";
        const MS_ENABLED_OPTION = "clef_multisite_enabled";
        const MS_OVERRIDE_OPTION = 'clef_multisite_override';

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

    }
?>