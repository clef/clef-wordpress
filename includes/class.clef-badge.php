<?php

class ClefBadge {
    const SETTING_NAME = "support_clef_badge";
    const PROMPT_HIDDEN = "badge_prompt_hidden";

    private static $instance = null;

    private $settings;
    private $onboarding;

    private function __construct($settings, $onboarding) {
        $this->settings = $settings;
        $this->onboarding = $onboarding;

        add_action('clef_hook_onboarding', array($this, 'hook_onboarding'));
    }

    public function is_active() {
        $setting = $this->settings->get(self::SETTING_NAME);
        return $setting != "" && $setting != "disabled";
    }

    public function should_display_prompt() {
        if ($this->is_active()) return false;
        $login_count = $this->onboarding->get_login_count_for_current_user();
        $prompt_hidden = $this->onboarding->get_key(self::PROMPT_HIDDEN);
        $has_admin_capability = current_user_can('manage_options');
        return $login_count > 0 && !$prompt_hidden && $has_admin_capability;
    }

    public function hook_onboarding() {
        if (empty($_POST)) {
            if ($this->should_display_prompt()) {
                add_action('admin_enqueue_scripts', array($this, 'register_scripts'));
                add_action('admin_notices', array($this, 'badge_prompt_html'));
            }
        } else {
            global $clef_ajax;
            $clef_ajax->add_action('clef_badge_prompt', array($this, 'handle_badge_prompt_ajax'));
        }

    }

    public function hook_display() {
        if (!$this->is_active()) return;
        add_action('wp_footer', array($this, 'draw'));
    }

    public function draw() {
        $pretty = $this->settings->get(self::SETTING_NAME) == "badge";
        echo ClefUtils::render_template('badge.tpl', array("pretty" => $pretty));
    }

    public function badge_prompt_html() {
        if (!$this->should_display_prompt()) return;

        $had_clef_before_onboarding = $this->onboarding->had_clef_before_onboarding();
        echo ClefUtils::render_template('admin/badge-prompt.tpl', array(
            "had_clef_before_onboarding" => $had_clef_before_onboarding
        ));

        // Ensure the prompt is hidden on next load
        $this->hide_prompt();
    }

    public function register_scripts() {
        $ident = ClefUtils::register_script('badge');
        wp_enqueue_script($ident);
    }

    public function handle_badge_prompt_ajax() {
        if (isset($_POST['enable'])) {
            $this->settings->set(self::SETTING_NAME, $_POST['enable']);
        }

        $this->hide_prompt();

        return array( "success" => true );
    }

    /**
     * Mark settings so that the badge prompt will be hidden on next page load.
     */
    public function hide_prompt() {
        $this->onboarding->set_key(self::PROMPT_HIDDEN, true);
    }

    public static function start($settings, $onboarding) {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self($settings, $onboarding);
        }
        return self::$instance;
    }
}
