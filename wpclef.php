<?php
/*
Plugin Name: Clef
Plugin URI: http://wordpress.org/extend/plugins/wpclef
Description: Clef lets you log in and register on your Wordpress site using only your phone — forget your usernames and passwords.
Version: 1.7
Author: David Michael Ross
Author URI: http://www.davidmichaelross.com/
License: MIT
License URI: http://opensource.org/licenses/MIT
 */

/**

Copyright (c) 2012 David Ross <dave@davidmichaelross.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

**/

if ( ! defined('ABSPATH') ) exit('restricted access');

if ( !session_id() ) {
	session_start();
}

if ( !isset( $_SESSION['WPClef_Messages'] ) ) {
	$_SESSION['WPClef_Messages'] = array();
}

class WPClef {

	const API_BASE = 'https://clef.io/api/v1/';
	const OPTIONS_NAME = 'wpclef';

	public static function setting( $name ) {
		static $clef_settings = NULL;
		if ( $clef_settings === NULL ) {
			$clef_settings = get_option( self::OPTIONS_NAME );
		}
		if ( isset( $clef_settings[$name] ) ) {
			return $clef_settings[$name];
		}

		// Fall-through
		return FALSE;

	}

	public static function init() {

		if ( isset( $_REQUEST['clef_callback'] ) && isset( $_REQUEST['code'] ) ) {

			// Authenticate

			$args = array(
				'code' => $_REQUEST['code'],
				'app_id' => self::setting( 'clef_settings_app_id' ),
				'app_secret' => self::setting( 'clef_settings_app_secret' ),
			);

			$response = wp_remote_post( self::API_BASE . 'authorize', array( 'method'=> 'POST', 'body' => $args, 'timeout' => 20 ) ); 

			if ( is_wp_error($response)  ) {
				$_SESSION['WPClef_Messages'][] = "Something went wrong: " . $response->get_error_message();
				self::redirect_error();
				return;
			}

			$body = json_decode( $response['body'] );

			if ( !isset($body->success) || $body->success != 1 ) {
				$_SESSION['WPClef_Messages'][] = 'Error retrieving Clef access token: ' . $body->error;
				self::redirect_error();
			}

			$access_token = $body->access_token;
			$_SESSION['wpclef_access_token'] = $access_token;

			// Get info
			$response = wp_remote_get( self::API_BASE . "info?access_token={$access_token}" );
			if ( is_wp_error($response)  ) {
				$_SESSION['WPClef_Messages'][] = "Something went wrong: " . $response->get_error_message();
				self::redirect_error();
				return;
			}

			$body = json_decode( $response['body'] );

			if ( !isset($body->success) || $body->success != 1 ) {
				$_SESSION['WPClef_Messages'][] = 'Error retrieving Clef user data: '  . $body->error;
				self::redirect_error();
			}

			$first_name = $body->info->first_name;
			$last_name = $body->info->last_name;
			$email = $body->info->email;
			$clef_id = $body->info->id;

			if (is_user_logged_in() && !get_user_meta(wp_get_current_user()->ID, "clef_id", true)) {
				$existing_user = wp_get_current_user();
				update_user_meta($existing_user->ID, 'clef_id', $clef_id);
				$redirect = get_edit_profile_url($existing_user->ID) . "?updated=1";
			} else {

				$users = get_users(array('meta_key' => 'clef_id', 'meta_value' => $clef_id));
				if ($users) $existing_user = $users[0];
				else $existing_user =  WP_User::get_data_by( 'email', $email );

				if ( !$existing_user ) {
					$users_can_register = get_option('users_can_register', 0);
					if(!$users_can_register) {
						$_SESSION['WPClef_Messages'][] = "There's no user whose email address matches your phone's Clef account. You must either connect your Clef account on your WordPress profile page or use the same email for both WordPress and Clef.";
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
						$_SESSION['WPClef_Messages'][] = "An error occurred when creating your new account: " . $res->get_error_message();
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

	public static function logout_handler() {

		if(isset($_POST['logout_token'])) {

			$args = array(
				'logout_token' => $_REQUEST['logout_token'],
				'app_id' => self::setting( 'clef_settings_app_id' ),
				'app_secret' => self::setting( 'clef_settings_app_secret' ),
			);

			$response = wp_remote_post( self::API_BASE . 'logout', array( 'method' => 'POST',
				'timeout' => 45, 'body' => $args ) ); 
			$body = json_decode( $response['body'] );

			if (isset($body->success) && $body->success == 1 && isset($body->clef_id)) {
				$user = get_users(array('meta_key' => 'clef_id', 'meta_value' => $body->clef_id));
				$user = $user[0];

				// upon success, log user out
				update_user_meta($user->ID, 'logged_out_at', time());
			}
		}
	}

	public static function login_form() {
		$app_id = self::setting( 'clef_settings_app_id' );
		$redirect_url = trailingslashit( home_url() ) . "?clef_callback=clef_callback&";
		include dirname( __FILE__ )."/login_page.tpl.php";
	}

	public static function show_user_profile() {
		$connected = !!get_user_meta(wp_get_current_user()->ID, "clef_id", true);
		$app_id = self::setting( 'clef_settings_app_id' );
		$redirect_url = trailingslashit( home_url() ) . "?clef_callback=clef_callback&connecting=true";
		include dirname( __FILE__ )."/user_profile.tpl.php";
	}

	public static function edit_user_profile_update($user_id) {
		if (isset($_POST['remove_clef']) && $_POST['remove_clef']) {
			delete_user_meta($user_id, "clef_id");
		}
	}

	public static function edit_profile_errors($errors) {
		if ($_SESSION['WPClef_Messages']) {
			$_SESSION['WPClef_Messages'] = array_unique( $_SESSION['WPClef_Messages'] );
			echo '<div id="login_error">';
			foreach ( $_SESSION['WPClef_Messages'] as $message ) {
				echo '<p><strong>ERROR</strong>: '. $message . ' </p>';
			}
			echo '</div>';
			$_SESSION['WPClef_Messages'] = array();
		}
	}

	public static function login_message() {
		$_SESSION['WPClef_Messages'] = array_unique( $_SESSION['WPClef_Messages'] );
		foreach ( $_SESSION['WPClef_Messages'] as $message ) {
			echo '<div id="login_error"><p><strong>ERROR</strong>: ' . $message . '</p></div>';
		}
		$_SESSION['WPClef_Messages'] = array();
	}

	public static function redirect_error() {
		if (!is_user_logged_in()) {
			header( 'Location: ' . wp_login_url() );
		} else {
			header( 'Location: ' . get_edit_profile_url(wp_get_current_user()->ID));
		}
		exit();
	}

	public static function logged_out_check($redirect=true) {
		// if the user is logged into WP but logged out with Clef, sign them out of Wordpress
		if (is_user_logged_in() && isset($_SESSION['logged_in_at']) && $_SESSION['logged_in_at'] < get_user_meta(wp_get_current_user()->ID, "logged_out_at", true)) {
			wp_logout();
			if ($redirect) {
				self::redirect_error();
			} else {
				return true;
			}
		}
		return false;
	}

	public static function disable_passwords($username) {
		if (empty($_POST)) return;
		
		if (isset($_POST['override']) && $_POST['override'] == self::setting('clef_password_settings_override_key')) {
			return;
		}

		$exit = false;

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
			$_SESSION['WPClef_Messages'][] = "Passwords have been disabled.";
			header("Location: " . wp_login_url());
			exit();
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
	
	public static function disable_lost_password_form() {
		if (!empty($_POST['user_login'])) {
			$user = get_user_by( 'login', $_POST['user_login'] );
		}
		if ( (self::setting( 'clef_password_settings_disable_passwords' ) == 1 && get_user_meta($user->ID, 'clef_id')) || (self::setting( 'clef_password_settings_force' ) == 1)) {
			$_SESSION['WPClef_Messages'][] = "Lost password resets have been disabled.";
			header("Location: " . wp_login_url());
			exit();
		}
	}

	public static function clear_logout_hook($user) {
		if (isset($_SESSION['logged_in_at'])) {
			unset($_SESSION['logged_in_at']);
		}
		return $user;
	}

	public static function hook_heartbeat($response, $data, $screen_id) {
		$logged_out = self::logged_out_check(false);
		if ($logged_out) {
			$response['cleflogout'] = true;
		}
		return $response;
	}
	
	public static function uninstall_wpclef() {
		if (current_user_can( 'delete_plugins' )) {	
			global $wpdb;
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE meta_key = %s", 'clef_id' ) );
			delete_option(self::OPTIONS_NAME);
		}
	}

}

include dirname( __FILE__ )."/wpclef.admin.inc";

add_action( 'init', array( 'WPClef', 'init' ) );
add_action( 'login_form', array( 'WPClef', 'login_form' ) );
add_action( 'login_form_login', array( 'WPClef', 'disable_login_form' ) );
add_action( 'login_message', array( 'WPClef', 'login_message' ) );
add_action( 'lost_password', array( 'WPClef', 'disable_lost_password_form' ) );
add_action( 'lostpassword_post', array( 'WPClef', 'disable_lost_password_form' ) );
add_action('init', array('WPClef', 'logout_handler'));
add_action('init', array('WPClef', 'logged_out_check'));
add_action('show_user_profile', array('WPClef', 'show_user_profile'));
add_action('edit_user_profile', array('WPClef', 'show_user_profile'));
add_action('edit_user_profile_update', array('WPClef', 'edit_user_profile_update'));
add_action('personal_options_update', array('WPClef', 'edit_user_profile_update'));
add_action('admin_notices', array('WPClef', 'edit_profile_errors'));

add_filter( 'heartbeat_received',  array("WPClef", "hook_heartbeat"), 10, 3);
add_filter('wp_authenticate_user', array('WPClef', 'clear_logout_hook'));
add_filter('wp_authenticate', array('WPClef', 'disable_passwords'));
add_filter('wp_authenticate', array('WPClef', 'disable_passwords'));
add_filter('wp_login_failed', array('WPClef', 'handle_login_failed'));

register_uninstall_hook(__FILE__, array('WPClef', 'uninstall_wpclef'));
