<?php

class ClefOnboarding {
    const ONBOARDING_KEY = "onboarding_data";
    const LOGINS = "logins";

    private static $instance = null;

    private $settings;

    private function __construct($settings) {
        $this->settings = $settings;
        add_action('clef_login', array($this, 'mark_login_for_current_user'));
    }

    public function get_data() {
        $data = $this->settings->get(self::ONBOARDING_KEY);
        if ($data) {
            return unserialize($data);
        } else {
            return array();
        }
    }

    public function set_data($data) {
        return $this->settings->set(self::ONBOARDING_KEY, serialize($data));
    }

    public function get_key($key, $default=false) {
        $onboarding_data = $this->get_data();
        if (isset($onboarding_data[$key])) {
            return $onboarding_data[$key];
        } else {
            return $default;
        }
    }

    public function set_key($key, $value) {
        $data = $this->get_data();
        $data[$key] = $value;

        return $this->set_data($data);
    }

    public function increment_key($key, $increment=1) {
        $value = $this->get_key($key, 0);
        $value += $increment;
        $this->set_key($key, $value);
        return $value;
    }

    public function mark_login() {
        $this->increment_key(self::LOGINS);
    }

    public function mark_login_for_current_user() {
        $user_id = get_current_user_id();
        $login_count = get_user_meta($user_id, 'clef_logins', true);
        update_user_meta($user_id, 'clef_logins', $login_count + 1);
    }

    public function get_login_count_for_current_user() {
        $user_id = get_current_user_id();
        return get_user_meta($user_id, 'clef_logins', true);
    }

    public function get_login_count() {
        return $this->get_key(self::LOGINS, 0);
    }

    public function had_clef_before_onboarding() {
        return version_compare($this->settings->get("installed_at"), "1.9", "<");
    }

    public static function start($settings) {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self($settings);
        }
        return self::$instance;
    }
}
