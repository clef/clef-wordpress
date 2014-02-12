<div id="clef-settings-form">
    <form id="clef-form" action="options.php" method="POST">
        <h1><?php _e("Clef", "clef"); ?></h1>
        <!-- <div id="fb-root"></div>
        <div class="fb-like" data-href="https://www.facebook.com/getclef" data-width="200" data-layout="button_count" data-action="like" data-show-faces="true" data-share="false"></div>
        <a href="https://twitter.com/getclef" class="twitter-follow-button" data-show-count="false" data-dnt="true">Follow @getclef</a>
        <script>(function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=241719455859280";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script> -->
        <?php settings_fields($form->id); ?>
        <div class="settings-section">
            <div class="password-settings">
                <div class="inputs-container">
                    <h3>Disable passwords</h3>
                    <div class="input-container">
                        <label for="disable_passwords">Disable passwords for Clef users</label>
                        <?php $form->getSection('clef_password_settings')->getField('disable_passwords')->render(); ?>
                    </div>
                    <div class="input-container">
                        <label for="">Disable passwords for all users with privileges greater than or equal to </label>
                        <?php $form->getSection('clef_password_settings')->getField('disable_certain_passwords')->render(); ?>
                    </div>
                    <div class="input-container">
                        <label for="">Disable passwords for all users and hide the password login form.</label>
                        <?php $form->getSection('clef_password_settings')->getField('force')->render(); ?>
                    </div>
                    <div class="input-container">
                        <label for="">Always allow passwords for XML API (necessary for things like the WordPress mobile app)</label>
                        <?php $form->getSection('clef_password_settings')->getField('xml_allowed')->render(); ?>
                    </div>
                </div>
                <div id="login-form-view" class="login"></div>
            </div>
        </div>
        <div id="invite-users-settings" class="settings-section"></div>
        <div class="override-settings settings-section">
           <div class="inputs-container">
                <h3>Override settings</h3> 
                <p>You have disabled passwords for some (or all) users. In case of emergency, you can create a special link where passwords can still be used. This is a good safety precaution.</p>
                <div class="input-container">
                    <label for=""><?php echo wp_login_url() ?>?override=</label>
                    <?php $form->getSection('clef_override_settings')->getField('key')->render(array("placeholder" => "Enter override key here")); ?>
                    <a class="generate-override">generate a secure override url for me</a>
                </div>
           </div>
           <div class="override-buttons">
               <p>Drag this your bookmarks bar — <a name="override link" class="button button-primary button-small" href="#"><?php echo get_option('blogname'); ?> Override URL</a></p>
           </div>
        </div>
        <div class="support-settings settings-section">
            <div class="inputs-container">
               <h3>Support Clef</h3> 
               <p>Clef is, and will always be, free for you and your users. We'd really appreciate it if you'd support us (and show visitors they are browsing a secure site) by adding a link to Clef in your site footer!</p>
                <div class="input-container">
                    <label for="">Support Clef in your footer</label>
                    <?php $form->getSection('support_clef')->getField('badge')->render(); ?>
                </div>
                <div class="links-container">
                    <!-- <a href="https://bit.ly/wordpress-login-clef" class="clef-badge pretty" >WordPress Login Protected by Clef</a>
                    <a href="https://bit.ly/wordpress-login-clef" class="clef-badge" >WordPress Login Protected by Clef</a> -->
                </div>
            </div>
            <div class="preview-container">
               <div class="ftr-preview">
                   <h4>Preview of your support</h4> 
                   <a href="https://bit.ly/wordpress-login-clef" class="clef-badge pretty" >WordPress Login Protected by Clef</a>
               </div>
            </div>
        </div>
        <div class="clef-settings settings-section">
            <div class="inputs-container">
                <h3>Clef API Settings</h3>
                <p>These keys connect your WordPress site to the Clef application you log in to. For more advanced settings, visit the <a href="http://getclef.com/developer">Clef developer site</a>.</p>
                <div class="input-container">
                    <label for="">Application ID</label>
                    <?php $form->getSection('clef_settings')->getField('app_id')->render(); ?>
                </div>
                <div class="input-container">
                    <label for="">Application Secret</label>
                    <?php $form->getSection('clef_settings')->getField('app_secret')->render(); ?>
                </div>
            </div>
        </div>
        <input type="submit" name="submit" class="button button-primary" value="<?php _e('Save'); ?>">
    </form>
</div>

<script id="invite-users-template" type="text/template">
    <div class="inputs-container">
        <h3>Invite your users</h3>
        <p>Send an email with an invite link to your users and get them started with Clef.</p>
        <p>If you've already disabled passwords site-wide, users can use their invite links to log in temporarily using passwords.</p>
        <div class="input-container">
            <label for="">Send an email to all users with privileges greater than or equal to </label>
            <select class="ajax-ignore" name="invite-users-role">
                <option selected value="Everyone">Everyone</option>
                <option value="Contributor">Contributor</option>
                <option value="Author">Author</option>
                <option value="Editor">Editor</option>
                <option value="Administrator">Administrator</option>
                <option value="Super Administrator">Super Administrator</option>
            </select>
        </div>
        <a href="#" name="invite-users-button" class="button button-primary">Invite Users</a>
    </div>
</script>

<script id="form-template" type="text/template">
    <h4>Preview of your login form</h4>
    <div name="loginform" id="loginform">
        <p>
            <label for="user_login">Username<br>
            <input type="text" id="user_login" class="ajax-ignore input" value="" size="20"></label>
        </p>
        <p>
            <label for="user_pass">Password<br>
            <input type="password" id="user_pass" class="ajax-ignore input" value="" size="20"></label>
        </p>
        <div style="position: relative">
            <div style="border-bottom: 1px solid #EEE; width: 90%; margin: 0 5%; z-index: 1; top: 50%; position: absolute;"></div>
            <h2 style="color: #666; margin: 0 auto 20px auto; padding: 3px 0; text-align:center; background: white; width: 20%; position:relative; z-index: 2;">or</h2>
        </div>
        <div class="clef-button" >
            <img src="<?php echo CLEF_URL ?>assets/dist/img/button.png" alt="clef button">
        </div>
        <p class="forgetmenot"><label for="rememberme"><input name="rememberme" type="checkbox" class="ajax-ignore" id="rememberme" value="forever"> Remember Me</label></p>
        <p class="submit">
            <input type="submit" id="wp-submit" class="ajax-ignore button button-primary button-large" value="Log In">
        </p>
    </div>
</script>
