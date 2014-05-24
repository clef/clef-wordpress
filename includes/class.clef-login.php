<?php
require_once(CLEF_PATH . 'includes/class.clef-invite.php');

class ClefLogin {
    private static $instance = null;

    private $settings;

    private function __construct($settings) {
        $this->settings = $settings;
        $this->session = ClefSession::start();
        $this->initialize_hooks();
    }

    public function initialize_hooks() {
        // Authenticate with Clef is there is a valid OAuth code present
        add_action('authenticate', array($this, 'authenticate_clef'), 10, 3);

        // Disable password authentication according to settings
        add_action('wp_authenticate_user', array($this, 'disable_passwords'));
        // Clear logout hook if the user is logging in again
        add_filter('wp_authenticate_user', array($this, 'clear_logout_hook'));

        // adds classes which hide login form if appropriate
        add_action('login_body_class', array($this, 'add_login_form_classes' ));

        // Render the login form with the Clef button no matter what
        add_action('login_form', array( $this, 'login_form' ) );

        add_filter('wp_login_failed', array($this, 'handle_login_failed'));
        add_action('login_enqueue_scripts', array($this, 'load_base_styles'));

        // Show an error message if there is an invite code but it is invalid
        add_filter('login_message', array($this, 'validate_invite'));

        // Disable password reset, according to settings
        add_filter('allow_password_reset', array($this, 'disable_password_reset'), 10, 2);

        // Redirect to an Clef onboarding page if a user logs in with an invite
        // code
        add_filter('login_redirect', array($this, 'redirect_if_invite_code'), 10, 3);

        // Allow the Clef button to be rendered anywhere
        add_action('clef_render_login_button', array($this, 'render_login_button'), 10, 2);

        if (defined('MEMBERSHIP_MASTER_ADMIN') && defined('MEMBERSHIP_SETACTIVATORAS_ADMIN')) {
            add_action('signup_hidden_fields', array($this, 'add_clef_login_button_to_wpmu'));
            add_action('bp_before_account_details_fields', array($this, 'add_clef_login_button_to_wpmu'));
            add_action('membership_popover_extend_registration_form', array($this, 'add_clef_login_button_to_wpmu'));
            add_action('signup_extra_fields', array($this, 'add_clef_login_button_to_wpmu'));
            add_action('membership_popover_extend_login_form', array($this, 'add_clef_login_button_to_wpmu'));
        }

        if ($this->settings->registration_with_clef_is_allowed()) {
            add_action('register_form', array($this, 'login_form'));
        }

        $this->apply_filter_and_action_fixes('init');
    }

    public function add_clef_login_button_to_wpmu() {
        if ($this->settings->is_configured()) {
            $_REQUEST['redirect_to'] = $redirect_url = apply_filters('wdfb_registration_redirect_url', '');
            do_action('clef_render_login_button');
        }
    }

    public function load_base_styles() {
        $ident = ClefUtils::register_style('main');
        wp_enqueue_style($ident);
        if (!has_action('login_enqueue_scripts', 'wp_print_styles'))
            add_action('login_enqueue_scripts', 'wp_print_styles', 11);
    }

    public function redirect_if_invite_code($redirect_to, $request, $user) {
        if (isset($_REQUEST['clef_invite_code'])) {
            $invite_code = $_REQUEST['clef_invite_code'];
            $invite_email = base64_decode(ClefUtils::isset_GET('clef_invite_id'));
            $error = $this->validate_invite_code($invite_code, $invite_email);
            if (!$error) {
                return admin_url('admin.php?page=connect_clef_account');
            }
        }
        return $redirect_to;
    }

    public function validate_invite() {
        $invite_code = ClefUtils::isset_GET('clef_invite_code');
        $invite_email = base64_decode(ClefUtils::isset_GET('clef_invite_id'));
        $error = $this->validate_invite_code($invite_code, $invite_email);
        if ($invite_code && $error) {
            return '<div id="login_error">' . $error . '</div>';
        }
    }

