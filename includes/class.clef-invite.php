<?php

class ClefInvite {
    public $code;
    public $created_at;
    private $user_email;

    function __construct($user, $is_network_admin=false) {
        $this->code = md5(uniqid(mt_rand(), true));
        $this->user_email = $user->user_email;
        $this->user_id = $user->ID;
        $this->created_at = time();

        $sites = get_blogs_of_user($user->ID);
        if ($is_network_admin || count($sites) == 0) {
            $site = array_shift($sites);
            switch_to_blog($site->userblog_id);
            $this->site_name = get_bloginfo('name');
            $this->login_url = wp_login_url();
            restore_current_blog();
        } else {
            $this->site_name = get_bloginfo('name');
            $this->login_url = wp_login_url();
        }
    }

    function persist() {
        update_user_meta($this->user_id, 'clef_invite_code', $this);
    }

    function get_link() {
        return ($this->login_url .
            '?clef_invite_code=' . $this->code .
            '&clef_invite_id=' . urlencode(base64_encode($this->user_email)));
    }

    function send_email() {
        if (empty($this->user_email)) return true;

        $subject = '['. $this->site_name . '] ' . __('Set up Clef for your account', "wpclef");
        $template = 'invite_email.tpl';
        $vars = array(
            "invite_link" =>  $this->get_link(),
            "site_name" => $this->site_name
        );

        return ClefUtils::send_email(
            $this->user_email,
            $subject,
            $template,
            $vars
        );
    }

    public static function invite_users($users, $is_network_admin) {
        $errors = array();
        foreach ($users as &$user) {
            if (!ClefUtils::user_has_clef($user)) {
                $invite = new ClefInvite($user, $is_network_admin);
                $invite->persist();
                $success = $invite->send_email();
                if (!$success) {
                    $errors[] = $user->user_email;
                }
            }
        }

        if (count($errors) > 0) {
            if (count($errors) == count($filtered_users)) {
                $message = __("there was an error sending the invite email to all users. Copy and paste the preview email to your users and they'll be walked through a tutorial to connect with Clef", 'wpclef');
            } else {
                $message = __("unable to send emails to the following users: ", 'wpclef');
                $message .= join(", ", $errors);
                $message .= __(". Copy and paste the preview email to your users and they'll be walked through a tutorial to connect with Clef", 'wpclef');
            }
            throw new Exception($message);
        } else {
            return true;
        }
    }
}

?>
