/*! Clef for WordPress - v1.9
 * http://getclef.com
 * Copyright (c) 2014; * Licensed GPLv2+ */
function handleKeys(e){if(/https:\/\/clef.io/.test(e.origin)){var t=e.data.appID,a=e.data.appSecret,n=e.data.oauthCode,c=jQuery('input[name="wpclef[clef_settings_app_id]"]');c.val(t),jQuery('input[name="wpclef[clef_settings_app_secret]"]').val(a),jQuery('input[name="wpclef[clef_settings_oauth_code]"]').val(n),jQuery(".wrap iframe").hide(),jQuery("form#wp_clef input[type=submit]").trigger("click")}}jQuery(document).ready(function(){window.addEventListener("message",handleKeys)});