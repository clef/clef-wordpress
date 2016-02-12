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
                    "connect" => __("There was a problem automatically connecting your Clef account: <%= error %>. Please refresh and try again.", "wpclef"),
                    "create" => __("There was a problem creating a new Clef application for your WordPress site: <%= error %>. Please refresh and try again. If the issue, persists, visit <a href='http://support.getclef.com' target='_blank'>support.getclef.com</a> to get help.", "wpclef"),
                    "invite" => __("There was a problem sending invites: <%= error %>.", "wpclef"),
                    "generic" => __("Something wrong, please refresh and try again.", "wpclef"),
                    "disconnect" => __("There was a problem disconnecting your Clef account: <%= error %>.", "wpclef")
                ),
                "success" => array(
                    "connect" => __("You've successfully connected your account with Clef", "wpclef"),
                    "configured" => __("You're all set up!", "wpclef"),
                    "disconnect" => __("Successfully disconnected Clef account.", "wpclef")
                ),
                "saving" =>  __("Settings are being saved. Are you sure you want to navigate away?", "wpclef")
            )
        );

    }
}
