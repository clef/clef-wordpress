(($, Backbone) ->
    TutorialView = Backbone.View.extend
        el: $('#clef-tutorial')
        connectClefAccountAction: ajaxurl + "?action=connect_clef_account"
        events:
            "click .next": "next"
            "click .previous": "previous"
            "click .done": "done"

        iframePath: '/iframes/application/create/v1'

        initialize: (@opts) ->
            if window.chrome
                @$el.find('.waltz').addClass 'sub'

            @subs = []
            for sub in @$el.find('.sub')
                @subs.push new SubTutorialView { el: sub }

            @currentSub = @subs[0]

            $(window).on 'message', @handleMessages.bind(this)

        hide: (cb) ->
            @$el.slideUp(cb)

        render: ()->
            if @userIsLoggedIn
                if !@currentSub.$el.hasClass 'sync'
                    @$el.addClass 'no-sync'
                else
                    @$el.addClass 'user'


            if !@$el.is(':visible')
                @currentSub.render()
                @loadIFrame()
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
            else
                @done()

        previous: ()->
            newSub = @subs[_.indexOf(@subs, @currentSub) - 1]
            if newSub
                @currentSub.hide()
                newSub.render()
                @currentSub = newSub

        loadIFrame: () ->
            frame = @$el.find("iframe")
            src = "#{@opts.clefBase}#{@iframePath}?source=wordpress\
                    &domain=#{encodeURIComponent(@opts.setup.siteDomain)}\
                    &name=#{encodeURIComponent(@opts.setup.siteName)}"
            frame.attr('src', src)

        handleMessages: (data) ->
            return unless data.originalEvent.origin.indexOf @opts.clefBase >= 0
            data = data.originalEvent.data
            if data.type == "keys"
                @connectClefAccount data,
                    () =>
                        @trigger 'applicationCreated', data
                        @next()

            else if data.type == "user"
                @userIsLoggedIn = true
                @render()

        onConfigured: () ->
            setTimeout (()->
                # show logout error message after an amount of time
                $(".logout-hook-error").slideDown()
            ), 20000

        connectClefAccount: (data, cb) ->
            connectData =
                _wp_nonce: @opts.setup._wp_nonce_connect_clef
                clefID: data.clefID

            $.post @connectClefAccountAction,
                connectData,
                (data) =>
                    if data.error
                        msg = "There was a problem automatically connecting \
                        your Clef account: #{data.error}. Please refresh \
                        and try again."
                        @trigger 'message', message: msg, type: "error"
                    else
                        cb() if typeof(cb) == "function"


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
            @$el.find('iframe').length

    this.TutorialView = TutorialView

).call(this, jQuery, Backbone)
