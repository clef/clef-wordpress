<?php
/**
 * Add WP-CLI integration to wpclef.
 * 
 * This class adds WP-CLI (http://wp-cli.org) functionality to the wpclef plugin (https://wordpress.org/plugins/wpclef/) thereby enabling WP admins to configure wpclef’s settings from the command line.
 * 
 * Contributions are welcome. See https://github.com/clef/wordpress.
 * 
 * @since 2.2.9
 */


/**
 * Manage wpclef from the command line.
 */ 
class Clef_WPCLI_Command extends WP_CLI_Command {

    /**
     * Properties
     */ 
    const PWD_OPT_CLEF = 'clef_password_settings_disable_passwords';
    const PWD_OPT_WP = 'clef_password_settings_disable_certain_passwords';
    const PWD_OPT_ALL = 'clef_password_settings_force';
    const PWD_OPT_API = 'clef_password_settings_xml_allowed';
    const PWD_OPT_WAVE = 'clef_form_settings_embed_clef';
    const PWD_OPT_OVERRIDE = 'clef_override_settings_key';
    const PWD_OPT_BADGE = 'support_clef_badge';
    const PWD_OPT_API_ID = 'clef_settings_app_id';
    const PWD_OPT_API_SECRET = 'clef_settings_app_secret';
    private $site_url;
    private $admin_email;
    private $wpclef_opts;
    
    function __construct() {
        $this->wpclef_opts = get_option('wpclef');
        $this->site_url = site_url();
        $this->admin_email = get_option('admin_email');
    }
    
    /**
     * Methods
     */ 
    private function is_valid_command_input($args, $assoc_args, $command) {
        
        if (empty($args) && empty($assoc_args)) {
            self::error_invalid_option($command);
            return 0;
        } else {
            return 1;
        }
    }
    
    private function get_filtered_command_input($input) {
        $input = array_map('strtolower', $input);
        return $input;
    }
    
    private function error_invalid_option($command) {
         WP_CLI::error("Please enter a valid option for '$command'. For help, use 'wp help clef $command'.");
    }
    
    private function toggle_passwords($arg, $option, $role) {
        if ($arg == 'disable') {
            return self::update_wpclef_option($option, 1, "Passwords are now disabled for $role.");
        } elseif (($arg == 'enable')) {
            return self::update_wpclef_option($option, 0, "Passwords are now enabled for $role.");
        }
    }
    
    private function toggle_passwords_api($arg, $option, $role) {
        if ($arg == 'disable') {
            return self::update_wpclef_option($option, 0, "Passwords are now disabled for $role.");
        } elseif (($arg == 'enable')) {
            return self::update_wpclef_option($option, 1, "Passwords are now enabled for $role.");
        }
    }
    
    private function toggle_passwords_wprole($arg, $role, $wprole) {
        if ($arg == 'disable') {
            $value = $wprole;
            return self::update_wpclef_option($role, $value, "Passwords are disabled for WP roles >= $wprole.");
        } elseif (($arg == 'enable')) {
            $value = ''; // reset the SELECT box to null, which enables passwords for all WP roles
            return self::update_wpclef_option($role, $value, "Passwords are enabled for the $wprole role.");
        }
    }
    
    private function toggle_wave($arg) {
        if ($arg == 'disable') {
            return self::update_wpclef_option(self::PWD_OPT_WAVE, 0, 'Wp-login.php will show the standard WP login form.');
        } elseif (($arg == 'enable')) {
            return self::update_wpclef_option(self::PWD_OPT_WAVE, 1, 'Wp-login.php will show the Clef Wave.');
        }
    }
    
    private function toggle_badge($arg) {
        if ($arg == 'disable') {
            return self::update_wpclef_option(self::PWD_OPT_BADGE, 'disabled', 'Footer will not show Clef badge or link.');
        } elseif (($arg == 'enable')) {
            return self::update_wpclef_option(self::PWD_OPT_BADGE, 'badge', 'Footer will show Clef badge.');
        } elseif (($arg == 'link')) {
            return self::update_wpclef_option(self::PWD_OPT_BADGE, 'link', 'Footer will show Clef link.');
        }
    }
    
