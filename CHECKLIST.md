# Full Features Test Checklist
This file presents a master list of WP Clef’s features to facilitate systematic testing before major releases. It is designed to utilize GitHub’s MarkDown checklists. So, to run a full test, copy-n-paste the raw contents of this file into a [new GitHub Issue](https://github.com/clef/wordpress/issues/new). Then check the boxes as you complete the steps.

**Prerequisites**

1. Love.
1. Time.
 1. Basic testing (~ 5-min. run time): requires a WordPress single-site install with at least one user account for [each role](http://codex.wordpress.org/Roles_and_Capabilities).
 1. Whole-hog, full-boom testing (~ ∞-min. run time): requires bastic testing prereqs plus two WordPress multi-site installs, one for shared domain setups and one for custom domain setups. All super admin and sub-sites should have at least one active user account for [each role](http://codex.wordpress.org/Roles_and_Capabilities).
1. Cue theme music [track 1](http://www.youtube.com/watch?v=X5AfjAXcBXY), [track 2](http://www.youtube.com/watch?v=Gl83mI69nX4), [track 3](http://www.youtube.com/watch?v=Csy2XRlWMWE), and smile while pondering how fresh-n-clean Clef is. Awwww yeeeeah.

**General Legend:**
- SW: Setup Wizard
- WP: WordPress
- WPC: WP Clef (i.e., the Clef plugin for WordPress)


## Uninstall Process
1. Deactivate plugin.
 - [ ] WPC is deactivated, and normal password log in form appears on wp-login.php.
 - [ ] WPC’s settings are saved in the database (can verify via re-activation: pre-deactivation settings should re-appear).
1. Remove previous version of WPC via WP’s plugin uninstaller to ensure a clean install.
 - [ ] WPC’s files are deleted.
 - [ ] WPC’s database settings are deleted.

## Install Process
### Activation
1. Clone the repository and checkout the appropriate branch
 1. `git clone git://github.com/clef/wordpress.git wpclef`
 1. `cd wpclef`
 1. `git checkout [INSERT TESTING BRANCH NAME]`
- [ ] Activate WPC via WP’s Dashboard > Plugins > Installed Plugins
- [ ] SW loads automatically

### Setup Wizard
- [ ] "Skip setup" link takes you immediately to settings page

#### (A) SW State 1: Not logged in to Clef
- [ ] "Get started" takes you to Clef Wave screen

 - [ ] Text Clef App link successfully.
 - [ ] Sync Clef Wave and arrive at "One more click!" screen. 

#### (B) SW State 2: Logged in to Clef
- [ ] "Get started" takes you to "One-click setup!" screen.

#### (C) SW Tests for Both States
- [ ] Execute "one-click setup" and arrive at "Invite" screen.

 - [ ] Send invite e-mail to Everyone.
 - [ ] Send invite e-mail to roles >= Contributor.
 - [ ] Send invite e-mail to roles >= Author.
 - [ ] Send invite e-mail to roles >= Editor.
 - [ ] Send invite e-mail to roles >= Administrator.
 - [ ] Send invite e-mail to roles >= Super Administrator.
 - [ ] E-mail preview text matches actual e-mail. 

- [ ] Arrive at "3 tips" screen after send invite or skip invite.

 - [ ] "Go to Clef Settings" loads settings page with graceful slide up.
 
### Setup Wizard Multi-Site Iterations

#### State 1: Network-wide WPC install on shared domain name
- [ ] Run SW tests (A), (B), and (C) on Super Admin site.
- [ ] Run SW tests (A), (B), and (C) on one sub-site.

#### State 2: Network-wide WPC install with custom domain names
- [ ] Run SW tests (A), (B), and (C) on Super Admin site.
- [ ] Run SW tests (A), (B), and (C) on one sub-site.

#### State 3: Site-specific install on shared domain name
- [ ] Run SW tests (A), (B), and (C) on Super Admin site.
- [ ] Run SW tests (A), (B), and (C) on one sub-site.

#### State 4: Site-specific install on shared domain name
- [ ] Run SW tests (A), (B), and (C) on Super Admin site.
- [ ] Run SW tests (A), (B), and (C) on one sub-site.

## Connect Clef Account Actions
- [ ] Selecting “Disconnect Clef account”
 - returns success notification
 - loads SW automatically
- [ ] Selecting “Get Started” loads “Connect your Clef account” screen
- [ ] Text the download link to your phone.
- [ ] CB loads Clef Wave and syncs WP user account.
- [ ] Completing the SW sends you to the Dashboard.

## Password Settings and Login Actions
**WP-Login.php Legend**
- CA: Clef App
- CB: Clef button (i.e., “Log in w/ your phone”)
- LE: "Lost your password?" e-mail
- LF: "Lost your password?" form (i.e., wp-login.php?action=lostpassword)
- LL: "Lost your password?" link
- PF: Password form (i.e., the ordinary user/pass form displayed at wp-login.php)

**Settings Page Legend**
- P1: Disable passwords for Clef users
- P2: Disable passwords for all users with roles greater than or equal to
- P3: Disable passwords for all users and hide the password login form
- P4: Allow passwords for API
- O1: Override key
- O2: Generate secure override url link
- O3: Override url button
- O4: Override preview
- SS: "Setting saved" AJAX notification (appears then fades).
- XT: XML-RPC Test. Run this from command line (HT: Jesse): `curl --data '<?xml version="1.0"?><methodCall><methodName>wp.getOptions</methodName><params><param><value><i4>1</i4></value></param><param><value><string>USERNAME</string></value></param><param><value><string>PASSWORD</string></value></param></params></methodCall>' http://YOURWORDPRESSITE/xmlrpc.php`

Start the following tests from fresh install state (i.e., all settings except API keys should be null, false, or “disabled”).

### Disable passwords: P1 = true, P2–P4 = null.
- [ ] SS fades.
- [ ] P4 appears.
- [ ] Override settings section appears.
 - O1 = null.
 - O4 = hidden.
- [ ] selecting 02
 - shows SS,
 - inserts key in O1,
 - and shows O4.

1. Non-Clef user login
 - [ ] wp-login.php displays PF + CB with LL.
 - [ ] Log in via PF.

1. Non-Clef user reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.
 
1. Clef user login
 - [ ] wp-login.php displays PF + CB with LL.
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override.
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Clef user reset password via LF
 - [ ] Disabled. Returns “password resets have been disabled....”

1. XML-RPC login
 - [ ] Disabled. XT returns “passwords have been disabled....”

### Disable passwords: set P2 = not null, P3–P4 = null.
- [ ] wp-login.php displays PF + CB with LL.

#### When any non-null P2 option is selected
- [ ] SS fades.
- [ ] P4 appears.
- [ ] Override settings section appears.
 - O1 = null.
 - O4 = hidden.
- [ ] selecting 02
 - shows SS,
 - inserts key in O1,
 - and shows O4.

#### P2 = “Contributor”
1. Subscriber role login
 - [ ] Log in via PF.

1. Subscriber role reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.

1. Contributor role login
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override.
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Contributor role reset password via LF
 - [ ] Disabled. Returns “password resets have been disabled....”

1. Author role login
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override.
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Author role reset password via LF
 - [ ] Disabled. Returns “password resets have been disabled....”

1. Editor role login
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override. 
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Editor role reset password via LF
 - [ ] Disabled. Returns “password resets have been disabled....”

1. Administrator role login
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override.
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Administrator role reset password via LF
 - [ ] Disabled. Returns “password resets have been disabled....”

1. Super Administrator role login
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override.
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Super Administrator role reset password via LF
 - [ ] Disabled. Returns “password resets have been disabled....”

1. XML-RPC login
 - [ ] Disabled. XT returns “passwords have been disabled....”

#### P2 = “Author”
1. Subscriber role login
 - [ ] Log in via PF.

1. Subscriber role reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.

1. Contributor role login
 - [ ] Log in via PF.

1. Contributor role reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.

1. Author role login
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override.
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Author role reset password via LF
 - [ ] Disabled. Returns “password resets have been disabled....”

1. Editor role login
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override.
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Editor role reset password via LF
 - [ ] Disabled. Returns “password resets have been disabled....”

1. Administrator role login
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override.
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Administrator role reset password via LF
 - [ ] Disabled. Returns “password resets have been disabled....”

1. Super Administrator role login
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override.
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Super Administrator role reset password via LF
 - [ ] Disabled. Returns “password resets have been disabled....”

1. XML-RPC login
 - [ ] Disabled. XT returns “passwords have been disabled....”

#### P2 = “Editor”
1. Subscriber role login
 - [ ] Log in via PF.

1. Subscriber role reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.

1. Contributor role login
 - [ ] Log in via PF.

1. Contributor role reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.

1. Author role login
 - [ ] Log in via PF.

1. Author role reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.

1. Editor role login
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override.
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Editor role reset password via LF
 - [ ] Disabled. Returns error notification.

1. Administrator role login
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override.
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Administrator role reset password via LF
 - [ ] Disabled. Returns error notification.

1. Super Administrator role login
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override.
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Super Administrator role reset password via LF
 - [ ] Disabled. Returns error notification.

1. XML-RPC login
 - [ ] Disabled. XT returns “passwords have been disabled....”

#### P2 = “Administrator”
1. Subscriber role login
 - [ ] Log in via PF.

1. Subscriber role reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.

1. Contributor role login
 - [ ] Log in via PF.

1. Contributor role reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.

1. Author role login
 - [ ] Log in via PF.

1. Author role reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.

1. Editor role login
 - [ ] Log in via PF.

1. Editor role reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.

1. Administrator role login
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override.
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Administrator role reset password via LF
 - [ ] Disabled. Returns error notification.

1. Super Administrator role login
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override.
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Super Administrator role reset password via LF
 - [ ] Disabled. Returns error notification.

1. XML-RPC login
 - [ ] Disabled. XT returns “passwords have been disabled....”

#### P2 = “Super Administrator”
1. Subscriber role login
 - [ ] Log in via PF.

1. Subscriber role reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.

1. Contributor role login
 - [ ] Log in via PF.

1. Contributor role reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.

1. Author role login
 - [ ] Log in via PF.

1. Author role reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.

1. Editor role login
 - [ ] Log in via PF.

1. Editor role reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.

1. Administrator role login
 - [ ] Log in via PF.

1. Administrator role reset password
 - [ ] LF sends password reset e-mail.
 - [ ] Set new password.

1. Super Administrator role login
 - [ ] Log in via PF returns “passwords have been disabled....”
 - [ ] Log in via override.
 - [ ] Log in via CB.
 - [ ] Log out via CA.

1. Super Administrator role reset password via LF
 - [ ] Disabled. Returns error notification.

1. XML-RPC login
 - [ ] Disabled. XT returns “passwords have been disabled....”

### Disable passwords: set P3 = true, P4 = null.
- [ ] SS fades.
- [ ] P4 appears.
- [ ] Override settings section appears.
 - O1 = null.
 - O4 = hidden.
- [ ] selecting 02
 - shows SS,
 - inserts key in O1,
 - and shows O4.

- [ ] wp-login.php displays CB only (no PF and no LL).
- [ ] LF disabled for all users. Returns error notification.
- [ ] Log in via override.
- [ ] Log in via CB.
- [ ] Log out via CA.

### Disable passwords: set P4 = true (assumes P1, P2, and/or P3 are not null).
- [ ] SS fades.
- [ ] Log in via XML-RPC. XT returns success notification.

## Support Clef settings
- [ ] Set to “Badge”
 - flashes SS
 - and prints `img` and functioning `a` in site footer.

- [ ] Set to “Link”
 - Flashes SS
 - Prints functioning `a` in site footer.

- [ ] Set to “Disabled”
 - Flashes SS
 - Removes `img` and/or `a` from site footer.

### Support Clef timed pop ups
1. State 1a: after first login via CB.
 - [ ] Selecting “Badge” prints `img` and functioning `a` in site footer and saves the setting (verify on setting page).

1. State 1b: after first login via CB.
 - [ ] Selecting “Link” prints functioning `a` in site footer and saves the setting (verify on setting page).

## Browser Iterations
### Setup Wizard
- [ ] Successful run through in Chrome.
- [ ] Successful run through in FireFox.
- [ ] Successful run through in Safari.
- [ ] Successful run through in IE.

### AJAX-Powered Settings Page
- [ ] Functioning in Chrome.
- [ ] Functioning in FireFox.
- [ ] Functioning in Safari.
- [ ] Functioning in IE.

## Translations
- [ ] All new translatable text blocks placed in appropriate wrapper functions.

## BruteProtect
- [ ] One-click install & activate successful.

## The End
- [ ] To the precious few who make it this far: [treat yo self](http://www.youtube.com/watch?v=-K4if6QkDbo) to a :boom:.
