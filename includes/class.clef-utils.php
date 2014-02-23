<?php
/**
 * Plugin-wide utility functions
 *
 * @package Clef
 * @since 2.0
 */
class ClefUtils {
    /**
     * Runs esc_html on strings. Leaves input untouched if it's not a string.
     *
     * @return mixed
     */
    private static function escape_string($maybe_string) {
        $escaped = $maybe_string;
        if (is_string($maybe_string)) {
            $escaped = esc_html($maybe_string);
        }
        return $escaped;
    }

    /**
     * Renders the specified template, giving it access to $variables. 
     * Strings are escaped.
     *
     * @param string $name The name (with no .php extension) of a file in 
     *   templates/.
     * @param array $variables A list of variables to be used in the 
     *   template.
     * @return string
     */
    public static function render_template($name, $variables=false, $sanitize=true) {
        if ($variables) {
            $escaped_variables = $variables;
            if ($sanitize) {
                $escaped_variables = array_map(array(__CLASS__, 'escape_string'), $variables);
            }
            extract($escaped_variables, EXTR_SKIP);
        }
        ob_start();
        require(CLEF_TEMPLATE_PATH . $name . '.php');
        return ob_get_clean();
    }

    /**
     * Return $_GET[$key] if it exists.
     *
     * @param string $key
     * @return mixed
     */
    public static function isset_GET($key) {
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }

    /**
     * Return $_POST[$key] if it exists.
     *
     * @param string $key
     * @return mixed
     */
    public static function isset_POST($key) {
        return isset($_POST[$key]) ? $_POST[$key] : null;
    }

    /**
     * Return $_REQUEST[$key] if it exists.
     *
     * @param string $key
     * @return mixed
     */
    public static function isset_REQUEST($key) {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
    }

    public static function set_html_content_type() {
        return 'text/html';
    }

    public static function register_script($name, $dependencies=array('jquery')) {
        $ident = "wpclef-" . $name;
        if (!CLEF_DEBUG)  {
            $name .= '.min';
        }
        $name .= '.js';
        wp_register_script(
            $ident, 
            CLEF_URL .'assets/dist/js/' . $name, 
            $dependencies, 
            CLEF_VERSION, 
            TRUE
        );
        wp_localize_script($ident, "clefTranslations", ClefTranslation::javascript());
        return $ident;
    }

    public static function register_style($name) {
        $ident = "wpclef-" . $name;
        if (!CLEF_DEBUG) {
            $name .= '.min';
        }
        $name .= '.css';
        wp_register_style(
            $ident, 
            CLEF_URL . 'assets/dist/css/' . $name, 
            FALSE, 
            CLEF_VERSION
        ); 
        return $ident;
    }

    public static function user_has_clef($user=false) {
        # if no user is provided, defaults to current user
        if (!$user) $user = wp_get_current_user();
        return !!get_user_meta($user->ID, "clef_id", true);
    }

    public static function associate_clef_id($clef_id, $user_id=false) {
        if (!$user_id) {
            $user_id = wp_get_current_user()->ID;
        }

        update_user_meta($user_id, 'clef_id', $clef_id);
    }

    public static function dissociate_clef_id($user_id=false) {
        if (!$user_id) {
            $user_id = wp_get_current_user()->ID;
        }
        
        delete_user_meta($user_id, "clef_id");
    }

    public static function exchange_oauth_code_for_info($code, $settings=null, $app_id=false, $app_secret=false) {
        if ($settings) {
            if (!$app_id) $app_id = $settings->get( 'clef_settings_app_id' );
            if (!$app_secret) $app_secret = $settings->get( 'clef_settings_app_secret' );
        }

        $args = array(
            'code' => $code,
            'app_id' => $app_id,
            'app_secret' => $app_secret,
        );

        $response = wp_remote_post( CLEF_API_BASE . 'authorize', array( 'method'=> 'POST', 'body' => $args, 'timeout' => 20 ) ); 

        if ( is_wp_error($response)  ) {
            throw new LoginException(__( "Something went wrong: ", 'clef' ) . $response->get_error_message());
        }

        $body = json_decode( $response['body'] );

        if ( !isset($body->success) || $body->success != 1 ) {
            throw new LoginException(__( 'Error retrieving Clef access token: ', 'clef') . $body->error);
        }

        $access_token = $body->access_token;

        // Get info
        $response = wp_remote_get( CLEF_API_BASE . "info?access_token={$access_token}" );
        if ( is_wp_error($response)  ) {
            throw new LoginException(__( "Something went wrong: ", 'clef' ) . $response->get_error_message());
        }

        $body = json_decode( $response['body'] );

        if ( !isset($body->success) || $body->success != 1 ) {
            throw new LoginException(__('Error retrieving Clef user data: ', 'clef')  . $body->error);
        }

        return $body->info;
    }

    public static function user_fulfills_role($user, $role) {
        $fulfills_role = false;
        $role_map = array( 
            "subscriber",
            "contributor",
            "author",
            "editor",
            "administrator",
            "super administrator"
        );

        foreach ($user->roles as &$user_role) {
            $rank = array_search($user_role, $role_map);
            if ($rank != 0 && $rank >= array_search($role, $role_map)) {
                $fulfills_role = true;
                break;
            }
        } 

        if ($role == "super administrator" && is_super_admin($user->ID)) {
            $fulfills_role = true;
        }
        return $fulfills_role;
    }

}
?>
