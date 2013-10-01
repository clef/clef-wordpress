<?php settings_errors() ?>
<div class="wrap">
    <div id="icon-options-general" class="frmicon icon32"><br></div>
    <h2>Disable Clef on Multisites</h2>
    <p>Disabling Clef on Multisite will remove global settings from each multisite.<br>
    If a multisite set their Clef settings manually, their configuration will not be changed.</p>
    <form action="<?php echo $form_url ?>" method="POST">
        <?php wp_nonce_field("clef_multisite") ?>
        <input type="submit" value="Disable multisite" class="button-primary">
    </form>
</div>
