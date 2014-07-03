<div class="clef-login-container">

    <?php if (!$passwords_disabled || $override_key || $invite_code) { ?>

        <?php if ($override_key) { ?>
            <input type="hidden" value="<?php echo $override_key ?>" name="override"/>
        <?php } if ($invite_code) { ?>
            <input type="hidden" value="<?php echo $invite_code ?>" name="clef_invite_code"/>
            <input type="hidden" value="<?php echo $invite_email ?>" name="clef_invite_id"/>
        <?php } ?>

        <div style="position: relative" class="or-container">
            <div style="border-bottom: 1px solid #EEE; width: 90%; margin: 0 5%; z-index: 1; top: 50%; position: absolute;"></div>
            <h2 style="color: #666; margin: 0 auto 20px auto; padding: 3px 0; text-align:center; background: white; width: 20%; position:relative; z-index: 2;">or</h2>
        </div>

    <?php } ?>

    <?php if ($clef_embedded && !$override_key && !$invite_code) { ?>

    <div class="clef-button-container">
        <?php do_action('clef_render_login_button', $redirect_url, $app_id, true); ?>
    </div>

    <?php if (!$passwords_disabled) { ?>
    <div class="close-overlay overlay-text"><?php _e('close and log in with a password'); ?></div>
    <div class="open-overlay overlay-text"><?php _e('show clef login'); ?></div>
    <?php } ?>

    <div class="overlay-info closed">
        <div class="open">?</div>
        <div class="info">
            <p><?php _e('<a href="http://getclef.com">Clef</a> lets you securely log in with your phone.'); ?></p>
            <p><?php _e('To change the way the login form displays with Clef, log in and go to the Clef settings page.'); ?></p>
        </div>
    </div>

    <?php } else { ?>

    <div class="clef-button-container">
        <?php do_action('clef_render_login_button', $redirect_url, $app_id) ?>
    </div>

    <?php } ?>

</div>

