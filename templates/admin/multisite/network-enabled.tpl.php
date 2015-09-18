<div class="settings-section">
    <form action="edit.php?action=clef_multisite" method="POST">
        <div class="inputs-container">
            <h2><?php _e('Disable network-wide settings', 'wpclef'); ?></h2>
            <p><?php _e('Currently, you control Clef settings for all the sites in your network. If you disable network-wide settings, individual sites in the network will need to manage their own Clef settings.', 'wpclef'); ?></p>
                <?php wp_nonce_field("clef_multisite"); ?>
                <input type="hidden" name="disable" value="1">
                <input type="submit" value="<?php _e('Disable network-wide settings', 'wpclef'); ?>" class="button-primary ajax-ignore">
            </div>
            <div class="inputs-container">
        </div>
    </form>
</div>


<form id="multisite-settings">
    <div class="settings-section">
    <h3><?php _e('Individual site network settings', 'wpclef'); ?></h3>
    <div class="inputs-container">
    <p><?php _e('You can let individual sites manage their own Clef configuration if you would like to customize how Clef works on each site.', 'wpclef') ?></p>
        <?php wp_nonce_field(ClefNetworkAdmin::MULTISITE_SETTINGS_ACTION) ?>
        <div class="input-container">
            <input type="hidden" name="allow_override_form">
            <label for="allow_override"><?php _e('Allow individual sites to manage settings', 'wpclef'); ?></label>
            <input type="checkbox" name="allow_override" value="<?php echo $options['isSingleSiteSettingsAllowed'] ?>" <?php if ($options['isSingleSiteSettingsAllowed']) { ?> checked="checked" <?php } ?>>
        </div>
    </div>
    </div>
</form>
