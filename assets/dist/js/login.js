(function($) {
  var closeOverlay, openOverlay;
  closeOverlay = function() {
    return $('.clef-login-form.clef-overlay').addClass('closed');
  };
  openOverlay = function() {
    return $('.clef-login-form.clef-overlay').removeClass('closed');
  };
  return $(function() {
    $('.close-overlay').click(closeOverlay);
    $('.open-overlay').click(openOverlay);
    return $('.overlay-info .open').click(function() {
      return $('.overlay-info').removeClass('closed');
    });
  });
}).call(this, jQuery);
