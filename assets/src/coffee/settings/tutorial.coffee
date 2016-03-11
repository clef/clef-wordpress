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
            @subs = []
            potentialSubs = @$el.find(".#{@slideClass}")
                .filter(@opts.slideFilterSelector)

            for sub in potentialSubs
                @subs.push new SubTutorialView { el: sub }

            @currentSub = @subs[0]

            $(window).on 'message', @handleMessages.bind(this)

            @hide()
            @render()

        slideUp: (cb) ->
            @$el.slideUp(cb)

        hide: (cb) ->
            @$el.hide(cb)

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
            return unless e.originalEvent.origin.indexOf(@opts.clefBase) >= 0
            data = e.originalEvent.data
            data = JSON.parse(data) if typeof(data) == "string"
            data

        connectClefAccount: (data, cb) ->
            connectData =
                _wpnonce: @opts.nonces.connectClef
                identifier: data.identifier
                state: data.state
                action: @connectClefAction

            failure = (data) =>
                msg = ClefUtils.getErrorMessage(data)
                @showMessage
                    message: _.template(
                        clefTranslations.messages.error.connect
                    )(error: msg),
                    type: "error"

            $.post "#{ajaxurl}?action=#{@connectClefAction}", connectData
                .success (data) ->
                    if data.success
                        cb(data) if typeof(cb) == "function"
                    else
                        failure data
                .fail (res) -> failure res.responseText

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
        find: (query) ->
            @$el.find(query)
        isLogin: () ->
            @$el.find('iframe.setup').length
        isSync: ->
            @$el.hasClass('sync') && @$el.find('iframe').length


    SetupTutorialView = TutorialView.extend
        connectClefAction: "connect_clef_account_clef_id"
        iframePath: '/iframes/application/create/v2'

        initialize: (opts) ->
            opts.slideFilterSelector = '.setup'
            @inviter = new InviteUsersView _.extend  {
                el: @$el.find '.invite-users-container'
            }, opts
            @listenTo @inviter, "invited", @usersInvited

            @constructor.__super__.initialize.call this, opts

            @on 'next', @shouldLoadIFrame


        render: ()->
            @inviter.render()

            @constructor.__super__.render.call this

        shouldLoadIFrame: ->
            if @currentSub.isSync()
                @loadIFrame =>
                    @currentSub.find('.spinner-container').hide()
                    @iframe.fadeIn()

        loadIFrame: (cb) ->
            return if @iframe
            @iframe = @$el.find("iframe.setup")
            affiliates = encodeURIComponent(@opts.setup.affiliates.join(','))
            src = "#{@opts.clefBase}#{@iframePath}?\
                    source=#{encodeURIComponent(@opts.setup.source)}\
                    &domain=#{encodeURIComponent(@opts.setup.siteDomain)}\
                    &logout_hook=#{encodeURIComponent(@opts.setup.logoutHook)}\
                    &name=#{encodeURIComponent(@opts.setup.siteName)}\
                    &affiliates=#{affiliates}"
            @iframe.attr('src', src)
            @iframe.on 'load', cb

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
        render: ->
            @addButton()
            @constructor.__super__.render.call this
        addButton: ->
            return if @button

            redirectURL = window.location.href
            if /\?/.test redirectURL
                redirectURL += "&connect_clef_account=1"
            else
                redirectURL += "?connect_clef_account=1"

            target = $('#clef-button-target')
                .attr('data-app-id', @opts.appID)
                .attr('data-redirect-url', redirectURL)
                .attr('data-state', @opts.state)
                .attr('data-embed', true)
            @button = new ClefButton el: $('#clef-button-target')[0]
            @button.render()

    this.TutorialView = TutorialView
    this.SetupTutorialView = SetupTutorialView
    this.ConnectTutorialView = ConnectTutorialView

).call(this, jQuery, Backbone)
