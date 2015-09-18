<form action="admin.php?page=clef&action=clef_multisite" method="POST">
    <div class="settings-section">
        <div class="inputs-container">
            <h2><?php _e("You're overriding multi-site settings.", 'wpclef'); ?></h2>
            <p><?php _e("You've decided to manage your own settings for Clef. To re-enabled the settings configured by your site's network administrator, please click the button below.", 'wpclef'); ?></p>
            <?php wp_nonce_field("clef_multisite") ?>
            <input type="submit" value="<?php _e('Use network settings', 'wpclef'); ?>" class="button-primary">
        </div>
    </div>
</form>

