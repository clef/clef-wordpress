<?php
/**
 * Plugin-wide utility functions
 *
 * @package Clef
 * @since 2.0
 */
class ClefUtils {
    /**
     * Runs esc_html on strings. Leaves input untouched if it's not a string.
     *
     * @return mixed
     */
    private static function escape_string($maybe_string) {
        $escaped = $maybe_string;
        if (is_string($maybe_string)) {
            $escaped = esc_html($maybe_string);
        }
        return $escaped;
    }

    /**
     * Renders the specified template, giving it access to $variables. 
     * Strings are escaped.
     *
     * @param string $name The name (with no .php extension) of a file in 
     *   templates/.
     * @param array $variables A list of variables to be used in the 
     *   template.
     * @return string
     */
    public static function render_template($name, $variables=false) {
        if ($variables) {
            $escaped_variables = array_map(array(__CLASS__, 'escape_string'), $variables);
            extract($escaped_variables, EXTR_SKIP);
        }
        ob_start();
        require(CLEF_TEMPLATE_PATH . $name . '.php');
        return ob_get_clean();
    }

    /**
     * Return $_GET[$key] if it exists.
     *
     * @param string $key
     * @return mixed
     */
    public static function isset_GET($key) {
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }

    /**
     * Return $_POST[$key] if it exists.
     *
     * @param string $key
     * @return mixed
     */
    public static function isset_POST($key) {
        return isset($_POST[$key]) ? $_POST[$key] : null;
    }

    /**
     * Return $_REQUEST[$key] if it exists.
     *
     * @param string $key
     * @return mixed
     */
    public static function isset_REQUEST($key) {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
    }

    public static function set_html_content_type() {
        return 'text/html';
    }

    public static function register_script($name, $dependencies=array('jquery')) {
        $ident = "wpclef-" . $name;
        if (CLEF_DEBUG)  {
            $name .= '.min';
        }
        $name .= '.js';
        wp_register_script(
            $ident, 
            CLEF_URL .'assets/dist/js/' . $name, 
            $dependencies, 
            CLEF_VERSION, 
            TRUE
        );
        return $ident;
    }

    public static function register_style($name) {
        $ident = "wpclef-" . $name;
        if (CLEF_DEBUG) {
            $name .= '.min';
        }
        $name .= '.css';
        wp_register_style(
            $ident, 
            CLEF_URL . 'assets/dist/css/' . $name, 
            FALSE, 
            CLEF_VERSION
        ); 
        return $ident;
    }
}
?>
