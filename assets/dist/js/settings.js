(function($, Backbone) {
  var Utils;
  Utils = (function() {
    function Utils() {}

    Utils.getErrorMessage = function(data) {
      if (typeof data === "string") {
        try {
          data = $.parseJSON(data);
        } catch (_error) {

        }
      }
      if (data.error) {
        return data.error;
      } else if (data.data && data.data.error) {
        return data.data.error;
      }
      return data;
    };

    Utils.getURLParams = function() {
      var key, params, query, raw_vars, v, val, _i, _len, _ref;
      query = window.location.search.substring(1);
      raw_vars = query.split("&");
      params = {};
      for (_i = 0, _len = raw_vars.length; _i < _len; _i++) {
        v = raw_vars[_i];
        _ref = v.split("="), key = _ref[0], val = _ref[1];
        params[key] = decodeURIComponent(val);
      }
      return params;
    };

    return Utils;

  })();
  return window.ClefUtils = Utils;
}).call(this, jQuery, Backbone);

(function($, Backbone) {
  var InviteUsersView;
  InviteUsersView = Backbone.View.extend({
    el: '#invite-users-settings',
    events: {
      "click a[name='invite-users-button']": 'inviteUsers'
    },
    messageTemplate: _.template("<div class='<%=type%> invite-users-message'><%=message%></div>"),
    showMessage: function(data) {
      var $messageEl;
      $messageEl = this.$el.find('.invite-users-message');
      if ($messageEl.length) {
        $messageEl.remove();
      }
      return this.$el.find('.invite-role-button').first().before(this.messageTemplate(data));
    },
    template: function() {
      return _.template($('#invite-users-template').html());
    },
    initialize: function(opts) {
      this.opts = opts;
      if (this.opts.el) {
        return this.setElement(this.opts.el);
      }
    },
    inviteUsersAction: "clef_invite_users",
    inviteUsers: function(e) {
      var data, failure;
      e.preventDefault();
      $(e.target).attr('disabled', 'disabled');
      data = {
        _wpnonce: this.opts.nonces.inviteUsers,
        roles: $("select[name='invite-users-role']").val(),
        networkAdmin: this.opts.isNetworkSettings,
        action: this.inviteUsersAction
      };
      failure = (function(_this) {
        return function(data) {
          var msg;
          msg = ClefUtils.getErrorMessage(data);
          $(e.target).removeAttr('disabled');
          return _this.showMessage({
            message: _.template(clefTranslations.messages.error.invite)({
              error: msg
            }),
            type: "error"
          });
        };
      })(this);
      return $.post("" + ajaxurl + "?action=" + this.inviteUsersAction, data).success((function(_this) {
        return function(data) {
          $(e.target).removeAttr('disabled');
          if (data.success) {
            _this.trigger("invited");
            return _this.showMessage({
              message: data.message,
              type: "updated"
            });
          } else {
            return failure(data);
          }
        };
      })(this)).fail(function(res) {
        return failure(res.responseText);
      });
    },
    hideButton: function() {
      return this.$el.find('.button').hide();
    },
    render: function() {
      return this.$el.html(this.template);
    }
  });
  return this.InviteUsersView = InviteUsersView;
}).call(this, jQuery, Backbone);

(function($) {
  var MultisiteOptionsModel, MultisiteOptionsView;
  MultisiteOptionsView = AjaxSettingsView.extend({
    el: '#multisite-settings',
    initialize: function(opts) {
      this.modelClass = MultisiteOptionsModel;
      return MultisiteOptionsView.__super__.initialize.call(this, opts);
    }
  });
  MultisiteOptionsModel = AjaxSettingsModel.extend({
    parse: function(data, options) {
      options.url = ajaxurl + '?action=clef_multisite_settings';
      return MultisiteOptionsModel.__super__.parse.call(this, data, options);
    },
    addActionToData: function(data) {
      data.action = "clef_multisite_settings";
      return data;
    }
  });
  this.MultisiteOptionsModel = MultisiteOptionsModel;
  return this.MultisiteOptionsView = MultisiteOptionsView;
}).call(this, jQuery);

