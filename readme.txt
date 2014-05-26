=== Clef ===
Contributors: csixty4, jessepollak, zrathustra, inthylight
Donate link: http://www.giffordcatshelter.org
Tags: login, two factor, two-factor, 2-factor, two-step, 2-step, 2-step authentication, 2-step verification, two factor authentication, security, single sign on, log in, authentication, iphone, android, password, mfa, 2fa, multi-factor, sso, oauth, phone, mobile, encryption, admin, wp-admin, ssl
Requires at least: 3.5
Tested up to: 3.9
Stable tag: 2.1.3
License: MIT
License URI: http://opensource.org/licenses/MIT

The easiest and most secure way to log in to WordPress: no passwords, no temporary codes, single sign-on/off.

== Description ==

= What is Clef? =
[Clef](https://getclef.com) replaces passwords with beautifully simple, strongly encrypted, two-factor authentication using your smartphone.

= How do I log in to WordPress using Clef? =

http://vimeo.com/77091221

1. Click the "Log in with your phone" button
2. Sync the Clef Wave with your [Clef app](https://getclef.com/apps)
3. You're securely logged in without ever typing a password

Watch the [30 second video](http://getclef.wistia.com/medias/8mnrh6og39) and see for yourself.

= How secure is Clef? =

[Very](http://blog.getclef.com/2013/05/why-we-need-real-cryptography-instead-of-passwords/). Clef leverages the computational power of your smartphone and the proven strengths of distributed, [asymmetric cryptography](http://blog.getclef.com/2013/10/asymmetric-cryptography-use/) and [multi-factor authentication](http://blog.getclef.com/2013/10/2-factor-authentication/) to provide secure WordPress logins in a beautifully simple and easy-to-use package.

= How does single sign on with Clef work? =

Once you sign in to one WordPress site using Clef, you can sign into all of your Clef-enabled sites with a single click — no more typing and retyping passwords. When you're done managing your site, just hit the "log out" button on your phone and you are automatically signed out of all your WordPress sites.

= What happens if I lose my phone? =

Simply visit [Clef's "lost phone" page](https://getclef.com/lost) to deactivate your phone. Once you've deactivated, you can reactivate on a new device and all of your accounts will transfer over.

== Installation ==

= Installing the Plugin =

Install Clef automatically from your WordPress Dashboard by selecting "Plugins" and then "Add New" from the sidebar menu. Search for Clef, and then choose "Install Now."

Or:

1. Download the latest version of Clef for WordPress (via the download button at the top right).
1. Unzip the archive and upload the `wpclef` directory to the `/wp-content/plugins/` directory on your WordPress site.
1. Activate the plugin through the "Plugins" menu in WordPress

= Getting Started =

1. If you don't already have a Clef account, download the Clef app for your [iPhone](http://itunes.apple.com/us/app/clef/id558706348?ls=1&mt=8) or [Android](https://play.google.com/store/apps/details?id=io.clef) device and sign up.
2. With your Clef app in hand, visit the Clef settings page in your WordPress Dashboard and walk through the brief setup wizard.

If you have any questions or problems, don't hesitate to contact Clef support at [support@getclef.com](mailto:support@getclef.com).


== Frequently Asked Questions ==

= How secure is Clef? =

[Very](http://blog.getclef.com/2013/05/why-we-need-real-cryptography-instead-of-passwords/). Clef leverages the computational power of your smartphone and the proven strengths of distributed, [asymmetric cryptography](http://blog.getclef.com/2013/10/asymmetric-cryptography-use/) and [multi-factor authentication](http://blog.getclef.com/2013/10/2-factor-authentication/) to provide secure WordPress logins in a beautifully simple and easy-to-use package.

Clef protects WordPress not only from insecure passwords but also from malicious forgotten password resets and bruteforce attacks.

= How secure is my data on Clef's servers? =

Clef's architecture is fully distributed so it stores no user credentials on its servers. When you use the Clef App, you create a profile and a private encryption key that never leave your phone. The Clef App uses that data to generate a unique, encrypted, digital signature every time you log in. Since all of your personal info stays on your phone, nothing in the Clef database can compromise your identity even in the unlikely event that the server is hacked.

= What if I lose my phone? =

Simply visit [Clef's "lost phone" page](https://getclef.com/lost) to deactivate your phone. Once you've deactivated, you can reactivate on a new device and all of your accounts will transfer over.

= How much does Clef cost? =

Clef is and will always be 100% free.

= Can existing users on my WordPress site sign in with Clef? =

As long as your users register on their Clef Apps using the same email address as their WordPress accounts, they can start using Clef instantly. Also, once Clef for WordPress is activated on your site, your existing WordPress users can link their accounts to Clef via the Profile page in WordPress.

= What if I have some users who do not have smartphones? =

Clef can protect WordPress in hybrid mode (passwords allowed) or full Clef mode (no passwords). If you want to enjoy the protection of full Clef mode but still need to allow passwords for users who do not have smartphones, then on the Clef Settings page you can create a secure override key that enables password logins at a secret URL.

== Screenshots ==

1. WordPress login form with Clef login enabled.

2. Location of the Clef configuration screen.

3. Clef setup wizard.

4. A correctly configured settings page for Clef.

== Changelog ==

= 2.1.3 =

* Bug fix: fixes XMLRPC edge case with login on WordPress Mobile App

= 2.1.2 =

* Feature: improved onboarding experience for new users
* Bug fix: login now now works with Theme My Login (github/clef/wordpress#125)
* Bug fix: login now works with Google Captcha (github/clef/wordpress#127)
* Bug fix: removes unnecessary CSS files on frontend

= 2.1.1 =

* Feature: add framework for Clef affiliate referrals
* Feature: add a shortcode for displaying the Clef settings
* Bug fix: error where invite emails errored when there was a blank email
* Bug fix: issue where Clef button displayed multiple times

= 2.1 =

* Feature: customize the Clef login page
* Feature: configure default site settings that will be set for all new sites

= 2.0.1 =

* Bug fix: fixes compatibility issue with 5.2

= 2.0 =

* Feature: new and improved single-page responsive settings page
* Feature: easy user invitations
* Feature: beautiful introduction and tutorial to Clef
* Feature: finer grained password controls
* Feature: restructured code base for easier integrations

= 1.9.1.2 =

* Bug fix: fix override URL issue

= 1.9.1.1 =

* Bug fix: fix issue where upgrades are detected incorrectly

= 1.9.1 =

* New feature: Clef account is automatically connected when you set up a new account
* New feature: clarifies settings language so it's a little bit clearer
* Bug fix: resolves issue with badge prompt displaying multiple times

= 1.9 =

* New feature: automatically add a badge showing off that your login is protected by Clef
* New feature: adds framework for adding translations to plugin
* Various stability fixes

= 1.8.1.2 =

* New feature: hides login with Clef button if Clef is not configured
* Bug fix: fixes issue with mobile login where Clef would not work

= 1.8.1 =

* New feature: force users with certain permissions to log in with Clef
* New feature: force multisite settings on sub-site users
* New feature: adds integration with BruteProtect
* Compatibility update: supports 3.7+

= 1.8.0 =

* New feature: adds support using Clef on multisite networks (currently only supports single-domain setups)
* New feature: warns user if they try and disable passwords without a connected Clef account
* New developer feature: restructures plugin to allow easier development
* New developer feature: adds testing framework

= 1.7.1 =

* Bug fix: fixes issue caused by Clef applications that did not request last name from users
* Bug fix: adds state parameter to OAuth flow for connecting a Clef account to a WordPress account

= 1.7 =

* New feature: require Clef authentication for all users with optional override key. When this new setting is selected, Clef for WordPress enables true password-free WordPress authentication by hiding the default login form and requiring Clef authentication for all users. If the need arises, you can set an optional override key to allow password logins at a secret URL. Secure keys can be generated automatically, or you can input your own key.
* New feature: lost password reset protection. If you are running Clef for WordPress in hybrid mode, then lost password resets are disabled for Clef users only. If you are running in full Clef mode, then lost password resets are disabled for all users.
* New feature: Clef for Wordpress' settings are deleted on uninstall

= 1.6.3 =

* Updated feature: better error messages
* Various bug fixes

= 1.6 =

* New feature: connect any Clef account to any WordPress account. This feature removes the restriction on matching emails on Clef and WordPress accounts.

= 1.5.4 =

* New feature: better error messages
* Fix for FPD
* Various bug fixes

= 1.5.3 =

* New feature: JavaScript logout through heartbeat API

= 1.5.2 =

* New feature: autofill setup variables
* Changes tested WP compatibility version
* Various bug fixes

= 1.5 =
* Updated feature: instead of changing Clef-enabled users' passwords every time they sign in to WordPress, usernames and passwords are entirely disabled for Clef accounts

= 1.4 =
* New feature: single sign-off. When you sign out of your phone, you sign out of all of your WordPress sites.
* New feature: greater password protection. If a site admin opts-in, a user's passwords will be reset to a random 40 characters every time they sign in.

= 1.3 =
* Beautified WordPress login form with Clef
* New feature: setup wizard for easy install

= 1.2 =
* Updates for Clef v2 API

= 1.1 =
* Added an admin "pointer" to call out the configuration screen on new installs
* User registration

= 1.0 =
* Initial release: log in using the Clef app.

== Upgrade Notice ==

1.7 adds significant security and functionality improvements designed to foil botnets and brute force attacks. A new setting allows you to hide the default login form and to require Clef authentication for all users.

== Credits ==

[Dave Ross](http://davidmichaelross.com) created the original Clef for WordPress plugin. The Clef team continues to develop the plugin further. All trademarks, including the Clef logo, are the property of Clef.

== Roadmap ==

* Better international support (if you want to help, you can get started [here](http://transifex.com/projects/p/wpclef/))
* Improved invite system for adding new Clef users
* Support for Clef team member login and account creation
