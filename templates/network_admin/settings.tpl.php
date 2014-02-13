<div id="clef-settings-container">
    <div class="message"><p></p></div>
    <div id="clef-tutorial" style="display:none;">
        <?php include CLEF_TEMPLATE_PATH . 'admin/tutorial.tpl.php'; ?>
    </div>
    <div id="clef-settings">
        <?php include CLEF_TEMPLATE_PATH . 'admin/form.tpl.php'; ?>
    </div>
</div>
<script type="text/javascript"> var options = <?php echo json_encode($options); ?></script>

