<div id="clef-settings-container">
    <div class="message"><p></p></div>

    <?php if ($options['isMultisite']) { ?>
    <div id='clef-multisite-options'>
        <?php include CLEF_TEMPLATE_PATH . 'admin/multisite.tpl.php'; ?>
    </div>
    <?php } ?>

    <?php include CLEF_TEMPLATE_PATH . 'admin/tutorial.tpl.php'; ?>

    <div id="clef-settings">
        <?php include CLEF_TEMPLATE_PATH . 'admin/form.tpl.php'; ?>
    </div>
</div>

<?php include CLEF_TEMPLATE_PATH . 'js-templates/invite.tpl.php'; ?>
<script type="text/javascript">
    var app,
        clefOptions = <?php echo json_encode($options); ?>;

    jQuery(document).ready(function() {
        app = new AppView(_.extend(ajaxSetOpt, clefOptions));
    });
</script>
