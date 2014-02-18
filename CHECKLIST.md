# Features Test Checklist
This file presents a master list of WP Clef’s features to facilitate systematic testing before new releases.

Abbreviations:
- SW = Setup Wizard
- WP = WordPress
- WPC = WP Clef (i.e., the Clef plugin for WordPress)
- :boom: = boom = awwww yeeeeah = [cue theme music](http://www.youtube.com/watch?v=X5AfjAXcBXY), RT [this](https://twitter.com/landakram/status/434091346916167680), smile and remember how fresh-n-clean Clef is

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
- [ ] Arrive at "Get Waltz" screen.

 - [ ] "Try Waltz" loads http://getwaltz.com in new tab.
 - [ ] "Go to Clef Settings" loads settings page with graceful slide up.
 
### Setup Wizard Multi-Site Iterations
#### State 1: Network-wide WPC install on shared domain name
- [ ] SW tests (A), (B), and (C) performed on Super Admin site and one sub-site.
#### State 2: Network-wide WPC install with custom domain names
- [ ] SW tests (A), (B), and (C) performed on Super Admin site and one sub-site.
#### State 3: Site-specific install on shared domain name
- [ ] SW tests (A), (B), and (C) performed on Super Admin site and one sub-site.
#### State 4: Site-specific install on shared domain name
- [ ] SW tests (A), (B), and (C) performed on Super Admin site and one sub-site. 

## Password Settings

## Browser Iterations

## Translations