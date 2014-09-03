(function($) {
  var closeOverlay, openOverlay;
  closeOverlay = function() {
    $('.clef-login-form.clef-login-form-embed').addClass('clef-closed');
    return false;
  };
  openOverlay = function(e) {
    $('.clef-login-form.clef-login-form-embed').removeClass('clef-closed');
    return false;
  };
  return $(function() {
    var $embedContainer, $iframe, $spinnerContainer;
    $embedContainer = $('.clef-embed-container');
    $('.close-overlay').click(closeOverlay);
    $('.open-overlay').click(openOverlay);
    $('.overlay-info .open').click(function() {
      return $('.overlay-info').removeClass('closed');
    });
    if ($embedContainer.length) {
      $spinnerContainer = $('.spinner-container');
      $iframe = $embedContainer.find('iframe');
      $iframe.load(function() {
        $spinnerContainer.hide();
        return setTimeout(function() {
          return $embedContainer.slideDown();
        });
      });
      if (!$iframe.attr('data-loaded')) {
        $embedContainer.hide();
        return $spinnerContainer.show();
      }
    }
  });
}).call(this, jQuery);
