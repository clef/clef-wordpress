<?php

class LoginException extends Exception {}
class ClefStateException extends Exception {}

class ClefCore {
    private static $instance = null;

    private $settings;
    private $badge;
    private $onboarding;

    private function __construct() {
        // General utility functions
        require_once(CLEF_PATH . 'includes/lib/utils.inc');
        require_once(CLEF_PATH . 'includes/class.clef-utils.php');
        require_once(CLEF_PATH . 'includes/class.clef-translation.php');

        require_once(CLEF_PATH . 'includes/class.clef-session.php');

        // Site options
        require_once(CLEF_PATH . 'includes/class.clef-internal-settings.php');
        $settings = ClefInternalSettings::start();

        global $clef_ajax;
        require_once(CLEF_PATH . 'includes/class.clef-ajax.php');
        $clef_ajax = ClefAjax::start($settings);

        // Onboarding settings
        require_once(CLEF_PATH . 'includes/class.clef-onboarding.php');
        $onboarding = ClefOnboarding::start($settings);

        require_once(CLEF_PATH. 'includes/class.clef-user-settings.php');
        $user_settings = ClefUserSettings::start($settings);

        // Clef login functions
        require_once(CLEF_PATH . 'includes/class.clef-login.php');
        $login = ClefLogin::start($settings);

        // Clef logout hook functions
        require_once(CLEF_PATH . 'includes/class.clef-logout.php');
        $logout = ClefLogout::start($settings);

        // Badge display options
        require_once(CLEF_PATH . 'includes/class.clef-badge.php');
        $badge = ClefBadge::start($settings, $onboarding);
        $badge->hook_display();

        // Admin functions and hooks
        require_once(CLEF_PATH . 'includes/class.clef-admin.php');
        $admin = ClefAdmin::start($settings);

        require_once(CLEF_PATH . 'includes/class.clef-network-admin.php');
        $network_admin = ClefNetworkAdmin::start($settings);

        require_once(CLEF_PATH . 'includes/pro/class.clef-pro.php');
        $pro = ClefPro::start($settings);

        // Plugin setup hooks
        require_once(CLEF_PATH . 'includes/class.clef-setup.php');

        $this->settings = $settings;
        $this->badge = $badge;
        $this->onboarding = $onboarding;

        // Register public hooks
        if ($admin) {
            add_action('clef_render_settings', array($admin, 'general_settings'));
        }
        add_action('clef_plugin_uninstall', array('ClefSetup', 'uninstall_plugin'));
        add_action('clef_plugin_updated', array($this, 'plugin_updated'), 10, 2);

        // Run migrations and other hooks upon plugin update
        $old_version = $settings->get('version');
        $current_version = CLEF_VERSION;
        if (!$old_version || $current_version != $old_version) {
            do_action('clef_plugin_updated', $current_version, $old_version);
        }

        if (CLEF_IS_BASE_PLUGIN) {
            do_action('clef_hook_admin_menu');
        }
    }

    public function plugin_updated($version, $previous_version) {
        $settings_changes = false;

        if ($previous_version) {

            if (version_compare($previous_version, '2.2.9.1', '<')) {
                $this->onboarding->set_first_login_true();
            }

            if (version_compare($previous_version, '2.1', '<')) {
                if (!session_id()) @session_start();
                if (isset($_SESSION['logged_in_at'])) {
                    $session = ClefSession::start();
                    $session->set('logged_in_at', $_SESSION['logged_in_at']);
                }
            }

            if (version_compare($previous_version, '2.0', '<')) {
                $this->onboarding->migrate_global_login_count();
                $this->badge->hide_prompt();
                if ($this->settings->get('clef_password_settings_disable_certain_passwords') == "Disabled") {
                    $this->settings->set('clef_password_settings_disable_certain_passwords', '');
                }
            }

            if (version_compare($previous_version, "1.9.1.1", '<')) {
                $this->badge->hide_prompt();
            }

            if (version_compare($previous_version, "1.9", '<')) {
               if (!$previous_version) {
                    $previous_version = $version;
               }
               $this->settings->get('installed_at', $previous_version);
            }

            if (version_compare($previous_version, "1.8.0", '<')) {
                $settings_changes = array(
                    "clef_password_settings_override_key" => "clef_override_settings_key"
                );
            }

        } else {
            $this->settings->set('installed_at', $version);
            $this->settings->set('clef_form_settings_embed_clef', 1);
            $this->settings->set_saved_affiliates();
        }

        if ($settings_changes) {
            foreach ($settings_changes as $old_name => $new_name) {
                $value = $this->settings->get($old_name);
                if ($value) {
                    $this->settings->set($new_name, $value);
                    $this->settings->remove($old_name);
                }
            }
        }

        $this->settings->set("version", $version);
    }

    public static function manage_wp_fix() {
        if (isset($_REQUEST['action']) && preg_match('/ajax_settings/', $_REQUEST['action']) && function_exists('mmb_authenticate')) {
            remove_action('plugins_loaded', 'mmb_authenticate', 1);
        }
    }

    public static function start() {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}
