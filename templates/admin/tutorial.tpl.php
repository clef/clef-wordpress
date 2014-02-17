<div id="clef-tutorial" style="display:none;">
    <div class="clef-tutorial-container">

        <div class="sub intro connect setup">
            <h1><?php _e("Clef makes logging into your site safer and easier. <br> It's <b>free</b>, and getting set up takes <b>30 seconds</b>.", "clef"); ?></h1>
            <div class="next button button-hero button-primary center"><?php _e("Get Started", "clef"); ?></div>
            <div class="quotes">
                <blockquote>
                    "Passwords are <a class="highlight red" href="http://wordpress.com/security">the least secure part</a> of everything you do online."
                    <cite>— WordPress Security Team</cite>
                </blockquote>
                <blockquote>
                    "With Clef, the often painful process of logging into a site feels, admittedly, <a class="highlight green" href="http://bits.blogs.nytimes.com/2013/12/18/new-clef-plug-in-lets-you-forget-about-your-password/">a little bit magical."</a>
                    <cite>— The New York Times</cite>
                </blockquote>
                <blockquote>
                    "Passwordless login with Clef hits <a class="highlight orange" href="http://wptavern.com/password-free-login-with-clef-hits-all-the-high-notes">all the high notes."</a>
                    <cite>— WP Tavern</cite>
                </blockquote>
            </div>
            <a href="#" class='skip done'><?php _e("Skip setup, I already have Clef API keys", "clef"); ?></a>
        </div>        

        <div class="sub sync setup">
            <h1 class='no-user'><?php _e("Sync with the Wave to set up your site", "clef"); ?></h1>
            <div class="user">
                <h1><?php _e("One more click!", "clef"); ?></h1>
                <p><?php _e("We've automatically configured Clef for your site. Click the button below to complete setup!", "clef"); ?></p>
            </div>
            <div class="no-sync">
                <h1><?php _e("One-click setup!", "clef"); ?></h1>
                <p><?php _e("You're already logged in to Clef, so setting up this site is extra easy. Just click the button below and you'll be configured.", "clef"); ?></p>
            </div>
            <iframe class="setup" src="" frameborder="0"></iframe>
        </div>

        <div class="sub invite setup">
            <h1><?php _e("Want to invite your users to Clef?", "clef"); ?></h1>
            <div class="invite-users-container"></div>
        </div>

        <div class="sub connect login">
            <h1><?php _e("Connect your Clef account", "clef"); ?></h1>
            <p><?php _e("To connect, click the button below and sync the Wave. Once you're connected, you'll be able to log in and out of your WordPress site with just your phone.", "clef"); ?></p>
            <div class="button-wrapper">
                <div id="clef-button-target"></div>
            </div>
            <h3><?php _e("Don't have the app? Let us text you the download link.", "clef"); ?></h3>
            <iframe src="<?php echo CLEF_BASE ?>/iframes/text" frameborder="0"></iframe>
        </div>

        <div class="sub using-clef setup connect">
            <h1><?php _e("3 tips for using Clef", "clef"); ?></h1>
            <h3>1. <?php _e("Sync once, log in everywhere.", "clef"); ?></h3>
            <p><?php _e("When you scan a Clef Wave, you'll be logged in to all of your sites on that computer. This means you don't have to keep scanning as you browse the web.", "clef"); ?></p>
            <h3>2. <?php _e("Log out with your phone.", "clef"); ?></h3>
            <p><?php _e("When you want to log out of your sites, click the logout button <b>on your phone</b>. This will log you out of all of your sites and can be done from anywhere (including after you walk away).", "clef"); ?></p>
            <h3>3. <?php _e("Lose your device?", "clef"); ?></h3>
            <p><?php _e("If you lose your device, don't fret! Just visit <a href='https://getclef.com/lost'>getclef.com/lost</a>, deactivate with your PIN, and reactivate on a new device.", "clef"); ?></p>
            <div class="button button-primary button-hero next"><?php _e("Got it!", "clef"); ?></div>
        </div>

        <div class="waltz setup">
            <img src="<?php echo CLEF_URL . "assets/dist/img/waltz.png" ?>" alt="Waltz">
            <h1<?php _e(">Want to use Clef everywhere?", "clef"); ?></h1>
            <p><?php _e("<a href='http://getwaltz.com'>Waltz</a> is a chrome extension that lets you log in to sites like Facebook, Gmail, and Twitter using Clef. It takes 5 seconds to install and will give you the full Clef experience.", "clef"); ?></p>
            <a class="button button-primary button-hero" href="http://getwaltz.com" target="_blank"><?php _e("Try Waltz", "clef"); ?></a>
            <a href="#" class="button button-hero next"><?php _e("Go to Clef settings", "clef"); ?></a>
        </div>
        
        <div class="waltz connect">
            <img src="<?php echo CLEF_URL . "assets/dist/img/waltz.png" ?>" alt="Waltz">
            <h1><?php _e("Want to use Clef everywhere?", "clef"); ?></h1>
            <p><?php _e("Since you use Chrome, you can use Clef on sites like Facebook, Gmail, and Twitter with <a href='http://getwaltz.com'>Waltz</a>. It takes 5 seconds to install and will give you the full Clef experience.", "clef"); ?></p>
            <a class="button button-primary button-hero" href="http://getwaltz.com" target="_blank"><?php _e("Get Waltz", "clef"); ?></a>
            <a href="<?php echo admin_url(); ?>" class="button button-hero"><?php _e("Go to dashboard", "clef"); ?></a>
        </div>

    </div>
</div>

