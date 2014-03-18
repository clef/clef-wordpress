
<div id="clef-settings-form">
    <form id="clef-form" action="options.php" method="POST" autocomplete="off">
        <h1 id="clef-settings-header" class="hide-if-js"><?php _e("Clef", "clef"); ?></h1>
        <div id="fb-root"></div>
        <div class="fb-like" data-href="https://www.facebook.com/getclef" data-width="200" data-layout="button_count" data-action="like" data-show-faces="true" data-share="false"></div>
        <a href="https://twitter.com/getclef" class="twitter-follow-button" data-show-count="false" data-dnt="true"><?php printf( __( 'Follow %s', 'clef' ), '@getclef' ); ?></a>
        <script>(function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=241719455859280";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
        <?php settings_fields($form->id); ?>
        <div class="settings-section">
            <div class="password-settings">
                <div class="inputs-container">
                    <h3>Disable passwords</h3>
                    <div class="input-container">
                        <label for="disable_passwords"><?php _e("Disable passwords for Clef users", "clef"); ?></label>
                        <?php $form->getSection('clef_password_settings')->getField('disable_passwords')->render(); ?>
                    </div>
                    <div class="input-container">
                        <label for=""><?php _e("Disable passwords for all users with privileges greater than or equal to ", "clef"); ?></label>
                        <?php $form->getSection('clef_password_settings')->getField('disable_certain_passwords')->render(); ?>
                    </div>
                    <div class="input-container">
                        <label for=""><?php _e("Disable passwords for all users and hide the password login form.", "clef"); ?></label>
                        <?php $form->getSection('clef_password_settings')->getField('force')->render(); ?>
                    </div>
                    <div class="input-container">
                        <label for=""><?php _e("Allow passwords for API (necessary for things like the WordPress mobile app)", "clef"); ?></label>
                        <?php $form->getSection('clef_password_settings')->getField('xml_allowed')->render(); ?>
                    </div>
                </div>
                <div id="login-form-view" class="login"></div>
            </div>
        </div>
        <div class="override-settings settings-section">
           <div class="inputs-container">
                <h3><?php _e("Override settings", "clef"); ?></h3> 
                <p><?php _e("You have disabled passwords for some (or all) users. In case of emergency, you can create a special link where passwords can still be used. This is a good safety precaution.", "clef"); ?></p>
                <div class="input-container">
                    <label for=""><?php echo wp_login_url() ?>?override=</label>
                    <?php $form->getSection('clef_override_settings')->getField('key')->render(array("placeholder" => __( "Enter override key here", 'clef' ))); ?>
                    <a class="generate-override hide-if-no-js"><?php _e("generate a secure override url for me", "clef"); ?></a>
                </div>
               <?php
               	$opts = get_option( 'wpclef' );
               	$css_hide = !empty( $opts['clef_override_settings_key'] ) ? '' : ' hidden'; 
               	$url = !empty( $opts['clef_override_settings_key'] ) ? esc_url( wp_login_url() . '?override=' . $opts['clef_override_settings_key'] ) : '#error';
               ?>
               <div class="override-buttons<?php echo $css_hide; ?>">
                   <a name="override link" class="button button-primary button-hero" href="<?php echo $url; ?>"><?php bloginfo('name', 'display'); ?> <?php _e("Override URL", "clef"); ?></a>
                   <p><?php _e("Drag this your bookmarks bar", "clef"); ?></p>
               </div>
               <?php unset( $opts, $url ); ?>
           </div>
            <div class="preview-container">
                <img src="<?php echo CLEF_URL ?>assets/dist/img/bookmark.png" alt="add to bookmarks bar">
            </div>
        </div>
        <div class="support-settings settings-section">
            <div class="inputs-container">
               <h3><?php _e("Support Clef", "clef"); ?></h3> 
               <p><?php _e("Clef is, and will always be, free for you and your users. We'd really appreciate it if you'd support us (and show visitors they are browsing a secure site) by adding a link to Clef in your site footer!", "clef"); ?></p>
                <div class="input-container">
                    <label for=""><?php _e("Support Clef in your footer", "clef"); ?></label>
                    <?php $form->getSection('support_clef')->getField('badge')->render(); ?>
                </div>
                <a href="#" class="show-support-html hide-if-no-js"><?php _e( 'I want to add the badge or link elsewhere', 'clef' ); ?></a>
                <span class="hide-if-js"><b><?php _e( 'I want to add the badge or link elsewhere', 'clef' ); ?>:</b></span>
                <div class="support-html-container hide-if-js">
                    <h4><?php _e( 'Copy this HTML where you want to add the badge', 'clef' ); ?></h4>
                    <textarea class="ajax-ignore"><?php echo esc_textarea( '<a href="https://bit.ly/wordpress-login-clef" class="clef-badge pretty">'.__( 'WordPress Login Protected by Clef', 'clef' ).'</a>'); ?></textarea>
                    <h4><?php _e( 'Copy this HTML where you want to add the link', 'clef' ); ?></h4>
                    <textarea class="ajax-ignore"><?php echo esc_textarea('<a href="https://bit.ly/wordpress-login-clef" class="clef-badge">'.__( 'WordPress Login Protected by Clef', 'clef' ).'</a>'); ?></textarea>
                </div>
            </div>
            <div class="preview-container hide-if-no-js">
               <div class="ftr-preview">
                   <h4><?php _e("Preview of your support", "clef"); ?></h4> 
                   <a href="https://bit.ly/wordpress-login-clef" class="clef-badge pretty" ><?php _e("WordPress Login Protected by Clef", "clef"); ?></a>
                   <span class="hide-if-js">
	                   <br /><?php _e( 'or', 'clef' ); ?><br />
	                   <a href="https://bit.ly/wordpress-login-clef" class="clef-badge" ><?php _e("WordPress Login Protected by Clef", "clef"); ?></a>
                   </span>
               </div>
            </div>
        </div>
        <div id="invite-users-settings" class="settings-section"></div>
        <?php include CLEF_TEMPLATE_PATH . 'pro/form.tpl.php'; ?>
        <div class="clef-settings settings-section">
            <div class="inputs-container">
                <h3><?php _e("Clef API Settings", "clef"); ?></h3>
                <p><?php _e("These keys connect your WordPress site to the Clef application you log in to. For more advanced settings, visit the <a href='http://getclef.com/developer'>Clef developer site</a>.", "clef"); ?></p>
                <div class="input-container">
                    <label for=""><?php _e("Application ID", "clef"); ?></label>
                    <?php $form->getSection('clef_settings')->getField('app_id')->render(); ?>
                </div>
                <div class="input-container">
                    <label for=""><?php _e("Application Secret", "clef"); ?></label>
                    <?php $form->getSection('clef_settings')->getField('app_secret')->render(); ?>
                </div>
                <div class="input-container">
                    <label for=""><?php _e("Allow visitors to your site to register with Clef"); ?></label>
                    <?php $form->getSection('clef_settings')->getField('register')->render(); ?>
                </div>
            </div>
        </div>
        <?php submit_button(); ?>
    </form>

</div>

<script id="form-template" type="text/template">
    <h4><?php _e("Preview of your login form", "clef"); ?></h4>
    <div name="loginform" id="loginform">
        <p>
            <label for="user_login"><?php _e("Username"); ?><br>
            <input type="text" id="user_login" class="ajax-ignore input" value="" size="20"></label>
        </p>
        <p>
            <label for="user_pass"><?php _e("Password"); ?><br>
            <input type="text" id="user_pass" class="ajax-ignore input" value="" size="20"></label>
        </p>
        <div style="position: relative">
            <div style="border-bottom: 1px solid #EEE; width: 90%; margin: 0 5%; z-index: 1; top: 50%; position: absolute;"></div>
            <h2 style="color: #666; margin: 0 auto 20px auto; padding: 3px 0; text-align:center; background: white; width: 20%; position:relative; z-index: 2;"><?php _e("or", "clef"); ?></h2>
        </div>
        <div class="clef-button" >
            <img src="<?php echo CLEF_URL ?>assets/dist/img/button.png" alt="clef button">
        </div>
        <p class="forgetmenot"><label for="rememberme"><input name="rememberme" type="checkbox" class="ajax-ignore" id="rememberme" value="forever"> <?php _e("Remember Me"); ?></label></p>
        <p class="submit">
            <input type="submit" id="wp-submit" class="ajax-ignore button button-primary button-large" value='<?php _e("Log In"); ?>'>
        </p>
    </div>
</script>
