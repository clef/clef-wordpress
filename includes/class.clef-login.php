<?php
    class ClefLogin extends ClefBase {

        public static function init() {
            add_action('authenticate', array(__CLASS__, 'authenticate_clef'), 10, 3);

            add_action('wp_authenticate_user', array(__CLASS__, 'disable_passwords'));
            add_filter('wp_authenticate_user', array(__CLASS__, 'clear_logout_hook'));

            add_action('login_form', array( __CLASS__, 'login_form' ) );
            add_action('login_form_login', array( __CLASS__, 'disable_login_form' ) );
            add_filter('wp_login_failed', array(__CLASS__, 'handle_login_failed'));

            add_filter('login_message', array(__CLASS__, 'validate_invite'));

            add_filter('allow_password_reset', array(__CLASS__, 'disable_password_reset'));

            add_filter('login_redirect', array(__CLASS__, 'redirect_if_invite_code'), 10, 3);
        }

        public static function redirect_if_invite_code($redirect_to, $request, $user) {
            if (isset($_REQUEST['clef_invite_code'])) {
                $invite_code = $_REQUEST['clef_invite_code'];
                $invite_email = base64_decode(ClefUtils::isset_GET('clef_invite_id'));
                $error = self::validate_invite_code($invite_code, $invite_email);
                if (!$error) {
                    return get_edit_user_link();
                }
            }
            return $redirect_to;
        }

        public static function validate_invite() {
            $invite_code = ClefUtils::isset_GET('clef_invite_code');
            $invite_email = base64_decode(ClefUtils::isset_GET('clef_invite_id'));
            $error = self::validate_invite_code($invite_code, $invite_email);
            if ($invite_code && $error) {
                return '<div id="login_error">' . $error . '</div>';
            }
        }

        public static function login_form() {
            if(self::is_configured()) {
                $redirect_url = add_query_arg(array( 'clef' => 'true'), wp_login_url());

                # add redirect to if it exists
                if (isset($_REQUEST['redirect_to'])) {
                    $redirect_url = add_query_arg(
                        array('redirect_to' => $_REQUEST['redirect_to']), 
                        $redirect_url
                    );
                }

                $passwords_disabled = Clef::setting('clef_password_settings_force');

                $override_key = ClefUtils::isset_GET('override');
                if (!self::is_valid_override_key($override_key)) {
                    $override_key = null;
                }

                $invite_code = ClefUtils::isset_GET('clef_invite_code');
                $invite_email_encoded = ClefUtils::isset_GET('clef_invite_id');
                if (!self::has_valid_invite_code()) {
                    $invite_code = null;
                    $invite_email = null;
                } else {
                    $redirect_url = add_query_arg(array( 'invite_code' => $invite_code), $redirect_url);
                }

                $app_id = self::setting( 'clef_settings_app_id' );
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

        public static function handle_login_failed($errors) {
            if (isset($_POST['override'])) {
                // if the person submitted an override before, automatically 
                // submit it for them the next time
                $_GET['override'] = $_POST['override'];
            }
        }

        private static function is_valid_override_key($override_key) {
            $valid_key = self::setting( 'clef_override_settings_key' );
            $is_valid_override_key = 
                (!empty($valid_key) && 
                !empty($override_key) && 
                $valid_key != '' &&
                $override_key != '' &&
                $override_key === $valid_key);
            return $is_valid_override_key;
        }

        private static function validate_invite_code($incoming_invite_code, $email) {
            $generic_error_message = "Sorry, that isn't a valid invite code.";
            if (!$incoming_invite_code || !$email) {
                return $generic_error_message;
            }
            $user = get_user_by('email', $email);
            if (empty($user)) {
                return $generic_error_message;
            }
            return self::validate_invite_code_for_user($incoming_invite_code, $user);
        }

        private static function validate_invite_code_for_user($incoming_invite_code, $user) {
            $invite_code = get_user_meta($user->ID, 'clef_invite_code', true);
            $three_days_ago = time() - 3 * 24 * 60 * 60;
            if ((empty($invite_code)) ||
                ($invite_code->created_at < $three_days_ago) ||
                ($invite_code->code !== $incoming_invite_code)) {
                    return "Sorry, this invite link has expired. " . 
                        "Please contact your administrator for a new one.";
            }
        }

        private static function has_valid_invite_code() {
            if (!isset($_REQUEST['clef_invite_code']) || !isset($_REQUEST['clef_invite_id'])) {
                return false;
            }
            $incoming_invite_code = $_REQUEST['clef_invite_code'];
            $invite_email = base64_decode($_REQUEST['clef_invite_id']);
            error_log(print_r($invite_email));
            if (!$incoming_invite_code || !$invite_email) {
                return false;
            }
            $error = self::validate_invite_code($incoming_invite_code, $invite_email);
            return !$error;
        }

        public static function disable_login_form($user) {
            if ( (self::setting( 'clef_password_settings_force' ) == 1) && !isset($_REQUEST['clef']) && !isset($_REQUEST['code'])) {

                $override_key = ClefUtils::isset_GET('override');

                $is_overridden = self::is_valid_override_key($override_key) || self::has_valid_invite_code();

                if (is_user_logged_in()) {
                    header("Location: " . admin_url() );
                    exit();
                } elseif ($is_overridden) {
                    return;
                } else {
                    wp_enqueue_script('jquery');
                    login_header(__('Log In', 'clef')); ?>
                    <form name="loginform" id="loginform" action="" method="post">
                    <?php do_action('login_form'); ?>
                    </form>
                    <?php login_footer();
                    exit();
                }
            }
        }

        public static function disable_passwords($user) {
            if (empty($_POST)) return $user;

            if (isset($_POST['override']) && self::is_valid_override_key($_POST['override'])) {
                return $user;
            }
            if (self::has_valid_invite_code()) {
                return $user;
            }

            $disabled_for_user = self::passwords_are_disabled_for_user($user);
            $disabled_for_xml_rpc = !(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST && self::xml_passwords_enabled());

            if ($disabled_for_user && $disabled_for_xml_rpc) {
                add_filter('xmlrpc_login_error', array(__CLASS__, "return_xml_error_message"));
                return new WP_Error('passwords_disabled', "Passwords have been disabled for this user.");
            } else {
                return $user;
            }
        }

        public static function authenticate_clef($user, $username, $password) {
            if ( isset( $_REQUEST['clef'] ) && isset( $_REQUEST['code'] ) ) {
                // Authenticate
                try {
                    $info = self::exchange_oauth_code_for_info($_REQUEST['code']);
                } catch (LoginException $e) {
                    return new WP_Error('clef', $e->getMessage());
                }

                $clef_id = $info->id;
                $email = isset($info->email) ? $info->email : "";
                $first_name = isset($info->first_name) ? $info->first_name : "";
                $last_name = isset($info->last_name) ? $info->last_name : "";

                $current_user = wp_get_current_user();

                $users = get_users(array('meta_key' => 'clef_id', 'meta_value' => $clef_id));
                if ($users) {
                    // already have a user with this clef_id
                    $user = $users[0];
                } else {
                    $user = WP_User::get_data_by( 'email', $email );

                    if (!$user) {
                        $users_can_register = get_site_option('users_can_register', 0);
                        if(!$users_can_register) {
                            return new WP_Error(
                                'clef', 
                                __("There's no user whose email address matches your phone's Clef account. You must either connect your Clef account on your WordPress profile page or use the same email for both WordPress and Clef.", 'clef')
                            );
                        }

                        // Users can register, so create a new user
                        // and attach the clef_id to them
                        $userdata = new WP_User();
                        $userdata->first_name = $first_name;
                        $userdata->last_name = $last_name;
                        $userdata->user_email = $email;
                        $userdata->user_login = $email;
                        $password = wp_generate_password(16, FALSE);
                        $userdata->user_pass = $password;

                        $id = wp_insert_user($userdata);
                        if(is_wp_error($id)) {
                            return new WP_Error(
                                'clef',
                                __("An error occurred when creating your new account: ", 'clef') . $res->get_error_message()
                            );
                        }
                        $user = get_user_by('id', $id );
                    }

                    self::associate_clef_id($clef_id, $user->ID);
                }

                ClefOnboarding::mark_login();

                // Log in the user
                $_SESSION['logged_in_at'] = time();
                return $user;
            }
        }

        public static function disable_password_reset($user_id) {
            $user = get_user_by('id', $user_id);
            return !self::passwords_are_disabled_for_user($user);
        }

        public static function clear_logout_hook($user) {
            if (isset($_SESSION['logged_in_at'])) {
                unset($_SESSION['logged_in_at']);
            }
            return $user;
        }

        public static function return_xml_error_message() {
            return new IXR_Error( 403, "Passwords have been disabled for this user." );
        }
    }
?>
