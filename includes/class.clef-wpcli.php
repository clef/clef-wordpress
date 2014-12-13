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
     * <name>|<title>
     * : The name of the person to greet.
     * 
     * ## EXAMPLES
     * 
     *     wp example hello Newman
     *
     * @synopsis <name>
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
     * (1) disable
     * (2) allow
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
     * : Display wpclef’s current password settings.
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

        //If no commands or flags are entered, exit; otherwise, filter input and execute the commands and flags.
        self::is_valid_command_input($args, $assoc_args, 'passwords');
        
        $args = self::get_filtered_command_input($args);
        $assoc_args = self::get_filtered_command_input($assoc_args);
        
        // Execute commands. The order of the positional arguments: $args[0] = <action>; $args[1] = <role>.
        foreach (array($args) as $arg) {
            switch ($arg[1]) {
                case 'clef':
                    if ($arg[0] == 'disable') {
                        self::update_wpclef_option('clef_password_settings_disable_passwords', 1, 'Passwords are disabled for Clef users.');
                    } elseif (($arg[0] == 'enable')) {
                        self::update_wpclef_option('clef_password_settings_disable_passwords', 0, 'Passwords are enabled for Clef users.');
                    } else {
                        self::error_invalid_option('passwords');
                    }
                    break;
                case 'none':
                    self::update_wpclef_option('clef_password_settings_disable_certain_passwords', '', 'Passwords are NOT disabled for any WP roles.');
                    break;
                case 'subscriber':
                    if ($arg[0] == 'disable') {
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', 'Subscriber', 'Passwords are disabled for subscriber and higher roles.');
                    } elseif (($arg[0] == 'enable')) {
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', '', 'Passwords are enabled for all standard WP roles.');
                    } else {
                        self::error_invalid_option('passwords');
                    }
                    break;
                case 'contributor':
                    if ($arg[0] == 'disable') {
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', 'Contributor', 'Passwords are disabled for contributor and higher roles.');
                    } elseif (($arg[0] == 'enable')) {
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', '', 'Passwords are enabled for all standard WP roles.');
                    } else {
                        self::error_invalid_option('passwords');
                    }
                    break;
                case 'author':
                    if ($arg[0] == 'disable') {
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', 'Author', 'Passwords are disabled for author and higher roles.');
                    } elseif (($arg[0] == 'enable')) {
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', '', 'Passwords are enabled for all standard WP roles.');
                    } else {
                        self::error_invalid_option('passwords');
                    }
                    break;
                case 'editor':
                    if ($arg[0] == 'disable') {
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', 'Editor', 'Passwords are disabled for editor and higher roles.');
                    } elseif (($arg[0] == 'enable')) {
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', '', 'Passwords are enabled for all standard WP roles.');
                    } else {
                        self::error_invalid_option('passwords');
                    }
                    break;
                case 'admin':
                    if ($arg[0] == 'disable') {
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', 'Administrator', 'Passwords are disabled for administrator and higher roles.');
                    } elseif (($arg[0] == 'enable')) {
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', '', 'Passwords are enabled for all standard WP roles.');
                    } else {
                        self::error_invalid_option('passwords');
                    }
                    break;
                case 'superadmin':
                    if ($arg[0] == 'disable') {
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', 'Super Administrator', 'Passwords are disabled for super administrator and higher roles.');
                    } elseif (($arg[0] == 'enable')) {
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', '', 'Passwords are enabled for all standard WP roles.');
                    } else {
                        self::error_invalid_option('passwords');
                    }
                    break;
                case 'all': 
                    if ($arg[0] == 'disable') {
                        self::update_wpclef_option('clef_password_settings_force', 1, 'Passwords are disabled for all WP users.');
                    } elseif (($arg[0] == 'enable')) {
                        self::update_wpclef_option('clef_password_settings_force', '', 'Passwords are enabled for all WP users.');
                    } else {
                        self::error_invalid_option('passwords');
                    }
                    break;
                case 'api': 
                    if ($arg[0] == 'disable') {
                        self::update_wpclef_option('clef_password_settings_xml_allowed', 0, 'Passwords are disabled for the WP API.');
                    } elseif (($arg[0] == 'enable')) {
                        self::update_wpclef_option('clef_password_settings_xml_allowed', 1, 'Passwords are enabled for the WP API.');
                    } else {
                        self::error_invalid_option('passwords');
                    }    
                break;
                case 'reset':
                    // If confirm = true, reset the settings.
                    WP_CLI::confirm('Are you sure you want to reset your password settings to their fresh-install default values?');
                        self::update_wpclef_option('clef_password_settings_force', 0);
                        self::update_wpclef_option('clef_password_settings_disable_passwords', 1);
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', '');    
                        self::update_wpclef_option('clef_password_settings_xml_allowed', 0);
                        self::update_wpclef_option('clef_form_settings_embed_clef', 1);
                        WP_CLI::success('Clef’s password settings have been reset to their fresh-install default values.');
                    break;
                case 'info':
                    if ($this->wpclef_opts['clef_password_settings_force'] == 1) {
                        WP_CLI::line('Disable passwords for Clef users:');
                    }
                    break;
                default:
                    self::error_invalid_option('pass');
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
                        self::update_wpclef_option('clef_form_settings_embed_clef', 1, 'Wp-login.php will show the Clef Wave.');
                        break;
                    case 'disable':
                        self::update_wpclef_option('clef_form_settings_embed_clef', 0, 'Wp-login.php will show the standard WP login form.');
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
     * [<url>]
     * : Manually enter the logout hook URL setting from the Clef application that
     * is connected to your WP site. You will find this URL in your
     * getclef.com/user dashboard.
     *
     *[<siteurl>]
     * : The WordPress site_url(). Use this debugging option when the logout hook
     * URL in your Clef application is diffefent than the value of your site_url().  
     *
     * ## EXAMPLES
     * 
     *     wp clef hook http://blog.getclef.com
     *     wp clef hook siteurl
     *
     * @synopsis [<url>] [<siteurl>]
     */
    function hook($args, $assoc_args) {
        
       //If no commands or flags are entered, exit; otherwise, execute the commands and flags.
        self::is_valid_command_input($args, $assoc_args, 'hook');
        
        // Execute commands.
        if ($commands = self::get_filtered_command_input($args)) {
        
            foreach ($commands as $command) {
                switch ($command) {
                    // user enters logout hook url manually
                    case (preg_match('/(https?):\/\/([A-Za-z0-9]+)(\.+)([A-Za-z]+)/', $command) ? true : false):
                        
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
                        break;
                    
                    // user tests with WP's site_url() 
                    case 'siteurl':

                        $hook_url = site_url();
                        if (preg_match('/localhost/', $hook_url)) {
                            WP_CLI::error('Clef’s logout hook server cannot ping local servers that are not connected to the internet (e.g., http://localhost).');
                        }

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $hook_url);
                        curl_setopt($ch, CURLOPT_USERAGENT, 'Clef/1.0 (https://getclef.com)');
                        curl_setopt($ch, CURLOPT_POSTFIELDS, 'logout_token=1234567890');
                        curl_exec($ch);
                        curl_close($ch);
                    
                        WP_CLI::line('');
                        break;
                    default:
                        self::error_invalid_option('hook');
                        break;
                }
            }
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