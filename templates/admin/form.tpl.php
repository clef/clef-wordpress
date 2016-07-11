
<div id="clef-settings-form">
    <form id="clef-form" action="options.php" method="POST" autocomplete="off">
        <h1 id="clef-settings-header" class="hide-if-js"><?php _e("Clef", "wpclef"); ?></h1>
        <div id="fb-root"></div>
        <div class="fb-like" data-href="https://www.facebook.com/getclef" data-width="200" data-layout="button_count" data-action="like" data-show-faces="true" data-share="false"></div>
        <a href="https://twitter.com/getclef" class="twitter-follow-button" data-show-count="false" data-dnt="true"><?php printf( __( 'Follow %s', 'wpclef' ), '@getclef' ); ?></a>
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
                    <h3><?php _e("Disable passwords", "wpclef"); ?> <a class="setting-info" href="http://support.getclef.com/article/60-recommended-password-settings-for-clef-wordpress-plugin" target="clef">Learn more about these settings</a></h3>
                    <div class="input-container">
                        <label for="disable_passwords"><?php _e("Disable passwords for Clef users", "wpclef"); ?></label>
                        <?php $form->getSection('clef_password_settings')->getField('disable_passwords')->render(); ?>
                    </div>
                    <div class="input-container">
                        <label for=""><?php _e("Disable passwords for all users with privileges greater than or equal to ", "wpclef"); ?></label>
                        <?php $form->getSection('clef_password_settings')->getField('disable_certain_passwords')->render(); ?>
                    </div>
                    <?php if (property_exists($form->getSection('clef_password_settings'), 'custom_roles')) { ?>
                    <div class="input-container custom-roles">
                        <label class="title"><?php _e("Disable passwords for custom roles"); ?></label>
                        <?php foreach($form->getSection('clef_password_settings')->custom_roles as $role => $role_obj) { ?>
                            <div class="custom-role">
                                <label for=""><?php echo $role_obj['name'] ?></label>
                                <?php $form->getSection('clef_password_settings')->getField('disable_passwords_custom_role_' . $role)->render(); ?>
                            </div>
                        <?php } ?>
                    </div>
                    <?php }?>
                    <div class="input-container">
                        <label for=""><?php _e("Disable passwords for all users and hide the password login form.", "wpclef"); ?></label>
                        <?php $form->getSection('clef_password_settings')->getField('force')->render(); ?>
                    </div>
                    <div class="input-container">
                        <label for=""><?php _e("Allow passwords for API (necessary for things like the WordPress mobile app)", "wpclef"); ?></label>
                        <?php $form->getSection('clef_password_settings')->getField('xml_allowed')->render(); ?>
                    </div>
                    <h3><?php _e("Form style", "wpclef"); ?></h3>
                    <div class="input-container">
                        <label for=""><?php _e("Show Clef wave as primary login option", "wpclef"); ?></label>
                        <?php $form->getSection('clef_form_settings')->getField('embed_clef')->render(); ?>
                    </div>
                </div>
                <div id="login-form-view" class="login"></div>
            </div>
        </div>
        <div class="override-settings settings-section">
           <div class="inputs-container">
                <h3><?php _e("Override URL", "wpclef"); ?> <a class="setting-info" href="http://support.getclef.com/article/11-creating-a-secret-url-where-you-can-log-into-your-wordpress-site-with-a-password" target="clef">Learn more about this setting</a></h3>
                <p><?php _e("You have disabled passwords for some (or all) users. In case of emergency, you can create a special link where passwords can still be used. This is a good safety precaution.", "wpclef"); ?></p>
                <div class="input-container">
                    <label for=""><?php echo wp_login_url() ?>?override=</label>
                    <?php $form->getSection('clef_override_settings')->getField('key')->render(array("placeholder" => __( "Enter override key here", 'wpclef' ))); ?>
                    <a class="generate-override hide-if-no-js"><?php _e("generate a secure override url for me", "wpclef"); ?></a>
                </div>
               <?php
               	$opts = get_option( 'wpclef' );
               	$css_hide = !empty( $opts['clef_override_settings_key'] ) ? '' : ' hidden';
               	$url = ClefInternalSettings::get_instance()->get_override_link();
               ?>
               <div class="override-buttons<?php echo $css_hide; ?>">
                   <a name="override link" class="button button-primary button-hero" href="<?php echo $url; ?>"><?php bloginfo('name', 'display'); ?> <?php _e("Override URL", "wpclef"); ?></a>
                   <p><?php _e("Drag this to your bookmarks bar", "wpclef"); ?></p>
               </div>
               <?php unset( $opts, $url ); ?>
           </div>
            <div class="preview-container">
                <img src="<?php echo CLEF_URL ?>assets/dist/img/bookmark.png" alt="add to bookmarks bar">
            </div>
        </div>
        <div class="support-settings settings-section">
            <div class="inputs-container">
               <h3><?php _e("Support Clef", "wpclef"); ?></h3>
               <p><?php _e("Clef is, and will always be, free for you and your users. We'd really appreciate it if you'd support us (and show visitors they are browsing a secure site) by adding a link to Clef in your site footer!", "wpclef"); ?></p>
                <div class="input-container">
                    <label for=""><?php _e("Support Clef in your footer", "wpclef"); ?></label>
                    <?php $form->getSection('support_clef')->getField('badge')->render(); ?>
                </div>
                <a href="#" class="show-support-html hide-if-no-js"><?php _e( 'I want to add the badge or link elsewhere', 'wpclef' ); ?></a>
                <span class="hide-if-js"><b><?php _e( 'I want to add the badge or link elsewhere', 'wpclef' ); ?>:</b></span>
                <div class="support-html-container hide-if-js">
                    <h4><?php _e( 'Copy this HTML where you want to add the badge', 'wpclef' ); ?></h4>
                    <textarea class="ajax-ignore"><?php echo esc_textarea(ClefUtils::render_template('badge.tpl', array("pretty" => true)))?></textarea>
                    <h4><?php _e( 'Copy this HTML where you want to add the link', 'wpclef' ); ?></h4>
                    <textarea class="ajax-ignore"><?php echo esc_textarea(ClefUtils::render_template('badge.tpl', array("pretty" => false))); ?></textarea>
                </div>
            </div>
            <div class="preview-container hide-if-no-js">
               <div class="ftr-preview">
                   <h4><?php _e("Preview of your support", "wpclef"); ?></h4>
                   <a href="https://bit.ly/wordpress-login-clef" class="clef-badge pretty" ><?php _e("WordPress Login Protected by Clef", "wpclef"); ?></a>
                   <span class="hide-if-js">
	                   <br /><?php _e( 'or', 'wpclef' ); ?><br />
	                   <a href="https://bit.ly/wordpress-login-clef" class="clef-badge" ><?php _e("WordPress Login Protected by Clef", "wpclef"); ?></a>
                   </span>
               </div>
            </div>
        </div>
        <div id="invite-users-settings" class="settings-section"></div>
        <?php include CLEF_TEMPLATE_PATH . 'pro/form.tpl.php'; ?>
        <div id="registration-settings" class="settings-section">
           <div class="inputs-container">
                <h3><?php _e("Register with your phone", "wpclef"); ?></h3>
                <p><?php _e("Register new users with the Clef mobile app. The <strong>Membership: anyone can register</strong> setting also must be enabled in WordPress's <a href='".admin_url('options-general.php')."'><strong>General Settings</strong></a>.", "wpclef"); ?></p>
                <div class="input-container">
                    <label for=""><?php _e("Allow visitors to your site to register with Clef", "wpclef"); ?></label>
                    <?php $form->getSection('clef_settings')->getField('register')->render(); ?>
                </div>
            </div>
        </div>
        <div id="shortcode-settings" class="settings-section">
           <div class="inputs-container">
                <h3><?php _e("Shortcode support", "wpclef"); ?> <a class="setting-info" href="http://support.getclef.com/article/56-how-do-i-use-the-clef-login-shortcode" target="clef">Learn more about this setting</a></h3>
                <p><?php _e("Use the <code>[clef_render_login_button]</code> or <br /><code>[clef_render_login_button embed=true]</code> shortcodes on a custom login page.", "wpclef"); ?></p>
                <p><?php _e("Enabling shortcode support means that the OAuth2 state parameter cookie (i.e., <code>wordpress_clef_state</code>) is set on every request for all users including anonymous users browsing the front end. Thus server-side caches such as Varnish may lose the ability to provide full-page caching to anonymous users.", "wpclef"); ?></p>
                <div class="input-container">
                    <label for=""><?php _e("Enable the Clef login shortcode", "wpclef"); ?></label>
                    <?php $form->getSection('shortcode_settings')->getField('shortcode')->render(); ?>
                </div>
            </div>
        </div>
        <div class="clef-settings settings-section">
            <div class="inputs-container">
                <h3><?php _e("Clef API Settings", "wpclef"); ?></h3>
                <p><?php _e("These keys connect your WordPress site to the Clef application you log in to. For more advanced settings, visit the <a href='http://getclef.com/developer'>Clef developer site</a>.", "wpclef"); ?></p>
                <div class="input-container">
                    <label for=""><?php _e("Application ID", "wpclef"); ?></label>
                    <?php $form->getSection('clef_settings')->getField('app_id')->render(); ?>
                </div>
                <div class="input-container">
                    <label for=""><?php _e("Application Secret", "wpclef"); ?></label>
                    <?php $form->getSection('clef_settings')->getField('app_secret')->render(); ?>
                </div>
            </div>
        </div>
        <div class="clef-settings clef-settings__buttons">
            <?php submit_button("Save settings", "primary clef-settings__saveButton"); ?>
            <?php submit_button("Reset Application ID and Secret", "delete clef-settings__resetButton"); ?>
        </div>
    </form>

