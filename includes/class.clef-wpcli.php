<?php

/**
 * Define globals.
 */
$wpclef_opts = get_option('wpclef');


/**
 * Configure wpclef from the command line.
 */
class Clef_Password_Command extends WP_CLI_Command {

    /**
     * Disable passwords for select WP roles; show the Clef Wave or the default login form on wp-login.php.
     * 
     * ## OPTIONS
     * 
     * [<all>] [<clef>] [<subscriber>] [<contributor>] [<author>] [<editor>] [<admin>] [superadmin>] [--allow-api=<yes/no>] [--show-wave=<yes/no>] [<reset>]
     * : The name of the user role for which you wish to disable passwords.
     * 1. Highest security: <all>
     * 2. Higher security: select one of the standard roles
     *    (e.g., <contributor>)
     * 3. High security: <clef>
     *
     * The <clef> role is all WP users who have connected their Clef mobile apps to their WP users regardless of their WP role.
     *
     * You can select more than one role at a time. It only makes sense to do this with <clef> plus a standard WP role like <author> or <editor>, etc. Use the WP Dashboard to select custom WP roles.
     * 
     * [--allow-api=<yes/no>]
     * : Whether to allow password logins via the WP API (including XML-RPC).
     *  Default: no.
     *
     * [--show-wave=<yes/no>]
     * : Whether to show the Clef Wave as the primary option on wp-login.php.
     *  Default: yes.
     *
     * * [<reset>] 
     * : Returns the disable password settings to their post-installation defaults.
     *
     * ## EXAMPLES
     * 
     *     wp clef disable all
     *     wp clef disable clef author
     *     wp clef disable --allow-api=yes
     *     wp clef disable --show-wave=no
     *
     * @synopsis [<role>] [<other-role>] [--allow-api=<yes/no>] [--show-wave=<yes/no>]
     */
    function disable($args, $assoc_args) {
        
        global $wpclef_opts;

        // If options for 'disable' are entered, run the commands.
        if (!empty($args)) {
        
            $args = array_map('strtolower', $args);
            
            foreach ($args as $arg) {
                switch ($arg) {
                    case 'all':
                        $wpclef_opts['clef_password_settings_force'] = 1;
                        update_option('wpclef', $wpclef_opts);
                        WP_CLI::success("Passwords are disabled for all users.");
                        break;
                    case 'clef':
                        $wpclef_opts['clef_password_settings_disable_passwords'] = 1;
                        update_option('wpclef', $wpclef_opts);
                        WP_CLI::success("Passwords are disabled for Clef users.");
                        break;
                    case 'subscriber':   
                        $wpclef_opts['clef_password_settings_disable_certain_passwords'] = 'Subscriber';
                        update_option('wpclef', $wpclef_opts);
                        WP_CLI::success("Passwords are disabled subscriber and higher roles.");
                        break;
                    case 'contributor':
                        $wpclef_opts['clef_password_settings_disable_certain_passwords'] = 'Contributor';
                        update_option('wpclef', $wpclef_opts);
                        WP_CLI::success("Passwords are disabled for contributor and higher.");
                        break;
                    case 'author':
                        $wpclef_opts['clef_password_settings_disable_certain_passwords'] = 'Author';
                        update_option('wpclef', $wpclef_opts);
                        WP_CLI::success("Passwords are disabled for author and higher roles. (Clef is no longer protecting your site!)");
                        break;
                    case 'editor':
                        $wpclef_opts['clef_password_settings_disable_certain_passwords'] = 'Editor';
                        update_option('wpclef', $wpclef_opts);
                        WP_CLI::success("Passwords are disabled for editor and higher roles");
                        break;
                    case 'admin':
                        $wpclef_opts['clef_password_settings_disable_certain_passwords'] = 'Administrator';
                        update_option('wpclef', $wpclef_opts);
                        WP_CLI::success("Passwords are disabled for administrator and higher roles.");
                        break;
                    case 'superadmin':
                        $wpclef_opts['clef_password_settings_disable_certain_passwords'] = 'Super Administrator';
                        update_option('wpclef', $wpclef_opts);
                        WP_CLI::success('Passwords are disabled for super administrator and higher roles.');
                        break;
                    case 'reset':
                        // If confirm = true, reset the settings.
                        WP_CLI::confirm('Are you sure you want to reset your password settings to their default values?');
                            $wpclef_opts['clef_password_settings_force'] = 0;
                            $wpclef_opts['clef_password_settings_disable_passwords'] = 1;
                            $wpclef_opts['clef_password_settings_disable_certain_passwords'] = '';    
                            $wpclef_opts['clef_password_settings_xml_allowed'] = 0;
                            $wpclef_opts['clef_form_settings_embed_clef'] = 1;
                            update_option('wpclef', $wpclef_opts);
                            WP_CLI::success('Disable password settings have been reset to their default values.');
                        break;
                    default:
                        WP_CLI::error("Please enter a valid option for the 'disable' command. For help use 'wp help clef disable'.");
                    break;
                }
            }
        }
        
        // If flags are entered, run the commands.
        if (!empty($assoc_args)) {
            
            $assoc_args = array_map('strtolower', $assoc_args);
            
            foreach ($assoc_args as $key => $value) {
                switch ($key) {
                    case 'allow-api':
                        if ($value == 'yes') { 
                            $wpclef_opts['clef_password_settings_xml_allowed'] = 1;
                            update_option('wpclef', $wpclef_opts);
                            WP_CLI::success("Passwords are allowed for the WP API.");
                        } elseif ($value == 'no') { 
                            $wpclef_opts['clef_password_settings_xml_allowed'] = 0;
                            update_option('wpclef', $wpclef_opts);
                            WP_CLI::success('Passwords are disabled for the WP API.');
                        } else {
                            WP_CLI::error("Please enter 'yes' or 'no' for --allow-api=");
                        }
                        break;
                    case 'show-wave':
                        if ($value == 'yes') { 
                            $wpclef_opts['clef_form_settings_embed_clef'] = 1;
                            update_option('wpclef', $wpclef_opts);
                            WP_CLI::success('The Clef Wave will show as primary login option on wp-login.php.');
                        } elseif ($value == 'no') { 
                            $wpclef_opts['clef_form_settings_embed_clef'] = 0;
                            update_option('wpclef', $wpclef_opts);
                            WP_CLI::success('Wp-login.php will show the default login form.');
                        } else {
                            WP_CLI::error("Please enter 'yes' or 'no' for --show-wave=");
                        }
                        break;
                }
            }
        }
            
    }
}

WP_CLI::add_command('clef', 'Clef_Password_Command');?>