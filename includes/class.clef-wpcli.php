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

   /**
    * Define class properties.
    */
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
    
   /**
    * Define utility methods.
    */
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
        
        // Check whether a standard WP role was sent, which indicates
        // that we're dealing with a string (i.e., the standard WP roles used in the SELECT option in wpclef's pwd settings)
        if (!empty($wprole)) {
            if ($arg == 'info') {
                self::get_option_info($role);
            } elseif ($arg == 'disable') {
                $value = $wprole;
                self::update_wpclef_option($role, $value, $msg_disable);
            } 
            elseif (($arg == 'enable')) {
                $value = '';
                self::update_wpclef_option($role, $value, $msg_enable);
            } else {
                self::error_invalid_option($cmd_name);
            }
        } 
        // Otherwise, we're dealing with a boolean option.
        // But the WP API boolean value is inverted, so run a check for that option first
        // before handling the ordinary boolean options.
        elseif ($role == self::PWD_OPT_API) {
            if ($arg == 'info') {
                self::get_option_info($role);
            } elseif ($arg == 'disable') {
                $value = 0;
                self::update_wpclef_option($role, $value, $msg_disable);
            } 
            elseif (($arg == 'enable')) {
                $value = 1;
                self::update_wpclef_option($role, $value, $msg_enable);
            } else {
                self::error_invalid_option($cmd_name);
            }
        } 
        // Now handle the ordinary boolean options.
        elseif ($arg == 'info') {
            self::get_option_info($role);
        } elseif ($arg == 'disable') {
            $value = 1;
            self::update_wpclef_option($role, $value, $msg_disable);
        } elseif (($arg == 'enable')) {
            $value = 0;
            self::update_wpclef_option($role, $value, $msg_enable);
        } else {
            self::error_invalid_option($cmd_name);
        }
    }
    
    function get_option_info($option) {
            
        $current_value = $this->wpclef_opts[$option];
        $msg = null;
        
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
            case 'all-roles':
                break;
            default:
                break;
        }
        
        if (isset($msg)) {
            return WP_CLI::line($msg);
        } else {
            return WP_CLI::error("Unable to complete get_option_info() for $option");
        }
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
     * Prints a greeting.
     * 
     * ## OPTIONS
     * 
     * <name>
     * : The name of the person to greet.
     * 
     * --boom
     * : Here comes the
     * 
     * ## EXAMPLES
     * 
     *     wp example hello Newman
     *
     * @synopsis [<name>] [--boom]
     */
    function hello( $args, $assoc_args ) {
        list( $name ) = $args;

        // Print a success message
        WP_CLI::success( "Hello, $name!" );
    }
    
    
    /**
     * Configure password disabling for select WP roles and for the WP API.
     * 
     * ## OPTIONS
     * 
     * <action>
     * : The password disabling action to perform. The valid actions are:
     * (1) disable (turn off passwords)
     * (2) allow (turn on passwords)
     * (3) info (show current setting)
     * 
     * <role>
     * : The role to which you want to apply the action. The valid roles include:
     * (1) all (all WP users; default value: allow).
     * (2) clef (all Clef mobile app users; default value: disable).
     * (3) subscriber (WP roles >= Subscriber; default value: allow).
     * (4) contributor (WP roles >= Contributor; default value: allow).
     * (5) author (WP roles >= Author; default value: allow).
     * (6) editor (WP roles >= Editor; default value: allow).
     * (7) admin (WP roles >= Administrator; default value: allow).
     * (8) superadmin (Super Administrator WP role; default value: allow).
     * (9) api (the WP API including XML-RPC; default value: disable).
     * (*) all-roles (use with <action> 'info' to show all pwd settings).
     * 
     * --none
     * : Use this flag to remove password password disabling from all of the
     * standard WP user roles (i.e., author, editor, admin, etc.).
     * This flag does not reset the 'all' or 'clef' role options.
     * 
     * --reset
     * : Return all password settings to their default values.
     * 
     * --info
     * : Display a role’s current password settings.
     * 
     * ## EXAMPLES
     * 
     *     wp clef pass disable all
     *     wp clef pass enable clef 
     *     wp clef pass --reset
     *
     * @synopsis <action> <role>
     */
    function pass($args, $assoc_args) {
        $cmd_name = 'pass';
        
        //If no commands or flags are entered, exit; otherwise, filter input and execute the commands and flags.
        self::is_valid_command_input($args, $assoc_args, $cmd_name);
        
        $args = self::get_filtered_command_input($args);
        $assoc_args = self::get_filtered_command_input($assoc_args);
        
        
        // Execute flags. The order of the positional arguments: $args[0] = <action>; $args[1] = <role>.
        foreach ($assoc_args as $key => $value) {
            switch ($key) {
                case 'none':
                    self::update_wpclef_option(self::PWD_OPT_WP, '', 'Passwords are NOT disabled for any WP roles.');
                    break;
                case 'reset':
                    // If confirm = true, reset the settings.
                    WP_CLI::confirm('Are you sure you want to reset your password settings to their fresh-install default values?');
                        self::update_wpclef_option(self::PWD_OPT_ALL, 0);
                        self::update_wpclef_option(self::PWD_OPT_CLEF, 1);
                        self::update_wpclef_option(self::PWD_OPT_WP, '');    
                        self::update_wpclef_option(self::PWD_OPT_API, 0);
                        self::update_wpclef_option(PWD_OPT_WAVE, 1);
                        WP_CLI::success('Clef’s password settings have been reset to their fresh-install default values.');
                    break;
                case 'info':
                    if ($this->wpclef_opts[self::PWD_OPT_ALL] == 1) {
                        WP_CLI::line('Disable passwords for Clef users:');
                    }
                    break;
                default:
                    self::error_invalid_option('pass');
                    break;
            }
        }
        
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
                default:
                    self::error_invalid_option($cmd_name);
                    break;
            }
        }
    }
    
    /**
     * Display either the Clef Wave (on) or the standard WP login form (off) on wp-login.php.
     * 
     * ## OPTIONS
     * 
     * <action>
     * : Show or hide the Clef Wave on wp-login.php. There are two action options:
     * (1) enable (show the Clef Wave on wp-login.php).
     * (2) disable (show the standard WP login form on wp-login.php).
     * Default: enable.
     *
     * ## EXAMPLES
     * 
     *     wp clef wave enable
     *     wp clef wave disable
     *
     * @synopsis <action>
     */
    function wave($args, $assoc_args) {

        //If no commands or flags are entered, exit; otherwise, execute the commands and flags.
        self::is_valid_command_input($args, $assoc_args, 'wave');
        
        // Execute commands.
        $args = self::get_filtered_command_input($args);
        
            foreach (array($args) as $arg) {
                switch ($arg[0]) {
                    case 'enable':
                        self::update_wpclef_option(self::PWD_OPT_WAVE, 1, 'Wp-login.php will show the Clef Wave.');
                        break;
                    case 'disable':
                        self::update_wpclef_option(self::PWD_OPT_WAVE, 0, 'Wp-login.php will show the standard WP login form.');
                        break;
                    default:
                        self::error_invalid_option('wave');
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
     * : The WordPress site_url(). Use this debugging option when the logout hook
     * URL in your Clef application is diffefent than the value of your site_url().  
     *
     * ## EXAMPLES
     * 
     *     wp clef hook http://blog.getclef.com
     *     wp clef hook --siteurl
     * 
     * @synopsis <url>
     * */
    function hook($args, $assoc_args) {
        
        //If no commands or flags are entered, exit; otherwise, execute the commands and flags.
        self::is_valid_command_input($args, $assoc_args, 'hook');
        
        // Execute commands.
        $args = self::get_filtered_command_input($args);
        $assoc_args = self::get_filtered_command_input($args);

        
        if (preg_match('/(https?):\/\/([A-Za-z0-9]+)(\.+)([A-Za-z]+)/', $args[0]) ? true : false) {

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
        } else {
            self::error_invalid_option('hook');
        }
       
    }
    
    /**
     * Configure an override URL that allows password-based logins via a secret URL.
     * 
     * ## OPTIONS
     * 
     * [--info]
     * : Display your current override URL. 
     *
     * [<create>]
     * : Create a new override URL or overwrite existing URL. 
     *
     * [--key=<your_key>]
     * : Create a custom key for your override URL. E.g., http://example.com?override=your_key.
     *
     * [--email=<me|address>]
     * : Send the override URL to an email address. Enter <me> to send it to your own WP user's email address. 
     * Enter a custom <address> (e.g., jane@doe.com) to send it anywhere else.
     *
     * [<delete>]
     * : Delete your existing override URL.
     * 
     * ## EXAMPLES
     * 
     *     wp clef override create
     *     wp clef override create --key=my_secret_key
     *     wp clef override --email=me
     *     wp clef override --email=jane@doe.com
     *     wp clef override delete
     *
     * @synopsis [--info] [<create>] [--key=<your_key>] [--email=<me|address>] [<delete>]
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