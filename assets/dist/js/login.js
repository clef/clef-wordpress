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
    var $embedContainer, $spinnerContainer;
    $embedContainer = $('.clef-embed-container');
    $('.close-overlay').click(closeOverlay);
    $('.open-overlay').click(openOverlay);
    $('.overlay-info .open').click(function() {
      return $('.overlay-info').removeClass('closed');
    });
    if ($embedContainer.length) {
      $spinnerContainer = $('.spinner-container');
      $embedContainer.hide();
      $spinnerContainer.show();
      return $('iframe').on('load', function() {
        if ($(this).attr('src').match('clef\.io/iframes/qr')) {
          $spinnerContainer.hide();
          return setTimeout(function() {
            return $('.clef-embed-container').slideDown();
          });
        }
      });
    }
  });
}).call(this, jQuery);
