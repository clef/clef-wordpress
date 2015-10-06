<?php

class ClefOnboarding {
    const ONBOARDING_KEY = "onboarding_data";
    const FIRST_LOGIN_KEY = "has_logged_in";
    const AFTER_FIRST_LOGIN_KEY = "after_first_login";
    const LOGINS = 'clef_logins';

    private static $instance = null;

    private $settings;

    private function __construct($settings) {
        $this->settings = $settings;
        add_action('admin_init', array($this, 'do_after_first_login_action'));
        add_action('clef_login', array($this, 'mark_login_for_user_id'));
        add_action('clef_login', array($this, 'do_first_login_action'));
        add_action('clef_onboarding_first_login', array($this, 'prepare_after_first_login'));
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

    public function mark_login_for_user_id($user) {
        $this->increment_logins_for_user_id($user->ID, 1);
    }

    public function increment_logins_for_user_id($user_id, $by=1) {
        $login_count = get_user_meta($user_id, self::LOGINS, true);
        update_user_meta($user_id, self::LOGINS, $login_count + $by);
    }

    public function get_login_count_for_current_user() {
        $user_id = get_current_user_id();
        return get_user_meta($user_id, self::LOGINS, true);
    }

    public function do_first_login_action($user) {
        if (!$this->get_key(self::FIRST_LOGIN_KEY)) {
            $this->set_key(self::FIRST_LOGIN_KEY, true);
            do_action('clef_onboarding_first_login', $user);
        }
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

    /**
    * Set FIRST_LOGIN true for users who are upgrading — we don't want
    * to disable passwords for all of our previous users before the 2.2.9.1
    * update.
    */
    public function set_first_login_true() {
        $this->set_key(self::FIRST_LOGIN_KEY, true);
    }

    public function prepare_after_first_login() {
        $this->set_key(self::AFTER_FIRST_LOGIN_KEY, true);
    }

    public function do_after_first_login_action() {
        if ($this->get_key(self::AFTER_FIRST_LOGIN_KEY)) {
            error_log("do after first login");
            $this->set_key(self::AFTER_FIRST_LOGIN_KEY, false);
            do_action('clef_onboarding_after_first_login');
        }
    }

    public static function start($settings) {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self($settings);
        }
        return self::$instance;
    }
}
