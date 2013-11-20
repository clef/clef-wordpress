<?php settings_errors() ?>
<div class="wrap multisite-settings">
    <div id="icon-options-general" class="frmicon icon32"><br></div>
    <h2>Clef for Multisite is Enabled</h2>
    <h3>Settings</h3>
    <form action="<?php echo $form_url ?>" method="POST">
        <?php wp_nonce_field("clef_multisite") ?>
        <label for="allow_override">Allow individual multi-sites to override settings</label>
        <input type="hidden" name="allow_override_form">
        <input type="checkbox" name="allow_override" value="<?php echo $allow_override ?>" <?php if ($allow_override) { ?> checked="checked" <?php } ?>>
        <p class="submit">
            <input type="submit" value="Save" class="button-primary">
        </p>
    </form>
    <h3>Disable Multsite</h3>
    <p>Disabling multisite will force individual sites to set their own settings.</p>
    <form action="<?php echo $form_url ?>" method="POST">
        <?php wp_nonce_field("clef_multisite") ?>
        <input type="hidden" name="disable" value="1">
        <p class="submit">
            <input type="submit" value="Disable multisite" class="button-primary">
        </p>
    </form>
</div>
