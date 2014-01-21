<div class="wrap">
    <div id="icon-options-general" class="frmicon icon32"><br></div>
    <h2><?php _e("Clef for Multisite is overridden", 'clef'); ?></h2>
    <p><?php _e("Currently, you've decided to use your own settings for Clef. To re-enabled the settings configured by your Multisite administrator, please click the button below.", 'clef'); ?></p>
    <form action="admin.php?action=clef_multisite" method="POST">
        <?php wp_nonce_field("clef_multisite") ?>
        <input type="submit" value="<?php _e('Enable multisite', 'clef'); ?>" class="button-primary">
    </form>
</div>
