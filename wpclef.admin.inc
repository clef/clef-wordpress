<?php

include dirname( __FILE__ )."/Settings_API_Util.inc";

function print_password_descript() {
	echo "Ready to let go of your password forever? By selecting this option, Clef will disable logging in with usernames and passwords for Clef-enabled users on this site.";
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

		$settings = $form->addSection('clef_settings', 'API Settings');
		$values = $settings->settings->values;
		if($values['clef_settings_app_id'] == '' ||
			$values['clef_settings_app_secret'] == '') {
			$site_name = urlencode(get_option('blogname'));
			$site_domain = urlencode(get_option('siteurl'));
			error_log($site_domain);
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

		$pw_settings = $form->addSection('clef_password_settings', "Protect yourself from your password", "print_password_descript");
		
		$pw_settings->addField('disable_passwords', "Disable password login for Clef users", Settings_API_Util_Field::TYPE_CHECKBOX);

	}

	public static function admin_enqueue_scripts($hook) {
		$exploded_path = explode('/', $hook);
		$settings_page_name = array_shift($exploded_path);
		
		if($settings_page_name === 'settings_page_wpclef') {
			wp_register_style('wpclef_styles', plugin_dir_url(__FILE__) . '/clef.css', FALSE, '1.0.0');
			wp_enqueue_style('wpclef_styles');

			wp_register_script('wpclef_keys', plugin_dir_url(__FILE__) . '/keys.js', array('jquery'), '1.0.0', TRUE );

			wp_enqueue_script('wpclef_keys');
		} else {
			if(in_array('wpclef_configure', self::dismissed_wp_pointers())) {
				return;
			}

			wp_register_script('wpclef_admin', plugin_dir_url(__FILE__) . '/admin_pointer.js', array('jquery', 'wp-pointer'), '1.0', TRUE );

			wp_enqueue_script('wp-pointer');
			wp_enqueue_style('wp-pointer');
			wp_enqueue_script('wpclef_admin');
		}


	}


	public static function admin_menu() {
		add_options_page("Clef", __('Clef', 'wpclef'), 'manage_options', __FILE__, array('WPClefAdmin', 'plugin_options'));
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