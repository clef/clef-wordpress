=== Plugin Name ===
Contributors: csixty4, jessepollak, zrathustra, inthylight
Donate link: http://www.giffordcatshelter.org
Tags: login, log in, authentication, identity, security, clef, wave, iphone, android, password, mfa, 2fa, multi-factor, sso, single sign-on, openid, oauth, phone, smartphone, mobile, encryption, admin, wp-admin, ssl
Requires at least: 3.5
Tested up to: 3.6.1
Stable tag: 1.7
License: MIT
License URI: http://opensource.org/licenses/MIT

Clef is the simplest and most secure way to sign in to WordPress: no passwords, no temporary codes, strong encryption, multi-factor authentication, single sign-on/off, free forever.

== Description ==

[Clef](https://getclef.com) replaces insecure password-based authentication with beautifully simple and impenatrably secure smartphone-based multi-factor authentication. Simply click the "Log in with your phone" button, scan the Clef Wave with your [Clef app](https://getclef.com/apps), and you are instantly and securely logged in to all of your Clef-enabled websites.

Clef provides the best single sign-on solution for WordPress. Once you sign in to one WordPress site using Clef, you can sign into all of your Clef-enabled sites with a single click. And once you sign out of the app on your phone, you are automatically signed out of all your WordPress sites online. This means password-free, single sign-on, multi-factor authentication for all of your WordPress sites--try it today!

[See more on how Clef works](https://getclef.com/how-clef-works), or watch the video below.

http://vimeo.com/61393630

== Installation ==

= Installing the Plugin =

Install Clef automatically from your WordPress Dashboard by selecting "Plugins" and then "Add New" from the sidebar menu. Search for Clef, and then choose "Install Now."

Or:

1. Download the latest Clef archive from WordPress.org.
1. Unzip the archive and upload the `wpclef` directory to the `/wp-content/plugins/` directory on your WordPress site.
1. Activate the plugin through the "Plugins" menu in WordPress

= Getting Started =

If you don't already have a Clef account, download the Clef app for your [iPhone](http://itunes.apple.com/us/app/clef/id558706348?ls=1&mt=8) or [Android](https://play.google.com/store/apps/details?id=io.clef) device and sign up.

With your Clef app in hand, visit the Clef settings page in your WordPress Dashboard and walk through the brief setup wizard.

If you have any questions or problems, don't hesitate to contact Clef support at [support@getclef.com](mailto:support@getclef.com).


== Frequently Asked Questions ==

= What if I lose my phone? =

Visit [Clef's "lost phone" page](https://getclef.com/lost) to deactivate your phone and disconnect it from your Clef account.

= How much does Clef cost? =

Clef is and will always be 100% free.

= Can existing users on my site sign in with Clef? =

As long as your users register on the Clef App using the same email address as their WordPress accounts, they can start using Clef right away. Also, your existing WordPress users can link their accounts to Clef via the Profile page in WordPress.

== Screenshots ==

1. WordPress login form with Clef login enabled.

2. Location of the Clef configuration screen.

3. Clef setup wizard.

4. A correctly configured settings page for Clef.

== Changelog ==

= 1.7 =

* adds "force Clef authentication for all users" feature with optional override key
* adds lost password reset protection
* adds settings removal on uninstall

= 1.6.3 =

* adds bug fixes
* adds better error messages

= 1.6 =

* adds ability to connect any Clef account to any WordPress account, removing restriction of matching emails on Clef and WordPress accounts

= 1.5.4 =

* various bug fixes
* better error messages
* fixes FPD

= 1.5.3 =

* Adds javascript logout through heartbeat API

= 1.5.2 =

* Autofills setup variables
* Changes tested compatibility version
* Various bug fixes

= 1.5 =
* Update to the optional security feature: instead of changing users' passwords every time they sign in, usernames and passwords are entirely disabled for accounts using Clef

= 1.4 =
* Adds single sign-off functionality - when you sign out of your phone, you sign out of all of your WordPress sites
* Adds greater password protection - if a site admin opts-in, a user's passwords will be reset to a random 40 characters every time they sign in

= 1.3 =
* Beautified WordPress login form with Clef
* Added setup wizard for easy setup

= 1.2 =
* Updated for Clef v2 API

= 1.1 =
* Added an admin "pointer" to call out the configuration screen on new installs
* User registration

= 1.0 =
* Initial release. Supports logging in using the Clef app.

== Upgrade Notice ==

We just upgraded to 1.4

== Credits ==

[Dave Ross](http://davidmichaelross.com) created the original WP Clef plugin. The Clef team continues to develop the plugin further. All trademarks, including the Clef logo, are the property of Clef.

== Roadmap ==

* Multisite compabibility
* Translation into other languages