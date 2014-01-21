<div class="wrap">
    <div id="icon-options-general" class="frmicon icon32"><br></div>
    <h2><?php _e('Clef for Multisite is enabled', 'clef'); ?></h2>
    <p><?php _e('Your Clef installation is currently managed by your multisite administrator. To use your own Clef settings, please click the button below.', 'clef'); ?></p>
    <form action="admin.php?action=clef_multisite" method="POST">
        <?php wp_nonce_field("clef_multisite") ?>
        <input type="submit" value="<?php _e('Override multisite', 'clef'); ?>" class="button-primary">
    </form>
</div>
