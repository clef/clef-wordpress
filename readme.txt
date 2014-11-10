=== Clef Two-Factor Authentication ===
Contributors: csixty4, jessepollak, zrathustra, inthylight
Donate link: http://www.giffordcatshelter.org
Tags: login, security, two factor, two-factor, two factor authentication, single sign on, log in, authentication, iphone, android, password, mfa, 2fa, multi-factor, sso, oauth, phone, mobile, smartphone, encryption, admin, wp-admin, ssl, 2-factor, two-step, 2-step, 2-step authentication, 2-step verification
Requires at least: 3.5
Tested up to: 4.0
Stable tag: 2.2.7
License: MIT
License URI: http://opensource.org/licenses/MIT

Futuristic two-factor that people love to use: no passwords or temporary codes, single sign on/off, prevents password attacks.

== Description ==

[Clef](https://getclef.com) replaces passwords with beautifully simple, strongly encrypted two-factor authentication using [your smartphone](getclef.com/apps). Watch [our 30-second demonstration](http://vimeo.com/77091221).

http://vimeo.com/77091221

= Features =

- **Strong authentication**: Clef [replaces passwords](http://blog.getclef.com/2013/05/why-we-need-real-cryptography-instead-of-passwords/) with encrypted authentication using the well respected [RSA](https://en.wikipedia.org/wiki/RSA_(cryptosystem)) public key cryptosystem.

- **Comprehensive password protection**: Clef disables passwords at [all three WP password authentication vectors](http://support.getclef.com/article/50-how-to-use-the-password-settings-in-clefs-wordpress-plugin#vectors): DashBoard access, API access, and automatic password resets via e-mail.

- **Fine-tuned control**: Clef’s powerful [settings](http://support.getclef.com/article/50-how-to-use-the-password-settings-in-clefs-wordpress-plugin) allow you to selectively disable passwords and to accommodate [users without smartphones](http://support.getclef.com/article/57-how-does-clef-accommodate-wordpress-logins-for-users-who-do-not-have-smartphones).

- **Single sign on/off**: Clef makes administering multiple WP sites a breeze. Sign in once at your first Clef-enabled site, and you are automatically signed in to all of your subsequent Clef-enabled sites. Also, thanks to the customizable timer you are automatically logged out of all your sites after the duration of your choosing.

- **[Login button shortcode](http://support.getclef.com/article/56-how-do-i-use-the-clef-login-shortcode)**: insert the Clef login button or embedded Clef Wave in any post, page, or widget using the **clef_render_login_button** shortcode.

- **Multisite network support**

- **[Internationalization support](https://www.transifex.com/projects/p/wpclef/)**: Danish, Dutch, French, German, Portuguese, Russian, Spanish. More on the way; [contributions welcome](https://www.transifex.com/projects/p/wpclef/).

- **Fulsome [documentation](http://support.getclef.com/collection/1-wordpress)** 

- **Free [e-mail support](mailto:support@getclef.com)**

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

= 2.2.6 =

* Fix: bug where registration and lost password pages do not render correctly
* Fix: bug where login button shortcode renders out of order
* Fix: bug where invalid logout hook caused PHP error
* Fix: bug where other login page plugins rendered on top of embedded wave

= 2.2.5 =

* Feature: allow administrators to disable passwords for custom roles
* Fix: setup tutorial now works in IE9
* Fix: password login error message displays correctly with Clef login embed

= 2.2.4 =

* Feature: adds more translations!
* Fix: issue where embedded login was hidden then shown
* Fix: conflict with Polylang plugin where settings would not save
* Fix: issue where PHP error occurred if an error occurred during user registration with Clef

= 2.2.3 =

* Fix: store affiliates as string rather than array

= 2.2.2 =

* Feature: adds even easier affiliates
* Fix: plugin conflict with NextGen Gallery where login iframe does not load
* Fix: plugin conflict with BuddyPress

= 2.2.1 =

* Feature: adds easier affiliates
* Fix: adds better error checking

= 2.2.0 =

* Feature: adds shortcode to easily render login button
* Feature: use modal to allow users to login and preserve state when they are logged out with Clef
* Feature: menu notification when user hasn't configured Clef
* Feature: by default, embed the Wave in the login form rather than making users click a button
* Bug fix: issue where if passwords were fully disabled, but Clef wasn't configured, no login form would be shown
* Bug fix: issue where a new session was opened for every request, not just ones where it was necessary (in admin for Clef users)
* Bug fix: issue where users can connect the same Clef account to two WordPress accounts
* Bug fix: issue where if the heartbeat API wasn't available, Clef could interfere with the loading of other plugins
* Bug fix: issue where prompt to add Clef badge was shown even if the badge was already displayed

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
