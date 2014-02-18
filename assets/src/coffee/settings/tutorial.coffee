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
            if window.chrome and not window.waltzIsInstalled
                @$el.find('.waltz').addClass @slideClass

            @subs = []
            potentialSubs = @$el.find(".#{@slideClass}")
                .filter(@opts.slideFilterSelector)

            for sub in potentialSubs
                @subs.push new SubTutorialView { el: sub }

            @currentSub = @subs[0]

            $(window).on 'message', @handleMessages.bind(this)

            @render()

        hide: (cb) ->
            @$el.slideUp(cb)

        show: ->
            @$el.fadeIn()

        render: ()->
            @currentSub.render()

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
                _wpnonce: @opts.nonces.connectClef
                identifier: data.identifier

            $.post @connectClefAccountAction,
                connectData,
                (data) =>
                    if data.success
                        cb(data) if typeof(cb) == "function"
                    else if data.data and data.data.error
                        @showMessage
                            message: _.template(
                                clefTranslations.messages.error.connect
                            )(error: data.data.error), 
                            type: "error"

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
            @inviter = new InviteUsersView _.extend  {
                el: @$el.find '.invite-users-container'
            }, opts
            @listenTo @inviter, "invited", @usersInvited
            
            @constructor.__super__.initialize.call this, opts


        render: ()->
            if @userIsLoggedIn
                if !@currentSub.$el.hasClass 'sync'
                    @$el.addClass 'no-sync'
                else
                    @$el.addClass 'user'

            @loadIFrame()
            @inviter.render()

            @constructor.__super__.render.call this

        loadIFrame: () ->
            return if @iframe
            @iframe = @$el.find("iframe.setup")
            src = "#{@opts.clefBase}#{@iframePath}?\
                    source=#{encodeURIComponent(@opts.setup.source)}\
                    &domain=#{encodeURIComponent(@opts.setup.siteDomain)}\
                    &logout_hook=#{encodeURIComponent(@opts.setup.logoutHook)}\
                    &name=#{encodeURIComponent(@opts.setup.siteName)}"
            @iframe.attr('src', src)

        handleMessages: (data) ->
            data = @constructor.__super__.handleMessages.call this, data
            return if !data

            if data.type == "keys"
                @connectClefAccount identifier: data.clefID,
                    () =>
                        @trigger 'applicationCreated', data
                        @next()
                        @showMessage
                            message: clefTranslations.messages.success.connect
                            type: "updated"
                            removeNext: true
            else if data.type == "user"
                @userIsLoggedIn = true
                @render()
            else if data.type == "error"
                @showMessage 
                    message: _.template(
                        clefTranslations.messages.error.create
                    )(error: data.message)
                    type: 'error'

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
            @addButton()
            @constructor.__super__.render.call this

        addButton: ->
            return if @button
            target = $('#clef-button-target')
                .attr('data-app-id', @opts.appID)
                .attr('data-redirect-url', @opts.redirectURL)
            @button = new ClefButton el: $('#clef-button-target')[0]
            @button.render()

            @button.login = (data) =>
                @button.overlayClose()
                @connectClefAccount identifier: data.code,
                    (result) =>
                        @next()
                        @showMessage 
                            message: clefTranslations.messages.success.connect, 
                            type: "updated"
                            removeNext: true

                return undefined

    this.TutorialView = TutorialView
    this.SetupTutorialView = SetupTutorialView
    this.ConnectTutorialView = ConnectTutorialView

).call(this, jQuery, Backbone)
