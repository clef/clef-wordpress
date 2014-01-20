<?php

class ClefOnboarding extends ClefBase {

    const ONBOARDING_KEY = "onboarding_data";
    const LOGINS = "logins";

    public static function get_data() {
        $data = self::setting(self::ONBOARDING_KEY);
        if ($data) {
            return unserialize($data);
        } else {
            return array();
        }
    }

    public static function set_data($data) {
        return self::setting(self::ONBOARDING_KEY, serialize($data));
    }

    public static function get_key($key, $default=false) {
        $onboarding_data = self::get_data();
        if (isset($onboarding_data[$key])) {
            return $onboarding_data[$key];
        } else {
            return $default;
        }
    }

    public static function set_key($key, $value) {
        $data = self::get_data();
        $data[$key] = $value;

        return self::set_data($data);
    }

    public static function increment_key($key, $increment=1) {
        $value = self::get_key($key, 0);
        $value += $increment;
        self::set_key($key, $value);
        return $value;
    }

    public static function mark_login() {
        self::increment_key(self::LOGINS);
    }

    public static function get_login_count() {
        return self::get_key(self::LOGINS, 0);
    }

    public static function had_clef_before_onboarding() {
        return version_compare(self::setting("installed_at"), "1.9", "<");
    }
}