<?php settings_errors() ?>
<div class="wrap">
    <div id="icon-options-general" class="frmicon icon32"><br></div>
    <h2>Enable Clef on Multisites</h2>
    <p>Enabling this will copy your Clef settings to every multisite.
    <form action="<?php echo $form_url ?>" method="POST">
        <?php wp_nonce_field("clef_multisite") ?>
        <input type="hidden" name="enable" value="1">
        <input type="submit" value="Enable multisite" class="button-primary">
    </form>
</div>