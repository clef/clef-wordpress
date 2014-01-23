function handleKeys(data) {
    if (!(/https:\/\/clef.io/.test(data.origin))){
        return;
    }
    var appID = data.data.appID,
        appSecret = data.data.appSecret,
        oauthCode = data.data.oauthCode;

    var _ref = jQuery('input[name="wpclef[clef_settings_app_id]"]');
    _ref.val(appID);
    jQuery('input[name="wpclef[clef_settings_app_secret]"]').val(appSecret);
    jQuery('input[name="wpclef[clef_settings_oauth_code]"]').val(oauthCode);
    jQuery('.wrap iframe').hide();
    jQuery('form#wp_clef input[type=submit]').trigger('click');
}

jQuery(document).ready(function() {
    window.addEventListener('message', handleKeys);
});

