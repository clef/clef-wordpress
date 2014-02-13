<script id="multisite-disabled-template" type="text/template">
<?php 
if ($options['is_network_settings']) {
    include CLEF_TEMPLATE_PATH . 'admin/multisite/network-disabled.tpl.php';
}
else {
    include CLEF_TEMPLATE_PATH . 'admin/multisite/single-disabled.tpl.php';

}
?>
</script>

<script id="multisite-enabled-template" type="text/template">
<?php 
if ($options['is_network_settings']) {
    include CLEF_TEMPLATE_PATH . 'admin/multisite/network-enabled.tpl.php';
}
else {
    include CLEF_TEMPLATE_PATH . 'admin/multisite/single-enabled.tpl.php';

}
?>
</script>

