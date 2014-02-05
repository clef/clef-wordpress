<?php

class InviteCode {
    public $code;
    public $created_at;
    function __construct() {
        $this->code = md5(uniqid(mt_rand(), true));
        $this->created_at = time();
    }

    function get_link() {
        return wp_login_url() . '?clef_invite=' . $this->code; 
    }
}

?>
