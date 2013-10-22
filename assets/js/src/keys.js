(function($) {
    function handleKeys(data) {
        if (!(/https:\/\/clef.io/.test(data.origin))){
            return;
        }
        var appID = data.data.appID,
            appSecret = data.data.appSecret,
            $idInput = $('input[name="wpclef[clef_settings_app_id]"]'),
            $secretInput = $('input[name="wpclef[clef_settings_app_secret]"]'),
            $submit = $('form#wp_clef input[type=submit]');

        if ($submit.length === 0) {
            $idInput = $('input[name="woocommerce_clef_clef_app_id"]'),
            $secretInput =  $('input[name="woocommerce_clef_clef_app_secret"]'),
            $submit =  $('form#mainform input[type=submit]');
        }

        $idInput.val(appID);
        $secretInput.val(appSecret);
        $submit.trigger('click');
    }

    $(document).ready(function() {
        window.addEventListener('message', handleKeys);
    });
}).call(this, jQuery);