</div>

<script id="form-template" type="text/template">
    <h4><?php _e("Preview of your login form", "wpclef"); ?></h4>
    <div name="loginform" id="loginform">
        <p>
            <label for="user_login"><?php _e("Username"); ?><br>
            <input type="text" id="user_login" class="ajax-ignore input" value="" size="20"></label>
        </p>
        <p>
            <label for="user_pass"><?php _e("Password"); ?><br>
            <input type="text" id="user_pass" class="ajax-ignore input" value="" size="20"></label>
        </p>
        <div class="or-container">
            <div style="position: relative">
                <div style="border-bottom: 1px solid #EEE; width: 90%; margin: 0 5%; z-index: 1; top: 50%; position: absolute;"></div>
                <h2 style="color: #666; margin: 0 auto 20px auto; padding: 3px 0; text-align:center; background: white; width: 20%; position:relative; z-index: 2;"><?php _e("or", "wpclef"); ?></h2>
            </div>
        </div>
        <div class="clef-button" >
            <img src="<?php echo CLEF_URL ?>assets/dist/img/button.png" alt="clef button">
        </div>
        <div class="clef-overlay">
            <img src="<?php echo CLEF_URL ?>assets/dist/img/overlay.png" alt="clef overlay">
            <div class="close-overlay overlay-text"><?php _e('log in with a password', "wpclef"); ?></div>
        </div>
        <p class="forgetmenot"><label for="rememberme"><input name="rememberme" type="checkbox" class="ajax-ignore" id="rememberme" value="forever"> <?php _e("Remember Me"); ?></label></p>
        <p class="submit">
            <input type="submit" id="wp-submit" class="ajax-ignore button button-primary button-large" value='<?php _e("Log In"); ?>'>
        </p>
    </div>
</script>
