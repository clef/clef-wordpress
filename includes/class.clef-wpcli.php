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

        /**
        * If no options for 'disable' are entered, display error; otherwise, execute the commands and flags.
        */
        if (empty($args) && empty($assoc_args)) {
            
            WP_CLI::error("Please enter a valid option for 'disable'. For help use 'wp help clef disable'.");
            
        } 
        
        // commands
        if (!empty($args)) {
        
            $args = array_map('strtolower', $args);
            
            foreach ($args as $arg) {
                switch ($arg) {
                    case 'all':
                        $this->wpclef_opts['clef_password_settings_force'] = 1;
                        update_option('wpclef', $this->wpclef_opts);
                        WP_CLI::success("Passwords are disabled for all users.");
                        break;
                    case 'clef':
                        $this->wpclef_opts['clef_password_settings_disable_passwords'] = 1;
                        update_option('wpclef', $this->wpclef_opts);
                        WP_CLI::success("Passwords are disabled for Clef users.");
                        break;
                    case 'subscriber':   
                        $this->wpclef_opts['clef_password_settings_disable_certain_passwords'] = 'Subscriber';
                        update_option('wpclef', $this->wpclef_opts);
                        WP_CLI::success("Passwords are disabled subscriber and higher roles.");
                        break;
                    case 'contributor':
                        $this->wpclef_opts['clef_password_settings_disable_certain_passwords'] = 'Contributor';
                        update_option('wpclef', $this->wpclef_opts);
                        WP_CLI::success("Passwords are disabled for contributor and higher.");
                        break;
                    case 'author':
                        $this->wpclef_opts['clef_password_settings_disable_certain_passwords'] = 'Author';
                        update_option('wpclef', $this->wpclef_opts);
                        WP_CLI::success("Passwords are disabled for author and higher roles. (Clef is no longer protecting your site!)");
                        break;
                    case 'editor':
                        $this->wpclef_opts['clef_password_settings_disable_certain_passwords'] = 'Editor';
                        update_option('wpclef', $this->wpclef_opts);
                        WP_CLI::success("Passwords are disabled for editor and higher roles");
                        break;
                    case 'admin':
                        $this->wpclef_opts['clef_password_settings_disable_certain_passwords'] = 'Administrator';
                        update_option('wpclef', $this->wpclef_opts);
                        WP_CLI::success("Passwords are disabled for administrator and higher roles.");
                        break;
                    case 'superadmin':
                        $this->wpclef_opts['clef_password_settings_disable_certain_passwords'] = 'Super Administrator';
                        update_option('wpclef', $this->wpclef_opts);
                        WP_CLI::success('Passwords are disabled for super administrator and higher roles.');
                        break;
                    case 'reset':
                        // If confirm = true, reset the settings.
                        WP_CLI::confirm('Are you sure you want to reset your password settings to their default values?');
                            $this->wpclef_opts['clef_password_settings_force'] = 0;
                            $this->wpclef_opts['clef_password_settings_disable_passwords'] = 1;
                            $this->wpclef_opts['clef_password_settings_disable_certain_passwords'] = '';    
                            $this->wpclef_opts['clef_password_settings_xml_allowed'] = 0;
                            $this->wpclef_opts['clef_form_settings_embed_clef'] = 1;
                            update_option('wpclef', $this->wpclef_opts);
                            WP_CLI::success('Disable password settings have been reset to their default values.');
                        break;
                    default:
                        WP_CLI::error("Please enter a valid option for the 'disable' command. For help use 'wp help clef disable'.");
                    break;
                }
            }
        } 
        
        // flags
        if (!empty($assoc_args)) {
            
            $assoc_args = array_map('strtolower', $assoc_args);
            
            foreach ($assoc_args as $key => $value) {
                switch ($key) {
                    case 'allow-api':
                        if ($value == 'yes') { 
                            $this->wpclef_opts['clef_password_settings_xml_allowed'] = 1;
                            update_option('wpclef', $this->wpclef_opts);
                            WP_CLI::success("Passwords are allowed for the WP API.");
                            break;
                        } elseif ($value == 'no') { 
                            $this->wpclef_opts['clef_password_settings_xml_allowed'] = 0;
                            update_option('wpclef', $this->wpclef_opts);
                            WP_CLI::success('Passwords are disabled for the WP API.');
                            break;
                        } else {
                            WP_CLI::error("Please enter 'yes' or 'no' for --allow-api=");
                            break;
                        }
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

        // If options for 'disable' are entered, run the commands.
        if (!empty($args)) {
        
            $args = array_map('strtolower', $args);
            
            foreach ($args as $arg) {
                switch ($arg) {
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
                        WP_CLI::error("Please enter a valid option for the 'wave' command. For help use 'wp help clef wave'.");
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
        
        // If an url for 'hook' is entered, run the logout hook test via curl.
        if (!empty($args)) {
        
            $args = array_map('strtolower', $args);
            
            foreach ($args as $arg) {
                switch ($arg) {
                    // user enters logout hook url manually
                    case (preg_match('/(https?):\/\/([A-Za-z0-9]+)(\.+)([A-Za-z]+)/', $arg) ? true : false):
                        
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
                            WP_CLI::error('The logout hook test does not work on local test servers that are not connected to the internet (e.g., http://localhost).');
                            break;
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
                        WP_CLI::error("Please enter a valid option for the 'hook' command. For help use 'wp help clef hook'.");
                    break;
                }
            }
        }
    }
    
    /**
     * Disable passwords for select WP roles and for the WP API.
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
     * : Option to enter a custom key. E.g., http://example.com?override=your_key.
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

        /**
        * Function vars.
        */
        $this->siteurl = site_url();
        
        /**
        * Function methods.
        */
        function create_override($key) {
            if (!empty($wpclef_opts['clef_override_settings_key'])) {
                WP_CLI::confirm('Your current override URL is: '
                                .$siteurl
                                .'/override=?'
                                .$wpclef_opts['clef_override_settings_key']
                                .' Do you want to overwrite this with a new one?');
                    if (isset($key)) {
                        $wpclef_opts['clef_override_settings_key'] = urlencode($key);
                    } else {
                        $wpclef_opts['clef_override_settings_key'] = substr ( (md5(uniqid(mt_rand(), true))), 0, 15 );
                    }
                
            } else {
                if (isset($key)) {
                        $wpclef_opts['clef_override_settings_key'] = urlencode($key);
                    } else {
                        $wpclef_opts['clef_override_settings_key'] = substr ( (md5(uniqid(mt_rand(), true))), 0, 15 );
                    }
            }
            
            if (update_option('wpclef', $wpclef_opts)) {
                return $wpclef_opts['clef_override_settings_key'];
            } else {
                return false;
            }

        }
        
        function send_email($to, $url) {
            $msg = 
'Hello,

Here is your Clef override URL:

'.$url;
            if (wp_mail($to, 'Clef override URL', $msg)) {
                return true;
            }
        }
        
        function get_override_url() {
            global $wpclef_opts;
            $url = site_url();
            $url .= '/override=?';
            $url .= $wpclef_opts['clef_override_settings_key'];
            
            return $url;
        }
        
        /**
        * If no options are entered, display error; otherwise, execute the commands and flags.
        */
        if (empty($args) && empty($assoc_args)) {
            
            WP_CLI::error("Please enter a valid option for 'override'. For help use 'wp help clef override'.");
            
        } 
        
        // commands
        if (!empty($args)) {
        
            $args = array_map('strtolower', $args);
            
            foreach ($args as $arg) {
                switch ($arg) {
                    case 'create':
                        if (create_override()) {
                            WP_CLI::success('Your new override URL is: '.$this->siteurl.'/override=?'.$this->wpclef_opts['clef_override_settings_key']);
                            WP_CLI::confirm("Would you like to email yourself a copy of your override URL so you don't forget it?");
                                WP_CLI::run_command(['clef', 'override'],['email' => 'me']);
                        }
                        break;
                    case 'clef':
                        $this->wpclef_opts['clef_password_settings_disable_passwords'] = 1;
                        update_option('wpclef', $this->wpclef_opts);
                        WP_CLI::success("Passwords are disabled for Clef users.");
                        break;
                    default:
                        WP_CLI::error("Please enter a valid option for the 'override' command. For help use 'wp help clef override'.");
                    break;
                }
            }
        } 
        
        // flags
        if (!empty($assoc_args)) {
            
            $assoc_args = array_map('strtolower', $assoc_args);
            
            foreach ($assoc_args as $key => $value) {
                switch ($key) {
                    case 'info':
                        if (!empty($this->wpclef_opts['clef_override_settings_key'])) {
                            WP_CLI::line($this->siteurl.'/override=?'.$this->wpclef_opts['clef_override_settings_key']);
                        break;
                        } else {
                            WP_CLI::confirm('Your override URL is currently not enabled. Would you like to create one?');
                                WP_CLI::run_command(['clef', 'override', 'create']);
                        break;
                        }
                    case 'key':
                        if ($this->wpclef_opts['clef_override_settings_key'] = create_override($value)) {
                            WP_CLI::success('Your new override URL is: '.$this->siteurl.'/override=?'.$this->wpclef_opts['clef_override_settings_key']);
                            WP_CLI::confirm("Would you like to email yourself a copy of your override URL so you don't forget it?");
                                //***WP_CLI::run_command(['clef', 'override'],['email' => 'me']);
                        }
                        break;
                    case 'email':
                        if (strtolower($value) == 'me') {
                            $current_user = wp_get_current_user();
                            $to = $current_user->user_email;
                            $url = get_override_url();
                            
                            WP_CLI::line($url);
                            
                            if (send_email($to, $url)) {
                                WP_CLI::success('Email sent to: ' .$to);
                            } else {
                                WP_CLI::error("Email not sent!");
                            }
                            
                        } elseif (strtolower($value) == 'blah') {
                            WP_CLI::success('Email sent to: ');
                        } else {
                            WP_CLI::error("Please enter a valid option for the 'email' flag.");
                        }
                        break;
                    default:
                        WP_CLI::error("Please enter a valid option for the 'override' command. For help use 'wp help clef override'.");
                        break;
                }
            }
        }
    }
    
}

WP_CLI::add_command('clef', 'Clef_WPCLI_Command');
?>