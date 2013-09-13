<?php

include dirname( __FILE__ )."/Settings_API_Util.inc";

function print_api_descript() {
	echo 'To update or delete the Clef application that powers your WordPress site, please visit <a href="https://developer.getclef.com">the Clef developer site</a>.';
}

function print_password_descript() {
	$key = WPClef::setting( 'clef_password_settings_override_key' );
	$msg ="The first option (i.e., hybrid mode) disables passwords for Clef-enabled users but continues to dispaly the default user/pass login form for non-Clef users. The second, more secure, option (i.e., full Clef mode) hides the default user/pass login form, requires Clef-based authentication for all users, and disables forgotten password resets. If you have users who are not able to use Clef but you still want to use full Clef mode, you can set a key that will open the user/pass login form at the following secret URL: ";
	if (!empty($key)) {
		$url = wp_login_url() .'?ForceClefOverrideKey=' .$key;
		$msg .= "<a href='" .$url ."' target='new'>" .$url ."</a>.";
	} else {
		$msg .= wp_login_url() .'?ForceClefOverrideKey=YOUR_KEY_HERE';
	}
	echo $msg;
}

class WPClefAdmin {

	const OPTIONS_NAME = 'wpclef';

	public static function hook($hook, $method = NULL) {
		if($method === NULL) {
			$method = $hook;
		}
		add_action($hook, array(__CLASS__, $hook));
	}

	public static function admin_init() {

		$formID = 'clef';
		$form = Settings_API_Util::forID($formID, WPClefAdmin::OPTIONS_NAME);

		$settings = $form->addSection('clef_settings', 'API Settings', 'print_api_descript');
		$values = $settings->settings->values;
		if($values['clef_settings_app_id'] == '' ||
			$values['clef_settings_app_secret'] == '') {
			$site_name = urlencode(get_option('blogname'));
			$site_domain = urlencode(get_option('siteurl'));
			ob_start();
			include dirname(__FILE__)."/keys_generation.tpl.php";
			$form->introHTML = ob_get_clean();
		} else {
			ob_start();
			include dirname(__FILE__)."/admin_intro.tpl.php";
			$form->introHTML = ob_get_clean();
		}

		$settings->addField('app_id', 'App ID', Settings_API_Util_Field::TYPE_TEXTFIELD);
		$settings->addField('app_secret', 'App Secret', Settings_API_Util_Field::TYPE_TEXTFIELD);

		$pw_settings = $form->addSection('clef_password_settings', "Password Settings", "print_password_descript");
		$pw_settings->addField('disable_passwords', "Disable passwords for Clef users", Settings_API_Util_Field::TYPE_CHECKBOX);
		$pw_settings->addField('force', "Disable passwords for all users", Settings_API_Util_Field::TYPE_CHECKBOX);
		$pw_settings->addField('override_key', "Set an override key (optional)", Settings_API_Util_Field::TYPE_TEXTFIELD);

	}

	public static function admin_enqueue_scripts($hook) {

		$exploded_path = explode('/', $hook);
		$settings_page_name = array_shift($exploded_path);

		// only register clef logout if user is a clef user
		if (get_user_meta(wp_get_current_user()->ID, 'clef_id')) {
			wp_register_script('wpclef_logout', plugins_url('wpclef/clef-heartbeat.js'), array('jquery'), '1.0', TRUE);
			wp_enqueue_script('wpclef_logout');
		}
		
		if($settings_page_name === 'settings_page_wpclef') {
			wp_register_style('wpclef_styles', plugins_url('wpclef/clef.css'), FALSE, '1.0.0');
			wp_enqueue_style('wpclef_styles');

			wp_register_script('wpclef_keys', plugins_url('wpclef/keys.js'), array('jquery'), '1.0.0', TRUE );
			wp_enqueue_script('wpclef_keys');
		} else {
			if(in_array('wpclef_configure', self::dismissed_wp_pointers())) {
				return;
			}

			wp_register_script('wpclef_admin', plugins_url('wpclef/admin_pointer.js'), array('jquery', 'wp-pointer'), '1.0', TRUE );

			wp_enqueue_script('wp-pointer');
			wp_enqueue_style('wp-pointer');
			wp_enqueue_script('wpclef_admin');
		}


	}

	public static function admin_menu() {
		add_options_page("Clef", __('Clef', 'wpclef'), 'manage_options', 'wpclef', array('WPClefAdmin', 'plugin_options'));
	}

	public static function plugin_options() {
		$form = Settings_API_Util::forID('clef');
		$form->renderBasicForm('Clef Settings', Settings_API_Util::ICON_SETTINGS);
	}

	public static function update_option_wpclef() {

		// Automatically dismiss the pointer once settings are saved
		$dismissed = self::dismissed_wp_pointers();
		if(in_array('wpclef_configure', $dismissed)) {
			return;
		}
		$dismissed[] = 'wpclef_configure';
		$dismissed = implode(',', $dismissed);

		update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', $dismissed );
	}

	private static function dismissed_wp_pointers() {
		$dismissed = explode( ',',
			(string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true )
		);
		return $dismissed;
	}
}

WPClefAdmin::hook('admin_init');
WPClefAdmin::hook('admin_menu');
WPClefAdmin::hook('admin_enqueue_scripts');
WPClefAdmin::hook('admin_enqueue_styles');
WPClefAdmin::hook('update_option_wpclef');