    private function reset_pass_settings() {
        WP_CLI::confirm('Are you sure you want to reset your password settings to their fresh-install default values?');
            self::update_wpclef_option(self::PWD_OPT_ALL, 0);
            self::update_wpclef_option(self::PWD_OPT_CLEF, 1);
            self::update_wpclef_option(self::PWD_OPT_WP, '');    
            self::update_wpclef_option(self::PWD_OPT_API, 0);
            self::update_wpclef_option(PWD_OPT_WAVE, 1);

            return WP_CLI::success('Clef’s password settings have been reset to their fresh-install default values.');
    }
    
    private function update_api_option($arg, $opt) {
        // allowed $opt values include '' (i.e., delete) and 32 char length (i.e., api key).
        if ((strlen($opt) != 32) && (strlen($opt) > 0) ) {
            return WP_CLI::error('Invalid API option');
        }
        
        if ($arg == 'id') {
            return self::update_wpclef_option(self::PWD_OPT_API_ID, $opt, "New application ID: $opt");
        } elseif (($arg == 'secret')) {
            return self::update_wpclef_option(self::PWD_OPT_API_SECRET, $opt, "New application secret: $opt");
        }
    }
    
    private function print_option_info($option) {
        $msg = null;
        $current_value = $this->wpclef_opts[$option];
        
        switch($option) {
            case self::PWD_OPT_ALL:
                if ($current_value == 1) {
                    $msg = 'Passwords are disabled for all users.';
                } elseif ($current_value == 0) {
                    $msg = 'Passwords are enabled for all users.';
                }
                break;
            case self::PWD_OPT_CLEF:
                if ($current_value == 1) {
                    $msg = 'Passwords are disabled for clef users.';
                } elseif ($current_value == 0) {
                    $msg = 'Passwords are enabled for clef users.';
                }
                break;
            case self::PWD_OPT_WP:
                if ($current_value == 'Subscriber') {
                    $msg = 'Passwords are disabled for WP roles >= Subscriber.';
                } elseif ($current_value == 'Contributor') {
                    $msg = 'Passwords are disabled for WP roles >= Contributor';
                } elseif ($current_value == 'Author') {
                    $msg = 'Passwords are disabled for WP roles >= Author';
                } elseif ($current_value == 'Editor') {
                    $msg = 'Passwords are disabled for WP roles >= Editor';
                } elseif ($current_value == 'Administrator') {
                    $msg = 'Passwords are disabled for WP roles >= Administrator';
                } elseif ($current_value == 'Super Administrator') {
                    $msg = 'Passwords are disabled for WP roles >= Super Administrator';
                } elseif ($current_value == '') {
                    $msg = 'Passwords are not disabled for standard WP roles.';
                }
                break;
            case self::PWD_OPT_API:
                if ($current_value == 0) {
                    $msg = 'Passwords are disabled for the WP API.';
                } elseif ($current_value == 1) {
                    $msg = 'Passwords are enabled for the WP API.';
                }
                break;
            case self::PWD_OPT_WAVE:
                if ($current_value == 1) {
                    $msg = 'Wave: show Clef Wave on wp-login.php.';
                } elseif ($current_value == 0) {
                    $msg = 'Wave: show standard login form on wp-login.php.';
                }
                break;
            case self::PWD_OPT_OVERRIDE:
                if (empty($current_value)) {
                    $msg = 'An override URL has not been set.';
                } elseif ($current_value == 0) {
                    $msg = 'Override url: ';
                    $msg .= self::get_override_url();
                }    
                break;
            case self::PWD_OPT_BADGE:
                if ($current_value == 'disabled') {
                    $msg = 'Footer: do not show Clef badge or link.';
                } elseif ($current_value == 'badge') {
                    $msg = 'Footer: show Clef badge.';
                } elseif ($current_value == 'link') {
                    $msg = 'Footer: show Clef link.';
                }
                break;
            case self::PWD_OPT_API_ID:
                if (empty($current_value)) {
                    $msg = 'Application ID is not set!';
                } else {
                    $msg = "Application ID: $current_value";
                }
                break;
            case self::PWD_OPT_API_SECRET:
                if (empty($current_value)) {
                    $msg = 'Application secret is not set!';
                } else {
                    $msg = "Application secret: $current_value";
                }
                break;
            default:
                break;
        }
        
        if (!empty($msg)) {
            return WP_CLI::line($msg);
        } else {
            return WP_CLI::error("Unable to complete print_option_info() for $option");
        }
    }
    
