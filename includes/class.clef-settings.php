<?php

require_once(CLEF_PATH . '/includes/lib/Settings_API_Util.inc');

class ClefSettings extends Settings_API_Util {
    private function __construct($id, $optionName, $settings) {
        $this->id = $id;
        $this->optionName = $optionName;
        $this->sections = array();
        $this->introHTML = '';
        $this->outroHTML = '';

        register_setting( $id, $optionName, array(__CLASS__, 'validate'));

        if ($settings->use_individual_settings) {
            $this->values = get_option($optionName);
        } else {
            $this->values = get_site_option($optionName);
        }
    }

    public static function validate(array $input) {
        $input =  parent::validate($input);

        if (isset($input['clef_settings_app_id'])) {
            $input['clef_settings_app_id'] = esc_attr($input['clef_settings_app_id']);
        }

        if (isset($input['clef_settings_app_secret'])) {
            $input['clef_settings_app_secret'] = esc_attr($input['clef_settings_app_secret']);
        }

        if (isset($input['clef_password_settings_force'])) {
            if (!get_user_meta(wp_get_current_user()->ID, 'clef_id')) {
                unset($input['clef_password_settings_force']);
                $url = admin_url('profile.php#clef');
                add_settings_error(
                    CLEF_OPTIONS_NAME,
                    esc_attr("settings_updated"),
                    __( "Please link your Clef account before you fully disable passwords. You can do this <a href='" . $url . "'>here</a>." , 'clef'),
                    "error"
                );
            }
        }

        return $input;
    }

    public static function forID($id, $optionName=null, $settings=null) {
        if(null === $optionName) {
            $optionName = $id;
        }

        static $instances;

        if(!isset($instances)) {
            $instances = array();
        }

        if(!isset($instances[$id])) {
                $instances[$id] = new ClefSettings($id, $optionName, $settings);
        }

        return $instances[$id];

    }
}
?>
