(function($) {
  var ClefProfile;
  ClefProfile = (function() {
    ClefProfile.prototype.selector = '#clef-profile';

    ClefProfile.prototype.disconnectURL = ajaxurl + "?action=disconnect_clef_account";

    function ClefProfile(options) {
      this.options = options;
      this.$el = $(this.selector);
      this.render();
      this.attachHandlers();
    }

    ClefProfile.prototype.render = function() {
      return this.$el.toggleClass('has-clef', this.options.connected);
    };

    ClefProfile.prototype.attachHandlers = function() {
      return this.$el.find('#disconnect').click(this.disconnectClefAccount.bind(this));
    };

    return ClefProfile;

  })();
  return window.ClefProfile = ClefProfile;
}).call(this, jQuery);