    private function get_pass_option_value($option) {
        $current_value = $this->wpclef_opts[$option];
        return $current_value;
    }
    
    private function print_all_pass_option_info() {
        // build table row: clef                        
        if (self::get_pass_option_value(self::PWD_OPT_CLEF)) {
            $row_clef = array('Clef', 'Disabled'); 
        } else {
            $row_clef = array('Clef', 'Enabled');
        }
        
        // build table row: all
        if (self::get_pass_option_value(self::PWD_OPT_ALL)) {
            $row_all = array('All', 'Disabled'); 
        } else {
            $row_all = array('All', 'Enabled');
        }
        
        // build table row: api
        if (!self::get_pass_option_value(self::PWD_OPT_API)) {
            $row_api = array('WP API', 'Disabled'); 
        } else {
            $row_api = array('WP API', 'Enabled');
        }
        
        // build table row: wp roles
        $wp_role = self::get_pass_option_value(self::PWD_OPT_WP);
        
        if ($wp_role == '') {
            $row_wp = array('WP roles', 'Enabled');
        } else {
            switch($wp_role) {
                case 'Subscriber':
                case 'Contributor':
                case 'Author':
                case 'Editor':
                case 'Administrator':
                case 'Super Administrator':
                    $row_wp = array('WP roles', "Disabled for roles >= $wp_role");
                break;
            }
        }
        
        // build table columns
        $headers = array('Role', 'Passwords');
        $data = array(
            $row_clef,
            $row_all,
            $row_wp,
            $row_api
        );
        
        $table = new \cli\Table();
        $table->setHeaders($headers);
        $table->setRows($data);
        $table->display();
    }
    
    private function update_wpclef_option($option, $value, $msg=null) {
        // If the option is already set to the input value, return true.
        // Else, update the option to the input value, then return true.
        if ($this->wpclef_opts[$option] == $value) {
            
            if (isset($msg)) {
                WP_CLI::success($msg);
            }
            return 1;
            
        } else {
            // Update the option.
            $this->wpclef_opts[$option] = $value;
            
            if (update_option('wpclef', $this->wpclef_opts)) {
                
                if (isset($msg)) {
                    WP_CLI::success($msg);
                }
                
                return 1;
            } else {
                WP_CLI::error("Unable to complete update_wpclef_option() for $option.");
                return 0;
            }
        }
    }
    
    private function is_confirm_enable_passwords() {
        WP_CLI::confirm('Enabling passwords makes your site less secure. Are you sure you want to do this?');
        return 1;
    }
    
    private function create_override($key = null) {
        if (!empty($this->wpclef_opts[self::PWD_OPT_OVERRIDE])) {
            
            $current_url = self::get_override_url();
            
            WP_CLI::confirm('Your current override URL is: ' .$current_url .' Do you want to replace it with the new one?');
                if (isset($key)) {
                    $key = urlencode($key);
                    return self::update_wpclef_option(self::PWD_OPT_OVERRIDE, $key);
                } else {
                    $key = substr ( (md5(uniqid(mt_rand(), true))), 0, 15);
                    return self::update_wpclef_option(self::PWD_OPT_OVERRIDE, $key);
                }
        } else {
            
            if (isset($key)) {
                    $key = urlencode($key);
                    return self::update_wpclef_option(self::PWD_OPT_OVERRIDE, $key);
            } else {
                $key = substr ( (md5(uniqid(mt_rand(), true))), 0, 15);
                return self::update_wpclef_option(self::PWD_OPT_OVERRIDE, $key);
            }
        }
        return $this->wpclef_opts[self::PWD_OPT_OVERRIDE];
    }
    
    private function get_override_url() {
        $url = wp_login_url();
        $url .= '?override=';
        $url .= $this->wpclef_opts[self::PWD_OPT_OVERRIDE];

        return $url;
    }
    
    private function show_override_confirmation() {
        $msg = 'Your new override URL is: ';
        $msg .= self::get_override_url();

        WP_CLI::success($msg);
    }

