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
    
    protected function toggle_passwords($cmd_name, $arg, $option, $role) {
        if ($arg == 'disable') {
            return self::update_wpclef_option($option, 1, "Passwords are now disabled for $role.");
        } elseif (($arg == 'enable')) {
            return self::update_wpclef_option($option, 0, "Passwords are now enabled for $role.");
        }
    }
    
    protected function toggle_passwords_api($cmd_name, $arg, $option, $role) {
        if ($arg == 'disable') {
            return self::update_wpclef_option($option, 0, "Passwords are now disabled for $role.");
        } elseif (($arg == 'enable')) {
            return self::update_wpclef_option($option, 1, "Passwords are now enabled for $role.");
        }
    }
    
    function toggle_passwords_wprole($cmd_name, $arg, $role, $wprole) {
        if ($arg == 'disable') {
            $value = $wprole;
            return self::update_wpclef_option($role, $value, "Passwords are disabled for WP roles >= $wprole.");
        } elseif (($arg == 'enable')) {
            $value = ''; // reset the SELECT box to null, which enables passwords for all WP roles
            return self::update_wpclef_option($role, $value, "Passwords are enabled for the $wprole role.");
        }
    }
    
    protected function toggle_wave($cmd_name, $arg) {
        if ($arg == 'disable') {
            return self::update_wpclef_option(self::PWD_OPT_WAVE, 0, 'Wp-login.php will show the standard WP login form.');
        } elseif (($arg == 'enable')) {
            return self::update_wpclef_option(self::PWD_OPT_WAVE, 1, 'Wp-login.php will show the Clef Wave.');
        }
    }
    
    protected function reset_pass_settings() {
        WP_CLI::confirm('Are you sure you want to reset your password settings to their fresh-install default values?');
            self::update_wpclef_option(self::PWD_OPT_ALL, 0);
            self::update_wpclef_option(self::PWD_OPT_CLEF, 1);
            self::update_wpclef_option(self::PWD_OPT_WP, '');    
            self::update_wpclef_option(self::PWD_OPT_API, 0);
            self::update_wpclef_option(PWD_OPT_WAVE, 1);

            return WP_CLI::success('Clef’s password settings have been reset to their fresh-install default values.');
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
    
    protected function get_pass_option_value($option) {
        $current_value = $this->wpclef_opts[$option];
        return $current_value;
    }
    
    protected function get_all_pass_option_info() {
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
        
        // construct table
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
        
        // Show Clef Wave info
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
            self::get_all_pass_option_info();
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
                    self::toggle_passwords($cmd_name, $arg[0], self::PWD_OPT_CLEF, $role);
                    break;
                case 'all':
                    $role = ucwords($arg[1]) .' users';
                    self::toggle_passwords($cmd_name, $arg[0], self::PWD_OPT_ALL, $role);
                    break;
                case 'api':
                    $role = 'the WP API';
                    self::toggle_passwords_api($cmd_name, $arg[0], self::PWD_OPT_API, $role);
                    break;
                case 'subscriber':
                case 'contributor':
                case 'author':
                case 'editor':
                    $wprole = ucwords($arg[1]);
                    self::toggle_passwords_wprole($cmd_name, $arg[0], self::PWD_OPT_WP, $wprole);
                    break;
                case 'admin':
                    $wprole = 'Administrator';
                    self::toggle_passwords_wprole($cmd_name, $arg[0], self::PWD_OPT_WP, $wprole);
                    break;
                case 'superadmin':
                    $wprole = 'Super Administrator';
                    self::toggle_passwords_wprole($cmd_name, $arg[0], self::PWD_OPT_WP, $wprole);
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
                return self::get_option_info(self::PWD_OPT_WAVE);
                break;
            case 'enable':
            case 'disable':
                self::toggle_wave($cmd_name, $args[0]);
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
    
    /**
     * Prints a table.
     * 
     * ## OPTIONS
     * 
     * [<name>]
     * : The name of the person to greet.
     * 
     * ## EXAMPLES
     * 
     *     wp example hello Newman
     *
     * @synopsis [<name>]
     */
    function table( $args, $assoc_args ) {
        
        $headers = array('First Name', 'Last Name', 'City', 'State');
        $data = array(
            array('Maryam',   'Elliott',    'Elizabeth City',   'SD'),
            array('Jerry',    'Washington', 'Bessemer',         'ME'),
            array('Allegra',  'Hopkins',    'Altoona',          'ME'),
            array('Audrey',   'Oneil',      'Dalton',           'SK'),
            array('Ruth',     'Mcpherson',  'San Francisco',    'ID'),
            array('Odessa',   'Tate',       'Chattanooga',      'FL'),
            array('Violet',   'Nielsen',    'Valdosta',         'AB'),
            array('Summer',   'Rollins',    'Revere',           'SK'),
            array('Mufutau',  'Bowers',     'Scottsbluff',      'WI'),
            array('Grace',    'Rosario',    'Garden Grove',     'KY'),
            array('Amanda',   'Berry',      'La Habra',         'AZ'),
            array('Cassady',  'York',       'Fulton',           'BC'),
            array('Heather',  'Terrell',    'Statesboro',       'SC'),
            array('Dominic',  'Jimenez',    'West Valley City', 'ME'),
            array('Rhonda',   'Potter',     'Racine',           'BC'),
            array('Nathan',   'Velazquez',  'Cedarburg',        'BC'),
            array('Richard',  'Fletcher',   'Corpus Christi',   'BC'),
            array('Cheyenne', 'Rios',       'Broken Arrow',     'VA'),
            array('Velma',    'Clemons',    'Helena',           'IL'),
            array('Samuel',   'Berry',      'Lawrenceville',    'NU'),
            array('Marcia',   'Swanson',    'Fontana',          'QC'),
            array('Zachary',  'Silva',      'Port Washington',  'MB'),
            array('Hilary',   'Chambers',   'Suffolk',          'HI'),
            array('Idola',    'Carroll',    'West Sacramento',  'QC'),
            array('Kirestin', 'Stephens',   'Fitchburg',        'AB'),
        );
        $table = new \cli\Table();
        $table->setHeaders($headers);
        $table->setRows($data);
        $table->display();
        
        
    }
}

WP_CLI::add_command('clef', 'Clef_WPCLI_Command');
?>