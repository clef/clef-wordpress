<?php
/**
 * Plugin-wide utility functions
 *
 * @package Clef
 * @since 2.0
 */
class ClefUtils {
    public static $default_roles = array("Subscriber", "Contributor", "Author", "Editor", "Administrator", "Super Administrator" );
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

    public static function style_has_been_added($name) {
        $ident = "wpclef-" . $name;
        return wp_style_is($ident, 'enqueued')
            || wp_style_is($ident, 'done')
            || wp_style_is($ident, 'to_do');
    }

    public static function script_has_been_added($name) {
        $ident = "wpclef-" . $name;
        return wp_script_is($ident, 'enqueued')
            || wp_script_is($ident, 'done')
            || wp_script_is($ident, 'to_do');
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

        $user = get_users(array(
            'meta_key' => 'clef_id',
            'meta_value' => $clef_id,
            'blog_id' => false
        ));

        if (!empty($user))  {
            return new WP_Error(
                'clef_id_already_associated',
                __("The Clef account you're trying to connect is already associated to a different WordPress account", "wpclef")
            );
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
        ClefUtils::verify_state();

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
            throw new LoginException(__("Something went wrong: ", 'wpclef' ) . $response->get_error_message());
        }

        $body = json_decode( $response['body'] );

        if ( !isset($body->success) || $body->success != 1 ) {
            throw new LoginException(__('Error retrieving Clef access token: ', 'wpclef') . $body->error);
        }

        $access_token = $body->access_token;

        // Get info
        $response = wp_remote_get( CLEF_API_BASE . "info?access_token={$access_token}" );
        if ( is_wp_error($response)  ) {
            throw new LoginException(__("Something went wrong: ", 'wpclef' ) . $response->get_error_message());
        }

        $body = json_decode( $response['body'] );

        if ( !isset($body->success) || $body->success != 1 ) {
            throw new LoginException(__('Error retrieving Clef user data: ', 'wpclef')  . $body->error);
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

    public static function get_custom_roles() {
        $all_roles = get_editable_roles();
        $custom_roles = array();
        foreach($all_roles as $role => $role_obj) {
            if (isset($role_obj['name'])) {
                $role_name = $role_obj['name'];
                if (!in_array($role_name, self::$default_roles)) {
                    $custom_roles[$role] = $role_obj;
                }
            }
        }
        return $custom_roles;
    }

    public static function initialize_state($override = false) {
        if (!$override && isset($_COOKIE['_clef_state']) && $_COOKIE['_clef_state']) return;

        $state = wp_generate_password(24, false);
        @setcookie('_clef_state', $state, (time() + 60 * 60 * 24), '/', '', is_ssl(), true);
        $_COOKIE['_clef_state'] = $state;

        return $state;
    }

    public static function get_state() {
        if (!isset($_COOKIE['_clef_state']) || !$_COOKIE['_clef_state']) ClefUtils::initialize_state();
        return $_COOKIE['_clef_state'];
    }

    public static function verify_state() {
        $request_state = ClefUtils::isset_GET('state') ? ClefUtils::isset_GET('state') : ClefUtils::isset_POST('state');
        $correct_state = ClefUtils::get_state();

        if ($request_state && $correct_state && $correct_state == $request_state) {
            ClefUtils::initialize_state(true);
            return true;
        } else {
            throw new ClefStateException('The state parameter is not verified. This may be due to this page being cached by another WordPress plugin. Please refresh your page and try again. If the issue persists, please follow <a href="http://support.getclef.com/article/95-the-state-parameter-is-not-verified-error#caching" target="_blank">this guide</a> to debug the issue.');
        }
    }

    public static function send_email($email, $subject, $template, $vars) {
        // Get the site domain and get rid of www.
        $sitename = strtolower( $_SERVER['SERVER_NAME'] );
        if ( substr( $sitename, 0, 4 ) == 'www.' ) {
                $sitename = substr( $sitename, 4 );
        }
        $from_email = 'wordpress@' . $sitename;

        $message = ClefUtils::render_template(
            $template,
            $vars,
            false
        );

        $headers = "From: WordPress <".$from_email."> \r\n";

        add_filter('wp_mail_content_type', array('ClefUtils', 'set_html_content_type'));
        $sent = wp_mail($email, $subject, $message, $headers);
        remove_filter('wp_mail_content_type', array('ClefUtils', 'set_html_content_type'));

        return $sent;
    }
}
?>