    public function login_form() {
        if($this->settings->is_configured()) {
            $redirect_url = add_query_arg(array( 'clef' => 'true'), wp_login_url());

            # add redirect to if it exists
            if (isset($_REQUEST['redirect_to']) && $_REQUEST['redirect_to'] != '') {
                $redirect_url = add_query_arg(
                    array('redirect_to' => urlencode($_REQUEST['redirect_to'])),
                    $redirect_url
                );
            }

            $passwords_disabled = $this->settings->get('clef_password_settings_force');

            $override_key = ClefUtils::isset_GET('override');
            if (!$this->is_valid_override_key($override_key)) {
                $override_key = null;
            }

            $invite_code = ClefUtils::isset_GET('clef_invite_code');
            $invite_email_encoded = ClefUtils::isset_GET('clef_invite_id');
            if (!$this->has_valid_invite_code()) {
                $invite_code = null;
                $invite_email = null;
            } else {
                $redirect_url = add_query_arg(array( 'invite_code' => $invite_code), $redirect_url);
            }

            $app_id = $this->settings->get( 'clef_settings_app_id' );

            echo ClefUtils::render_template('login_page.tpl', array(
                "passwords_disabled" => $passwords_disabled,
                "override_key" => $override_key,
                "redirect_url" => $redirect_url,
                "invite_code" => $invite_code,
                "invite_email" => $invite_email_encoded,
                "app_id" => $app_id
            ));
        }
    }

    public function render_login_button($redirect_url=false, $app_id=false) {
        if (!$app_id) $app_id = $this->settings->get( 'clef_settings_app_id' );
        if (!$redirect_url) {
            $redirect_url = add_query_arg(array( 'clef' => 'true'), wp_login_url());

            # add redirect to if it exists
            if (isset($_REQUEST['redirect_to']) && $_REQUEST['redirect_to'] != '') {
                $redirect_url = add_query_arg(
                    array('redirect_to' => urlencode($_REQUEST['redirect_to'])),
                    $redirect_url
                );
            }
        }

        echo ClefUtils::render_template('button.tpl', array(
            "app_id" => $app_id,
            "redirect_url" => $redirect_url,
            "custom" => array(
                "logo" => $this->settings->get('customization_logo'),
                "message" => $this->settings->get('customization_message')
            )
        ));
    }

    public function handle_login_failed($errors) {
        if (isset($_POST['override'])) {
            // if the person submitted an override before, automatically
            // submit it for them the next time
            $_GET['override'] = $_POST['override'];
        }
    }

    private function is_valid_override_key($override_key) {
        $valid_key = $this->settings->get( 'clef_override_settings_key' );
        $is_valid_override_key =
            (!empty($valid_key) &&
            !empty($override_key) &&
            $valid_key != '' &&
            $override_key != '' &&
            $override_key === $valid_key);
        return $is_valid_override_key;
    }

    private function validate_invite_code($incoming_invite_code, $email) {
        $generic_error_message = __("Sorry, that isn't a valid invite code.", "clef");
        if (!$incoming_invite_code || !$email) {
            return $generic_error_message;
        }
        $user = get_user_by('email', $email);
        if (empty($user)) {
            return $generic_error_message;
        }
        return $this->validate_invite_code_for_user($incoming_invite_code, $user);
    }

    private function validate_invite_code_for_user($incoming_invite_code, $user) {
        $invite_code = get_user_meta($user->ID, 'clef_invite_code', true);
        $three_days_ago = time() - 3 * 24 * 60 * 60;
        if (empty($invite_code) ||
            ($invite_code->created_at < $three_days_ago) ||
            ($invite_code->code !== $incoming_invite_code)) {
                return __("Sorry, this invite link has expired. Please contact your administrator for a new one.", "clef");
        }
    }

    public function add_login_form_classes($classes) {
        array_push($classes, 'clef-login-form');
         $override_key = ClefUtils::isset_GET('override');

        if ($this->settings->get( 'clef_password_settings_force' )) {
            if (!$this->is_valid_override_key($override_key) && !$this->has_valid_invite_code()) {
                array_push($classes, 'clef-hidden');
            }
        }
        return $classes;
    }

    private function has_valid_invite_code() {
        if (!isset($_REQUEST['clef_invite_code']) || !isset($_REQUEST['clef_invite_id'])) {
            return false;
        }
        $incoming_invite_code = $_REQUEST['clef_invite_code'];
        $invite_email = base64_decode($_REQUEST['clef_invite_id']);
        if (!$incoming_invite_code || !$invite_email) {
            return false;
        }
        $error = $this->validate_invite_code($incoming_invite_code, $invite_email);
        return !$error;
    }

