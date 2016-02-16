<?php
require_once(CLEF_PATH . 'includes/class.clef-settings.php');
require_once(CLEF_PATH . 'includes/class.clef-invite.php');

class ClefAdmin {
    const FORM_ID = "clef";
    const CONNECT_CLEF_ID_ACTION = "connect_clef_account_clef_id";
    const INVITE_USERS_ACTION = "clef_invite_users";

    const CONNECT_CLEF_PAGE = "connect_clef_account";

    const HIDE_USER_SETUP_BADGE = 'clef_hide_user_setup_badge';

    private static $instance = null;
    private static $affiliates = array('siteground');


    protected $settings;

    protected function __construct($settings) {
        $this->settings = $settings;
        $this->initialize_hooks();

        if (is_admin()) {
            require_once(CLEF_PATH . "/includes/lib/ajax-settings/ajax-settings.php");
            $this->ajax_settings = AjaxSettings::start(array(
                "options_name" => CLEF_OPTIONS_NAME,
                "initialize" => false,
                "base_url" => CLEF_URL . "includes/lib/ajax-settings/",
                "formSelector" => "#clef-form"
            ));
        }
    }

    public function initialize_hooks() {
        add_action('admin_init', array($this, "setup_plugin"));
        add_action('admin_init', array($this, "settings_form"));
        add_action('admin_init', array($this, "multisite_settings_edit"));
        add_action('admin_init', array($this, 'clef_hook_onboarding')); // Display the badge message, if appropriate
        add_action('clef_hook_admin_menu', array($this, "hook_admin_menu"));
        add_filter('clef_add_affiliate', array($this, "add_affiliates"));
        add_action('admin_enqueue_scripts', array($this, "admin_enqueue_scripts"));
        add_action('admin_footer-users.php', array($this, "add_invite_bulk_action_to_dropdown"));
        add_action('admin_action_invite_to_clef', array($this, "handle_invite_bulk_action"));
        add_action('admin_notices', array($this, 'handle_invite_bulk_action_admin_notices') );
        add_action('admin_notices', array($this, 'display_messages') );
        add_action('clef_onboarding_after_first_login', array($this, 'disable_passwords_for_clef_users'));
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));

        add_filter( 'plugin_action_links_'.plugin_basename( CLEF_PATH.'wpclef.php' ), array($this, 'clef_settings_action_links' ) );
        global $clef_ajax;
        $clef_ajax->add_action(self::CONNECT_CLEF_ID_ACTION, array($this, 'ajax_connect_clef_account_with_clef_id'));
        $clef_ajax->add_action(self::INVITE_USERS_ACTION, array($this, 'ajax_invite_users'));
    }

    public function clef_hook_onboarding() {
        do_action('clef_hook_onboarding');
    }

    public function hook_admin_menu() {
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    public function display_messages() {
        settings_errors( CLEF_OPTIONS_NAME );
    }

    public function admin_enqueue_scripts($hook) {
        $exploded_path = explode('/', $hook);
        $settings_page_name = array_shift($exploded_path);

        // only register clef logout if user is a clef user
        if (ClefUtils::user_has_clef()) {
            $ident = ClefUtils::register_script('clef_heartbeat');
            wp_enqueue_script($ident);
        }

        $ident = ClefUtils::register_style('admin');
        wp_enqueue_style($ident);

        if(preg_match("/".$this->settings->settings_path."/", $settings_page_name)) {
            wp_enqueue_media();
            $ident = ClefUtils::register_script(
                'settings',
                array('jquery', 'backbone', 'underscore', $this->ajax_settings->identifier())
            );
            wp_enqueue_script($ident);
        }
    }

    /**
     * @return array Users filtered by >= $role
     */
    protected function filter_users_by_role($users, $role) {
        $filtered_users = array();
        if ($role === 'everyone')  {
            $filtered_users = $users;
        }
        else {
            foreach ($users as $user) {
                if (ClefUtils::user_fulfills_role($user, $role)) {
                    $filtered_users[] = $user;
                }
            }
        }
        return $filtered_users;
    }

    public function admin_menu() {
        if (ClefUtils::isset_REQUEST('page') === $this->settings->connect_path &&
          $this->get_menu_badge() === _('add security')) {
            update_user_meta(get_current_user_id(), self::HIDE_USER_SETUP_BADGE, true);
        }

        if ($this->settings->multisite_disallow_settings_override()) {
            if ($this->settings->is_configured()) {
                // if the single site override of settings is not allowed
                // let's add a menu page that only lets a user connect
                // their clef account
                add_menu_page(
                    __("Clef", 'wpclef'),
                    __("Clef", 'wpclef'),
                    "read",
                    $this->settings->settings_path,
                    array($this, 'render_clef_user_settings'),
                    CLEF_URL . 'assets/dist/img/gradient_icon_16.png'
                );
            }
            return;
        }

        $clef_menu_title = $this->get_clef_menu_title();
        $menu_name = $this->settings->settings_path;
        add_menu_page(
            __("Clef", 'wpclef'),
            $clef_menu_title,
            "manage_options",
            $menu_name,
            array($this, 'general_settings'),
            CLEF_URL . 'assets/dist/img/gradient_icon_16.png'
        );

        if ($this->settings->is_configured()) {
            if (ClefUtils::user_has_clef()) $name = __('Disconnect Clef account', 'wpclef');
            else $name = __('Connect Clef account', 'wpclef');
            add_submenu_page(
                $menu_name,
                $name,
                $name,
                'read',
                self::CONNECT_CLEF_PAGE,
                array($this, 'render_clef_user_settings')
            );
        }
    }


	public function clef_settings_action_links( $links ) {

	    array_unshift( $links, '<a href="' . add_query_arg( array( 'page' => $this->settings->settings_path ), admin_url( 'admin.php' ) ) . '">' . __( 'Settings' ) . '</a>' );

	    return $links;
	}

    /**
     * Determines whether to badge the Clef menu icon.
     *
     * @return string The title of the menu with or without a badge
     */
    public function get_clef_menu_title() {
        $clef_menu_title = __('Clef', 'wpclef');

        if ($badge = $this->get_menu_badge()) {
            $clef_menu_title .= $this->render_badge($badge);
        }

        return $clef_menu_title;
    }

    public function get_menu_badge() {
        $user_is_admin = current_user_can('manage_options');
        $needs_setup_badge = ($user_is_admin && !$this->settings->is_configured());
        if ($needs_setup_badge) return _('needs setup');

        $has_seen_user_setup_badge = get_user_meta(get_current_user_id(), self::HIDE_USER_SETUP_BADGE, true);
        $needs_connect_badge = !$user_is_admin && $this->settings->is_configured() && !ClefUtils::user_has_clef() && !$has_seen_user_setup_badge;
        if ($needs_connect_badge) return _('add security');

        return false;
    }

    public function render_clef_user_settings() {
        do_action('clef_render_user_settings');
    }

    public function render_badge($count) {
        return " <span class='update-plugins count-1'><span class='update-count'>" . $count . "</span></span>";
    }

    public function general_settings($options = false) {
        $form = ClefSettings::forID(self::FORM_ID, CLEF_OPTIONS_NAME, $this->settings);

        if (!$options) {
            $options = $this->settings->get_site_option();
        }

        $options = array_merge(array(
            'setup' => array(
                'siteName' => get_option('blogname'),
                'siteDomain' => get_option('siteurl'),
                'logoutHook' => wp_login_url(),
                'source' => 'wordpress',
                'affiliates' => apply_filters('clef_add_affiliate', array())
            ),
            'nonces' => array(
                'connectClef' => wp_create_nonce(self::CONNECT_CLEF_ID_ACTION),
                'inviteUsers' => wp_create_nonce(self::INVITE_USERS_ACTION),
                'getProServices' => wp_create_nonce(ClefPro::GET_PRO_SERVICES_ACTION)
            ),
            'configured' => $this->settings->is_configured(),
            'clefBase' => CLEF_BASE,
            'optionsName' => CLEF_OPTIONS_NAME,
            'settingsPath' => $this->settings->settings_path,
            'isMultisite' => is_multisite(),
            'isNetworkSettings' => false,
            'isNetworkSettingsEnabled' => $this->settings->network_settings_enabled(),
            'isSingleSiteSettingsAllowed' => $this->settings->single_site_settings_allowed(),
            'isUsingIndividualSettings' => $this->settings->use_individual_settings,
            'connectClefUrl' => admin_url('admin.php?page=' . ClefAdmin::CONNECT_CLEF_PAGE)
        ), $options);

        echo ClefUtils::render_template('admin/settings.tpl', array(
            "form" => $form,
            "options" => $options
        ));
    }

    public function add_affiliates($affiliates) {
        if (get_site_option("bruteprotect_installed_clef")) {
            array_push($affiliates, "bruteprotect");
        }

        $theme = wp_get_theme();
        if ($theme && strtolower($theme->name) == "responsive") {
            array_push($affiliates, "responsive");
        }

        $saved_affiliates = $this->settings->get_saved_affiliates();
        if ($saved_affiliates && count($saved_affiliates) > 0) {
            $affiliates = array_unique(array_merge($affiliates, $saved_affiliates));
        }

        return $affiliates;
    }

    public function multisite_settings() {
        echo ClefUtils::render_template('admin/multisite-disabled.tpl');
    }

    public function settings_form() {
        $form = ClefSettings::forID(self::FORM_ID, CLEF_OPTIONS_NAME, $this->settings);


        $settings = $form->addSection('clef_settings', __('API Settings', 'wpclef'));
        $settings->addField('app_id', __('Application ID', "wpclef"), Settings_API_Util_Field::TYPE_TEXTFIELD);
        $settings->addField('app_secret', __('Application Secret', "wpclef"), Settings_API_Util_Field::TYPE_TEXTFIELD);
        $settings->addField('register', __('Register with Clef', 'wpclef'), Settings_API_Util_Field::TYPE_CHECKBOX);

        $pw_settings = $form->addSection('clef_password_settings', __('Password Settings', 'wpclef'), '');
        $pw_settings->addField('disable_passwords', __('Disable passwords for Clef users', "wpclef"), Settings_API_Util_Field::TYPE_CHECKBOX);
        $pw_settings->addField(
            'disable_certain_passwords',
            __('Disable certain passwords', "wpclef"),
            Settings_API_Util_Field::TYPE_SELECT,
            "Disabled",
            array( "options" => array_merge(array(""), ClefUtils::$default_roles))
        );

        $custom_roles = ClefUtils::get_custom_roles();
        if (count($custom_roles) > 0) {
            $pw_settings->custom_roles = $custom_roles;
            foreach ($custom_roles as $role => $role_obj) {
                $pw_settings->addField(
                    "disable_passwords_custom_role_$role",
                    $role_obj['name'],
                    Settings_API_Util_Field::TYPE_CHECKBOX
                );
            }
        }

        $pw_settings->addField('force', __('Disable all passwords', "wpclef"), Settings_API_Util_Field::TYPE_CHECKBOX);
        $pw_settings->addField(
            'xml_allowed',
            __('Allow XML', 'wpclef'),
            Settings_API_Util_Field::TYPE_CHECKBOX
        );

        $form_settings = $form->addSection('clef_form_settings', __('Form settings', 'wpclef'), '');
        $form_settings->addField('embed_clef', __('Embed Clef wave in the login form', 'wpclef'), Settings_API_Util_Field::TYPE_CHECKBOX);

        $override_settings = $form->addSection('clef_override_settings', __('Override Settings', 'wpclef'));
        $override_settings->addField('key', __("Override key", "wpclef"), Settings_API_Util_Field::TYPE_TEXTFIELD);

        $support_clef_settings = $form->addSection('support_clef', __('Support Clef', "wpclef"));
        $support_clef_settings->addField(
            'badge',
            __("Support Clef by automatically adding a link!", "wpclef"),
            Settings_API_Util_Field::TYPE_SELECT,
            "disabled",
            array("options" => array(array(__("Badge", "wpclef"), "badge") , array(__("Link", "wpclef"), "link"), array(__("Disabled", "wpclef"), "disabled")))
        );

        $invite_users_settings = $form->addSection('invite_users', __('Invite Users', "wpclef"));

        $pro = ClefPro::start();
        $pro->add_settings($form);

        return $form;
    }

    public function multisite_settings_edit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
            ClefUtils::isset_GET('page') == 'clef' &&
            ClefUtils::isset_GET('action') == 'clef_multisite' &&
            !is_network_admin()
        ) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'clef_multisite')) {
                die(__("Security check; nonce failed.", "wpclef"));
            }

            $override = get_option(ClefInternalSettings::MS_OVERRIDE_OPTION);

            if (!add_option(ClefInternalSettings::MS_OVERRIDE_OPTION, !$override)) {
                update_option(ClefInternalSettings::MS_OVERRIDE_OPTION, !$override);
            }

            wp_redirect(add_query_arg(array('page' => $this->settings->settings_path, 'updated' => 'true'), admin_url('admin.php')));
            exit();
        }
    }

    public function setup_plugin() {
        if (is_admin() && get_option("Clef_Activated")) {
            delete_option("Clef_Activated");

            if (!$this->settings->is_configured()) {
                wp_redirect(add_query_arg(array('page' => $this->settings->settings_path), admin_url('options.php')));
                exit();
            }
        }
    }

    public function affiliate_check() {
        $saved_affiliates = $this->settings->get_saved_affiliates();
        foreach (self::$affiliates as $affiliate) {
            if (in_array($affiliate, $saved_affiliates)) return true;
        }
        return false;
    }

    public function disable_passwords_for_clef_users() {
        $this->settings->disable_passwords_for_clef_users();
        $this->settings->generate_and_send_override_link(wp_get_current_user());
    }

    public function add_invite_bulk_action_to_dropdown() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('<option>').val('invite_to_clef').text('Invite to use Clef')
                    .appendTo("select[name='action'], select[name='action2']");
            });
        </script>
        <?php
    }

    public function handle_invite_bulk_action () {
        $user_is_admin = current_user_can('manage_options');
        if (isset($_REQUEST['users']) && isset($_REQUEST['_wpnonce']) && $user_is_admin) {
            check_admin_referer('bulk-users');

            $users = get_users(array("include" => $_REQUEST['users']));
            if (empty($users)) {
                return new WP_Error('no_users', __("No users were invited to use Clef.", "wpclef"));
            }

            try {
                ClefInvite::invite_users($users, false);

                $redirect_to = remove_query_arg('action', wp_get_referer());
                wp_redirect(add_query_arg('clef_invite_success', count($users), $redirect_to));
                exit();

            } catch (Exception $e) {
                add_settings_error(
                    CLEF_OPTIONS_NAME, 
                    "clef_invite_error",
                    sprintf(__("There was an error inviting users to Clef: %s", "wpclef"), $e->getMessage()),
                    "error"
                );
                unset($_GET['_wp_http_referer']);
            }

        }
    }

    public function handle_invite_bulk_action_admin_notices() {
        if (isset($_GET['clef_invite_success'])) {
            $num_invited = (int) $_GET['clef_invite_success'];
            add_settings_error(
                CLEF_OPTIONS_NAME,
                "clef_invite_success",
                sprintf(__("%d %s successfully invited to Clef.", "wpclef"), $num_invited, _n("user", "users", $num_invited)),
                "updated"
            );
        }
    }

    public function add_dashboard_widget() {
        if ($this->settings->is_configured()) return;
        if (!current_user_can('manage_options')) return;


        $name = 'clef_setup_widget';

        wp_add_dashboard_widget(
            $name,
            'Your site is Clef enabled!',
            array($this, 'render_dashboard_widget')
        );

        // Globalize the metaboxes array, this holds all the widgets for wp-admin

        global $wp_meta_boxes;

        // Get the regular dashboard widgets array
        // (which has our new widget already but at the end)

        $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

        // Backup and delete our new dashboard widget from the end of the array

        $widget_backup = array( $name => $normal_dashboard[$name] );
        unset( $normal_dashboard[$name] );

        // Merge the two arrays together so our widget is at the beginning

        $sorted_dashboard = array_merge( $widget_backup, $normal_dashboard );

        // Save the sorted array back into the original metaboxes

        $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
    }

    public function render_dashboard_widget() {
    ?>
        <p><?php _e("Clef is the best way to log in to WordPress <b>without usernames or passwords</b>. You're a few clicks away from joining more than 600,000 other WordPress sites that use Clef to make logging in safer and easier."); ?></p>
        <p><?php _e("Click below to finish setting up Clef or learn more <a href='https://getclef.com/wordpress' target='_blank'>here</a>.") ?></p>
        <a href="<?php echo admin_url('admin.php?page=clef') ?>" class="button button-primary"><?php _e("Finish setup"); ?></a>
    <?php
    }

    /**** BEGIN AJAX HANDLERS ******/

    public function ajax_invite_users() {
        $role = strtolower(ClefUtils::isset_POST('roles'));
        $is_network_admin = filter_var(ClefUtils::isset_POST('networkAdmin'), FILTER_VALIDATE_BOOLEAN);

        if (!$role) {
            return new WP_Error('invalid_role', __('invalid role', 'wpclef'));
        }

        $opts = array(
            'exclude' => array(get_current_user_id()),
            'meta_query' => array(array(
                'key' => 'clef_id',
                'value' => '',
                'compare' => 'NOT EXISTS'
            ))
        );
        # if we are on the network admin page, don't filter users by
        # blog ID.
        if ($is_network_admin) $opts['blog_id'] = false;

        $other_users = get_users($opts);
        $filtered_users = $this->filter_users_by_role($other_users, $role);

        if (empty($filtered_users)) {
            return new WP_Error('no_users', __("there are no other users without Clef with this role or greater", "wpclef"));
        }

        try {
            ClefInvite::invite_users($filtered_users, $is_network_admin);
            $num_invited = count($filtered_users);
            return array("success" => true, "message" => sprintf(__("%d %s successfully invited to Clef.", "wpclef"), $num_invited, _n("user", "users", $num_invited)));
        } catch (Exception $e) {
            return new WP_Error('clef_email_error', $e->getMessage());
        }
    }

    public function ajax_connect_clef_account_with_clef_id() {
        if (!ClefUtils::isset_POST('identifier')) {
            return new WP_Error("invalid_clef_id", __("invalid Clef ID", "wpclef"));
        }

        $result = ClefUtils::associate_clef_id($_POST["identifier"]);

        if (is_wp_error($result)) {
            return $result;
        } else {
            $session = ClefSession::start();
            $session->set('logged_in_at', time());

            return array("success" => true);
        }
    }

    /**** END AJAX HANDLERS ******/

    public static function start($settings) {
        if (!isset(self::$instance) || self::$instance === null) {
            self::$instance = new self($settings);
        }
        return self::$instance;
    }
}
