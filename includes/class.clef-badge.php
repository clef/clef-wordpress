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
        return $this->onboarding->get_login_count() > 0 && 
            !$this->onboarding->get_key(self::PROMPT_HIDDEN);
    }

    public function hook_onboarding() {
        if (!$this->should_display_prompt()) return;

        if (empty($_POST)) {
            $this->register_styles();  
            $this->register_scripts();      
            $this->hide_prompt();
            add_action('admin_notices', array($this, 'badge_prompt_html'));
        } else {
            add_action('wp_ajax_clef_badge_prompt', array($this, 'handle_badge_prompt_ajax'));
        }

    }

    public function hook_display() {
        if (!$this->is_active()) return;

        $this->register_styles();        
        add_action('wp_footer', array($this, 'draw'));
    }

    public function draw() {
        $pretty = $this->settings->get(self::SETTING_NAME) == "badge";
        echo ClefUtils::render_template('badge.tpl', array("pretty" => $pretty));
    }

    public function badge_prompt_html() {
        $had_clef_before_onboarding = $this->onboarding->had_clef_before_onboarding();
        echo ClefUtils::render_template('admin/badge-prompt.tpl', array(
            "had_clef_before_onboarding" => $had_clef_before_onboarding
        ));
    }

    public function register_styles() {
        ClefUtils::register_styles();
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

        echo json_encode(array( "success" => true ));
        die(); 
    }

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
