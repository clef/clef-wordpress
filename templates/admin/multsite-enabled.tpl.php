<div class="wrap">
    <div id="icon-options-general" class="frmicon icon32"><br></div>
    <h2>Clef for Multisite is enabled</h2>
    <p>Your Clef installation is currently managed by your multisite administrator. To use your own Clef settings, please click the button below.</p>
    <form action="admin.php?action=clef_multisite" method="POST">
        <?php wp_nonce_field("clef_multisite") ?>
        <input type="submit" value="Override multisite" class="button-primary">
    </form>
</div>