<?php
/**
 * Manage wpclef from the command line.
 * 
 * This class adds WP-CLI (http://wp-cli.org) functionality to the wpclef plugin (https://wordpress.org/plugins/wpclef/).
 * 
 * Contributions are welcome! See https://github.com/clef/wordpress.
 * 
 * @author Laurence O’Donnell <laurence@getclef.com>
 * @version 0.0
 * @since 2.2.9
 * @uses 
 */ 
class Clef_WPCLI_Command extends WP_CLI_Command {

   // Define class properties.
    private $wpclef_opts;
    const PWD_OPT_CLEF = 'clef_password_settings_disable_passwords';
    const PWD_OPT_WP = 'clef_password_settings_disable_certain_passwords';
    const PWD_OPT_ALL = 'clef_password_settings_force';
    const PWD_OPT_API = 'clef_password_settings_xml_allowed';
    const PWD_OPT_WAVE = 'clef_form_settings_embed_clef';
    private $site_url;
    private $user_email;
    
    function __construct() {
        $this->wpclef_opts = get_option('wpclef');
        $this->site_url = site_url();
        $this->user_email = $user->user_email;
    }
    
   // Define utility methods. 
    function is_valid_command_input($args, $assoc_args, $command) {
        
        if (empty($args) && empty($assoc_args)) {
            self::error_invalid_option($command);
            return 0;
        } else {
            return 1;
        }
    }
    
    function get_filtered_command_input($input) {
        
        $input = array_map('strtolower', $input);
        return $input;
    }
    
    function error_invalid_option($command) {
        
        WP_CLI::error("Please enter a valid option for '$command'. For help, use 'wp help clef $command'.");
    }
    
    function do_pass_command($cmd_name, $arg, $role, $wprole, $msg_disable, $msg_enable) {
        
        // Check whether a standard WP role was sent, which indicates that we're dealing with a string
        // (i.e., the standard WP roles used in the SELECT option in wpclef's pwd settings)
        if (!empty($wprole)) {
            if ($arg == 'info') {
                return self::get_option_info($role);
            } elseif ($arg == 'disable') {
                $value = $wprole;
                return self::update_wpclef_option($role, $value, $msg_disable);
            } elseif (($arg == 'enable')) {
                $value = '';
                return self::update_wpclef_option($role, $value, $msg_enable);
            } else {
                return self::error_invalid_option($cmd_name);
            }
        }
        // Otherwise, if the all-options role is set, handle the appropriate command.
        elseif ( ($arg == 'reset') && ($role == 'all-options') ) {
                WP_CLI::confirm('Are you sure you want to reset your password settings to their fresh-install default values?');
                    self::update_wpclef_option(self::PWD_OPT_ALL, 0);
                    self::update_wpclef_option(self::PWD_OPT_CLEF, 1);
                    self::update_wpclef_option(self::PWD_OPT_WP, '');    
                    self::update_wpclef_option(self::PWD_OPT_API, 0);
                    self::update_wpclef_option(PWD_OPT_WAVE, 1);

                    return WP_CLI::success('Clef’s password settings have been reset to their fresh-install default values.');

        } elseif ( ($arg == 'info') && ($role == 'all-options') ) {
                return self::get_all_pass_option_info();
        }
        // Otherwise, we're dealing with a boolean option.
        // The WP API and wave options have inverted boolean values, so handle those options first
        // before handling the ordinary boolean options.
        elseif ( ($role == self::PWD_OPT_API) || ($role == self::PWD_OPT_WAVE) ) {
            if ($arg == 'info') {
                return self::get_option_info($role);
            } elseif ($arg == 'disable') {
                $value = 0;
                return self::update_wpclef_option($role, $value, $msg_disable);
            } 
            elseif (($arg == 'enable')) {
                $value = 1;
                return self::update_wpclef_option($role, $value, $msg_enable);
            } else {
                return self::error_invalid_option($cmd_name);
            }
        } 
        // Now handle the ordinary boolean options.
        // Exclude the 'all-options' option as invalid for the boolean options.
        elseif ($arg == 'info') {
            return self::get_option_info($role);
        } elseif ( ($arg == 'disable') && ($role != 'all-options') ) {
            $value = 1;
            return self::update_wpclef_option($role, $value, $msg_disable);
        } elseif ( ($arg == 'enable') && ($role != 'all-options') ) {
            $value = 0;
            return self::update_wpclef_option($role, $value, $msg_enable);
        } else {
            return self::error_invalid_option($cmd_name);
        }
    }
    
