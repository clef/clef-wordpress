<?php if (!class_exists('Clef')) {

class Clef {
    private static $instance = null;

    private function __construct() {
        $this->define_constants();

        if (CLEF_IS_BASE_PLUGIN) {
            require_once(CLEF_PATH . 'includes/class.clef-setup.php');
            ClefSetup::register_plugin_hooks();
        }

        // Load translations
        load_plugin_textdomain( 'wpclef', false, dirname(plugin_basename(__FILE__)) .'/languages' );

        require_once(CLEF_PATH . 'includes/class.clef-core.php');
        add_action('plugins_loaded', array('ClefCore', 'manage_wp_fix'), 0);
        add_action('plugins_loaded', array('ClefCore', 'start'));
    }

    private function define_constants() {
        define('CLEF_VERSION', '2.4.1');

        if (!defined('CLEF_IS_BASE_PLUGIN')) define('CLEF_IS_BASE_PLUGIN', false);

        if (!defined('CLEF_DEBUG')) define('CLEF_DEBUG', false);
        define('CLEF_PATH', plugin_dir_path(__FILE__));
        define('CLEF_URL', plugin_dir_url(__FILE__));
        define('CLEF_TEMPLATE_PATH', CLEF_PATH . 'templates/');
        define('CLEF_OPTIONS_NAME', 'wpclef');

        if (!defined('CLEF_BASE')) define( 'CLEF_BASE', 'https://clef.io');
        if (!defined('CLEF_JS_URL')) define( 'CLEF_JS_URL', CLEF_BASE . '/v3/clef.js');
        if (!defined('CLEF_API_BASE')) define( 'CLEF_API_BASE', CLEF_BASE . '/api/v1/');
    }

    public static function start() {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}

} ?>
