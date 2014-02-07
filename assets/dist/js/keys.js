(function($) {
  var handleKeys;
  handleKeys = function(data) {
    var appID, appSecret, oauthCode;
    if (!/https:\/\/clef.io/.test(data.origin)) {
      return;
    }
    appID = data.data.appID;
    appSecret = data.data.appSecret;
    oauthCode = data.data.oauthCode;
    $("input[name=\"wpclef[clef_settings_app_id]\"]").val(appID);
    $("input[name=\"wpclef[clef_settings_app_secret]\"]").val(appSecret);
    $("input[name=\"wpclef[clef_settings_oauth_code]\"]").val(oauthCode);
    $(".wrap iframe").hide();
    return $("form#wp_clef input[type=submit]").trigger("click");
  };
  return $(document).ready(function() {
    window.addEventListener("message", handleKeys);
    return setTimeout((function() {
      return $(".logout-hook-error").slideDown();
    }), 20000);
  });
}).call(this, jQuery);
