<div class="wrap">
    <div id="icon-options-general" class="frmicon icon32"><br></div>
    <h2>Clef for Multisite is overridden</h2>
    <p>Currently, you've decided to use your own settings for Clef. To re-enabled the settings configured by your Multisite administrator, please click the button below.</p>
    <form action="admin.php?action=clef_multisite" method="POST">
        <?php wp_nonce_field("clef_multisite") ?>
        <input type="submit" value="Enable multisite" class="button-primary">
    </form>
</div>