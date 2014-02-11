(function($) {
  var AppView, FormVisualization, SettingsModel, SettingsView;
  Backbone.emulateHTTP = true;
  AppView = Backbone.View.extend({
    el: $('#clef-settings-container'),
    connectClefAccountAction: ajaxurl + "?action=connect_clef_account",
    initialize: function(opts) {
      this.opts = opts;
      this.$msgContainer = this.$el.find('.message');
      this.settings = new SettingsView(_.extend({
        options_name: "wpclef"
      }, this.opts));
      this.tutorial = new TutorialView(_.extend({}, this.opts));
      if (this.settings.isConfigured()) {
        return this.settings.render();
      } else {
        this.tutorial.render();
        return this.listenToOnce(this.tutorial, 'applicationCreated', this.configure.bind(this));
      }
    },
    configure: function(data) {
      this.connectClefAccount(data);
      this.settings.model.configure(data);
      this.tutorial.hide();
      return this.settings.render();
    },
    connectClefAccount: function(data) {
      var connectData;
      connectData = {
        _wp_nonce: this.opts.setup._wp_nonce,
        clefID: data.clefID
      };
      return $.post(this.connectClefAccountAction, connectData, (function(_this) {
        return function(data) {
          var msg;
          if (data.error) {
            msg = "There was a problem automatically connecting your Clef account: " + data.error + ".";
            return _this.displayMessage(msg, "error");
          }
        };
      })(this));
    },
    displayMessage: function(msg, opts) {
      this.$msgContainer.find('p').text(msg);
      this.$msgContainer.addClass(opts.type).slideDown();
      if (opts.fade) {
        return setTimeout((function() {
          return this.$msgContainer.slideUp();
        }), 3000);
      }
    }
  });
  SettingsView = AjaxSettingsView.extend({
    addEvents: {
      "click .generate-override": "generateOverride"
    },
    constructor: function(opts) {
      this.events = _.extend(this.events, this.addEvents);
      return SettingsView.__super__.constructor.call(this, opts);
    },
    initialize: function(opts) {
      this.modelClass = SettingsModel;
      SettingsView.__super__.initialize.call(this, opts);
      this.formView = new FormVisualization({
        model: this.model
      });
      this.xmlEl = this.model.cFindInput('clef_password_settings_xml_allowed').parents('.input-container');
      this.overrideContainer = this.$el.find('.override-settings');
      this.overrideButtonContainer = this.$el.find('.override-buttons');
      this.setOverrideLink();
      this.badgePreviewContainer = this.$el.find('.support-settings .footer-preview');
      return window.onbeforeunload = (function(_this) {
        return function(e) {
          if (_this.isSaving()) {
            return "Settings are being saved. Still want to navigate away?";
          }
        };
      })(this);
    },
    updated: function(obj, data) {
      SettingsView.__super__.updated.call(this, obj, data);
      return this.setOverrideLink();
    },
    render: function() {
      var passwordsDisabled;
      SettingsView.__super__.render.call(this);
      passwordsDisabled = this.model.passwordsDisabled();
      this.xmlEl.toggle(passwordsDisabled);
      this.toggleOverrideContainer(passwordsDisabled);
      this.overrideButtonContainer.toggle(this.model.overrideIsSet());
      this.renderSupportBadge();
      if (this.$el.is(':not(:visible)')) {
        return this.$el.fadeIn();
      }
    },
    toggleInputs: function(e) {
      return this.formView.toggleForm(!!parseInt(e.currentTarget.value));
    },
    toggleOverrideContainer: function(show) {
      return this.overrideContainer.toggle(show);
    },
    generateOverride: function() {
      var rnd;
      rnd = Math.random().toString(36).slice(2);
      return this.model.save({
        'wpclef[clef_override_settings_key]': rnd
      });
    },
    setOverrideLink: function() {
      var button, key;
      key = this.model.overrideKey();
      if (!key) {
        return;
      }
      if (!this.overrideBase) {
        this.overrideBase = this.overrideContainer.find('label').text();
      }
      button = this.overrideButtonContainer.find('a');
      button.on('click', function(e) {
        return e.preventDefault();
      });
      return button.attr('href', this.overrideBase + key);
    },
    isSaving: function() {
      return this.model.saving;
    },
    renderSupportBadge: function() {
      var setting;
      setting = this.model.badgeSetting();
      this.badgePreviewContainer.toggle(setting !== "disabled");
      return this.badgePreviewContainer.find('a').toggleClass('pretty', setting === "badge");
    },
    isConfigured: function() {
      return this.model.isConfigured();
    }
  });
  SettingsModel = AjaxSettingsModel.extend({
    cFindInput: function(name) {
      return this.findInput("wpclef[" + name + "]");
    },
    cget: function(key) {
      return this.get("wpclef[" + key + "]");
    },
    passwordsDisabled: function() {
      return !!parseInt(this.cget('clef_password_settings_disable_passwords')) || this.cget('clef_password_settings_disable_certain_passwords') !== "" || this.passwordsFullyDisabled();
    },
    passwordsFullyDisabled: function() {
      return !!parseInt(this.cget('clef_password_settings_force'));
    },
    overrideIsSet: function() {
      return !!this.overrideKey();
    },
    overrideKey: function() {
      return this.cget('clef_override_settings_key');
    },
    badgeSetting: function() {
      return this.cget('support_clef_badge').toLowerCase();
    },
    isConfigured: function() {
      return !!(this.cget('clef_settings_app_id') && this.cget('clef_settings_app_secret'));
    },
    configure: function(data) {
      return this.save({
        'wpclef[clef_settings_app_id]': data.appID,
        'wpclef[clef_settings_app_secret]': data.appSecret
      });
    }
  });
  FormVisualization = Backbone.View.extend({
    el: $("#login-form-view"),
    template: _.template($('#form-template').html()),
    initialize: function(opts) {
      this.opts = opts;
      this.model = this.opts.model;
      this.listenTo(this.model, 'change', this.toggleForm);
      return this.render();
    },
    render: function() {
      this.$el.html(this.template);
      this.$el.find('input[type="submit"]').on('click', function(e) {
        return e.preventDefault();
      });
      return this.toggleForm();
    },
    toggleForm: function(e) {
      return this.$el.toggleClass('only-clef', this.model.passwordsFullyDisabled());
    }
  });
  this.AppView = AppView;
  $(document).ready(function() {
    var app;
    return app = new AppView(options);
  });
  return $.fn.serializeObject = function(form) {
    var obj, serialized, _i, _len, _ref;
    serialized = {};
    _ref = $(this).serializeArray();
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      obj = _ref[_i];
      serialized[obj.name] = obj.value;
    }
    return serialized;
  };
}).call(this, jQuery);

