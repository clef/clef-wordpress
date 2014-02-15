<?php include CLEF_TEMPLATE_PATH . 'admin/tutorial.tpl.php'; ?>

<script src="<?php echo $options['clefJSURL'] ?>" type="text/javascript"></script>
<script>
    jQuery(document).ready(function() {
        var options = <?php echo json_encode($options); ?>;
        var tutorial = new ConnectTutorialView(_.extend(options, { slideFilterSelector: '.connect' }));
        tutorial.render();
    });
</script>
