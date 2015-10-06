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

        // sanitize inputs as text fields
        foreach ($input as $key => &$value) {
            $input[$key] = sanitize_text_field($value);
        }

        $attrs_to_escape = array(
            'clef_settings_app_id',
            'clef_settings_app_secret',
            'customization_message',
            'customization_logo'
        );

        foreach ($attrs_to_escape as $attr) {
            if (isset($input[$attr])) {
                $input[$attr] = esc_attr($input[$attr]);
            }
        }

        if (isset($input['clef_password_settings_force']) && $input['clef_password_settings_force'] == "1") {
            if (!ClefUtils::user_has_clef()) {
                unset($input['clef_password_settings_force']);
                $url = admin_url('admin.php?page=' . ClefAdmin::CONNECT_CLEF_PAGE);
                add_settings_error(
                    CLEF_OPTIONS_NAME,
                    'clef_password_settings_force',
                    sprintf(__( "Please link your Clef account before you fully disable passwords. You can do this <a href='%s'>here</a>", "wpclef"), $url),
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
