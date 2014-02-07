(function($) {
  var AppView, FormVisualization, SettingsModel, SettingsView, SubTutorialView, TutorialView;
  Backbone.emulateHTTP = true;
  AppView = Backbone.View.extend({
    id: "clef-settings-container",
    initialize: function(opts) {
      this.opts = opts;
      this.settings = new SettingsView(_.extend(this.opts, {
        options_name: "wpclef"
      }));
      this.settings.render();
      return window.onbeforeunload = (function(_this) {
        return function(e) {
          if (_this.settings.isSaving()) {
            return "Some settings are still being saved. Are you sure you want to navigate away?";
          }
        };
      })(this);
    }
  });
  TutorialView = Backbone.View.extend({
    el: $('#clef-tutorial'),
    events: {
      "click .next": "next",
      "click .previous": "previous"
    },
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
      return $(window).on('message', this.handleLogin.bind(this));
    },
    render: function() {
      this.currentSub.render();
      this.loadIFrame();
      return this.$el.fadeIn();
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
      var frame;
      frame = this.$el.find("iframe");
      return frame.attr('src', frame.data('src'));
    },
    handleLogin: function() {
      this.loggedIn = true;
      if (this.currentSub.isLogin()) {
        return this.next();
      }
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
  SettingsView = AjaxSettingsView.extend({
    initialize: function(opts) {
      this.modelClass = SettingsModel;
      SettingsView.__super__.initialize.call(this, opts);
      this.formView = new FormVisualization({
        model: this.model
      });
      this.xmlEl = this.model.cfindInput('clef_password_settings_xml_allowed').parents('.input-container');
      this.overrideContainer = this.$el.find('.override-settings');
      this.overrideButtonContainer = this.$el.find('.override-buttons');
      this.setOverrideLink();
      return this.badgePreviewContainer = this.$el.find('.support-settings .footer-preview');
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
      this.overrideContainer.toggle(passwordsDisabled);
      this.overrideButtonContainer.toggle(this.model.overrideIsSet());
      this.renderSupportBadge();
      if (this.$el.is(':not(:visible)')) {
        return this.$el.fadeIn();
      }
    },
    toggleInputs: function(e) {
      return this.formView.toggleForm(!!parseInt(e.currentTarget.value));
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
    }
  });
  SettingsModel = AjaxSettingsModel.extend({
    cget: function(key) {
      return this.get("wpclef[" + key + "]");
    },
    cfindInput: function(name) {
      name = "wpclef[" + name + "]";
      return SettingsModel.__super__.findInput.call(this, name);
    },
    passwordsDisabled: function() {
      return !!parseInt(this.cget('clef_password_settings_disable_passwords')) || this.cget('clef_password_settings_disable_certain_passwords') !== "Disabled" || this.passwordsFullyDisabled();
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
