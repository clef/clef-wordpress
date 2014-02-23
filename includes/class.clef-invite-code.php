<?php

class InviteCode {
    public $code;
    public $created_at;
    private $user_email;
    function __construct($user) {
        $this->code = md5(uniqid(mt_rand(), true));
        $this->user_email = $user->user_email;
        $this->created_at = time();
    }

    function get_link() {
        return (wp_login_url() . 
            '?clef_invite_code=' . $this->code . 
            '&clef_invite_id=' . urlencode(base64_encode($this->user_email))); 
    }
}

?>
