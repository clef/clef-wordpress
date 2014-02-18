(function($) {
  var dismissWaltzNotification;
  dismissWaltzNotification = function(e) {
    e.preventDefault();
    $('.waltz-notification').remove();
    return $.post(ajaxurl + '?action=clef_dismiss_waltz_notification');
  };
  return $(document).ready(function() {
    if (window.waltzIsInstalled) {
      return dismissWaltzNotification();
    } else {
      return $('.waltz-notification .next').click(dismissWaltzNotification);
    }
  });
}).call(this, jQuery);
