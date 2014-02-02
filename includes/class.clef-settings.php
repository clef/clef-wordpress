<?php

    include dirname( __FILE__ )."/lib/Settings_API_Util.inc";

    class ClefSettings extends Settings_API_Util {

        private function __construct($id, $optionName) {

            $this->id = $id;
            $this->optionName = $optionName;
            $this->sections = array();
            $this->introHTML = '';
            $this->outroHTML = '';

            register_setting( $id, $optionName, array(__CLASS__, 'validate'));

            if (Clef::individual_settings()) {
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

            if (isset($input['clef_settings_oauth_code'])) {
                $oauth_code = $input['clef_settings_oauth_code'];

                if ($oauth_code != "" && strlen($oauth_code) == 32)  {

                    try {
                        $info = ClefBase::exchange_oauth_code_for_info(
                            $oauth_code,
                            $input['clef_settings_app_id'],
                            $input['clef_settings_app_secret']
                        );
                        ClefBase::associate_clef_id($info->id);

                        $logout_url = wp_logout_url();

                        add_settings_error(
                            CLEF_OPTIONS_NAME,
                            esc_attr("settings_updated"),
                            __("You're configured and ready to use Clef. Click the logout button on your phone and you'll be automatically logged out in a few seconds."),
                            "updated"
                        );

                        add_settings_error(
                            CLEF_OPTIONS_NAME,
                            esc_attr("settings_updated"),
                            __("Didn't get logged out? Something may have gone wrong with our automatic configuration of your logout hook. We've sent you an email to get it fixed. For now, click <a href='$logout_url'>this link</a> to logout and try Clef!"),
                            "error logout-hook-error"
                        );
                    } catch (LoginException $e) {
                        $message = __("Your site is configured, but there was an error automatically connecting your Clef account. Please try linking your Clef account again <a href='" . admin_url('profile.php#clef') . "'>here</a>. If the issue persists, please email <a href='mailto:support@getclef.com'>support@getclef.com</a>.");
                        $message .= "<br/><br/>";
                        $message .= $e->getMessage();

                        add_settings_error(
                            CLEF_OPTIONS_NAME,
                            esc_attr("settings_updated"),
                            $message,
                            "error"
                        );
                    }
                }

                $input['clef_settings_oauth_code'] = "";
            }

            return $input;
        }

        public static function forID($id, $optionName = null) {
            if(null === $optionName) {
                $optionName = $id;
            }

            static $instances;

            if(!isset($instances)) {
                $instances = array();
            }

            if(!isset($instances[$id])) {
                 $instances[$id] = new ClefSettings($id, $optionName);
            }

            return $instances[$id];

        }
    }
?>
