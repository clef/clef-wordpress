<div id="connect-clef-account" style="display:none;">
    <?php include CLEF_TEMPLATE_PATH . 'admin/tutorial.tpl.php'; ?>

    <div class="settings-section disconnect-clef" style="display:none;">
        <h3><?php _e("Disconnect your Clef account", "clef"); ?></h3>
        <p><?php _e("You currently have a Clef account connected to this WordPress user. To disconnect this Clef account, click the button below.", "clef"); ?></p>
        <a href="#" id="disconnect" class="button button-primary button-hero"><?php _e("Disconnect your Clef account", "clef"); ?></a>
    </div>
</div>

<script src="<?php echo $options['clefJSURL'] ?>" type="text/javascript"></script>
<script>
    jQuery(document).ready(function() {
        var options = <?php echo json_encode($options); ?>;
        var connect = new ConnectView(_.extend(options, { slideFilterSelector: '.connect' }));
        connect.show()
    });
</script>
