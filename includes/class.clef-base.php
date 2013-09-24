<?php
    class ClefBase {

        public static function setting( $name , $value=false ) {
            static $clef_settings = NULL;
            if ( $clef_settings === NULL ) {
                $clef_settings = get_option( CLEF_OPTIONS_NAME );
            }
            if ($value) {
                $clef_settings[$name] = $value;
                update_option(CLEF_OPTIONS_NAME, $clef_settings);
                return $value;
            } else {
                if ( isset( $clef_settings[$name] ) ) {
                    return $clef_settings[$name];
                }
            }

            // Fall-through
            return FALSE;
        }

        public static function delete_setting($name) {
            static $clef_settings = NULL;
            if ( $clef_settings === NULL ) {
                $clef_settings = get_option( CLEF_OPTIONS_NAME );
            }
            if (isset($clef_settings[$name])) {
                $value = $clef_settings[$name];
                unset($clef_settings[$name]);
                update_option(CLEF_OPTIONS_NAME, $clef_settings);
                return $value;
            }
            
            return FALSE;
        }

    }
?>