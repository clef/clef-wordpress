(function($) {
  var dismissWaltzNotification;
  dismissWaltzNotification = function(e) {
    var $el, data;
    if (e) {
      e.preventDefault();
    }
    $el = $('.waltz-notification');
    data = {};
    $el.find('input').each(function() {
      return data[$(this).attr('name')] = $(this).val();
    });
    $el.remove();
    return $.post(ajaxurl + '?action=clef_dismiss_waltz', data);
  };
  return $(document).ready(function() {
    setTimeout(function() {
      if (window.waltzIsInstalled) {
        return dismissWaltzNotification();
      }
    }, 1000);
    return $('.waltz-notification .next').click(dismissWaltzNotification);
  });
}).call(this, jQuery);
