<?php 
global $clef_js_included; 
if (!$clef_js_included) { 
    echo '<script data-cfasync="false" src='.CLEF_JS_URL.'></script>';
    $clef_js_included = true;
}
?>
<div class='clef-button' 
    data-app-id='<?php echo $app_id; ?>' 
    data-redirect-url='<?php echo $redirect_url; ?>'>
</div>
<script data-cfasync="false" type='text/javascript'>
    var scripts = document.getElementsByTagName('script'),
        currentScript = scripts[scripts.length - 1],
        el = currentScript.previousElementSibling;
    var button = new ClefButton({el: el});
    button.render();
</script>
