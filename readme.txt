=== Plugin Name ===
Contributors: csixty4, jessepollak, zrathustra
Donate link: http://www.giffordcatshelter.org
Tags: login, log in, authentication, identity, security, clef, wave, iphone, android, password, mfa, 2fa, multi-factor, sso, single sign-on, openid, oauth, phone, smartphone, mobile, encryption, admin, wp-admin, ssl
Requires at least: 3.5
Tested up to: 3.6.1
Stable tag: 1.6.3
License: MIT
License URI: http://opensource.org/licenses/MIT

Clef replaces WordPress’s insecure username/password authentication with wonderfully free, beautifully simple, and impenatrably secure smartphone-based multi-factor authentication: no passwords, no temporary codes, one-click single sign on and off.

== Description ==

[Clef](https://getclef.com) is a replacement for usernames and passwords that lets you use your phone to identify yourself. When you visit a Clef enabled site, simply click the "Log in with your phone" button, scan the Clef Wave with your [Clef app](https://getclef.com/apps), and you are instantly and securely logged in.

Clef is the best single sign on solution for your WordPress sites. Once you sign in to one website using Clef, you are signed into every other Clef enabled site with a single click. And once you sign out of the app on your phone, you are automatically signed out of all your WordPress sites online. If you have multiple WordPress accounts, this means password-free, single sign on, 2-factor authentication for all of your WordPress sites -- give it a try today!

Want to better understand how Clef works? Visit [getclef.com](https://getclef.com) or watch the video below.

http://vimeo.com/61393630

= Note =

The Clef plugin was originally created by [Dave Ross](http://davidmichaelross.com), but has since been contributed to by members of the Clef team. All trademarks, including the Clef logo, are the property of Clef.

== Installation ==

= Installing the Plugin =

Install Clef automatically from your admin account by selecting "Plugins", then "Add new" from the sidebar menu. Search for Clef, then choose "Install Now".

or

1. Download the latest Clef archive from WordPress.org.
1. Unzip the archive and upload the `wpclef` directory to the `/wp-content/plugins/` directory on your WordPress site.
1. Activate the plugin through the 'Plugins' menu in WordPress

= Getting Started =

If you don't already have a Clef account, download the Clef app for your [iPhone](http://itunes.apple.com/us/app/clef/id558706348?ls=1&mt=8) or [Android](https://play.google.com/store/apps/details?id=io.clef) device and sign up.

With your Clef app in hand, visit the Clef settings page in your WordPress admin panel and walk through the setup wizard.

If you have any questions or problems, don't hesitate to contact Clef support at support@getclef.com.


== Frequently Asked Questions ==

= What if I lose my phone? =

Visit [Clef's "lost phone" page](https://getclef.com/lost) to deactivate your phone and disconnect it from your Clef account.

= How much does Clef cost? =

Clef is and will always be 100% free.

= Can existing users on my site sign in with Clef? =

The Clef plugin matches users to the WordPress accounts by matching email addresses. As long as your users are registered with your WordPress site and Clef using the same email address, they can start using Clef right away.

== Screenshots ==

1. WordPress login form with Clef login enabled.

2. Location of the Clef configuration screen.

3. Clef setup wizard.

4. A correctly configured settings page for Clef.

== Changelog ==

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

== Roadmap ==

* Translation into other languages
