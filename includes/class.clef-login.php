<?php
    class ClefLogin extends ClefBase {

        public static function init() {
            add_action('login_form', array( __CLASS__, 'login_form' ) );
            add_action('wp_authenticate', array(__CLASS__, 'disable_passwords'));
            add_action('login_form_login', array( __CLASS__, 'disable_login_form' ) );
            add_action('login_message', array( __CLASS__, 'login_message' ) );
            add_filter('wp_login_failed', array(__CLASS__, 'handle_login_failed'));

            self::handle_callback();
        }

        public static function login_form() {
            $app_id = self::setting( 'clef_settings_app_id' );
            $redirect_url = trailingslashit( home_url() ) . "?clef_callback=clef_callback&";
            include CLEF_TEMPLATE_PATH."login_page.tpl.php";
        }

        public static function login_message() {
            $_SESSION['Clef_Messages'] = array_unique( $_SESSION['Clef_Messages'] );
            foreach ( $_SESSION['Clef_Messages'] as $message ) {
                echo '<div id="login_error"><p><strong>ERROR</strong>: ' . $message . '</p></div>';
            }
            $_SESSION['Clef_Messages'] = array();
        }

        public static function redirect_error() {
            if (!is_user_logged_in()) {
                header( 'Location: ' . wp_login_url() );
            } else {
                header( 'Location: ' . get_edit_profile_url(wp_get_current_user()->ID));
            }
            exit();
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
                $key = self::setting( 'clef_password_settings_override_key' );
                if (is_user_logged_in()) {
                    header("Location: " . admin_url() );
                    exit();
                } elseif ( !empty($key) && !empty($_GET['override']) && ($_GET['override'] === $key) ) {
                    return;
                } else {
                    wp_enqueue_script('jquery');
                    login_header(__('Log In'), ''); ?>
                    <form name="loginform" id="loginform" action="" method="post">
                    <?php do_action('login_form'); ?>
                    </form>
                    <?php login_footer();
                    exit();
                }
            }
        }

        public static function disable_passwords($username) {
            if (empty($_POST)) return;

            $exit = false;
            
            if (isset($_POST['override']) && self::valid_override($_POST['override'])) {
                return;
            }

            if (self::setting('clef_password_settings_force')) {
                $exit = true;
            }

            if (self::setting( 'clef_password_settings_disable_passwords' )) {
                if(username_exists($username)) {
                    $user = get_user_by('login', $username);

                    if (get_user_meta($user->ID, 'clef_id')) {
                        $exit = true;
                    }
                }
            }

            if ($exit) {
                $_SESSION['Clef_Messages'][] = "Passwords have been disabled.";
                header("Location: " . wp_login_url());
                exit();
            }
        }

        public static function handle_callback() {

            if ( isset( $_REQUEST['clef_callback'] ) && isset( $_REQUEST['code'] ) ) {

                // Authenticate

                $args = array(
                    'code' => $_REQUEST['code'],
                    'app_id' => self::setting( 'clef_settings_app_id' ),
                    'app_secret' => self::setting( 'clef_settings_app_secret' ),
                );

                $response = wp_remote_post( CLEF_API_BASE . 'authorize', array( 'method'=> 'POST', 'body' => $args, 'timeout' => 20 ) ); 

                if ( is_wp_error($response)  ) {
                    $_SESSION['Clef_Messages'][] = "Something went wrong: " . $response->get_error_message();
                    self::redirect_error();
                    return;
                }

                $body = json_decode( $response['body'] );

                if ( !isset($body->success) || $body->success != 1 ) {
                    $_SESSION['Clef_Messages'][] = 'Error retrieving Clef access token: ' . $body->error;
                    self::redirect_error();
                }

                $access_token = $body->access_token;
                $_SESSION['wpclef_access_token'] = $access_token;

                // Get info
                $response = wp_remote_get( CLEF_API_BASE . "info?access_token={$access_token}" );
                if ( is_wp_error($response)  ) {
                    $_SESSION['Clef_Messages'][] = "Something went wrong: " . $response->get_error_message();
                    self::redirect_error();
                    return;
                }

                $body = json_decode( $response['body'] );

                if ( !isset($body->success) || $body->success != 1 ) {
                    $_SESSION['Clef_Messages'][] = 'Error retrieving Clef user data: '  . $body->error;
                    self::redirect_error();
                }

                $info = $body->info;
                $clef_id = $info->id;
                $email = isset($info->email) ? $info->email : "";
                $first_name = isset($info->first_name) ? $info->first_name : "";
                $last_name = isset($info->last_name) ? $info->last_name : "";

                if (is_user_logged_in() && !get_user_meta(wp_get_current_user()->ID, "clef_id", true)) {
                    // do state CSRF check
                    if (!isset($_GET['state']) || !wp_verify_nonce($_GET['state'], 'connect_clef')) die();
                    $existing_user = wp_get_current_user();
                    update_user_meta($existing_user->ID, 'clef_id', $clef_id);
                    $redirect = get_edit_profile_url($existing_user->ID) . "?updated=1";
                } else {

                    $users = get_users(array('meta_key' => 'clef_id', 'meta_value' => $clef_id));
                    if ($users) $existing_user = $users[0];
                    else $existing_user =  WP_User::get_data_by( 'email', $email );

                    if ( !$existing_user ) {
                        $users_can_register = get_site_option('users_can_register', 0);
                        if(!$users_can_register) {
                            $_SESSION['Clef_Messages'][] = "There's no user whose email address matches your phone's Clef account. You must either connect your Clef account on your WordPress profile page or use the same email for both WordPress and Clef.";
                            self::redirect_error();
                        }

                        // Register a new user
                        $userdata = new WP_User();
                        $userdata->first_name = $first_name;
                        $userdata->last_name = $last_name;
                        $userdata->user_email = $email;
                        $userdata->user_login = $email;
                        $password = wp_generate_password(16, FALSE);
                        $userdata->user_pass = $password;
                        $res = wp_insert_user($userdata);
                        if(is_wp_error($res)) {
                            $_SESSION['Clef_Messages'][] = "An error occurred when creating your new account: " . $res->get_error_message();
                            self::redirect_error();
                        }
                        $existing_user = WP_User::get_data_by( 'email', $email );

                        update_user_meta($existing_user->ID, 'clef_id', $clef_id);

                    }

                    update_user_meta($existing_user->ID, 'clef_id', $clef_id);

                    $user = wp_set_current_user( $existing_user->ID, $existing_user->user_nicename );
                    wp_set_auth_cookie( $existing_user->ID );
                    do_action( 'wp_login', $existing_user->ID );

                    $redirect = admin_url();

                }

                // Log in the user
                $_SESSION['logged_in_at'] = time();

                header( "Location: " . $redirect );
                exit();

            }
        }

        private static function valid_override($override) {
            $valid_override = self::setting('clef_password_settings_override_key');
            return $valid_override && $valid_override != "" && $override == $valid_override;
        }
    }
?>