/*! Clef for WordPress - v1.9.1
 * http://getclef.com
 * Copyright (c) 2014; * Licensed GPLv2+ */
(function(e){e(document).ready(function(){var t=e(".clef-badge-prompt"),a={action:"clef_badge_prompt"},n=!1;t.find(".add-badge").click(function(i){if(i.preventDefault(),!n){n=!0;var c=e.extend({enable:"badge"},a);t.slideUp(),e.post(ajaxurl,c,function(){},"json")}}),t.find(".no-badge, .dismiss").click(function(){var n=e.extend({disable:!0},a);e.post(ajaxurl,n,function(){},"json"),t.slideUp()})})}).call(this,jQuery);