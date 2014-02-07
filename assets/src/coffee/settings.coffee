(($) ->
    Backbone.emulateHTTP = true

    AppView = Backbone.View.extend
        id: "clef-settings-container"
        initialize: (@opts) ->
            @settings = new SettingsView _.extend @opts, { options_name: "wpclef" }
            @settings.render()

            window.onbeforeunload = (e) =>
                if @settings.isSaving()
                    "Some settings are still being saved. Are you sure you want to navigate away?"

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

            # @createApplication()

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

    SettingsView =  AjaxSettingsView.extend
        initialize: (opts) ->
            @modelClass = SettingsModel
            SettingsView.__super__.initialize.call(this, opts)

            @formView = new FormVisualization( model: @model )
            @xmlEl = @model
                .cfindInput('clef_password_settings_xml_allowed')
                .parents('.input-container')

            @overrideContainer = @$el.find '.override-settings'
            @overrideButtonContainer = @$el.find '.override-buttons'
            @setOverrideLink()

            @badgePreviewContainer = @$el.find '.support-settings .footer-preview'

        updated: (obj, data) ->
            SettingsView.__super__.updated.call(this, obj, data)
            @setOverrideLink()

        render: () ->
            SettingsView.__super__.render.call this
            passwordsDisabled = @model.passwordsDisabled()

            @xmlEl.toggle passwordsDisabled
            @overrideContainer.toggle passwordsDisabled

            @overrideButtonContainer.toggle @model.overrideIsSet()

            @renderSupportBadge()

            if @$el.is(':not(:visible)')
                @$el.fadeIn()

        toggleInputs: (e) ->
            @formView.toggleForm(!!parseInt(e.currentTarget.value))

        setOverrideLink: () ->
            key = @model.overrideKey()
            return if !key
            if !@overrideBase
                @overrideBase = @overrideContainer.find('label').text() 

            button = @overrideButtonContainer.find('a')
            button.on 'click', (e) -> e.preventDefault()
            button.attr(
                'href', 
                @overrideBase + key
            )

        isSaving: () ->
            @model.saving

        renderSupportBadge: () ->
            setting = @model.badgeSetting()
            @badgePreviewContainer.toggle(
                setting != "disabled"
            )

            @badgePreviewContainer.find('a').toggleClass(
                'pretty',
                setting == "badge"
            )


    
    SettingsModel = AjaxSettingsModel.extend
        cget: (key) ->
            @get "wpclef[#{key}]"
        cfindInput: (name) ->
            name = "wpclef[#{name}]"
            SettingsModel.__super__.findInput.call(this, name)

        passwordsDisabled: () ->
            !!parseInt(@cget('clef_password_settings_disable_passwords')) || 
            @cget('clef_password_settings_disable_certain_passwords') != "Disabled" || 
            @passwordsFullyDisabled()

        passwordsFullyDisabled: () ->
            !!parseInt @cget('clef_password_settings_force')

        overrideIsSet: () ->
            !!@overrideKey()

        overrideKey: () ->
            @cget('clef_override_settings_key')

        badgeSetting: () ->
            @cget('support_clef_badge').toLowerCase()
            

    FormVisualization = Backbone.View.extend
        el: $("#login-form-view")
        template: _.template($('#form-template').html())

        initialize: (@opts) ->
            @model = @opts.model
            @listenTo @model, 'change', @toggleForm
            @render()

        render: () ->
            @$el.html(@template)
            @$el.find('input[type="submit"]').on 'click', (e) -> e.preventDefault()
            @toggleForm()

        toggleForm: (e) ->
            @$el.toggleClass('only-clef', @model.passwordsFullyDisabled())



    this.AppView = AppView

    $(document).ready () ->
        app = new AppView options

    $.fn.serializeObject = (form) ->
        serialized = {}
        for obj in $(this).serializeArray()
            serialized[obj.name] = obj.value
        serialized


).call(this, jQuery)