(function($, Backbone) {
  var ConnectTutorialView, SetupTutorialView, SubTutorialView, TutorialView;
  TutorialView = Backbone.View.extend({
    el: $('#clef-tutorial'),
    messageTemplate: _.template("<div class='<%=type%> tutorial-message'><%=message%></div>"),
    events: {
      "click .next": "next",
      "click .previous": "previous",
      "click .done": "done"
    },
    slideClass: 'sub',
    initialize: function(opts) {
      var potentialSubs, sub, _i, _len;
      this.opts = opts;
      this.subs = [];
      potentialSubs = this.$el.find("." + this.slideClass).filter(this.opts.slideFilterSelector);
      for (_i = 0, _len = potentialSubs.length; _i < _len; _i++) {
        sub = potentialSubs[_i];
        this.subs.push(new SubTutorialView({
          el: sub
        }));
      }
      this.currentSub = this.subs[0];
      $(window).on('message', this.handleMessages.bind(this));
      this.hide();
      return this.render();
    },
    slideUp: function(cb) {
      return this.$el.slideUp(cb);
    },
    hide: function(cb) {
      return this.$el.hide(cb);
    },
    show: function() {
      return this.$el.fadeIn();
    },
    render: function() {
      return this.currentSub.render();
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
        this.currentSub = newSub;
        return this.trigger("next");
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
    handleMessages: function(e) {
      var data;
      if (!(e.originalEvent.origin.indexOf(this.opts.clefBase) >= 0)) {
        return;
      }
      data = e.originalEvent.data;
      if (typeof data === "string") {
        data = JSON.parse(data);
      }
      return data;
    },
    connectClefAccount: function(data, cb) {
      var connectData, failure;
      connectData = {
        _wpnonce: this.opts.nonces.connectClef,
        identifier: data.identifier,
        state: data.state,
        action: this.connectClefAction
      };
      failure = (function(_this) {
        return function(data) {
          var msg;
          msg = ClefUtils.getErrorMessage(data);
          return _this.showMessage({
            message: _.template(clefTranslations.messages.error.connect)({
              error: msg
            }),
            type: "error"
          });
        };
      })(this);
      return $.post("" + ajaxurl + "?action=" + this.connectClefAction, connectData).success(function(data) {
        if (data.success) {
          if (typeof cb === "function") {
            return cb(data);
          }
        } else {
          return failure(data);
        }
      }).fail(function(res) {
        return failure(res.responseText);
      });
    },
    showMessage: function(opts) {
      if (this.$currentMessage) {
        this.$currentMessage.remove();
      }
      this.$currentMessage = $(this.messageTemplate(opts)).hide().prependTo(this.$el).slideDown();
      if (opts.removeNext) {
        return this.listenToOnce(this, "next", function() {
          return this.$currentMessage.slideUp();
        });
      }
    }
  }, {
    extend: Backbone.View.extend
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
    find: function(query) {
      return this.$el.find(query);
    },
    isLogin: function() {
      return this.$el.find('iframe.setup').length;
    },
    isSync: function() {
      return this.$el.hasClass('sync') && this.$el.find('iframe').length;
    }
  });
  SetupTutorialView = TutorialView.extend({
    connectClefAction: "connect_clef_account_clef_id",
    iframePath: '/iframes/application/create/v2',
    initialize: function(opts) {
      opts.slideFilterSelector = '.setup';
      this.inviter = new InviteUsersView(_.extend({
        el: this.$el.find('.invite-users-container')
      }, opts));
      this.listenTo(this.inviter, "invited", this.usersInvited);
      this.constructor.__super__.initialize.call(this, opts);
      return this.on('next', this.shouldLoadIFrame);
    },
    render: function() {
      this.inviter.render();
      return this.constructor.__super__.render.call(this);
    },
    shouldLoadIFrame: function() {
      if (this.currentSub.isSync()) {
        return this.loadIFrame((function(_this) {
          return function() {
            _this.currentSub.find('.spinner-container').hide();
            return _this.iframe.fadeIn();
          };
        })(this));
      }
    },
    loadIFrame: function(cb) {
      var affiliates, src;
      if (this.iframe) {
        return;
      }
      this.iframe = this.$el.find("iframe.setup");
      affiliates = encodeURIComponent(this.opts.setup.affiliates.join(','));
      src = "" + this.opts.clefBase + this.iframePath + "?source=" + (encodeURIComponent(this.opts.setup.source)) + "&domain=" + (encodeURIComponent(this.opts.setup.siteDomain)) + "&logout_hook=" + (encodeURIComponent(this.opts.setup.logoutHook)) + "&name=" + (encodeURIComponent(this.opts.setup.siteName)) + "&affiliates=" + affiliates;
      this.iframe.attr('src', src);
      return this.iframe.on('load', cb);
    },
    handleMessages: function(data) {
      data = this.constructor.__super__.handleMessages.call(this, data);
      if (!data) {
        return;
      }
      if (data.type === "keys") {
        return this.connectClefAccount({
          identifier: data.clefID
        }, (function(_this) {
          return function() {
            _this.trigger('applicationCreated', data);
            _this.next();
            return _this.showMessage({
              message: clefTranslations.messages.success.connect,
              type: "updated",
              removeNext: true
            });
          };
        })(this));
      } else if (data.type === "error") {
        return this.showMessage({
          message: _.template(clefTranslations.messages.error.create)({
            error: data.message
          }),
          type: 'error'
        });
      }
    },
    onConfigured: function() {
      return setTimeout((function() {
        return $(".logout-hook-error").slideDown();
      }), 20000);
    },
    usersInvited: function() {
      this.inviter.hideButton();
      return setTimeout(((function(_this) {
        return function() {
          if (_this.currentSub.$el.hasClass('invite')) {
            return _this.currentSub.$el.find('.button').addClass('button-primary');
          }
        };
      })(this)), 1000);
    }
  });
  ConnectTutorialView = TutorialView.extend({
    render: function() {
      this.addButton();
      return this.constructor.__super__.render.call(this);
    },
    addButton: function() {
      var redirectURL, target;
      if (this.button) {
        return;
      }
      redirectURL = window.location.href;
      if (/\?/.test(redirectURL)) {
        redirectURL += "&connect_clef_account=1";
      } else {
        redirectURL += "?connect_clef_account=1";
      }
      target = $('#clef-button-target').attr('data-app-id', this.opts.appID).attr('data-redirect-url', redirectURL).attr('data-state', this.opts.state).attr('data-embed', true);
      this.button = new ClefButton({
        el: $('#clef-button-target')[0]
      });
      return this.button.render();
    }
  });
  this.TutorialView = TutorialView;
  this.SetupTutorialView = SetupTutorialView;
  return this.ConnectTutorialView = ConnectTutorialView;
}).call(this, jQuery, Backbone);

(function($) {
  var AppView, FormVisualization, SettingsModel, SettingsView;
  AppView = Backbone.View.extend({
    el: $('#clef-settings-container'),
    initialize: function(opts) {
      this.opts = opts;
      this.$msgContainer = this.$el.find('.message');
      this.settings = new SettingsView(_.extend({
        options_name: "wpclef"
      }, this.opts));
      this.settings.hide();
      if (!this.settings.isConfigured()) {
        this.tutorial = new SetupTutorialView(_.extend({}, this.opts));
        this.tutorial.hide();
        this.listenTo(this.tutorial, 'message', this.displayMessage);
      }
      if (this.opts.isNetworkSettings) {
        delete this.opts['formSelector'];
        this.multisiteOptionsView = new MultisiteOptionsView(this.opts);
      }
      this.listenTo(this.settings, 'message', this.displayMessage);
      return this.render();
    },
    render: function() {
      if (this.opts.isUsingIndividualSettings || (this.opts.isNetworkSettings && this.opts.isNetworkSettingsEnabled)) {
        if (this.multisiteOptionsView) {
          this.multisiteOptionsView.show();
        }
        if (this.settings.isConfigured()) {
          this.settings.show();
        } else {
          this.tutorial.show();
          this.listenToOnce(this.tutorial, 'applicationCreated', this.configure);
          this.listenToOnce(this.tutorial, 'done', this.hideTutorial);
        }
      }
      return this.$el.fadeIn();
    },
    configure: function(data) {
      this.settings.model.configure(data);
      return this.settings.render();
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
        this.displayMessage(clefTranslations.messages.success.configured);
        ({
          type: "updated"
        });
      }
      this.tutorial.slideUp();
      return this.settings.show();
    }
  });
  SettingsView = AjaxSettingsView.extend({
    errorTemplate: _.template("<div class='error form-error'><%=message%></div>"),
    genericErrorMessage: clefTranslations.messages.error.generic,
    addEvents: {
      "click .generate-override": "generateOverride",
      "click .clef-settings__saveButton": "saveForm",
      "click .clef-settings__resetButton": "resetForm",
      "click a.show-support-html": "showSupportHTML"
    },
    constructor: function(opts) {
      this.events = _.extend(this.events, this.addEvents);
      return SettingsView.__super__.constructor.call(this, opts);
    },
    initialize: function(opts) {
      this.opts = opts;
      this.modelClass = SettingsModel;
      SettingsView.__super__.initialize.call(this, opts);
      this.pro = new ClefProView(opts, this.model);
      this.inviteUsersView = new InviteUsersView(opts);
      this.formView = new FormVisualization({
        model: this.model
      });
      this.xmlEl = this.model.cFindInput('clef_password_settings_xml_allowed').parents('.input-container');
      this.overrideContainer = this.$el.find('.override-settings');
      this.setOverrideLink();
      this.badgePreviewContainer = this.$el.find('.support-settings .ftr-preview');
      this.listenTo(this.model, "change", this.clearErrors);
      this.listenTo(this.model, "error", this.error);
      window.onbeforeunload = (function(_this) {
        return function(e) {
          if (_this.isSaving()) {
            return clefTranslations.messages.saving;
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
      this.overrideContainer.toggleClass('set', this.model.overrideIsSet());
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
      button = this.overrideContainer.find('a.button');
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
    saveForm: function(e) {
      e.preventDefault();
      return this.model.save({}, {
        success: (function(_this) {
          return function() {
            _this.trigger('message', {
              message: "Settings saved.",
              type: 'updated'
            });
            return $('html, body').animate({
              scrollTop: 0
            }, "slow");
          };
        })(this),
        error: this.model.saveError.bind(this.model)
      });
    },
    resetForm: function(e) {
      e.preventDefault();
      if (confirm("Are you sure you want to clear your settings?")) {
        return this.model.reset({
          success: function() {
            return window.location = window.location;
          }
        });
      }
    },
    showSupportHTML: function(e) {
      e.preventDefault();
      return $('.support-html-container').slideDown();
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
    loginIsEmbedded: function() {
      return !!parseInt(this.cget('clef_form_settings_embed_clef'));
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
    reset: function(options) {
      if (options == null) {
        options = {};
      }
      return this.configure({
        appID: "",
        appSecret: ""
      }, options);
    },
    configure: function(data, options) {
      var k, toSave, v, _ref;
      if (options == null) {
        options = {};
      }
      toSave = {
        'wpclef[clef_settings_app_id]': data.appID,
        'wpclef[clef_settings_app_secret]': data.appSecret
      };
      if (data.configuration) {
        _ref = data.configuration;
        for (k in _ref) {
          v = _ref[k];
          toSave["wpclef[" + k + "]"] = v;
        }
      }
      return this.save(toSave, options);
    }
  });
  FormVisualization = Backbone.View.extend({
    el: $("#login-form-view"),
    template: function() {
      return _.template($('#form-template').html());
    },
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
      this.$el.toggleClass('only-clef', this.model.passwordsFullyDisabled());
      return this.$el.toggleClass('embed-clef', this.model.loginIsEmbedded());
    }
  });
  this.AppView = AppView;
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
  var ConnectView;
  ConnectView = Backbone.View.extend({
    el: "#connect-clef-account",
    events: {
      "click #disconnect": "disconnectClefAccount"
    },
    disconnectAction: "disconnect_clef_account",
    messageTemplate: _.template("<div class='<%=type%> connect-clef-message'><%=message%></div>"),
    initialize: function(opts) {
      this.opts = opts;
      this.tutorial = new ConnectTutorialView(_.clone(this.opts));
      this.disconnect = this.$el.find('.disconnect-clef');
      this.listenTo(this.tutorial, 'done', this.finishTutorial);
      return this.render();
    },
    show: function() {
      return this.$el.fadeIn();
    },
    render: function() {
      this.tutorial.render();
      if (!this.opts.connected) {
        this.disconnect.hide();
        return this.tutorial.show();
      } else {
        this.tutorial.slideUp();
        return this.disconnect.show();
      }
    },
    disconnectClefAccount: function(e) {
      var data, failure;
      e.preventDefault();
      failure = (function(_this) {
        return function(data) {
          var msg;
          msg = ClefUtils.getErrorMessage(data);
          return _this.showMessage({
            message: _.template(clefTranslations.messages.error.disconnect)({
              error: msg
            }),
            type: "error"
          });
        };
      })(this);
      data = {
        action: this.disconnectClefAction,
        _wpnonce: this.opts.nonces.disconnectClef
      };
      return $.post("" + ajaxurl + "?action=" + this.disconnectAction, data).success((function(_this) {
        return function(data) {
          var msg;
          if (data.success) {
            _this.opts.connected = false;
            _this.render();
            msg = clefTranslations.messages.success.disconnect;
            return _this.showMessage({
              message: msg,
              type: "updated"
            });
          } else {
            return failure(data);
          }
        };
      })(this)).fail(function(res) {
        return failure(res.responseText);
      });
    },
    showMessage: function(data) {
      if (this.message) {
        this.message.remove();
      }
      this.message = $(this.messageTemplate(data)).hide();
      return this.message.prependTo(this.$el).slideDown();
    },
    finishTutorial: function() {
      return window.location = '';
    }
  });
  return window.ConnectView = ConnectView;
}).call(this, jQuery, Backbone);

var __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

(function($, Backbone) {
  var CustomizationView, ProView;
  ProView = Backbone.View.extend({
    el: '#clef-pro-section',
    getServicesURL: ajaxurl + '?action=clef_get_pro_services',
    subViews: [],
    initialize: function(opts, model) {
      this.opts = opts;
      this.model = model;
      return $.getJSON(this.getServicesURL, {
        _wpnonce: this.opts.nonces.getProServices
      }).success((function(_this) {
        return function(data) {
          _this.servicesAvailable = data;
          if (__indexOf.call(_this.servicesAvailable, 'customize') >= 0) {
            _this.customizer = new CustomizationView(_this.opts, _this.model);
            _this.subViews.push(_this.customizer);
          }
          return _this.render();
        };
      })(this)).fail(function(res) {
        return console.log(res.responseText);
      });
    },
    render: function() {
      var view, _i, _len, _ref;
      _ref = this.subViews;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        view = _ref[_i];
        view.render();
      }
      return this.$el.show();
    }
  });
  CustomizationView = Backbone.View.extend({
    el: '#clef-pro-customization',
    events: {
      'click #clef-custom-logo-upload': 'openMediaUploader',
      'click #clef-custom-logo-clear': 'clearLogo',
      'change input, change textarea': 'render',
      'keyup textarea': 'render'
    },
    initialize: function(opts, model) {
      this.opts = opts;
      this.model = model;
      return this.preview = _.template($('#clef-customization-template').html());
    },
    render: function() {
      this.$el.find('#custom-login-view').html(this.preview({
        image: this.image(),
        message: this.message()
      }));
      this.$el.find('#clef-custom-logo-clear').toggle(!!this.image());
      return this.$el.show();
    },
    openMediaUploader: function() {
      if (this.uploader) {
        this.uploader.open();
        return;
      }
      this.uploader = wp.media.frames.file_frame = wp.media({
        title: 'Choose an image',
        button: {
          text: 'Choose an image'
        },
        multiple: false
      });
      this.uploader.on('select', (function(_this) {
        return function() {
          var attachment;
          attachment = _this.uploader.state().get('selection').first().toJSON();
          _this.model.save({
            'wpclef[customization_logo]': attachment.url
          });
          return _this.render();
        };
      })(this));
      return this.uploader.open();
    },
    clearLogo: function() {
      this.model.save({
        'wpclef[customization_logo]': ''
      });
      return this.render();
    },
    image: function() {
      return this.model.cget('customization_logo');
    },
    message: function() {
      return this.$el.find('textarea').val();
    }
  });
  return window.ClefProView = ProView;
}).call(this, jQuery, Backbone);
