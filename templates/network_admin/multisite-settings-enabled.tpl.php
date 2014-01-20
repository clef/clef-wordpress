<?php settings_errors() ?>
<div class="wrap multisite-settings">
    <div id="icon-options-general" class="frmicon icon32"><br></div>
    <h2><?php _e('Clef for Multisite is Enabled', 'clef'); ?></h2>
    <h3><?php _e('Settings', 'clef'); ?></h3>
    <form action="<?php echo $form_url ?>" method="POST">
        <?php wp_nonce_field("clef_multisite") ?>
        <label for="allow_override"><?php _e('Allow individual multi-sites to override settings', 'clef'); ?></label>
        <input type="hidden" name="allow_override_form">
        <input type="checkbox" name="allow_override" value="<?php echo $allow_override ?>" <?php if ($allow_override) { ?> checked="checked" <?php } ?>>
        <p class="submit">
            <input type="submit" value="Save" class="button-primary">
        </p>
    </form>
    <h3><?php _e('Disable Multsite', 'clef'); ?></h3>
    <p><?php _e('Disabling multisite will force individual sites to set their own settings.', 'clef'); ?></p>
    <form action="<?php echo $form_url ?>" method="POST">
        <?php wp_nonce_field("clef_multisite") ?>
        <input type="hidden" name="disable" value="1">
        <p class="submit">
            <input type="submit" value="<?php _e('Disable multisite', 'clef'); ?>"e" class="button-primary">
        </p>
    </form>
</div>