(function($, Backbone) {
  var SubTutorialView, TutorialView;
  TutorialView = Backbone.View.extend({
    el: $('#clef-tutorial'),
    events: {
      "click .next": "next",
      "click .previous": "previous"
    },
    iframePath: '/iframes/application/create/v1',
    initialize: function(opts) {
      var sub, _i, _len, _ref;
      this.opts = opts;
      this.subs = [];
      _ref = this.$el.find('.sub');
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        sub = _ref[_i];
        this.subs.push(new SubTutorialView({
          el: sub
        }));
      }
      this.currentSub = this.subs[0];
      return $(window).on('message', this.handleMessages.bind(this));
    },
    hide: function(cb) {
      return this.$el.fadeOut(cb);
    },
    render: function() {
      if (this.userIsLoggedIn) {
        this.$el.addClass('user');
      }
      if (!this.$el.is(':visible')) {
        this.currentSub.render();
        this.loadIFrame();
        return this.$el.fadeIn();
      }
    },
    next: function() {
      var newSub;
      newSub = this.subs[_.indexOf(this.subs, this.currentSub) + 1];
      if (newSub) {
        if (newSub.isLogin() && this.loggedIn) {
          newSub = this.subs[_.indexOf(this.subs, this.newSub) + 1];
        }
        this.currentSub.hide();
        newSub.render();
        return this.currentSub = newSub;
      }
    },
    previous: function() {
      var newSub;
      newSub = this.subs[_.indexOf(this.subs, this.currentSub) - 1];
      if (newSub) {
        this.currentSub.hide();
        newSub.render();
        return this.currentSub = newSub;
      }
    },
    loadIFrame: function() {
      var frame, src;
      frame = this.$el.find("iframe");
      src = "" + this.opts.clefBase + this.iframePath + "?source=wordpress&domain=" + (encodeURIComponent(this.opts.setup.siteDomain)) + "&name=" + (encodeURIComponent(this.opts.setup.siteName));
      return frame.attr('src', src);
    },
    handleMessages: function(data) {
      if (!data.originalEvent.origin.indexOf(this.opts.clefBase >= 0)) {
        return;
      }
      data = data.originalEvent.data;
      if (data.type === "keys") {
        return this.trigger('applicationCreated', data);
      } else if (data.type === "user") {
        this.userIsLoggedIn = true;
        return this.render();
      }
    },
    onConfigured: function() {
      return setTimeout((function() {
        return $(".logout-hook-error").slideDown();
      }), 20000);
    }
  });
  SubTutorialView = Backbone.View.extend({
    initialize: function(opts) {
      this.opts = opts;
      return this.setElement($(this.opts.el));
    },
    render: function() {
      return this.$el.show();
    },
    hide: function() {
      return this.$el.hide();
    },
    remove: function() {
      return this.$el.remove();
    },
    isLogin: function() {
      return this.$el.find('iframe').length;
    }
  });
  return this.TutorialView = TutorialView;
}).call(this, jQuery, Backbone);
