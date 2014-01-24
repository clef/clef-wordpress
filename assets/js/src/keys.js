(function($) {
    function handleKeys(data) {
        if (!(/http:\/\/arya.dev:5000/.test(data.origin))){
            return;
        }
        var appID = data.data.appID,
            appSecret = data.data.appSecret,
            oauthCode = data.data.oauthCode;

        var _ref = jQuery('input[name="wpclef[clef_settings_app_id]"]');
        _ref.val(appID);
        $('input[name="wpclef[clef_settings_app_secret]"]').val(appSecret);
        $('input[name="wpclef[clef_settings_oauth_code]"]').val(oauthCode);
        $('.wrap iframe').hide();
        $('form#wp_clef input[type=submit]').trigger('click');
    }

    $(document).ready(function() {
        window.addEventListener('message', handleKeys);

        // show logout error message after an amount of time
        setTimeout(function() {
            $('.logout-hook-error').slideDown();
        }, 20000);
    });

}).call(this, jQuery);

