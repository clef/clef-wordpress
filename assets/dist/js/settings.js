(function($) {
  var InviteUsersView;
  InviteUsersView = Backbone.View.extend({
    el: '#invite-users-settings',
    events: {
      "click a[name='invite-users-button']": 'inviteUsers'
    },
    messageTemplate: _.template("<div class='<%=type%> invite-users-message'><%=message%></div>"),
    showMessage: function(type, message) {
      var $messageEl;
      $messageEl = this.$el.find('.invite-users-message');
      if ($messageEl.length) {
        $messageEl.remove();
      }
      return this.$el.find('.button').first().before(this.messageTemplate({
        type: type,
        message: message
      }));
    },
    template: _.template($('#invite-users-template').html()),
    initialize: function(opts) {
      this.opts = opts;
      if (this.opts.el) {
        return this.setElement(this.opts.el);
      }
    },
    inviteUsersAction: ajaxurl + "?action=clef_invite_users",
    inviteUsers: function(e) {
      var data;
      e.preventDefault();
      data = {
        _wp_nonce: this.opts.setup._wp_nonce_invite_users,
        roles: $("select[name='invite-users-role']").val()
      };
      return $.post(this.inviteUsersAction, data, (function(_this) {
        return function(data) {
          var msg, type;
          msg = "";
          type = "";
          if (data.error) {
            msg = "There was a problem sending invites: " + data.error + ".";
            type = "error";
          } else if (data.success) {
            _this.trigger("invited");
            msg = "Email invitations have been sent to your users.";
            type = "updated";
          }
          return _this.showMessage(type, msg);
        };
      })(this));
    },
    hideButton: function() {
      return this.$el.find('.button').hide();
    },
    render: function() {
      return this.$el.html(this.template);
    }
  });
  return this.InviteUsersView = InviteUsersView;
}).call(this, jQuery);

(function($) {
  var MultisiteNetworkOptionsView, MultisiteOptionsModel, MultisiteOptionsView;
  MultisiteOptionsView = AjaxSettingsView.extend({
    el: '#clef-multisite-options',
    enabled_template: _.template($('#multisite-enabled-template').html()),
    disabled_template: _.template($('#multisite-disabled-template').html()),
    initialize: function(opts) {
      this.modelClass = MultisiteOptionsModel;
      return MultisiteOptionsView.__super__.initialize.call(this, opts);
    },
    render: function() {
      var template;
      if (this.opts.overridden_by_network_settings) {
        template = this.enabled_template;
      } else {
        template = this.disabled_template;
      }
      return this.$el.html(template());
    }
  });
  MultisiteNetworkOptionsView = MultisiteOptionsView.extend({
    render: function() {
      var template;
      console.log(this.modelClass);
      if (this.opts.network_settings_enabled) {
        template = this.enabled_template;
      } else {
        template = this.disabled_template;
      }
      return this.$el.html(template());
    }
  });
  MultisiteOptionsModel = AjaxSettingsModel.extend({
    parse: function(data, options) {
      options.url = ajaxurl + '?action=clef_multisite_options';
      return MultisiteOptionsModel.__super__.parse.call(this, data, options);
    }
  });
  this.MultisiteOptionsModel = MultisiteOptionsModel;
  this.MultisiteOptionsView = MultisiteOptionsView;
  return this.MultisiteNetworkOptionsView = MultisiteNetworkOptionsView;
}).call(this, jQuery);

