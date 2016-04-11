=== Clef Two-Factor Authentication ===
Contributors: csixty4, jessepollak, zrathustra, inthylight
Donate link: http://www.giffordcatshelter.org
Tags: two-factor, two factor, 2 step authentication, 2 factor, 2FA, admin, android, authentication, encryption, harden, iphone, log in, login, mfa, mobile, multifactor, multi factor, oauth, password, passwords, phone, secure, security, smartphone, single sign on, ssl, sso, strong authentication, tfa, two factor authentication, two step, wp-admin, wp-login
Requires at least: 3.6
Tested up to: 4.5
Stable tag: 2.4.1
License: MIT
License URI: http://opensource.org/licenses/MIT

Modern two-factor that people love to use: strong authentication without passwords or tokens; single sign on/off; magical user experience.

== Description ==

[The Clef mobile app](https://getclef.com) provides password-free, two-factor authentication that is highly secure and enjoyable to use. Just sync your phone with the Clef Wave to log in. [Watch the 30-sec. demo](http://vimeo.com/103148853).

http://vimeo.com/103148853

= Our Users’ Favorite Features =
- **No passwords**: login securely with the [Clef wave](https://getclef.com/tutorial/), and enjoy two-factor protection without one-time codes.

- **No extra devices**: use your smartphone instead of a “third device” such as a USB drive or security key.

- **[Single sign on/off](http://support.getclef.com/article/52-how-does-clef-s-single-sign-on-work)**: Sync with the Clef Wave once, then enjoy one-click sign ins for all subsequent sites. Also, sign out from all your sites with one-click any time, or [set the timer](http://support.getclef.com/article/72-how-to-adjust-the-duration-of-the-logout-timer) to log you out automatically when you’re done working.

= Security Features =

- **Strong authentication**: Clef [replaces passwords](http://blog.getclef.com/2013/05/why-we-need-real-cryptography-instead-of-passwords/) with highly secure, two-factor logins using the tried-and-true [RSA](https://en.wikipedia.org/wiki/RSA_(cryptosystem)) public-key cryptosystem.
 - Clef stores your encrypted private key on your phone rather than in a central database. Thus even in the unlikely event of a catastrophic security breach on Clef’s servers, your login credentials remain secure on your phone.
 - Every Clef login requires two identification factors: your phone and a fingerprint or PIN. So even if your phone is lost or stolen, you’re Clef account will remain secure.

- **Comprehensive protection**: Clef disables passwords for all three WordPress authentication points: Dashboard access, API access, and password resets. Thus it protects against the full spectrum of password-based attack vectors:
 - brute-force and botnet login attacks
 - weak, leaked, and recycled passwords
 - sending login credentials via an insecure (non-ssl) connection
 - password phishing attempts
 - account takeovers via email breaches

= Configuration Features =

- **[Flexible password settings](http://support.getclef.com/article/60-recommended-password-settings-for-clef-wordpress-plugin)**
 - Disable passwords for select WordPress user roles including custom roles.
 - Disable passwords for API access.
 - Accommodate [users who do not have smartphones](http://support.getclef.com/article/57-how-does-clef-accommodate-wordpress-logins-for-users-who-do-not-have-smartphones).

- **[Shortcode support](http://support.getclef.com/article/56-how-do-i-use-the-clef-login-shortcode)**: insert Clef’s “login with your phone” button or the Clef Wave in any post, page, or text widget using the **clef_render_login_button** shortcode.

- **Standards-based compatibility**: Clef’s WordPress plugin adheres to WordPress coding guidelines and is thus compatible with most mainstream plugins and themes.

- **Internationalization and localization support**: Arabic, Danish, Dutch, French, German, Greek, Japanese, Latvian, Portuguese, Russian, Spanish. More translations on the way. [Help translate Clef](http://support.getclef.com/article/62-how-to-help-translate-clef-wordpress-plugin) into your language.

- **[Multisite network](http://support.getclef.com/article/55-does-clefs-wordpress-plugin-support-multisite-networks) support**

- **Helpful [documentation](http://support.getclef.com/)**

- **Free [support](http://support.getclef.com/)**

== Installation ==

1. In your WordPress Dashboard select **Plugins** and then **Add New** from the sidebar menu. Search for **Clef**, and then choose **Install Now.**
1. Activate the plugin.
1. Run the automatic setup wizard.

For detailed setup instructions [see the installation guide](http://support.getclef.com/article/13-setting-up-clef-on-a-wordpress-site).

== Additional steps for multisite networks, staging sites, and cloned sites ==

- For multisite networks, see these [additional steps](http://support.getclef.com/article/55-does-clefs-wordpress-plugin-support-multisite-networks).
- For using Clef with staging and production URLs, see the [staging guide](http://support.getclef.com/article/75-using-staging-urls-with-clef-s-wordpress-plugin).
- For assistance with cloning a Clef-enabled site, see the [cloning guide](http://support.getclef.com/article/89-using-the-clef-plugin-on-cloned-wordpress-sites).

If you have any questions or installation issues, visit [support.getclef.com](http://support.getclef.com) to get help.

== Frequently Asked Questions ==

= Is Clef for WordPress really free? =

Yes. Really. Boom! And your free Clef account includes

- unlimited users,
- support,
- and basic usage analytics.

= Can existing users on my WordPress site sign in with Clef after I install the plugin? =

Yes. If your users register on their Clef mobile apps using the same email address as their WordPress accounts, they can start using Clef instantly. Otherwise, they can [link their WP users with their Clef accounts](http://support.getclef.com/article/69-linking-the-clef-mobile-app-to-wordpress-users) after logging in to the WordPress dashboard.

Also, Clef makes it easy to invite your users with optional invitation emails.

= How does Clef accommodate logins for WordPress users who do not have smartphones? =

The [disable passwords options](http://support.getclef.com/article/60-recommended-password-settings-for-clef-wordpress-plugin) and [secret override url](http://support.getclef.com/article/11-creating-a-secret-url-where-you-can-log-into-your-wordpress-site-with-a-password) provide several options for allowing password logins.

See [the guide for accommodating users without smartphones](http://support.getclef.com/article/57-how-does-clef-accommodate-wordpress-logins-for-users-who-do-not-have-smartphones) for details.

= What should I do if my phone is lost or stolen, or if I switch to a new phone? =

1. [Deactivate](http://support.getclef.com/article/32-what-should-i-do-if-my-phone-is-lost-or-stolen) your old phone.
1. [Reactivate](http://support.getclef.com/article/59-how-do-i-move-my-clef-account-to-a-new-phone) on your new phone.

= How do I create a custom login page or widget with the Clef login shortcode? =
You can add the Clef Wave or the Clef “login with your phone” button by inserting the **clef_render_login_button** shortcode into any post, page, or text widget. See the [shortcode guide](http://support.getclef.com/article/56-how-do-i-use-the-clef-login-shortcode) for details.

= How do I configure Clef for multisite networks? =

If you have a subdirectory network, then no additional configuration is required.

If you have a subdomain or full domain network, then you must [configure the application domain setting](http://support.getclef.com/article/55-does-clefs-wordpress-plugin-support-multisite-networks) to allow Clef logins at multiple subdomains or domains.

= How secure are Clef logins? =

[Very](http://blog.getclef.com/2013/05/why-we-need-real-cryptography-instead-of-passwords/). Clef leverages the computational power of your smartphone and the proven strengths of distributed, [asymmetric cryptography](http://blog.getclef.com/2013/10/asymmetric-cryptography-use/) and [multi-factor authentication](http://blog.getclef.com/2013/10/2-factor-authentication/) to provide secure WordPress logins in a beautifully simple and easy-to-use mobile app.

When configured to disable passwords, Clef protects WordPress users against the full spectrum of password-based attacks:

- brute-force and botnet attacks
- weak, leaked, and recycled passwords
- sending login credentials via an insecure (non-ssl) connection
- password phishing attempts
- account takeovers via email breaches

= How secure is my data on Clef’s servers? =

Clef’s security architecture is fully distributed, which means Clef stores no user credentials on its servers. When you use the Clef mobile app, you create a profile and a private encryption key that never leave your phone. The Clef app then uses that data to generate a unique, encrypted digital signature every time you log in. Since all of your personal info stays on your phone, nothing in the Clef database can compromise your identity even in the unlikely event that the server is hacked.

== Screenshots ==

1. Logging into WordPress with Clef
2. Passwords disabled on wp-login.php
3. Clef setup wizard
4. Clef settings page

== Changelog ==

= 2.4.1 =
Released 10 March 2016

* Fix: with certain setups, settings were unable to save

= 2.4.0 =
Released 16 February 2016

* Enhancement: better onboarding experience
* Enhancement: per-user and bulk user invites to use Clef
* Enhancement: less CSS loaded by default, including 0 on all non-admin pages
* Enhancement: dashboard widget to help new users get setup
* Enhancement: easily reset your Clef settings
* Fix: reduced frequency of "invalid state" errors

= 2.3.4 =
Released 27 January 2016

* Enhancement: updates support path

= 2.3.3 =
Released 21 December 2015

* Fix: update issue

= 2.3.2 =
Released 21 December 2015

* Fix: stop making unnecessary request to Clef API when site is not configured
* Enhancement: remove Waltz upsell

= 2.3.1 =
Released 6 October 2015

* Fix: accommodate new password reset action introduced in WP 4.3
* Feature: automatically send override link to site administrator
* Enhancement: update Clef logo and README
* Enhancement: upon activation redirect to default plugins page if Clef is already configured

= 2.3.0 =
Released 18 June 2015

* Feature: updates translations for all languages

= 2.2.9.5 =
Released 11 May 2015

* Fix: login issue with websites that had certain caching configurations

= 2.2.9.4 =
Released 27 April 2015

* Fix: login issue with websites that had custom login pages

= 2.2.9.3 =
Released 23 April 2015

* Fix: login issue with websites that did not do output buffering
* Fix: silenced messages for resetting password

= 2.2.9.2 =
Released 22 April 2015

* Fix: removes legacy other plugins install page
* Fix: uses state parameter on Clef button

= 2.2.9.1 =
Released 17 December 2014

* Enhancement: disable passwords by default for Clef users
* Fix: re-add ability to disable registration with Clef

= 2.2.8 =
Released 20 November 2014

* Enhancement: add learn more links to settings pages
* Enhancement: update README
* Fix: remove duplicate setting for managing ability to register with Clef
* Fix: change WordPress badge language

= 2.2.7 =
Released 3 November 2014

* Fix: bug where obfuscating login page URL causes rendering issues

= 2.2.6 =
Released 2 November 2014

* Fix: bug where registration and lost password pages do not render correctly
* Fix: bug where login button shortcode renders out of order
* Fix: bug where invalid logout hook caused PHP error
* Fix: bug where other login page plugins rendered on top of embedded wave

= 2.2.5 =
Released 29 September 2014

* Feature: allow administrators to disable passwords for custom roles
* Fix: setup tutorial now works in IE9
* Fix: password login error message displays correctly with Clef login embed

= 2.2.4 =
Released 4 September 2014

* Feature: adds more translations!
* Fix: issue where embedded login was hidden then shown
* Fix: conflict with Polylang plugin where settings would not save
* Fix: issue where PHP error occurred if an error occurred during user registration with Clef

= 2.2.3 =
Released 6 August 2014

* Fix: store affiliates as string rather than array

= 2.2.2 =
Released 6 August 2014

* Feature: adds even easier affiliates
* Fix: plugin conflict with NextGen Gallery where login iframe does not load
* Fix: plugin conflict with BuddyPress

= 2.2.1 =
Released 29 July 2014

* Feature: adds easier affiliates
* Fix: adds better error checking

= 2.2.0 =
Released 21 July 2014

* Feature: adds shortcode to easily render login button
* Feature: use modal to allow users to login and preserve state when they are logged out with Clef
* Feature: menu notification when user hasn’t configured Clef
* Feature: by default, embed the Wave in the login form rather than making users click a button
* Bug fix: issue where if passwords were fully disabled, but Clef wasn’t configured, no login form would be shown
* Bug fix: issue where a new session was opened for every request, not just ones where it was necessary (in admin for Clef users)
* Bug fix: issue where users can connect the same Clef account to two WordPress accounts
* Bug fix: issue where if the heartbeat API wasn’t available, Clef could interfere with the loading of other plugins
* Bug fix: issue where prompt to add Clef badge was shown even if the badge was already displayed

= 2.1.3 =
Released 26 May 2014

* Bug fix: fixes XMLRPC edge case with login on WordPress Mobile App

= 2.1.2 =
Released 22 May 2014

* Feature: improved onboarding experience for new users
* Bug fix: login now works with Theme My Login (github/clef/wordpress#125)
* Bug fix: login now works with Google Captcha (github/clef/wordpress#127)
* Bug fix: removes unnecessary CSS files on frontend

= 2.1.1 =
Released 17 April 2014

* Feature: add framework for Clef affiliate referrals
* Feature: add a shortcode for displaying the Clef settings
* Bug fix: error where invite emails erred when there was a blank email
* Bug fix: issue where Clef button displayed multiple times

= 2.1 =
Released 19 March 2014

* Feature: customize the Clef login page
* Feature: configure default site settings that will be set for all new sites

= 2.0.1 =
Released 12 March 2014

* Bug fix: fixes compatibility issue with 5.2

= 2.0 =
Released 24 February 2014

* Feature: new and improved single-page responsive settings page
* Feature: easy user invitations
* Feature: beautiful introduction and tutorial to Clef
* Feature: finer grained password controls
* Feature: restructured code base for easier integrations

= 1.9.1.2 =
Released 4 February 2014

* Bug fix: fix override URL issue

= 1.9.1.1 =
Released 30 January 2014

* Bug fix: fix issue where upgrades are detected incorrectly

= 1.9.1 =
Released 30 January 2014

* New feature: Clef account is automatically connected when you set up a new account
* New feature: clarifies settings language so it’s a little bit clearer
* Bug fix: resolves issue with badge prompt displaying multiple times

= 1.9 =
Released 21 January 2014

* New feature: automatically add a badge showing off that your login is protected by Clef
* New feature: adds framework for adding translations to plugin
* Various stability fixes

= 1.8.1.2 =
Released 13 January 2014

* New feature: hides login with Clef button if Clef is not configured
* Bug fix: fixes issue with mobile login where Clef would not work

= 1.8.1 =
Released 21 November 2013

* New feature: force users with certain permissions to log in with Clef
* New feature: force multisite settings on sub-site users
* New feature: adds integration with BruteProtect
* Compatibility update: supports 3.7+

= 1.8.0 =
Released 15 October 2013

* New feature: adds support using Clef on multisite networks (currently only supports single-domain setups)
* New feature: warns user if they try and disable passwords without a connected Clef account
* New developer feature: restructures plugin to allow easier development
* New developer feature: adds testing framework

= 1.7.1 =
Released 3 October 2013

* Bug fix: fixes issue caused by Clef applications that did not request last name from users
* Bug fix: adds state parameter to OAuth flow for connecting a Clef account to a WordPress account

= 1.7 =
Released 20 September 2013

* New feature: require Clef authentication for all users with optional override key. When this new setting is selected, Clef for WordPress enables true password-free WordPress authentication by hiding the default login form and requiring Clef authentication for all users. If the need arises, you can set an optional override key to allow password logins at a secret URL. Secure keys can be generated automatically, or you can input your own key.
* New feature: lost password reset protection. If you are running Clef for WordPress in hybrid mode, then lost password resets are disabled for Clef users only. If you are running in full Clef mode, then lost password resets are disabled for all users.
* New feature: Clef for Wordpress’ settings are deleted on uninstall

= 1.6.3 =
Released 10 September 2013

* Updated feature: better error messages
* Various bug fixes

= 1.6 =
Released 9 September 2013

* New feature: connect any Clef account to any WordPress account. This feature removes the restriction on matching emails on Clef and WordPress accounts.

= 1.5.4 =
Released 2 September 2013

* New feature: better error messages
* Fix for FPD
* Various bug fixes

= 1.5.3 =
Released 2 September 2013

* New feature: JavaScript logout through heartbeat API

= 1.5.2 =
Released 30 August 2013

* New feature: autofill setup variables
* Changes tested WP compatibility version
* Various bug fixes

= 1.5 =
Released 11 July 2013

* Updated feature: instead of changing Clef-enabled users’ passwords every time they sign in to WordPress, usernames and passwords are entirely disabled for Clef accounts

= 1.4 =
Released 19 June 2013

* New feature: single sign-off. When you sign out of your phone, you sign out of all of your WordPress sites.
* New feature: greater password protection. If a site admin opts-in, a user’s passwords will be reset to a random 40 characters every time they sign in.

= 1.3 =
Released 26 April 2013

* Beautified WordPress login form with Clef
* New feature: setup wizard for easy install

= 1.2 =
Released 22 January 2013

* Updates for Clef v2 API

= 1.1 =
Released 18 January 2013

* Added an admin “pointer” to call out the configuration screen on new installs
* User registration

= 1.0 =
Released 17 January 2013

* Initial release: log in using the Clef app.

== Upgrade Notice ==

1.7 adds significant security and functionality improvements designed to foil botnets and brute force attacks. A new setting allows you to hide the default login form and to require Clef authentication for all users.

== Credits ==

[Dave Ross](http://davidmichaelross.com) created the original Clef plugin for WordPress. The Clef team continues to develop the plugin further. All trademarks, including the Clef logo, are the property of Clef.

== Roadmap ==

* More localization. To help translate, see [the localization guide](http://support.getclef.com/article/62-how-to-help-translate-clef-wordpress-plugin).
* Improved invite system for adding new Clef users
* Support for Clef team member login and account creation
