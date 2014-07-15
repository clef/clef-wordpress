<?php

/**
 * Tests to test that that testing framework is testing tests. Meta, huh?
 *
 * @package wordpress-plugins-tests
 */

require_once dirname(__FILE__) . '/../bootstrap.php';
require_once BASE_TEST_DIR . '/clef-require.php';
Clef::start();
require_once BASE_TEST_DIR . '/includes/class.clef-session.php';
require_once BASE_TEST_DIR . '/includes/class.clef-login.php';

class WP_Test_Login_Disable_Passwords extends WP_UnitTestCase {

    public function setUp() {
        parent::setUp();

        $this->settings = ClefInternalSettings::start();
        $this->settings->set('clef_settings_app_id', 'test_app_id');
        $this->settings->set('clef_settings_app_secret', 'test_app_secret');
        $this->user = get_user_by('id', $this->factory->user->create());


        $this->login = ClefLogin::start($this->settings);


        $this->settings->set('clef_password_settings_force', true);
        global $_POST;
        $_POST['pwd'] = 'password';
    }

    function test_valid_override() {
        global $_POST;

        $override = 'test';
        $this->settings->set('clef_override_settings_key', $override);
        $_POST = array( 'override' => $override );

        $this->assertEquals($this->user, $this->login->disable_passwords($this->user));
    }

    function test_invalid_override() {
        global $_POST;

        $override = 'test';
        $this->settings->set('clef_override_settings_key', $override);
        $_POST = array( 'override' => 'bad');

        $this->assertInstanceOf('WP_Error', $this->login->disable_passwords($this->user));
    }

    function test_disabled() {
        $this->assertInstanceOf('WP_Error', $this->login->disable_passwords($this->user));
    }

     function test_empty_post() {
        global $_POST;
        $_POST = array();

        $this->assertInstanceOf('WP_Error', $this->login->disable_passwords($this->user));
    }

    /**
     * @runInSeparateProcess
     */
    function test_xml_disabled() {
        define('XMLRPC_REQUEST', true);

        $this->assertInstanceOf('WP_Error', $this->login->disable_passwords($this->user));
    }

    /**
     * @runInSeparateProcess
     */
    function test_xml_enabled() {
        define('XMLRPC_REQUEST', true);
        $this->settings->set('clef_password_settings_xml_allowed', true);

        $this->assertEquals($this->user, $this->login->disable_passwords($this->user));
    }

}