<?php 
if ($options['isNetworkSettings']) {
    if ($options['isNetworkSettingsEnabled']) {
        include CLEF_TEMPLATE_PATH . 'admin/multisite/network-enabled.tpl.php';
    } else {
        include CLEF_TEMPLATE_PATH . 'admin/multisite/network-disabled.tpl.php';
    }
}
else if ($options['isNetworkSettingsEnabled']) {
    if ($options['isUsingIndividualSettings']) {
        include CLEF_TEMPLATE_PATH . 'admin/multisite/single-disabled.tpl.php';
    } else {
        include CLEF_TEMPLATE_PATH . 'admin/multisite/single-enabled.tpl.php';
    }
}
