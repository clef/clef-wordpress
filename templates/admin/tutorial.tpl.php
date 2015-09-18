<div id="clef-tutorial">
    <div class="clef-tutorial-container">

        <div class="sub intro connect setup">
            <h1><?php _e("Clef makes logging into your site safer and easier. <br> It's <b>free</b>, and getting set up takes <b>30 seconds</b>.", "wpclef"); ?></h1>
            <div class="next button button-hero button-primary center"><?php _e("Get Started", "wpclef"); ?></div>
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
            <a href="#" class='skip done'><?php _e("Skip setup, I already have Clef API keys", "wpclef"); ?></a>
        </div>

        <div class="sub sync setup">
            <div class="spinner-container">
                <span class="spinner"></span>
                <h2>loading</h2>
            </div>
            <iframe class="setup" src="" frameborder="0"></iframe>
        </div>

        <div class="sub invite setup">
            <h1><?php _e("Want to invite your users to Clef?", "wpclef"); ?></h1>
            <div class="invite-users-container"></div>
        </div>

        <div class="sub connect login">
            <h1><?php _e("Connect your Clef account", "wpclef"); ?></h1>
            <p><?php _e("To connect, click the button below and sync the Wave. Once you're connected, you'll be able to log in and out of your WordPress site with just your phone.", "wpclef"); ?></p>
            <div class="button-wrapper">
                <div id="clef-button-target"></div>
            </div>
            <h3><?php _e("Don't have the app? Let us text you the download link.", "wpclef"); ?></h3>
            <iframe src="<?php echo CLEF_BASE ?>/iframes/text" frameborder="0"></iframe>
        </div>

        <div class="sub using-clef setup connect">
            <h1><?php _e("3 tips for using Clef", "wpclef"); ?></h1>
            <h3>1. <?php _e("Sync once, log in everywhere.", "wpclef"); ?></h3>
            <p><?php _e("When you scan a Clef Wave, you'll be logged in to all of your sites on that computer. This means you don't have to keep scanning as you browse the web.", "wpclef"); ?></p>
            <h3>2. <?php _e("Log out with your phone.", "wpclef"); ?></h3>
            <p><?php _e("When you want to log out of your sites, click the logout button <b>on your phone</b>. This will log you out of all of your sites and can be done from anywhere (including after you walk away).", "wpclef"); ?></p>
            <h3>3. <?php _e("Lose your device?", "wpclef"); ?></h3>
            <p><?php _e("If you lose your device, don't fret! Just visit <a href='https://getclef.com/lost'>getclef.com/lost</a>, deactivate with your PIN, and reactivate on a new device.", "wpclef"); ?></p>
            <div class="button button-primary button-hero next"><?php _e("Got it!", "wpclef"); ?></div>
        </div>

        <?php if (is_admin()) { ?>
        <?php
            echo ClefUtils::render_template('admin/waltz-prompt.tpl', array(
                "next_href" => '#',
                "next_text" => __("Go to Clef settings", "wpclef"),
                "class" => "setup"
            ));
        ?>

        <?php
            echo ClefUtils::render_template('admin/waltz-prompt.tpl', array(
                "next_href" => admin_url(),
                "next_text" => __("Go to dashboard", "wpclef"),
                "class" => "connect"
            ));
        ?>

        <?php } ?>

    </div>
</div>

