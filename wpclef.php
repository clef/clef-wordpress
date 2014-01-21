<?php 
/*
Plugin Name: Clef
Plugin URI: http://wordpress.org/extend/plugins/wpclef
Description: Clef lets you log in and register on your WordPress site using only your phone — forget your usernames and passwords.
Version: 1.9
Author: David Michael Ross
Author URI: http://www.davidmichaelross.com/
License: MIT
License URI: http://opensource.org/licenses/MIT
 */

/**

Copyright (c) 2012 David Ross <dave@davidmichaelross.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

**/

if ( ! defined('ABSPATH') ) exit();

// Useful global constants
define( 'CLEF_VERSION', '1.9' );
define( 'CLEF_PATH',    WP_PLUGIN_DIR . '/wpclef/' );
if (CLEF_DEBUG) {
    require_once('includes/lib/symlink-fix.php');
}
define( 'CLEF_URL',     plugin_dir_url( __FILE__ ) );
define( 'CLEF_TEMPLATE_PATH', CLEF_PATH . 'templates/');
// define( 'CLEF_BASE', 'http://arya.dev:5000');
// define('CLEF_JS_URL', CLEF_BASE . '/static/javascripts/v3/clef.js');
define( 'CLEF_BASE', 'https://clef.io' );
define( 'CLEF_JS_URL', CLEF_BASE . '/v3/clef.js');
define( 'CLEF_API_BASE', CLEF_BASE . '/api/v1/');
define( 'CLEF_OPTIONS_NAME', 'wpclef');


require_once('includes/lib/utils.inc');
require_once('includes/class.clef-base.php');
require_once('includes/class.clef-settings.php');
require_once('includes/class.clef.php');
require_once('includes/class.clef-onboarding.php');
require_once('includes/class.clef-admin.php');
require_once('includes/class.clef-network-admin.php');
require_once('includes/class.clef-login.php');
require_once('includes/class.clef-logout.php');
require_once('includes/class.clef-badge.php');

register_activation_hook(CLEF_PATH . 'wpclef.php', array('Clef', 'activate_plugin'));
register_deactivation_hook(CLEF_PATH . 'wpclef.php', array('Clef', 'deactivate_plugin'));
register_uninstall_hook(CLEF_PATH . 'wpclef.php', array('Clef', 'uninstall_plugin'));

// Load translations
load_plugin_textdomain( 'clef', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

if (!Clef::setting("version") || CLEF_VERSION != Clef::setting("version")) {
    Clef::update(CLEF_VERSION, Clef::setting("version"));
}

add_action( 'init', array('Clef', 'init'));
