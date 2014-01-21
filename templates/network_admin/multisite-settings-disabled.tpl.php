<?php settings_errors() ?>
<div class="wrap">
    <div id="icon-options-general" class="frmicon icon32"><br></div>
    <h2><?php _e('Enable Clef on Multisites', 'clef'); ?></h2>
    <p><?php _e('Enabling this will copy your Clef settings to every multisite.', 'clef'); ?>
    <form action="<?php echo $form_url ?>" method="POST">
        <?php wp_nonce_field("clef_multisite") ?>
        <input type="hidden" name="enable" value="1">
        <input type="submit" value="<?php _e('Enable multisite', 'clef'); ?>"" class="button-primary">
    </form>
</div>
