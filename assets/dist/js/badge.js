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
      data = {};
      $prompt.find('input').each(function() {
        return data[$(this).attr('name')] = $(this).val();
      });
      data.enable = 'badge';
      $.extend(data, ajaxData);
      $prompt.slideUp();
      return $.post(ajaxurl, data, (function() {}));
    });
    return $prompt.find(".no-badge, .dismiss").click(function(e) {
      var data;
      e.preventDefault();
      if (sending) {
        return;
      }
      sending = true;
      data = {};
      $prompt.find('input').each(function() {
        return data[$(this).attr('name')] = $(this).val();
      });
      data.disable = true;
      $.extend(data, ajaxData);
      $.post(ajaxurl, data, (function() {}));
      return $prompt.slideUp();
    });
  });
}).call(this, jQuery);
