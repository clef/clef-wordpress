(($, Backbone) ->
    TutorialView = Backbone.View.extend
        el: $('#clef-tutorial')
        events:
            "click .next": "next"
            "click .previous": "previous"

        iframePath: '/iframes/application/create/v1'

        initialize: (@opts) ->
            @subs = []
            for sub in @$el.find('.sub')
                @subs.push new SubTutorialView { el: sub }

            @currentSub = @subs[0]

            $(window).on 'message', @handleMessages.bind(this)

        hide: (cb) ->
            @$el.fadeOut(cb)

        render: ()->
            if @userIsLoggedIn
                $.each @subs, (i, el) -> el.userify()

            if !@$el.is(':visible')
                @currentSub.render()
                @loadIFrame()
                @$el.fadeIn()

        next: () ->
            newSub = @subs[_.indexOf(@subs, @currentSub) + 1]
            if newSub
                if newSub.isLogin() && @loggedIn
                    newSub = @subs[_.indexOf(@subs, @newSub) + 1]
                    
                @currentSub.hide()
                newSub.render()
                @currentSub = newSub

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
                @trigger 'applicationCreated', data
            else if data.type == "user"
                @userIsLoggedIn = true
                @render()

        onConfigured: () ->
            setTimeout (()->
                # show logout error message after an amount of time
                $(".logout-hook-error").slideDown()
            ), 20000

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
        userify: () ->
            @$el.addClass 'user'

    this.TutorialView = TutorialView

).call(this, jQuery, Backbone)