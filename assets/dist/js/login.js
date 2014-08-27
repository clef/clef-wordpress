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
    var $embedContainer, $iframe, $spinnerContainer, loaded;
    $embedContainer = $('.clef-embed-container');
    $('.close-overlay').click(closeOverlay);
    $('.open-overlay').click(openOverlay);
    $('.overlay-info .open').click(function() {
      return $('.overlay-info').removeClass('closed');
    });
    if ($embedContainer.length) {
      loaded = false;
      $spinnerContainer = $('.spinner-container');
      $iframe = $embedContainer.find('iframe');
      $iframe.load(function() {
        loaded = true;
        $spinnerContainer.hide();
        return setTimeout(function() {
          return $embedContainer.slideDown();
        });
      });
      if (!loaded) {
        $embedContainer.hide();
        return $spinnerContainer.show();
      }
    }
  });
}).call(this, jQuery);
