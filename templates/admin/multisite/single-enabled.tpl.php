<form action="admin.php?page=clef&action=clef_multisite" method="POST">
    <div class="settings-section">
        <div class="inputs-container">
            <h2><?php _e('Clef is being managed by multi-site.', 'wpclef'); ?></h2>
            <p><?php _e('Your Clef installation is currently managed by your network administrator. To use your own Clef settings, you can override the network-wide settings below.', 'wpclef'); ?></p>
            <?php wp_nonce_field("clef_multisite") ?>
            <input type="submit" value="<?php _e('Override network settings', 'wpclef'); ?>" class="button-primary">
        </div>
    </div>
</form>
