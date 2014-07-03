(function($) {
  var closeOverlay, openOverlay;
  closeOverlay = function() {
    return $('.clef-login-form.clef-login-form-embed').addClass('clef-closed');
  };
  openOverlay = function() {
    return $('.clef-login-form.clef-login-form-embed').removeClass('clef-closed');
  };
  return $(function() {
    $('.close-overlay').click(closeOverlay);
    $('.open-overlay').click(openOverlay);
    return $('.overlay-info .open').click(function() {
      return $('.overlay-info').removeClass('closed');
    });
  });
}).call(this, jQuery);
