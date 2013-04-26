jQuery(document).ready(function() {
    window.addEventListener('message', handleKeys);
});

function handleKeys(data) {
    if (!(/https:\/\/clef.io/.test(data.origin))){
        return;
    }
    var appID = data.data.appID,
        appSecret = data.data.appSecret;

    var _ref = jQuery('input[name="wpclef[clef_settings_app_id]"]');
    _ref.val(appID);
    jQuery('input[name="wpclef[clef_settings_app_secret]"]').val(appSecret);
    jQuery('.wrap iframe').hide();
    jQuery('form#wp_clef input[type=submit]').trigger('click');
}