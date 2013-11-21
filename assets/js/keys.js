/*! Clef for WordPress - v1.8.1.1
 * http://getclef.com
 * Copyright (c) 2013; * Licensed GPLv2+ */
function handleKeys(e){if(/https:\/\/clef.io/.test(e.origin)){var t=e.data.appID,a=e.data.appSecret,n=jQuery('input[name="wpclef[clef_settings_app_id]"]');n.val(t),jQuery('input[name="wpclef[clef_settings_app_secret]"]').val(a),jQuery(".wrap iframe").hide(),jQuery("form#wp_clef input[type=submit]").trigger("click")}}jQuery(document).ready(function(){window.addEventListener("message",handleKeys)});