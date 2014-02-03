(function($) {
  return $(document).ready(function() {
    var $prompt, ajaxData, sending;
    $prompt = $(".clef-badge-prompt");
    ajaxData = {
      action: "clef_badge_prompt"
    };
    sending = false;
    $prompt.find(".add-badge").click(function(e) {
      var data;
      e.preventDefault();
      if (sending) {
        return;
      }
      sending = true;
      data = $.extend({
        enable: "badge"
      }, ajaxData);
      $prompt.slideUp();
      return $.post(ajaxurl, data, (function() {}), "json");
    });
    return $prompt.find(".no-badge, .dismiss").click(function() {
      var data;
      e.preventDefault();
      if (sending) {
        return;
      }
      sending = true;
      data = $.extend({
        disable: true
      }, ajaxData);
      $.post(ajaxurl, data, (function() {}), "json");
      return $prompt.slideUp();
    });
  });
}).call(this, jQuery);
