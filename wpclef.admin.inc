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

	public static function admin_menu() {
		add_options_page("Clef", __('Clef', 'wpclef'), 'manage_options', __FILE__, array('WPClefAdmin', 'plugin_options'));
	}

	public static function plugin_options() {

		$form = Settings_API_Util::forID('clef');
		$form->renderBasicForm('Clef Settings', Settings_API_Util::ICON_SETTINGS);

	}
}

WPClefAdmin::hook('admin_init');
WPClefAdmin::hook('admin_menu');
