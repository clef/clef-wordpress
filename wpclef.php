<?php
/*
Plugin Name: Clef
Plugin URI: http://wordpress.org/extend/plugins/wpclef
Description: Clef lets you log in and register on your WordPress site using only your phone — forget your usernames and passwords.
Version: 2.6.0
Author: Clef
Author URI: https://getclef.com
License: MIT
License URI: http://opensource.org/licenses/MIT
 */

/**

Copyright (c) 2012 David Ross <dave@davidmichaelross.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

**/

if (!defined('ABSPATH')) exit();

if (!defined('CLEF_DEBUG')) define('CLEF_DEBUG', false);
if (CLEF_DEBUG) require_once('includes/lib/symlink-fix.php');

if (!defined('CLEF_IS_BASE_PLUGIN')) define('CLEF_IS_BASE_PLUGIN', true);

require_once('clef-require.php');

Clef::start();

?>
