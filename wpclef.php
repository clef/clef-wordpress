<?php

/*
Plugin Name: WPClef
Plugin URI: http://wordpress.org/extend/plugins/wpclef
Description: Log in using Clef.io
Version: 1.2
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

if ( !session_id() ) {
	session_start();
}

include dirname( __FILE__ )."/wpclef.admin.inc";


if ( !isset( $_SESSION['WPClef_Messages'] ) ) {
	$_SESSION['WPClef_Messages'] = array();
}

class WPClef {

	const API_BASE = 'https://clef.io/api/v1/';

	public static function setting( $name ) {
		static $clef_settings = NULL;
		if ( $clef_settings === NULL ) {
			$clef_settings = get_option( 'wpclef' );
		}
		if ( isset( $clef_settings[$name] ) ) {
			return $clef_settings[$name];
		}

		// Fall-through
		return FALSE;

	}

	function init() {

		if ( isset( $_REQUEST['clef_callback'] ) && isset( $_REQUEST['code'] ) ) {

			// Authenticate

			$args = array(
				'code' => $_REQUEST['code'],
				'app_id' => self::setting( 'clef_settings_app_id' ),
				'app_secret' => self::setting( 'clef_settings_app_secret' ),
			);

			$response = wp_remote_post( self::API_BASE . 'authorize', array( 'body' => $args ) );
			$body = json_decode( $response['body'] );
			$access_token = $body->access_token;
			$_SESSION['wpclef_access_token'] = $access_token;
			$success = $body->success;

			if ( $success != 1 ) {
				$_SESSION['WPClef_Messages'][] = 'Error retrieving Clef access token';
				self::redirect_to_login();
			}

			// Get info
			$response = wp_remote_get( self::API_BASE . "info?access_token={$access_token}" );

			$body = json_decode( $response['body'] );

			if ( $success != 1 ) {
				$_SESSION['WPClef_Messages'][] = 'Error retrieving Clef user data';
				self::redirect_to_login();
			}

			$first_name = $body->info->first_name;
			$last_name = $body->info->last_name;
			$email = $body->info->email;

			$existing_user = WP_User::get_data_by( 'email', $email );
			if ( !$existing_user ) {
				$users_can_register = get_option('users_can_register', 0);
				if(!$users_can_register) {
					$_SESSION['WPClef_Messages'][] = "There's no user whose email address matches your phone's Clef account";
					self::redirect_to_login();
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
					$_SESSION['WPClef_Messages'][] = "An error occurred when creating your new account.";
					self::redirect_to_login();					
				}
				$existing_user = WP_User::get_data_by( 'email', $email );

			}

			// Log in the user

			$user = set_current_user( $existing_user->ID, $existing_user->user_nicename );
			wp_set_auth_cookie( $existing_user->ID );
			do_action( 'wp_login', $existing_user->ID );

			header( "Location: " . admin_url() );

		}
	}

	public static function login_form() {
		$app_id = self::setting( 'clef_settings_app_id' );
		$redirect_url = trailingslashit( home_url() ) . "?clef_callback=clef_callback&";
		include dirname( __FILE__ )."/login_script.tpl.php";
	}

	public static function login_message() {
		$_SESSION['WPClef_Messages'] = array_unique( $_SESSION['WPClef_Messages'] );
		foreach ( $_SESSION['WPClef_Messages'] as $message ) {
			echo '<div class="message">' . $message . '</div>';
		}
		$_SESSION['WPClef_Messages'] = array();
	}

	public static function redirect_to_login() {
		header( 'Location: ' . wp_login_url() );
		exit();
	}
}

add_action( 'init', array( 'WPClef', 'init' ) );
add_action( 'login_form', array( 'WPClef', 'login_form' ) );
add_action( 'login_message', array( 'WPClef', 'login_message' ) );
