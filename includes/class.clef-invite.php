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

    function send_email($from_email) {
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
}

?>
