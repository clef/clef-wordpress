<?php global $clef_button_count;
    if (empty($clef_button_count)) {
        $clef_button_count = 1;
    } else {
        $clef_button_count++;
    }
?>
<div id="clefButton<?php echo $clef_button_count ?>" data-app-id='<?php echo $app_id; ?>' 
    data-redirect-url='<?php echo $redirect_url; ?>'>
</div>
<script data-cfasync="false" type='text/javascript'>
    function renderButton() {
        var button = new ClefButton({el: document.getElementById('<?php echo "clefButton" . $clef_button_count ?>') });
        button.render();
    }

    <?php if ($clef_button_count == 1) { ?>
    var s, r, t, p;
    r = false;
    s = document.createElement('script');
    s.type = 'text/javascript';
    s.src = "<?php echo CLEF_JS_URL ?>";
    s.onload = s.onreadystatechange = function() {
        if (!r && (!this.readyState || this.readyState == 'complete')) {
            r = true;
            renderButton();
        }
    };
    t = document.getElementsByTagName('script')[0];
    p = t.parent || t.parentNode;
    p.insertBefore(s, t);
    <?php } else { ?>
    renderButton();
    <?php } ?>
</script>
