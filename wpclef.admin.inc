<?php

include dirname( __FILE__ )."/Settings_API_Util.inc";

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

		$image_url = plugins_url( 'clef_logo.png' , __FILE__ );
		ob_start();
		include dirname(__FILE__)."/admin_intro.tpl.php";
		$form->introHTML = ob_get_clean();

		$settings = $form->addSection('clef_settings', 'API Settings');
		$settings->addField('app_id', 'App ID', Settings_API_Util_Field::TYPE_TEXTFIELD);
		$settings->addField('app_secret', 'App Secret', Settings_API_Util_Field::TYPE_TEXTFIELD);

	}

	public static function admin_enqueue_scripts($hook) {

		if(array_shift(explode('/', $hook)) === 'settings_page_wpclef') {
			return;
		}

		if(in_array('wpclef_configure', self::dismissed_wp_pointers())) {
			return;
		}

		wp_register_script('wpclef_admin', plugin_dir_url(__FILE__) . '/admin_pointer.js', array('jquery', 'wp-pointer'), '1.0', TRUE );

		wp_enqueue_script('wp-pointer');
		wp_enqueue_style('wp-pointer');
		wp_enqueue_script('wpclef_admin');
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
WPClefAdmin::hook('update_option_wpclef');