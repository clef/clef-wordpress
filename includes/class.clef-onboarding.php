<?php

class ClefOnboarding {
    const ONBOARDING_KEY = "onboarding_data";
    const LOGINS = 'clef_logins';

    private static $instance = null;

    private $settings;

    private function __construct($settings) {
        $this->settings = $settings;
        add_action('clef_login', array($this, 'mark_login_for_user_id'));
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

    public function mark_login_for_user_id($user_id) {
        $this->increment_logins_for_user_id($user_id, 1);
    }

    public function increment_logins_for_user_id($user_id, $by=1) {
        $login_count = get_user_meta($user_id, self::LOGINS, true);
        update_user_meta($user_id, self::LOGINS, $login_count + $by);
    }

    public function get_login_count_for_current_user() {
        $user_id = get_current_user_id();
        return get_user_meta($user_id, self::LOGINS, true);
    }

    public function had_clef_before_onboarding() {
        return version_compare($this->settings->get("installed_at"), "1.9", "<");
    }

    /**
     * Migrate the global login count to the user who is updating the plugin.
     */
    public function migrate_global_login_count() {
        $global_login_count = (int) $this->get_key('logins', 0);
        if (!empty($global_login_count) && current_user_can('manage_options')) {
            $this->increment_logins_for_user_id(get_current_user_id(), $global_login_count);
        }
    }

    public static function start($settings) {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self($settings);
        }
        return self::$instance;
    }
}
