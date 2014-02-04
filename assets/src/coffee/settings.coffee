(($) ->
    AppView = Backbone.View.extend
        id: "clef-settings-container"
        initialize: (@opts) ->

            @settings = new SettingsView @opts
            @tutorial = new TutorialView @opts

            if @opts.configured
                @settings.render()
            else
                @tutorial.render()

    TutorialView = Backbone.View.extend
        el: $('#clef-tutorial')
        events:
            "click .next": "next"
            "click .previous": "previous"

        initialize: (@opts) ->
            @subs = []
            for sub in @$el.find('.sub')
                @subs.push new SubTutorialView { el: sub }

            @currentSub = @subs[0]

            $(window).on 'message', @handleLogin.bind(this)

        render: ()->
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
            frame.attr('src', frame.data('src'))

        handleLogin: () ->
            @loggedIn = true
            if @currentSub.isLogin()
                @next()

            @createApplication()

        createApplication: () ->
            $.ajax
                method: "POST"
                url: "#{@opts.clefBase}/api/v1/manage/create"
                data: @opts.setup
                success: (data) ->
                    console.log data


    SubTutorialView = Backbone.View.extend
        initialize: (@opts) ->
            @setElement($(@opts.el))

        render: () ->
            @$el.show()
        hide: () ->
            @$el.hide()
        remove: () ->
            console.log 'removing'
            @$el.remove()
        isLogin: () ->
            @$el.find('iframe').length

    SettingsView = Backbone.View.extend
        el: $('#clef-settings')
        initialize: (@opts) ->
        hide: () ->
            @$el.hide()
        render: () ->
            @$el.fadeIn()



    this.AppView = AppView

).call(this, jQuery)

app = new AppView options