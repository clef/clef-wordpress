<div class="clef-button-container">
    <button
        id="clef_pay_button_selector"
        type="submit"
        class="button"
        data-reference="<?php echo $reference ?>"
        data-app-id="<?php echo $app_id; ?>"
        data-redirect-url="<?php echo $redirect_url; ?>"
        data-initiation-url="<?php echo $initiation_url; ?>">
        Checkout with your Phone
    </button>
    <div style="clear:both;"></div>
    <script type="text/javascript" src="<?php echo CLEF_JS_URL ?>"></script>
    <script type="text/javascript">
        var clef_pay_button = new ClefButton({
            el: clef_pay_button_selector,
            pay: true
        });
        clef_pay_button.body = jQuery('body')[0];
        jQuery(clef_pay_button_selector).click(function(e) {
            e.preventDefault();
            clef_pay_button.overlayOpen();
        });
    </script>
</div>