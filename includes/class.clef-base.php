<?php
    class ClefBase {

        public static function setting( $name ) {
            static $clef_settings = NULL;
            if ( $clef_settings === NULL ) {
                $clef_settings = get_option( CLEF_OPTIONS_NAME );
            }
            if ( isset( $clef_settings[$name] ) ) {
                return $clef_settings[$name];
            }

            // Fall-through
            return FALSE;
        }
    }
?>