var app;

(function($) {
  var AppView, SettingsView, SubTutorialView, TutorialView;
  AppView = Backbone.View.extend({
    id: "clef-settings-container",
    initialize: function(opts) {
      this.opts = opts;
      this.settings = new SettingsView(this.opts);
      this.tutorial = new TutorialView(this.opts);
      if (this.opts.configured) {
        return this.settings.render();
      } else {
        return this.tutorial.render();
      }
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
      if (this.currentSub.isLogin()) {
        this.next();
      }
      return this.createApplication();
    },
    createApplication: function() {
      return $.ajax({
        method: "POST",
        url: "" + this.opts.clefBase + "/api/v1/manage/create",
        data: this.opts.setup,
        success: function(data) {
          return console.log(data);
        }
      });
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
      console.log('removing');
      return this.$el.remove();
    },
    isLogin: function() {
      return this.$el.find('iframe').length;
    }
  });
  SettingsView = Backbone.View.extend({
    el: $('#clef-settings'),
    initialize: function(opts) {
      this.opts = opts;
    },
    hide: function() {
      return this.$el.hide();
    },
    render: function() {
      return this.$el.fadeIn();
    }
  });
  return this.AppView = AppView;
}).call(this, jQuery);

app = new AppView(options);
