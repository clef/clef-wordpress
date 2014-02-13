<div class="settings-section">
    <h2><?php _e('Network settings', 'clef'); ?></h2>
    <form action="edit.php?action=clef_multisite" method="POST">
        <div class="inputs-container">
        <p><?php _e('You can let individual sites manage their own Clef configuration if you would like to customize how Clef works on each site.', 'clef') ?></p>
            <?php wp_nonce_field("clef_multisite") ?>
            <div class="input-container">
                <input type="hidden" name="allow_override_form">
                <label for="allow_override"><?php _e('Allow individual sites to manage settings', 'clef'); ?></label>
                <input type="checkbox" name="allow_override" value="<?php echo $options['allow_single_site_settings'] ?>" <?php if ($options['allow_single_site_settings']) { ?> checked="checked" <?php } ?>>
            </div>
            <input type="submit" value="Save" class="button-primary">
        </div>
    </form>

    <form action="edit.php?action=clef_multisite" method="POST">
        <div class="inputs-container">
        <h3><?php _e('Disable network-wide settings', 'clef') ?></h3>
        <p><?php _e('If you disable network-wide settings, individual sites in the network will need to manage their own Clef settings.', 'clef') ?></p>
            <?php wp_nonce_field("clef_multisite") ?>
            <input type="hidden" name="disable" value="1">
            <input type="submit" value="<?php _e('Disable network-wide settings', 'clef'); ?>" class="button-primary ajax-ignore">
        </div>
    </form>
</div>
