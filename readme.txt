=== Plugin Name ===
Contributors: csixty4
Donate link: http://www.giffordcatshelter.org
Tags: login, authentication, qc code, qr codes
Requires at least: 3.5
Tested up to: 3.5
Stable tag: 1.0
License: MIT
License URI: http://opensource.org/licenses/MIT

Let users log into their WordPress accounts with the [Clef](http://clef.io) app on their phone and the Clef web service.

== Description ==

[Clef](http://clef.io) is a smartphone app that lets you "forget your passwords" and log into sites as easily as taking a photo. Once you've registered and signed into the Clef website, you can visit a [website that uses Clef](https://clef.io/websites) and sign in using the [Clef app for your iPhone](http://itunes.apple.com/us/app/clef/id558706348?ls=1&mt=8) (an Android app is coming in January, 2013).

WPClef adds a graphic (a "QR code" for the geeks out there) to the WordPress login form. Scanning that graphic with the Clef app automatically logs you into WordPress using your Clef account.

See [the installation instructions](http://wordpress.org/extend/plugins/wpclef/installation/) for details on how to sign up for Clef and start using it today.

http://vimeo.com/55652461

= Note =

WPClef is provided by [Dave Ross](http://davidmichaelross.com) and is not affiliated wtih the Clef or Brennen Byrne. All trademarks, including the Clef logo, are the property of Clef/Brennen Byrne.

== Installation ==

= Installing the Plugin =

Install WPClef automatically from your admin account by selecting "Plugins", then "Add new" from the sidebar menu. Search for WPClef, then choose "Install Now".

or

1. Download the latest WPClef archive from wordpress.org.
1. Unzip the archive and upload the `wpclef` directory to the `/wp-content/plugins/` directory on your WordPress site.
1. Activate the plugin through the 'Plugins' menu in WordPress

= Getting Started =

If you don't already have a Clef account, download the [Clef app for your iPhone](http://itunes.apple.com/us/app/clef/id558706348?ls=1&mt=8) (an Android app is coming in January, 2013) and sign up.

You will then need to register as a developer at https://developer.clef.io and [create a new application](https://developer.clef.io/applications/new) for your site. For the "iFrame parent URL", enter the address of your WordPress login page (i.e. `http://www.example.com/wp-login.php`) and tell Clef you need permission to see users' Email, First Name, and Last Name.

If you have any questions about registering with Clef or signing up as a developer, contact Clef support at info@clef.io

Once your site is registered, Clef will give you "Application ID" and "Application Secret" codes. Paste these codes in the Clef configuration screen on your WordPress site, by selecting "Settings", then "Clef". Save your changes and you're all set to start using Clef with your site.

== Frequently Asked Questions ==

= Can anyone use my phone to log into a site using Clef? =

The Clef app is protected by a four-digit PIN you set up when you register. This should be a different PIN than the one you use to unlock your phone.

= What if I lose my phone? =

Visit [Clef's "lost phone" page](https://clef.io/lost) to deactivate your phone and disconnect it from your Clef account.

= How much does Clef cost? =

Clef is free for sites that have less than 10,000 unique users logging in with Clef per month and costs half a cent per user per month for larger sites.

= Who owns Clef users' data? =

Clef only provides user data with the user's explicit permission, but once granted, ownership of the data is transferred with the data. You own any data about a user provided to you by Clef and use of that data is subject to your Terms of Service and Privacy Policy.

= Can existing users on my site sign in with Clef? =

WPClef matches users to the WordPress accounts by matching email addresses. As long as your users are registered with your WordPress site and Clef using the same email address, they can start using Clef right away.

== Screenshots ==

1. WordPress login form showing the Clef login graphic.

2. Location of the Clef configuration screen.

3. Configuring Clef in WordPress.

== Changelog ==

= 1.0 =
* Initial release. Supports logging in using the Clef app.

== Upgrade Notice ==

== Roadmap ==

* **Allow registering using Clef**. Will only work on sites where anyone can register, and accounts will be assigned the default role. The plugin will assign a complex password and email it to the address registered with Clef.

* Translation into other languages