    private function send_override_email($to=null) {
        $site_name = get_bloginfo('name');
        $subject = "$site_name Clef override URL";
        $from_email = $this->admin_email;
        if (isset($to)) {
            // validate email address
            if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $to_email = $to;
            } else {
                WP_CLI::error('Invalid email address entered for --to option.');
                return;
            }
            
            $to_email = $to;
        } else {
            $to_email = $this->admin_email;
        }
        $override_url = self::get_override_url();
        $message = "<p>Your Clef override URL for $site_name: $override_url</p><p>Bookmark this URL for safekeeping.</p><p>Thanks for using Clef!<br>the Clef Team<br>https://getclef.com</p>";
            
        $headers = "From: WordPress Admin <$from_email> \r\n";
        add_filter('wp_mail_content_type', array('ClefUtils', 'set_html_content_type'));

        $sent = wp_mail($to_email, $subject, $message, $headers);

        remove_filter('wp_mail_content_type', array('ClefUtils', 'set_html_content_type'));
        return $sent;
    }
    
    private function print_all_option_info() {
        WP_CLI::line('');
        WP_CLI::line('DISABLE PASSWORDS SETTINGS:');
        self::print_all_pass_option_info();
        WP_CLI::line('');
        WP_CLI::line('OTHER SETTINGS:');
        
        // build table row: wave                        
        if (self::get_pass_option_value(self::PWD_OPT_WAVE)) {
            $row_wave = array('Wave', 'Show Clef wave on wp-login.php'); 
        } else {
            $row_wave = array('Wave', 'Show standard login form on wp-login.php');
        }
        
        // build table row: override                        
        if (self::get_pass_option_value(self::PWD_OPT_OVERRIDE)) {
            $row_override = array('Override URL', 'Disabled'); 
        } else {
            $row_override = array('Override URL', 'Enabled');
        }
        
        // build table row: badge
        if (self::get_pass_option_value(self::PWD_OPT_BADGE) == 'badge') {
            $row_badge = array('Badge:', 'Show badge'); 
        } elseif (self::get_pass_option_value(self::PWD_OPT_BADGE) == 'link') {
            $row_badge = array('Badge', 'Show link');
        } elseif (self::get_pass_option_value(self::PWD_OPT_BADGE) == 'disabled') {
            $row_badge = array('Badge', 'Disabled');
        }
        
        // build table row: application settings
        $row_application_id = array('Application ID', self::get_pass_option_value(self::PWD_OPT_API_ID));
        $row_application_secret = array('Application secret', self::get_pass_option_value(self::PWD_OPT_API_SECRET)); 
                
        // build table columns
        $headers = array('Setting', 'Value');
        $data = array(
            $row_wave,
            $row_override,
            $row_badge,
            $row_application_id,
            $row_application_secret
        );
        
        $table = new \cli\Table();
        $table->setHeaders($headers);
        $table->setRows($data);
        $table->display();
        WP_CLI::line('');
    }
    
    /**
     * Display wpclef’s current settings.
     * 
     * @synopsis
     */
    function info($args, $assoc_args) {
        self::print_all_option_info();
    }
    
    /**
     * Configure password disabling for select user roles and for the WP API.
     * 
     * ## ACTIONS
     * 
     * <disable>
     * : Turn off passwords (i.e., add Clef auth security).
     * 
     * <enable>
     * : Turn on passwords (i.e., remove Clef auth security).
     * 
     * <info> [<role>]
     * : Show the current password settings.
     * Use without options to view all password settings values.
     * Use with a role option to view only that role's password setting value.
     * 
     * <reset>
     * : Reset all password settings to their default values.
     * Use exclusively with the <all-options> option.
     * 
     * ## ROLES & OPTIONS
     * 
     * <all>
     * : All WP users. Default value: enable passwords.
     * 
     * <clef>
     * : All Clef mobile app users. Default value: disable passwords.
     * 
     * <subscriber>
     * : WP roles >= Subscriber. Default value: enable passwords.
     * 
     * <contributor>
     * : WP roles >= Contributor. Default value: enable passwords.
     * 
     * <author>
     * : WP roles >= Author. Default value: enable passwords.
     * 
     * <editor>
     * : WP roles >= Editor. Default value: enable passwords.
     * 
     * <admin>
     * : WP roles >= Administrator. Default value: enable passwords.
     * 
     * <superadmin>
     * : Super Administrator WP role. Default value: enable passwords.
     * 
     * <api>
     * : Tthe WP API including XML-RPC. Default value: disable passwords.
     * 
     * ## EXAMPLES
     * 
     *     wp clef passwords info
     *     wp clef passwords info admin
     *     wp clef passwords disable all
     *     wp clef passwords enable clef 
     *     wp clef passwords reset
     *
     * @synopsis <action> [<role|option>]
     */
    function passwords($args, $assoc_args) {
        $cmd_name = 'passwords';
        
        //If no commands or flags are entered, exit; otherwise, filter input and execute the commands and flags.
        self::is_valid_command_input($args, $assoc_args, $cmd_name);
        $args = self::get_filtered_command_input($args);
        $assoc_args = self::get_filtered_command_input($assoc_args);
        
        // Handle 'info' and 'reset' actions first.
        if ( ($args[0] == 'info') && (empty($args[1])) ) {
            self::print_all_pass_option_info();
            self::print_option_info(self::PWD_OPT_WAVE);
            return;
        } elseif ( ($args[0] == 'reset') && (empty($args[1])) ) {
            self::reset_pass_settings();
            return;
        }
            
        // Now handle all other 'passwords' subcommands (action + option).
        // The order of the positional arguments: $args[0] = <action>; $args[1] = <role|option>.
        foreach (array($args) as $arg) {
            switch ($arg[1]) {
                case 'clef':
                    $role = ucwords($arg[1]) .' users';
                    self::toggle_passwords($arg[0], self::PWD_OPT_CLEF, $role);
                    break;
                case 'all':
                    $role = ucwords($arg[1]) .' users';
                    self::toggle_passwords($arg[0], self::PWD_OPT_ALL, $role);
                    break;
                case 'api':
                    $role = 'the WP API';
                    self::toggle_passwords_api($arg[0], self::PWD_OPT_API, $role);
                    break;
                case 'subscriber':
                case 'contributor':
                case 'author':
                case 'editor':
                    $wprole = ucwords($arg[1]);
                    self::toggle_passwords_wprole($arg[0], self::PWD_OPT_WP, $wprole);
                    break;
                case 'admin':
                    $wprole = 'Administrator';
                    self::toggle_passwords_wprole($arg[0], self::PWD_OPT_WP, $wprole);
                    break;
                case 'superadmin':
                    $wprole = 'Super Administrator';
                    self::toggle_passwords_wprole($arg[0], self::PWD_OPT_WP, $wprole);
                    break;
                default:
                    self::error_invalid_option($cmd_name);
                    break;
            }
        }
    }
    
    /**
     * Display either the Clef Wave or the standard WP login form on wp-login.php.
     * 
     * ## OPTIONS
     * 
     * <info>
     * : Display the current value of the wave option.
     * 
     * <enable> (default value)
     * : Show the Clef Wave on wp-login.php.
     * 
     * <disable>
     * : Show the standard WP login form on wp-login.php.
     * 
     *
     * ## EXAMPLES
     * 
     *     wp clef wave info
     *     wp clef wave enable
     *     wp clef wave disable
     *
     * @synopsis <option>
     */
    function wave($args, $assoc_args) {
        $cmd_name = 'wave';
        
        //If no commands or flags are entered, exit; otherwise, execute the commands and flags.
        self::is_valid_command_input($args, $assoc_args, 'wave');
        
        // Execute commands.
        $args = self::get_filtered_command_input($args);

        switch($args[0]) {
            case 'info':
                return self::print_option_info(self::PWD_OPT_WAVE);
                break;
            case 'enable':
            case 'disable':
                self::toggle_wave($args[0]);
                break;
            default:
                self::error_invalid_option("$cmd_name");
                break;
        }
    }

    /**
     * Test your logout hook from the command line.
     * 
     * ## OPTIONS
     * 
     * <url>
     * : The logout hook URL from the Clef application that is connected
     * to your WP site. You will find this URL in your
     * getclef.com/user dashboard.
     *
     * --siteurl
     * : The WordPress site_url(). Use this option for debugging when the logout hook
     * URL in your Clef application is different than the value of your site_url().  
     *
     * ## EXAMPLES
     * 
     *     wp clef hook http://blog.getclef.com
     *     wp clef hook --siteurl
     * 
     * @synopsis [<url>] [--siteurl]
     * */
    function hook($args, $assoc_args) {
        $cmd_name = 'hook';
        //If no commands or flags are entered, exit; otherwise, execute the commands and flags.
        self::is_valid_command_input($args, $assoc_args, "$cmd_name");
        
        // Execute commands and flags.
        $args = self::get_filtered_command_input($args);
        $assoc_args = self::get_filtered_command_input($assoc_args);

        if ($assoc_args['siteurl']) {
            
            // return error if this is the localhost since Clef logout hooks require the server to be connected to the internet.
            $hook_url = site_url();
            if (preg_match('/localhost/', $hook_url)) {
                WP_CLI::error('Clef’s logout hook server cannot ping local servers that are not connected to the internet (e.g., http://localhost/).');
            } else {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $hook_url);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Clef/1.0 (https://getclef.com)');
                curl_setopt($ch, CURLOPT_POSTFIELDS, 'logout_token=1234567890');
                curl_exec($ch);
                curl_close($ch);
                WP_CLI::line('');
            }
        } elseif (preg_match('/(https?):\/\/([A-Za-z0-9]+)(\.+)([A-Za-z]+)/', $args[0]) ? true : false) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $command);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Clef/1.0 (https://getclef.com)');
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'logout_token=1234567890');
            curl_exec($ch);
            curl_close($ch);
            WP_CLI::line('');
        } else {
            self::error_invalid_option("$cmd_name");
        }
    }
    
    /**
     * Configure a secret override URL that allows password-based logins.
     * 
     * ## COMMANDS
     * 
     * <info>
     * : Display your current override URL. 
     *
     * <enable>
     * : Create a new override URL or overwrite the existing one.
     * 
     * <email>
     * : Email your override URL to your WP user’s email address. 
     * 
     * <disable>
     * : Delete the existing override URL.
     * 
     * ## OPTIONS
     * 
     * [--key=<your-custom-key>]
     * : Customize your override URL: http://example.com?override=your_custom_key.
     *
     * [--to=<email-address>]
     * : Email the override URL to the specified email address.
     * 
     * ## EXAMPLES
     * 
     *     wp clef override info
     *     wp clef override enable
     *     wp clef override enable --key=my_secret_key
     *     wp clef override email
     *     wp clef override email --to=jane@doe.com
     *     wp clef override disable
     *
     * @synopsis <command> [--key=<your_custom_key>] [--to=<email-address>]
     */
    function override($args, $assoc_args) {
        $cmd_name = 'override';
        
        //If no commands or flags are entered, exit; otherwise, execute the commands and flags.
        self::is_valid_command_input($args, $assoc_args, "$cmd_name");
        $args = self::get_filtered_command_input($args);
        $assoc_args = self::get_filtered_command_input($assoc_args);
        
        // 'wp clef override info'
        if ( ($args[0] == 'info') && (empty($args[1])) && (empty($assoc_args)) ) {
            return self::print_option_info(self::PWD_OPT_OVERRIDE);
        } 
        // 'wp clef override enable'
        elseif ( ($args[0] == 'enable') && (empty($args[1])) && (empty($assoc_args['key'])) ) {
            if (self::create_override()) {
                self::show_override_confirmation();
            }
        }
        // 'wp clef override enable --key=my_secret_key'
        elseif ( ($args[0] == 'enable') && (isset($assoc_args['key'])) ) {
            self::create_override($assoc_args['key']);
            self::show_override_confirmation();
        }
         // 'wp clef override email'
        elseif ( ($args[0] == 'email') && (empty($args[1])) && (empty($assoc_args['to'])) ) {
            // If an override URL is set, send the email
            if (!empty($this->wpclef_opts[self::PWD_OPT_OVERRIDE])) {
                WP_CLI::confirm("Email the override URL to $this->admin_email?");
                if (self::send_override_email()) {
                    WP_CLI::success('Email sent.');
                } else {
                    WP_CLI::success('Could not send email.');
                }
            } else {
                WP_CLI::line('The override URL has not been enabled. Enable it first, then you can email it.');
            }
                
            
        }
        // 'wp clef override email --to=jane@doe.com'
        elseif ( ($args[0] == 'email') && (isset($assoc_args['to'])) ) {
            // If override url is set, send email to --to=address
            if (!empty($this->wpclef_opts[self::PWD_OPT_OVERRIDE])) {
            
                // If valid email address, send the override email.
                if (filter_var($assoc_args['to'], FILTER_VALIDATE_EMAIL)) {

                    WP_CLI::confirm("Email the override URL to {$assoc_args['to']}?");
                    if (self::send_override_email($assoc_args['to'])) {
                        return WP_CLI::success('Email sent.');
                    } else {
                        return WP_CLI::error('Could not send email.');
                    }
                } else {
                    WP_CLI::error('Invalid email address entered for --to=address option.');
                    return;
                }
            
            } else {
                WP_CLI::line('The override URL has not been enabled. Enable it first, then you can email it.');
            }
        }
        // 'wp clef override disable'
        elseif ( ($args[0] == 'disable') && (empty($args[1])) && (empty($assoc_args)) ) {
            if (!empty($this->wpclef_opts[self::PWD_OPT_OVERRIDE])) {
                self::update_wpclef_option(self::PWD_OPT_OVERRIDE, '', 'Override URL deleted.');
            } else {
                WP_CLI::line('Your override URL is not set; there is nothing to delete.');
            }
        } else {
            self::error_invalid_option("$cmd_name");
        } 
    }
    
    /**
     * Display the Clef badge or link in the site’s footer.
     * 
     * ## OPTIONS
     * 
     * <info>
     * : Display the current value of the 'badge' option.
     * 
     * <enable>
     * : Show the Clef badge.
     * 
     * <link>
     * : Show the Clef link.
     * 
     * <disable> (default value)
     * : Do not show the Clef badge or link. 
     *
     * ## EXAMPLES
     * 
     *     wp clef badge info
     *     wp clef badge enable
     *     wp clef badge link
     *     wp clef badge disable
     *
     * @synopsis <option>
     */
    function badge($args, $assoc_args) {
        $cmd_name = 'badge';
        
        //If no commands or flags are entered, exit; otherwise, execute the commands and flags.
        self::is_valid_command_input($args, $assoc_args, "$cmd_name");
        
        // Execute commands.
        $args = self::get_filtered_command_input($args);

        switch($args[0]) {
            case 'info':
                return self::print_option_info(self::PWD_OPT_BADGE);
                break;
            case 'enable':
            case 'disable':
            case 'link':
                self::toggle_badge($args[0]);
                break;
            default:
                self::error_invalid_option("$cmd_name");
                break;
        }
    }
    
    /**
     * Manage wpclef’s local Clef API settings.
     * 
     * Use this command only if you manually create a new integration for your WP site via the getclef.com dashboard (https://getclef.com/user) and hence need to update your WP site’s local API settings to match the Clef server’s API settings.
     * 
     * ## OPTIONS
     * 
     * <info>
     * : Display the current value of the 'api' option.
     * 
     * <id> <value>
     * : Update the Clef application id.
     * 
     * <secret> <value>
     * : Update the Clef application secret.
     * 
     * ## EXAMPLES
     * 
     *     wp clef api info
     *     wp clef api id 00000000000000000000000000000000
     *     wp clef api secret 111111111111111111111111111111
     * 
     * If you need to delete the id or secret, use '' for the value like this:
     * 
     *     wp clef api id ''
     *
     * @synopsis <option> [<value>]
     */
    function api($args, $assoc_args) {
        $cmd_name = 'api';
        
        //If no commands or flags are entered, exit; otherwise, execute the commands and flags.
        self::is_valid_command_input($args, $assoc_args, "$cmd_name");
        
        // Execute commands.
        $args = self::get_filtered_command_input($args);

        switch($args[0]) {
            case 'info':
                self::print_option_info(self::PWD_OPT_API_ID);
                self::print_option_info(self::PWD_OPT_API_SECRET);
                break;
            case 'id':
            case 'secret':
                if (isset($args[1])) {
                    self::update_api_option($args[0], $args[1]);
                } else {
                    self::error_invalid_option("$cmd_name");
                }
                break;
            default:
                self::error_invalid_option("$cmd_name");
                break;
        }
    }
}

WP_CLI::add_command('clef', 'Clef_WPCLI_Command');
?>