<div id="clef-profile">
    <div class="class settings-section connect-clef">
        <h4><?php _e("Connect your Clef account", "clef"); ?></h4>
        <?php include CLEF_TEMPLATE_PATH . 'button.tpl.php'; ?>
    </div>

    <div class="settings-section disconnect-clef">
        <h4><?php _e("Disconnect your Clef account", "clef"); ?></h4>
        <p><?php _e("You currently have a Clef account connected to this WordPress user. To disconnect this Clef account, click the button below.", "clef"); ?></p>
        <a href="#" id="disconnect" class="button button-primary"><?php _e("Disconnect your Clef account", "clef"); ?></a>
    </div>
</div>
<link rel="stylesheet" href="<?php echo CLEF_URL ?>/assets/dist/css/profile.min.css">
<script type="text/javascript">
    var profileScriptURL = "<?php echo CLEF_URL ?>assets/dist/js/profile.min.js",
        initialize = function() { 
            profile = new ClefProfile(<?php echo json_encode($options) ?>);
        },
        profile;

    if (typeof ClefProfile === "undefined") {
        jQuery.getScript(profileScriptURL, initialize);
    } else {
        initialize();
    }
</script>