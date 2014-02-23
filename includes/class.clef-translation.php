<?php
/**
 * Plugin-wide utility functions
 *
 * @package Clef
 * @since 2.0
 */

class ClefTranslation {
    public static function javascript() {
        return array(
            "messages" => array(
                "error" => array( 
                    "connect" => __("There was a problem automatically connecting your Clef account: <%= error %>. Please refresh and try again.", "clef"),
                    "create" => __("There was a problem creating a new Clef application for your WordPress site: <%= error %>. Please refresh and try again. If the issue, persists, email <a href='mailto:support@getclef.com'>support@getclef.com</a>.", "clef"),
                    "invite" => __("There was a problem sending invites: <%= error %>.", "clef"),
                    "generic" => __("Something wrong, please refresh and try again.", "clef"),
                    "disconnect" => __("There was a problem disconnecting your Clef account: <%= error %>.", "clef")
                ),
                "success" => array(
                    "connect" => __("You've successfully connected your account with Clef", "clef"),
                    "configured" => __("You're all set up!", "clef"),
                    "invite" => __("Email invitations have been sent to your users.", "clef"),
                    "disconnect" => __("Successfully disconnected Clef account.", "clef")
                ),
                "saving" =>  __("Settings are being saved. Are you sure you want to navigate away?", "clef")
            )
        );

    }
}
