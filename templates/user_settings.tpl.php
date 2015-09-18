<div id="connect-clef-account" class="wp-core-ui">
    <?php include CLEF_TEMPLATE_PATH . 'admin/tutorial.tpl.php'; ?>

    <?php if ($connect_error) { ?>
        <div class="error">
            <p><?php echo $connect_error->get_error_message() ?></p>
        </div>
    <?php }?>

    <div class="settings-section disconnect-clef">
        <h3><?php _e("Disconnect your Clef account", "wpclef"); ?></h3>
        <p><?php _e("You currently have a Clef account connected to this WordPress user. To disconnect this Clef account, click the button below.", "wpclef"); ?></p>
        <p class='bold'><?php _e("Note: if passwords are disabled for all users and you disconnect your Clef account, you won't be able to log in.", "wpclef"); ?></p>
        <a href="#" id="disconnect" class="button button-primary button-hero"><?php _e("Disconnect your Clef account", "wpclef"); ?></a>
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