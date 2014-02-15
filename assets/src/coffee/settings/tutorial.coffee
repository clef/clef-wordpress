(($, Backbone) ->

    TutorialView = Backbone.View.extend
        el: $('#clef-tutorial')
        messageTemplate:
            _.template "<div class='<%=type%> tutorial-message'>\
                          <%=message%>\
                        </div>"
        events:
            "click .next": "next"
            "click .previous": "previous"
            "click .done": "done"

        slideClass: 'sub'

        initialize: (@opts) ->
            if window.chrome
                @$el.find('.waltz').addClass @slideClass

            @subs = []
            potentialSubs = @$el.find(".#{@slideClass}")
                .filter(@opts.slideFilterSelector)

            for sub in potentialSubs
                @subs.push new SubTutorialView { el: sub }

            @currentSub = @subs[0]

            $(window).on 'message', @handleMessages.bind(this)

        hide: (cb) ->
            @$el.slideUp(cb)

        render: ()->
            if !@$el.is(':visible')
                @currentSub.render()
                @$el.fadeIn()

        done: () ->
            @trigger "done"

        next: () ->
            newSub = @subs[_.indexOf(@subs, @currentSub) + 1]
            if newSub
                if newSub.isLogin() && @loggedIn
                    newSub = @subs[_.indexOf(@subs, @newSub) + 1]
                    
                @currentSub.hide()
                newSub.render()
                @currentSub = newSub
                @trigger "next"
            else
                @done()

        previous: ()->
            newSub = @subs[_.indexOf(@subs, @currentSub) - 1]
            if newSub
                @currentSub.hide()
                newSub.render()
                @currentSub = newSub

        handleMessages: (e) ->
            return unless e.originalEvent.origin.indexOf @opts.clefBase >= 0
            e.originalEvent.data

        connectClefAccount: (data, cb) ->
            connectData =
                _wp_nonce: @opts.nonces.connectClef
                identifier: data.identifier

            $.post @connectClefAccountAction,
                connectData,
                (data) =>
                    if data.error
                        msg = "There was a problem automatically connecting \
                        your Clef account: #{data.error}. Please refresh \
                        and try again."
                        @trigger 'message', message: msg, type: "error"
                    else
                        cb(data) if typeof(cb) == "function"

        showMessage: (opts) ->
            @$currentMessage.remove() if @$currentMessage
            @$currentMessage = $(@messageTemplate(opts))
                .hide()
                .prependTo(@$el)
                .slideDown()
            if opts.removeNext
                @listenToOnce this, "next", -> @$currentMessage.slideUp()

    , 
        extend: Backbone.View.extend


    SubTutorialView = Backbone.View.extend
        initialize: (@opts) ->
            @setElement($(@opts.el))
        render: () ->
            @$el.show()
        hide: () ->
            @$el.hide()
        remove: () ->
            @$el.remove()
        isLogin: () ->
            @$el.find('iframe.setup').length


    SetupTutorialView = TutorialView.extend
        connectClefAccountAction: ajaxurl + "?action=connect_clef_account_clef_id"
        iframePath: '/iframes/application/create/v1'

        initialize: (opts) ->
            opts.slideFilterSelector = '.setup'
            @constructor.__super__.initialize.call this, opts

            @inviter = new InviteUsersView _.extend  {
                el: @$el.find '.invite-users-container'
            }, @opts
            @listenTo @inviter, "invited", @usersInvited

        render: ()->
            if @userIsLoggedIn
                if !@currentSub.$el.hasClass 'sync'
                    @$el.addClass 'no-sync'
                else
                    @$el.addClass 'user'

            if !@$el.is(':visible')
                @loadIFrame()
                @inviter.render()

            @constructor.__super__.render.call this

        loadIFrame: () ->
            frame = @$el.find("iframe.setup")
            src = "#{@opts.clefBase}#{@iframePath}?\
                    source=#{encodeURIComponent(@opts.setup.source)}\
                    &domain=#{encodeURIComponent(@opts.setup.siteDomain)}\
                    &name=#{encodeURIComponent(@opts.setup.siteName)}"
            frame.attr('src', src)

        handleMessages: (data) ->
            data = @constructor.__super__.handleMessages.call this, data
            return if !data

            if data.type == "keys"
                @connectClefAccount identifier: data.clefID,
                    () =>
                        @trigger 'applicationCreated', data
                        @next()
            else if data.type == "user"
                @userIsLoggedIn = true
                @render()
            else if data.type == "error"
                msg = "There was a problem creating a new Clef \
                application for your WordPress site: #{data.message}\
                . Please refresh and try again. If the issue, \
                persists, email support@getclef.com."
                @showMessage message: msg, type: 'error'

        onConfigured: () ->
            setTimeout (()->
                # show logout error message after an amount of time
                $(".logout-hook-error").slideDown()
            ), 20000

        usersInvited: () ->
            @inviter.hideButton()
            setTimeout (() =>
                if @currentSub.$el.hasClass 'invite'
                    @currentSub.$el
                        .find('.button').addClass 'button-primary'
                ), 1000


    ConnectTutorialView = TutorialView.extend
        connectClefAccountAction: ajaxurl + "?action=connect_clef_account_oauth_code"
        render: ->
            if !@$el.is ':visible'
                @addButton()

            @constructor.__super__.render.call this

        addButton: ->
            target = $('#clef-button-target')
                .attr('data-app-id', @opts.appID)
                .attr('data-redirect-url', @opts.redirectURL)
            @button = new ClefButton el: $('#clef-button-target')[0]
            @button.render()

            @button.login = (data) =>
                @button.overlayClose()
                @connectClefAccount identifier: data.code,
                    (result) =>
                        msg = "You've successfully connected your account \
                        with Clef!"
                        @next()
                        @showMessage 
                            message: msg, 
                            type: "updated"
                            removeNext: true

                return undefined

    this.TutorialView = TutorialView
    this.SetupTutorialView = SetupTutorialView
    this.ConnectTutorialView = ConnectTutorialView

).call(this, jQuery, Backbone)