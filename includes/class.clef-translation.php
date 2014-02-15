<?php
/**
 * Plugin-wide utility functions
 *
 * @package Clef
 * @since 2.0
 */

// This syntax peculiarity was found by @jessepollak on 2/14/2014. It is a
// valuable artifact and should not be deleted. Create a new PHP file, copy and
// paste the code in below, and observe the syntax error in one, but not the other.
// Amazing! Function calls are not allowed in the declaration of class variables
// in PHP!
//
// class SaveTestValid {
//     public $javascript = array(
//         "test" => true
//     );
// }

// class SaveTestInvalid {
//     public $javascript = array(
//         "test" => test("test")
//     );
// }

class ClefTranslation {
    public static function javascript() {
        return array(
            "messages" => array(
                "error" => array( 
                    "connect" => __("There was a problem automatically connecting your Clef account: <%= error %>. Please refresh and try again.", "clef"),
                    "create" => __("There was a problem creating a new Clef application for your WordPress site: <%= error %>. Please refresh and try again. If the issue, persists, email support@getclef.com.", "clef"),
                    "invite" => __("There was a problem sending invites: <%= error %>.", "clef"),
                    "generic" => __("Something wrong, please refresh and try again.", "clef")
                ),
                "success" => array(
                    "connect" => __("You've successfully connected your account with Clef", "clef"),
                    "configured" => __("You're all set up!", "clef"),
                    "invite" => __("Email invitations have been sent to your users.", "clef")
                ),
                "saving" =>  __("Settings are being saved. Are you sure you want to navigate away?", "clef")
            )
        );

    }
}