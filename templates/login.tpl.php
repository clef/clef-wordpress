<div class="clef-login-container">

    <?php if (!$passwords_disabled || $override_key || $invite_code || $clef_id) { ?>

        <?php if ($override_key) { ?>
            <input type="hidden" value="<?php echo $override_key ?>" name="override"/>
        <?php } if ($invite_code) { ?>
            <input type="hidden" value="<?php echo $invite_code ?>" name="clef_invite_code"/>
            <input type="hidden" value="<?php echo $invite_email ?>" name="clef_invite_id"/>
        <?php } if ($clef_id) { ?>
            <input type="hidden" value="<?php echo $clef_id ?>" name="clef_id">
        <?php } ?>

        <div style="position: relative" class="or-container">
            <div style="border-bottom: 1px solid #EEE; width: 90%; margin: 0 5%; z-index: 1; top: 50%; position: absolute;"></div>
            <h2 style="color: #666; margin: 0 auto 20px auto; padding: 3px 0; text-align:center; background: white; width: 20%; position:relative; z-index: 2;">or</h2>
        </div>

    <?php } ?>

    <?php if ($clef_embedded && !$override_key && !$invite_code && !$clef_id) { ?>

    <div class="clef-button-container">
        <div class="spinner-container">
            <h2>loading clef login</h2>
            <span class="spinner is-active"></span>
        </div>
        <?php do_action('clef_render_login_button', $redirect_url, $app_id, $clef_embedded); ?>
    </div>
    <noscript>
        <style>
        .clef-login-container { display: none; }
        #login { width: 320px !important; }
        #loginform { height: auto !important; }
        </style>
    </noscript>

    <?php if (!$passwords_disabled) { ?>
    <a class="close-overlay overlay-text" href="?clefup=true"><?php _e('log in with a password', 'wpclef'); ?></a>
    <a class="open-overlay overlay-text" href="?clefup=false"><?php _e('show clef login', 'wpclef'); ?></a>
    <?php } ?>

    <div class="overlay-info closed">
        <div class="open">?</div>
        <div class="info">
            <p><?php _e('<a href="http://getclef.com">Clef</a> lets you securely log in with your phone.', 'wpclef'); ?></p>
            <p><?php _e('To change the way the login form displays with Clef, log in and go to the Clef settings page.', 'wpclef'); ?></p>
        </div>
    </div>

    <?php } else { ?>

    <div class="clef-button-container">
        <?php do_action('clef_render_login_button', $redirect_url, $app_id) ?>
    </div>

    <?php } ?>

</div>