(function($) {
  var AppView, FormVisualization, SettingsModel, SettingsView;
  Backbone.emulateHTTP = true;
  AppView = Backbone.View.extend({
    el: $('#clef-settings-container'),
    initialize: function(opts) {
      this.opts = opts;
      this.$msgContainer = this.$el.find('.message');
      this.settings = new SettingsView(_.extend({
        options_name: "wpclef"
      }, this.opts));
      this.tutorial = new TutorialView(_.extend({}, this.opts));
      if (this.opts.is_network_settings) {
        this.multisiteOptionsView = new MultisiteNetworkOptionsView(this.opts);
      } else {
        this.multisiteOptionsView = new MultisiteOptionsView(this.opts);
      }
      this.render();
      this.listenTo(this.settings, 'message', this.displayMessage);
      return this.listenTo(this.tutorial, 'message', this.displayMessage);
    },
    render: function() {
      if (this.opts.overridden_by_network_settings) {
        this.multisiteOptionsView.render();
        return;
      }
      if (this.settings.isConfigured()) {
        this.multisiteOptionsView.render();
        return this.settings.show();
      } else {
        this.tutorial.render();
        this.listenToOnce(this.tutorial, 'applicationCreated', this.configure);
        return this.listenToOnce(this.tutorial, 'done', this.hideTutorial);
      }
    },
    configure: function(data) {
      return this.settings.model.configure(data);
    },
    displayMessage: function(opts) {
      this.$msgContainer.find('p').text(opts.message);
      this.$msgContainer.addClass(opts.type).slideDown();
      if (opts.fade) {
        return setTimeout((function() {
          return this.$msgContainer.slideUp();
        }), 3000);
      }
    },
    hideTutorial: function() {
      if (this.settings.isConfigured()) {
        this.displayMessage("You're all set up!", {
          type: "updated"
        });
      }
      this.tutorial.hide();
      return this.settings.render();
    }
  });
  SettingsView = AjaxSettingsView.extend({
    errorTemplate: _.template("<div class='error form-error'><%=message%></div>"),
    genericErrorMessage: "Something went wrong, please refresh and try again.",
    addEvents: {
      "click .generate-override": "generateOverride",
      "click input[type='submit']:not(.ajax-ignore)": "saveForm"
    },
    constructor: function(opts) {
      this.events = _.extend(this.events, this.addEvents);
      return SettingsView.__super__.constructor.call(this, opts);
    },
    initialize: function(opts) {
      this.opts = opts;
      this.modelClass = SettingsModel;
      SettingsView.__super__.initialize.call(this, opts);
      this.inviteUsersView = new InviteUsersView(opts);
      this.formView = new FormVisualization({
        model: this.model
      });
      this.xmlEl = this.model.cFindInput('clef_password_settings_xml_allowed').parents('.input-container');
      this.overrideContainer = this.$el.find('.override-settings');
      this.overrideButtonContainer = this.$el.find('.override-buttons');
      this.setOverrideLink();
      this.badgePreviewContainer = this.$el.find('.support-settings .ftr-preview');
      this.listenTo(this.model, "change", this.clearErrors);
      this.listenTo(this.model, "error", this.error);
      window.onbeforeunload = (function(_this) {
        return function(e) {
          if (_this.isSaving()) {
            return "Settings are being saved. Still want to navigate away?";
          }
        };
      })(this);
      return this.render();
    },
    updated: function(obj, data) {
      SettingsView.__super__.updated.call(this, obj, data);
      return this.setOverrideLink();
    },
    render: function() {
      var passwordsDisabled;
      SettingsView.__super__.render.call(this);
      passwordsDisabled = this.model.passwordsDisabled();
      $('#clef-settings-header').show();
      this.xmlEl.toggle(passwordsDisabled);
      this.toggleOverrideContainer(passwordsDisabled);
      this.overrideButtonContainer.toggle(this.model.overrideIsSet());
      this.inviteUsersView.render();
      return this.renderSupportBadge();
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
    },
    clearErrors: function(model, data) {
      var inp, inputName, v, _ref, _results;
      _ref = model.changed;
      _results = [];
      for (inputName in _ref) {
        v = _ref[inputName];
        inp = this.model.findInput(inputName).parents('.input-container');
        if (inp.hasClass('error')) {
          _results.push(inp.removeClass('error').next('.error.form-error').remove());
        } else {
          _results.push(void 0);
        }
      }
      return _results;
    },
    error: function(model, data) {
      var inp, inputName, msg, _ref, _results;
      if (!data.responseJSON.errors) {
        this.trigger('message', {
          message: this.genericErrorMessage,
          type: 'error'
        });
        window.scrollTo(0, 0);
        return;
      }
      _ref = data.responseJSON.errors;
      _results = [];
      for (inputName in _ref) {
        msg = _ref[inputName];
        inp = this.model.cFindInput(inputName).parents('.input-container');
        if (inp.hasClass('error')) {
          _results.push(inp.next('.error.form-error').html(msg));
        } else {
          _results.push(inp.addClass('error').after(this.errorTemplate({
            message: msg
          })));
        }
      }
      return _results;
    },
    saveForm: function(e) {
      e.preventDefault();
      return this.model.save({}, {
        success: (function(_this) {
          return function() {
            _this.trigger('message', {
              message: "Settings saved.",
              type: 'updated'
            });
            return window.scrollTo(0, 0);
          };
        })(this),
        error: this.model.saveError.bind(this.model)
      });
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
    connectClefAccountAction: ajaxurl + "?action=connect_clef_account",
    events: {
      "click .next": "next",
      "click .previous": "previous",
      "click .done": "done"
    },
    iframePath: '/iframes/application/create/v1',
    initialize: function(opts) {
      var sub, _i, _len, _ref;
      this.opts = opts;
      if (window.chrome) {
        this.$el.find('.waltz').addClass('sub');
      }
      this.subs = [];
      _ref = this.$el.find('.sub');
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        sub = _ref[_i];
        this.subs.push(new SubTutorialView({
          el: sub
        }));
      }
      this.currentSub = this.subs[0];
      this.inviter = new InviteUsersView(_.extend({
        el: this.$el.find('.invite-users-container')
      }, this.opts));
      $(window).on('message', this.handleMessages.bind(this));
      return this.listenTo(this.inviter, "invited", this.usersInvited);
    },
    hide: function(cb) {
      return this.$el.slideUp(cb);
    },
    render: function() {
      if (this.userIsLoggedIn) {
        if (!this.currentSub.$el.hasClass('sync')) {
          this.$el.addClass('no-sync');
        } else {
          this.$el.addClass('user');
        }
      }
      if (!this.$el.is(':visible')) {
        this.currentSub.render();
        this.loadIFrame();
        this.$el.fadeIn();
        return this.inviter.render();
      }
    },
    done: function() {
      return this.trigger("done");
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
      } else {
        return this.done();
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
      frame = this.$el.find("iframe.setup");
      src = "" + this.opts.clefBase + this.iframePath + "?source=wordpress&domain=" + (encodeURIComponent(this.opts.setup.siteDomain)) + "&name=" + (encodeURIComponent(this.opts.setup.siteName));
      return frame.attr('src', src);
    },
    handleMessages: function(data) {
      if (!data.originalEvent.origin.indexOf(this.opts.clefBase >= 0)) {
        return;
      }
      data = data.originalEvent.data;
      if (data.type === "keys") {
        return this.connectClefAccount(data, (function(_this) {
          return function() {
            _this.trigger('applicationCreated', data);
            return _this.next();
          };
        })(this));
      } else if (data.type === "user") {
        this.userIsLoggedIn = true;
        return this.render();
      }
    },
    onConfigured: function() {
      return setTimeout((function() {
        return $(".logout-hook-error").slideDown();
      }), 20000);
    },
    connectClefAccount: function(data, cb) {
      var connectData;
      connectData = {
        _wp_nonce: this.opts.setup._wp_nonce_connect_clef,
        clefID: data.clefID
      };
      return $.post(this.connectClefAccountAction, connectData, (function(_this) {
        return function(data) {
          var msg;
          if (data.error) {
            msg = "There was a problem automatically connecting your Clef account: " + data.error + ". Please refresh and try again.";
            return _this.trigger('message', {
              message: msg,
              type: "error"
            });
          } else {
            if (typeof cb === "function") {
              return cb();
            }
          }
        };
      })(this));
    },
    usersInvited: function() {
      this.inviter.hideButton();
      return setTimeout((function(_this) {
        return function() {
          if (_this.currentSub.$el.hasClass('invite')) {
            return _this.currentSub.$el.find('.button').addClass('button-primary');
          }
        };
      })(this), 1000);
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
      return this.$el.find('iframe.setup').length;
    }
  });
  return this.TutorialView = TutorialView;
}).call(this, jQuery, Backbone);
