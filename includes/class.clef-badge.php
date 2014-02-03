<?php

class ClefBadge extends ClefBase {

    const SETTING_NAME = "support_clef_badge";
    const PROMPT_HIDDEN = "badge_prompt_hidden";

    public static function is_active() {
        $setting = self::setting(self::SETTING_NAME);
        return $setting != "" && $setting != "disabled";
    }

    public static function should_display_prompt() {
        return ClefOnboarding::get_login_count() > 0 && !ClefOnboarding::get_key(self::PROMPT_HIDDEN);
    }

    public static function hook_onboarding() {
        if (!self::should_display_prompt()) return;

        if (empty($_POST)) {
            self::register_styles();  
            self::register_scripts();      
            self::hide_prompt();
            add_action('admin_notices', array(__CLASS__, 'badge_prompt_html'));
        } else {
            add_action('wp_ajax_clef_badge_prompt', array(__CLASS__, 'handle_badge_prompt_ajax'));
        }

    }

    public static function hook_display() {
        if (!self::is_active()) return;

        self::register_styles();        
        add_action('wp_footer', array('ClefBadge', 'draw'));
    }

    public static function draw() {
        $pretty = self::setting(self::SETTING_NAME) == "badge";
        include CLEF_TEMPLATE_PATH . "badge.tpl.php";
    }

    public static function badge_prompt_html() {
        $had_clef_before_onboarding = ClefOnboarding::had_clef_before_onboarding();
        include CLEF_TEMPLATE_PATH . "admin/badge-prompt.tpl.php";
    }

    public static function register_styles() {
        Clef::register_styles();
    }

    public static function register_scripts() {
        self::register_script('badge');
        wp_enqueue_script('badge');
    }

    public static function handle_badge_prompt_ajax() {
        if (isset($_POST['enable'])) {
            self::setting(self::SETTING_NAME, $_POST['enable']);
        } 

        self::hide_prompt();

        echo json_encode(array( "success" => true ));
        die(); 
    }

    public static function hide_prompt() {
        ClefOnboarding::set_key(self::PROMPT_HIDDEN, true);
    }
}