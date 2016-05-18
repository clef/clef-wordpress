<?php
/**
 * Clef Session
 *
 * This is a wrapper class for WP_Session / PHP $_SESSION.
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class ClefSession {

    private static $instance = null;
    private static $cookie_name = 'wordpress_clef_session';

    /**
     * Holds our session data
     *
     * @var array
     * @access private
     * @since 1.5
     */
    private $session = array();


    /**
     * Whether to use PHP $_SESSION or WP_Session
     *
     * PHP $_SESSION is opt-in only by defining the CLEF_USE_PHP_SESSIONS constant
     *
     * @var bool
     * @access private
     * @since 1.5,1
     */
    private $use_php_sessions = false;


    /**
     * Get things started
     *
     * Defines our WP_Session constants, includes the necessary libraries and
     * retrieves the WP Session instance
     *
     * @since 1.5
     */
    public function __construct($cookie_name = false) {

        $this->use_php_sessions = defined( 'CLEF_USE_PHP_SESSIONS' ) && CLEF_USE_PHP_SESSIONS;

        if( $this->use_php_sessions ) {

            if( ! session_id() )
                add_action( 'init', 'session_start', -2 );

        } else {

            // Use WP_Session (default)
            if ( ! defined( 'CLEF_WP_SESSION_COOKIE' ) )
                define( 'CLEF_WP_SESSION_COOKIE', self::$cookie_name );

            if ( ! class_exists( 'Recursive_ArrayAccess' ) )
                require_once CLEF_PATH . 'includes/lib/wp-session/class-recursive-arrayaccess.php';

            if ( ! class_exists( 'ClefWP_Session' ) ) {
                require_once CLEF_PATH . 'includes/lib/wp-session/class-wp-session.php';
                require_once CLEF_PATH . 'includes/lib/wp-session/wp-session.php';
            }

            add_filter( 'clef_wp_session_expiration', array( $this, 'set_expiration_time' ), 99999 );
        }

        $this->init($cookie_name);
    }


    /**
     * Setup the WP_Session instance
     *
     * @access public
     * @since 1.5
     * @return void
     */
    public function init($cookie_name) {
        if( $this->use_php_sessions )
            $this->session = isset( $_SESSION['clef'] ) && is_array( $_SESSION['clef'] ) ? $_SESSION['clef'] : array();
        else
            $this->session = ClefWP_Session::get_instance($cookie_name);

        return $this->session;
    }


    /**
     * Retrieve session ID
     *
     * @access public
     * @since 1.6
     * @return string Session ID
     */
    public function get_id() {
        return $this->session->session_id;
    }


    /**
     * Retrieve a session variable
     *
     * @access public
     * @since 1.5
     * @param string $key Session key
     * @return string Session variable
     */
    public function get( $key ) {
        $key = sanitize_key( $key );
        return isset( $this->session[ $key ] ) ? maybe_unserialize( $this->session[ $key ] ) : false;
    }

    /**
     * Set a session variable
     *
     * @since 1.5
     *
     * @param $key Session key
     * @param $value Session variable
     * @return mixed Session variable
     */
    public function set( $key, $value ) {
        $key = sanitize_key( $key );

        if ( is_array( $value ) )
            $this->session[ $key ] = serialize( $value );
        else
            $this->session[ $key ] = $value;

        if( $this->use_php_sessions )
            $_SESSION['clef'] = $this->session;

        return $this->session[ $key ];
    }

    /**
     * Force the cookie expiration time to 365 days
     *
     * @access public
     * @since 1.9
     * @param int $exp Default expiration (1 hour)
     * @return int
     */
    public function set_expiration_time( $exp ) {
        return current_time( 'timestamp' ) + ( 60 * 60 * 24 * 365 );
    }

    public static function migrate($old_cookie_name) {
        if ( ! class_exists( 'Recursive_ArrayAccess' ) )
            require_once CLEF_PATH . 'includes/lib/wp-session/class-recursive-arrayaccess.php';

        if ( ! class_exists( 'ClefWP_Session' ) ) {
            require_once CLEF_PATH . 'includes/lib/wp-session/class-wp-session.php';
            require_once CLEF_PATH . 'includes/lib/wp-session/wp-session.php';
        }

        $old_session = new ClefWP_Session($old_cookie_name);
        $new_session = ClefSession::start();

        foreach ($old_session as $key => $value) {
            $new_session->set($key, $value);
        }
    }

    public static function start() {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
