<form action="edit.php?action=clef_multisite" method="POST">
    <div class="settings-section">
        <div class="inputs-container">
            <h2><?php _e('Enable Clef across the whole network', 'wpclef'); ?></h2>
            <p><?php _e('If you would like to enable Clef across all sites on the network using the options you set here, click the button below.', 'wpclef'); ?></p>
            <?php wp_nonce_field("clef_multisite"); ?>
            <input type="hidden" name="enable" value="1">
            <input type="submit" value="<?php _e('Use network settings everywhere', 'wpclef'); ?>" class="button-primary ajax-ignore">
        </div>
    </div>
</form>