    public function disable_passwords($user) {
        if (isset($_POST['override']) && $this->is_valid_override_key($_POST['override'])) {
            return $user;
        }

        if ($this->has_valid_invite_code()) {
            return $user;
        }

        $disabled_for_user = $this->settings->passwords_are_disabled_for_user($user);
        $disabled_for_xml_rpc = !(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST && $this->settings->xml_passwords_enabled());

        if ($disabled_for_user && $disabled_for_xml_rpc) {
            add_filter('xmlrpc_login_error', array($this, "return_xml_error_message"));
            return new WP_Error('passwords_disabled', __("Passwords have been disabled for this user.", "clef"));
        } else {
            return $user;
        }
    }

    public function authenticate_clef($user, $username, $password) {
        if ( isset( $_REQUEST['clef'] ) && isset( $_REQUEST['code'] ) ) {
            $this->apply_filter_and_action_fixes("authenticate");

            // Authenticate
            try {
                $info = ClefUtils::exchange_oauth_code_for_info($_REQUEST['code'], $this->settings);
            } catch (LoginException $e) {
                return new WP_Error('clef', $e->getMessage());
            }

            $clef_id = $info->id;
            $email = isset($info->email) ? $info->email : "";
            $first_name = isset($info->first_name) ? $info->first_name : "";
            $last_name = isset($info->last_name) ? $info->last_name : "";

            $users = get_users(array('meta_key' => 'clef_id', 'meta_value' => $clef_id, 'blog_id' => false));
            if ($users) {
                // already have a user with this clef_id
                $user = $users[0];
            } else {
                $user = WP_User::get_data_by( 'email', $email );

                if (!$user) {
                    if(!$this->settings->registration_with_clef_is_allowed()) {
                        return new WP_Error(
                            'clef',
                            __("Registration is not allowed and there's no user whose email address matches your phone's Clef account. You must either connect your Clef account on your WordPress profile page or use the same email for both WordPress and Clef.", 'clef')
                        );
                    }

                    // Users can register, so create a new user
                    $id = wp_create_user($email, wp_generate_password(16, FALSE), $email);
                    if(is_wp_error($id)) {
                        return new WP_Error(
                            'clef',
                            __("An error occurred when creating your new account: ", 'clef') . $res->get_error_message()
                        );
                    }
                    $user = get_user_by('id', $id );
                }

                ClefUtils::associate_clef_id($clef_id, $user->ID);
            }

            do_action('clef_login', $user->ID);

            // Log in the user

            $this->session->set('logged_in_at', time());
            return $user;
        } else {
            return $user;
        }
    }

    public function disable_password_reset($allow, $user_id) {
        $user = get_user_by('id', (int) $user_id);
        return !$this->settings->passwords_are_disabled_for_user($user);
    }

    public function clear_logout_hook($user) {
        if ($this->session->get('logged_in_at')) {
            $this->session->set('logged_in_at', null);
        }
        return $user;
    }

    public function return_xml_error_message() {
        return new IXR_Error( 403, __("Passwords have been disabled for this user.", "clef") );
    }

    public function apply_filter_and_action_fixes($hook) {
        if ($hook === "init") {
            // Hack to make Clef work with theme my login. This works
            // because Theme My Login only runs the login commands on their custom
            // login page if the request is a POST. Could potentially cause
            // other issues, so should be conscious of this.
            if (isset($_REQUEST['clef']) && isset($_REQUEST['code']) && function_exists('is_plugin_active') && is_plugin_active('theme-my-login/theme-my-login.php')) {
                $_SERVER['REQUEST_METHOD'] = 'POST';
                $_POST['log'] = true;
            }
        }

        if ($hook === "authenticate") {
            // remove login filters that cause problems — not necessary if we're
            // logging in with Clef. These filters suppress errors that
            // this login function throws.
            remove_filter('authenticate', 'dr_email_login_authenticate', 20, 3);
            remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);

            // remove google captcha filter that prevents redirect
            remove_filter('login_redirect', 'gglcptch_login_check');
        }
    }

    public static function start($settings) {
        if (!isset(self::$instance) || self::$instance === null || defined('CLEF_TESTING')) {
            self::$instance = new self($settings);
        }
        return self::$instance;
    }
}
?>
