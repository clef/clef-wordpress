<?php
/**
 * Configure wpclef from the command line.
 */
class Clef_WPCLI_Command extends WP_CLI_Command {

   /**
    * Define class properties.
    */
    function __construct() {
        $this->wpclef_opts = get_option('wpclef');
        $this->siteurl = site_url();
        $this->current_user = wp_get_current_user();
    }
    
   /**
    * Define utility methods.
    */
    function validate_command_input($args, $assoc_args, $command) {
        
        if (empty($args) && empty($assoc_args)) {
            self::error_invalid_option($command);
        }
    }
    
    function filter_command_input($input) {
        
        $input = array_map('strtolower', $input);
        return $input;
    }
    
    function error_invalid_option($command) {
        WP_CLI::error("Please enter a valid option for '$command'. For help, use 'wp help clef $command'.");
    }
    
    function update_wpclef_option($option, $value, $msg) {
            
        // Check whether the option is already set to the input value. If not, update the option.
        if ($this->wpclef_opts[$option] === $value) {
            WP_CLI::success($msg);
        } else {
            // Update the option.
            $this->wpclef_opts[$option] = $value;
            
            if (update_option('wpclef', $this->wpclef_opts)) {
                WP_CLI::success($msg);
            } else {
                WP_CLI::error("Unable to complete update_option() for $option.");
            }
        }
    }
    
    function create_override($key = null) {
        if (!empty($this->wpclef_opts['clef_override_settings_key'])) {
            
            $current_url = self::get_override_url();
            
            WP_CLI::confirm('Your current override URL is: ' .$current_url .' Do you want to replace it with the new one?');
                if (isset($key)) {
                    $this->wpclef_opts['clef_override_settings_key'] = urlencode($key);
                } else {
                    $this->wpclef_opts['clef_override_settings_key'] = substr ( (md5(uniqid(mt_rand(), true))), 0, 15 );
                }

        } else {
            if (isset($key)) {
                    $this->wpclef_opts['clef_override_settings_key'] = urlencode($key);
                } else {
                    $this->wpclef_opts['clef_override_settings_key'] = substr ( (md5(uniqid(mt_rand(), true))), 0, 15 );
                }
        }

        if (update_option('wpclef', $this->wpclef_opts)) {
            return $this->wpclef_opts['clef_override_settings_key'];
        } else {
            return false;
        }

    }

    function send_email($to, $url) {
        $msg = '<p>Hello,</p><p>Here is your Clef override URL:<br />'.$url;
        $subject = 'Clef override URL for ' .$this->siteurl;
        
        if (wp_mail($to, $subject, $msg)) {
            return true;
        } else {
            return false;
        }
    }

    function get_override_url() {
        $url = site_url();
        $url .= '/override=?';
        $url .= $this->wpclef_opts['clef_override_settings_key'];

        return $url;
    }
    
    
    
    
    
    
    /**
     * Disable passwords for select WP roles and for the WP API.
     * 
     * ## OPTIONS
     * 
     * [<all>]
     * : Disable passwords for all WP users. Highest security.
     *
     * [<subscriber>] [<contributor>] [<author>] [<editor>] [<admin>] [superadmin>]
     * : Disable passwords for select WP user roles. Higher security. 
     *
     * [<clef>]
     * : Disable passwords for all WP users who have connected their Clef mobile
     * apps to their WP users. High security.
     *
     * [--allow-api=<yes|no>]
     * : Yes allows password logins via the WP API (including XML-RPC).
     * No disallows them.
     *  Default: no.
     *
     * [<reset>]
     * : Return your disable password settings to their fresh-install defaults.
     *
     * You can disable more than one role at a time. However, it only makes sense to do this with the <clef> role plus a standard WP role such as <author> or <editor>, etc. Custom roles may be disabled via the WP Dashboard GUI.
     * 
     * ## EXAMPLES
     * 
     *     wp clef disable all
     *     wp clef disable clef author
     *     wp clef disable --allow-api=yes
     *
     * @synopsis [<role>] [<other-role>] [--allow-api=<yes|no>]
     */
    function disable($args, $assoc_args) {

         //If no commands or flags are entered, exit; otherwise, execute the commands and flags.
        self::validate_command_input($args, $assoc_args, 'disable');
        
        // Execute commands.
        if ($commands = self::filter_command_input($args)) {
        
            foreach ($commands as $command) {
                switch ($command) {
                    case 'all':
                        self::update_wpclef_option('clef_password_settings_force', 1, 'Passwords are disabled for all users.');
                        break;
                    case 'clef':
                        self::update_wpclef_option('clef_password_settings_disable_passwords', 1, 'Passwords are disabled for Clef users.');
                        break;
                    case 'subscriber':
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', 'Subscriber', 'Passwords are disabled subscriber and higher roles.');
                        break;
                    case 'contributor':
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', 'Contributor', 'Passwords are disabled for contributor and higher.');
                        break;
                    case 'author':
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', 'Author', 'Passwords are disabled for author and higher roles.');
                        break;
                    case 'editor':
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', 'Editor', 'Passwords are disabled for editor and higher roles');
                        break;
                    case 'admin':
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', 'Administrator', 'Passwords are disabled for administrator and higher roles.');
                        break;
                    case 'superadmin':
                        self::update_wpclef_option('clef_password_settings_disable_certain_passwords', 'Super Administrator', 'Passwords are disabled for super administrator and higher roles.');
                        break;
                    case 'reset':
                        // If confirm = true, reset the settings.
                        //WP_CLI::confirm('Are you sure you want to reset your password settings to their default values?');
                            
                    self::update_wpclef_option('clef_password_settings_force', 0, 'Passwords are disabled for all users.');
                    //self::update_wpclef_option('clef_password_settings_disable_passwords', 1, 'Passwords are disabled for Clef users.');
                            //self::update_wpclef_option('clef_password_settings_disable_certain_passwords', '', '');    
                            //self::update_wpclef_option('clef_password_settings_xml_allowed', 0, '');
                            //self::update_wpclef_option('clef_form_settings_embed_clef', 1, '');
                            WP_CLI::success('Disable password settings have been reset to their fresh-install default values.');
                        break;
                    default:
                        self::error_invalid_option('disable');
                    break;
                }
            }
        } 
        
        // Execute flags.
        if ($flags = self::filter_command_input($assoc_args)) {

            foreach ($flags as $key => $value) {
                switch ($key) {
                    case 'allow-api':
                        if ($value == 'yes') { 
                            $this->wpclef_opts['clef_password_settings_xml_allowed'] = 1;
                            update_option('wpclef', $this->wpclef_opts);
                            WP_CLI::success("Passwords are allowed for the WP API.");
                        } elseif ($value == 'no') { 
                            $this->wpclef_opts['clef_password_settings_xml_allowed'] = 0;
                            update_option('wpclef', $this->wpclef_opts);
                            WP_CLI::success('Passwords are disabled for the WP API.');
                        } else {
                            self::error_invalid_option('disable');
                        }
                        break;
                    default:
                        self::error_invalid_option('disable');
                        break;
                }
            }
        }
    }
    
