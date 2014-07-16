<?php

/**
 * Bootstrap the plugin unit testing environment.
 *
 * Edit 'active_plugins' setting below to point to your main plugin file.
 *
 * @package wordpress-plugin-tests
 */

// Activates this plugin in WordPress so it can be tested.
$GLOBALS['wp_tests_options'] = array(
    'active_plugins' => array( 'wpclef/wpclef.php' ),
);

define('BASE_TEST_DIR', dirname(dirname(__FILE__)));
define('CLEF_TESTING', true);

// If the develop repo location is defined (as WP_DEVELOP_DIR), use that
// location. Otherwise, we'll just assume that this plugin is installed in a
// WordPress develop SVN checkout.

if( false !== getenv( 'WP_TESTS_DIR' ) ) {
    require getenv( 'WP_TESTS_DIR' ) . '/tests/phpunit/includes/bootstrap.php';
} else {
    require '../../../../tests/phpunit/includes/bootstrap.php';
}