    function get_option_info($option) {
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
                    $msg = 'Wp-login.php will show the Clef Wave.';
                } elseif ($current_value == 0) {
                    $msg = 'Wp-login.php will show the standard WP login form.';
                }
                break;
            default:
                break;
        }
        
        if (!empty($msg)) {
            return WP_CLI::line($msg);
        } else {
            return WP_CLI::error("Unable to complete get_option_info() for $option");
        }
    }
    
    function get_all_pass_option_info() {
        self::get_option_info(self::PWD_OPT_CLEF);
        self::get_option_info(self::PWD_OPT_WP);
        self::get_option_info(self::PWD_OPT_ALL);
        self::get_option_info(self::PWD_OPT_API);
        self::get_option_info(self::PWD_OPT_WAVE);
    }
    
    function update_wpclef_option($option, $value, $msg = null) {
            
        // If the option is already set to the input value, return true.
        // Else, update the option to the input value and return true.
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
    
    function is_confirm_enable_passwords() {
        WP_CLI::confirm('Enabling passwords makes your site less secure. Are you sure you want to do this?');
        return 1;
    }
    
    function create_override($key = null) {
        if (!empty($this->wpclef_opts['clef_override_settings_key'])) {
            
            $current_url = self::get_override_url();
            
            WP_CLI::confirm('Your current override URL is: ' .$current_url .' Do you want to replace it with the new one?');
                if (isset($key)) {
                    $key = urlencode($key);
                    self::update_wpclef_option('clef_override_settings_key', $key);
                    //return true;
                } else {
                    $key = substr ( (md5(uniqid(mt_rand(), true))), 0, 15);
                    self::update_wpclef_option('clef_override_settings_key', $key);
                    //return true;
                }

        } else {
            
            if (isset($key)) {
                    $key = urlencode($key);
                    self::update_wpclef_option('clef_override_settings_key', $key);
                    //return true;
                } else {
                    $key = substr ( (md5(uniqid(mt_rand(), true))), 0, 15);
                    self::update_wpclef_option('clef_override_settings_key', $key);
                    //return true;
                }
        }
        
        return $this->wpclef_opts['clef_override_settings_key'];
    }
    
    function get_override_url() {
        $url = site_url();
        $url .= '/override=?';
        $url .= $this->wpclef_opts['clef_override_settings_key'];

        return $url;
    }
    
    function create_override_confirmation() {
        $msg = 'Your new override URL is: ';
        $msg .= self::get_override_url();

        WP_CLI::success($msg);
        WP_CLI::confirm("Would you like to email yourself a copy of your override URL so you don't forget it?");
            WP_CLI::run_command(['clef', 'override'],['email' => 'me']);
    }

    function send_email($to, $url) {
        $msg = '<p>Hello,</p><p>Here is your Clef override URL:<br />'.$url;
        $subject = 'Clef override URL for ' .$this->site_url;
        
        if (wp_mail($to, $subject, $msg)) {
            return true;
        } else {
            return false;
        }
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
     * <info>
     * : Show the current password settings for the selected role or option.
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
     * <all-options>
     * : All password options. Use exclusively with the <info> and <reset> actions.
     * 
     * ## EXAMPLES
     * 
     *     wp clef passwords disable all
     *     wp clef passwords enable clef 
     *     wp clef passwords info admin
     *     wp clef passwords info all-options
     *     wp clef passwords reset all-options
     *
     * @synopsis <action> <role|option>
     */
    function passwords($args, $assoc_args) {
        $cmd_name = 'passwords';
        
        //If no commands or flags are entered, exit; otherwise, filter input and execute the commands and flags.
        self::is_valid_command_input($args, $assoc_args, $cmd_name);
        
        $args = self::get_filtered_command_input($args);
        $assoc_args = self::get_filtered_command_input($assoc_args);
        
        // Execute commands. The order of the positional arguments: $args[0] = <action>; $args[1] = <role>.
        foreach (array($args) as $arg) {
            switch ($arg[1]) {
                case 'clef':
                    $role = ucwords($arg[1]) .' users';
                    self::do_pass_command($cmd_name, $arg[0], self::PWD_OPT_CLEF, null, "Passwords are now disabled for $role.", "Passwords are now enabled for $role.");
                    break;
                case 'all':
                    $role = ucwords($arg[1]) .' users';
                    self::do_pass_command($cmd_name, $arg[0], self::PWD_OPT_ALL, null, "Passwords are now disabled for $role.", "Passwords are now enabled for $role.");
                    break;
                case 'api':
                    $role = 'the WP API';
                    self::do_pass_command($cmd_name, $arg[0], self::PWD_OPT_API, null, "Passwords are now disabled for $role.", "Passwords are now enabled for $role.");
                    break;
                case 'subscriber':
                case 'contributor':
                case 'author':
                case 'editor':
                case 'admin':
                case 'superadmin':
                    $wprole = ucwords($arg[1]);
                    self::do_pass_command($cmd_name, $arg[0], self::PWD_OPT_WP, "$wprole", "Passwords are disabled for WP roles >= $wprole.", "Passwords are enabled for the $wprole role.");
                    break;
                case 'all-options':
                    self::do_pass_command($cmd_name, $arg[0], 'all-options', null, 'Disabled message', 'Enabled message');
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
     * <enable>
     * : Show the Clef Wave on wp-login.php.
     * 
     * <disable>
     * : Show the standard WP login form on wp-login.php.
     * 
     * Default value: enable.
     *
     * ## EXAMPLES
     * 
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
        $disable_msg = 'Wp-login.php will show the standard WP login form.';
        $enable_msg = 'Wp-login.php will show the Clef Wave.';

        foreach (array($args) as $arg) {
            switch ($arg[0]) {
                case 'enable':
                    self::do_pass_command($cmd_name, $arg[0], self::PWD_OPT_WAVE, null, $disable_msg, $enable_msg);
                    break;
                case 'disable':
                    self::do_pass_command($cmd_name, $arg[0], self::PWD_OPT_WAVE, null, $disable_msg, $enable_msg);
                    break;
                case 'info':
                    self::do_pass_command($cmd_name, $arg[0], self::PWD_OPT_WAVE, null, $disable_msg, $enable_msg);
                break;
                default:
                    self::error_invalid_option("$cmd_name");
                break;
            }
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
        
        //If no commands or flags are entered, exit; otherwise, execute the commands and flags.
        self::is_valid_command_input($args, $assoc_args, 'hook');
        
        // Execute commands and flags.
        $args = self::get_filtered_command_input($args);
        $assoc_args = self::get_filtered_command_input($assoc_args);

        if ($assoc_args['siteurl']) {
            
            // return error if this is the localhost since Clef logout hooks require the server to be connected to the internet.
            $hook_url = site_url();
            if (preg_match('/localhost/', $hook_url)) {
                WP_CLI::error('Clef’s logout hook server cannot ping local servers that are not connected to the internet (e.g., http://localhost).');
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

            // create a new cURL resource and set options
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $command);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Clef/1.0 (https://getclef.com)');
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'logout_token=1234567890');

            // execute cURL command and print to STDOUT
            curl_exec($ch);

            // close cURL
            curl_close($ch);

            WP_CLI::line('');
        } else {
            self::error_invalid_option('hook');
        }
       
    }
    
    /**
     * Configure an override URL that allows password-based logins via a secret URL.
     * 
     * ## COMMANDS
     * 
     * <info>
     * : Display your current override URL. 
     *
     * <create>
     * : Create a new override URL or overwrite the existing one.
     * 
     * <email>
     * : Email your override URL to your WP user’s email address. 
     * 
     * <delete>
     * : Delete the existing override URL.
     * 
     * ## OPTIONS
     * 
     * [--key=<your_custom_key>]
     * : Customize your override URL: http://example.com?override=your_custom_key.
     *
     * [--to=<address>]
     * : Email the override URL to the specified email address.
     * 
     * ## EXAMPLES
     * 
     *     wp clef override info
     *     wp clef override create
     *     wp clef override create --key=my_secret_key
     *     wp clef override email
     *     wp clef override email --to=jane@doe.com
     *     wp clef override delete
     *
     * @synopsis <command> [--option=<value>]
     */
    function override($args, $assoc_args) {
        
        //If no commands or flags are entered, exit; otherwise, execute the commands and flags.
        self::is_valid_command_input($args, $assoc_args, 'override');
        
        // Execute commands.
        if ($commands = self::get_filtered_command_input($args)) {

            foreach ($commands as $command) {
                switch ($command) {
                    case 'create':
                        if (self::create_override()) {
                            
                            self::create_override_confirmation();
                        }
                        break;
                    case 'delete':
                        if (!empty($this->wpclef_opts['clef_override_settings_key'])) {
                            
                            self::update_wpclef_option('clef_override_settings_key', '', 'Override URL deleted.');
                            
                        } else {
                            WP_CLI::line('Your override URL is not set; there is nothing to delete.');
                        }
                        break;
                    default:
                        self::error_invalid_option('override');
                    break;
                }
            }
        }
        
        // Execute flags.
        if ($flags = self::get_filtered_command_input($assoc_args)) {

            foreach ($flags as $key => $value) {
                switch ($key) {
                    case 'info':
                        if (!empty($this->wpclef_opts['clef_override_settings_key'])) {
                            WP_CLI::line($this->site_url.'/override=?'.$this->wpclef_opts['clef_override_settings_key']);
                        break;
                        } else {
                            WP_CLI::confirm('You have not yet set an override URL. Would you like to create one now?');
                                WP_CLI::run_command(['clef', 'override', 'create']);
                        break;
                        }
                    case 'key':
                        if ($this->wpclef_opts['clef_override_settings_key'] = self::create_override($value)) {
                            
                            self::create_override_confirmation();
                        }
                        break;
                    case 'email':
                        if (strtolower($value) == 'me') {
                            $url = self::get_override_url();

                            if (self::send_email('laurence.odonnell@gmail.com', $url)) {
                                WP_CLI::success('Email sent to ***Laurence.');
                            } else {
                                WP_CLI::error("Email not sent!");
                            }
                        } else {
                            self::error_invalid_option('override');
                        }
                        break;
                    default:
                        self::error_invalid_option('override');
                        break;
                }
            }       
        }   
    }
}

WP_CLI::add_command('clef', 'Clef_WPCLI_Command');
?>