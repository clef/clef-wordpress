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