    /**
     * Show the Clef Wave or the standard WP login form on wp-login.php.
     * 
     * ## OPTIONS
     * 
     * <yes|no>
     * : Yes shows the Clef Wave on wp-login.php. No shows the standard WP login form.
     * Default: yes.
     *
     * ## EXAMPLES
     * 
     *     wp clef wave yes
     *     wp clef wave no
     *
     * @synopsis <yes|no>
     */
    function wave($args, $assoc_args) {

        //If no commands or flags are entered, exit; otherwise, execute the commands and flags.
        self::validate_command_input($args, $assoc_args, 'wave');
        
        // Execute commands.
        if ($commands = self::filter_command_input($args)) {
        
            foreach ($commands as $command) {
                switch ($command) {
                    case 'yes':
                        $this->wpclef_opts['clef_form_settings_embed_clef'] = 1;
                        update_option('wpclef', $this->wpclef_opts);
                        WP_CLI::success("Wp-login.php will show the Clef Wave.");
                        break;
                    case 'no':
                        $this->wpclef_opts['clef_form_settings_embed_clef'] = 0;
                        update_option('wpclef', $this->wpclef_opts);
                        WP_CLI::success("Wp-login.php will show the standard WP login form.");
                        break;
                    default:
                        self::error_invalid_option('wave');
                    break;
                }
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
        self::validate_command_input($args, $assoc_args, 'hook');
        
        // Execute commands.
        if ($commands = self::filter_command_input($args)) {
        
            foreach ($commands as $command) {
                switch ($command) {
                    // user enters logout hook url manually
                    case (preg_match('/(https?):\/\/([A-Za-z0-9]+)(\.+)([A-Za-z]+)/', $command) ? true : false):
                        
                        // create a new cURL resource and set options
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $arg);
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
     * [<--info>]
     * : Display your current override URL. 
     *
     * [<create>]
     * : Create a new override URL or overwrite existing URL. 
     *
     * [<--key=<your_key>]
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
        self::validate_command_input($args, $assoc_args, 'override');
        
        // Execute commands.
        if ($commands = self::filter_command_input($args)) {

            foreach ($commands as $command) {
                switch ($command) {
                    case 'create':
                        if (self::create_override()) {

                            WP_CLI::success('Your new override URL is: '.$this->siteurl.'/override=?'.$this->wpclef_opts['clef_override_settings_key']);
                            WP_CLI::confirm("Would you like to email yourself a copy of your override URL so you don't forget it?");
                                WP_CLI::run_command(['clef', 'override'],['email' => 'me']);
                        }
                        break;
                    case 'delete':
                        if (!empty($this->wpclef_opts['clef_override_settings_key'])) {

                            $this->wpclef_opts['clef_override_settings_key'] = '';

                            if (update_option('wpclef', $this->wpclef_opts)) {
                                WP_CLI::success('Override URL deleted.');
                            } else {
                                WP_CLI::error('Could not delete override URL.');
                            }
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
        if ($flags = self::filter_command_input($assoc_args)) {

            foreach ($flags as $key => $value) {
                switch ($key) {
                    case 'info':
                        if (!empty($this->wpclef_opts['clef_override_settings_key'])) {
                            WP_CLI::line($this->siteurl.'/override=?'.$this->wpclef_opts['clef_override_settings_key']);
                        break;
                        } else {
                            WP_CLI::confirm('You have not yet set an override URL. Would you like to create one now?');
                                WP_CLI::run_command(['clef', 'override', 'create']);
                        break;
                        }
                    case 'key':
                        if ($this->wpclef_opts['clef_override_settings_key'] = self::create_override($value)) {
                            $msg = 'Your new override URL is: ';
                            $msg .= self::get_override_url();

                            WP_CLI::success($msg);
                            WP_CLI::confirm("Would you like to email yourself a copy of your override URL so you don't forget it?");
                                WP_CLI::run_command(['clef', 'override'],['email' => 'me']);
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