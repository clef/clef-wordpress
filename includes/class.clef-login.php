<?php
    class ClefLogin extends ClefBase {

        public static function init() {
            add_action('authenticate', array(__CLASS__, 'authenticate_clef'), 10, 3);

            add_action('wp_authenticate_user', array(__CLASS__, 'disable_passwords'));
            add_filter('wp_authenticate_user', array(__CLASS__, 'clear_logout_hook'));

            add_action('login_form', array( __CLASS__, 'login_form' ) );
            add_action('login_form_login', array( __CLASS__, 'disable_login_form' ) );
            add_filter('wp_login_failed', array(__CLASS__, 'handle_login_failed'));

            add_filter('allow_password_reset', array(__CLASS__, 'disable_password_reset'));
        }

        public static function login_form() {
            $app_id = self::setting( 'clef_settings_app_id' );
            $app_secret = self::setting( 'clef_settings_app_secret' );
            if( !empty( $app_id ) && !empty( $app_secret ) ) {
                $redirect_url = add_query_arg(array( 'clef' => 'true'), wp_login_url());

                # add redirect to if it exists
                if (isset($_REQUEST['redirect_to'])) {
                    $redirect_url = add_query_arg(
                        array('redirect_to' => $_REQUEST['redirect_to']), 
                        $redirect_url
                    );
                }

                include CLEF_TEMPLATE_PATH."login_page.tpl.php";
            }
        }

        public static function handle_login_failed($errors) {
            if (isset($_POST['override'])) {
                // if the person submitted an override before, automatically 
                // submit it for them the next time
                $_GET['override'] = $_POST['override'];
            }
        }

        public static function disable_login_form($user) {
            if ( (self::setting( 'clef_password_settings_force' ) == 1) && empty($_POST)) {
                $key = self::setting( 'clef_override_settings_key' );
                if (is_user_logged_in()) {
                    header("Location: " . admin_url() );
                    exit();
                } elseif ( !empty($key) && !empty($_GET['override']) && ($_GET['override'] === $key) ) {
                    return;
                } else {
                    wp_enqueue_script('jquery');
                    login_header(__('Log In'), 'clef'); ?>
                    <form name="loginform" id="loginform" action="" method="post">
                    <?php do_action('login_form'); ?>
                    </form>
                    <?php login_footer();
                    exit();
                }
            }
        }

        public static function disable_passwords($user) {
            if (empty($_POST)) return;

            if (isset($_POST['override']) && self::valid_override($_POST['override'])) {
                return;
            }

            $disable = !self::passwords_are_disabled_for_user($user->ID);

            if ($disable && !(XMLRPC_REQUEST && self::xml_passwords_enabled())) {
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
            return !self::passwords_are_disabled_for_user($user_id);
        }

        public static function clear_logout_hook($user) {
            if (isset($_SESSION['logged_in_at'])) {
                unset($_SESSION['logged_in_at']);
            }
            return $user;
        }

        private static function valid_override($override) {
            $valid_override = self::setting('clef_override_settings_key');
            return $valid_override && $valid_override != "" && $override == $valid_override;
        }

        public static function return_xml_error_message() {
            return new IXR_Error( 403, "Passwords have been disabled for this user." );
        }
    }
?>
