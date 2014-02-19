<?php if (!$passwords_disabled || $override_key || $invite_code) { ?>
    <?php if ($override_key) { ?>
    <input type="hidden" value="<?php echo $override_key ?>" name="override"/>
    <?php } ?>
    <?php if ($invite_code) { ?>
    <input type="hidden" value="<?php echo $invite_code ?>" name="clef_invite_code"/>
    <input type="hidden" value="<?php echo $invite_email ?>" name="clef_invite_id"/>
    <?php } ?>
    <div style="position: relative">
        <div style="border-bottom: 1px solid #EEE; width: 90%; margin: 0 5%; z-index: 1; top: 50%; position: absolute;"></div>
        <h2 style="color: #666; margin: 0 auto 20px auto; padding: 3px 0; text-align:center; background: white; width: 20%; position:relative; z-index: 2;">or</h2>
    </div>
<?php } ?>
<div class="clef-button-container">
    <?php do_action('clef_render_login_button', $redirect_url, $app_id) ?>
</div>
