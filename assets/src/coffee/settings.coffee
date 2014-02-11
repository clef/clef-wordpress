(($) ->
    Backbone.emulateHTTP = true

    AppView = Backbone.View.extend
        el: $('#clef-settings-container')
        connectClefAccountAction: ajaxurl + "?action=connect_clef_account"
        initialize: (@opts) ->
            @$msgContainer = @$el.find('.message')
            @settings = new SettingsView (
                _.extend { options_name: "wpclef" }, @opts
            )
            @tutorial = new TutorialView _.extend {}, @opts

            if @settings.isConfigured()
                @settings.render()
            else
                @tutorial.render()
                @listenToOnce(
                    @tutorial,
                    'applicationCreated',
                    @configure.bind this
                )

        configure: (data) ->
            @connectClefAccount data

            @settings.model.configure(data)
            @tutorial.hide()
            @settings.render()

        connectClefAccount: (data) ->
            connectData =
                _wp_nonce: @opts.setup._wp_nonce
                clefID: data.clefID

            $.post @connectClefAccountAction,
                connectData,
                (data) =>
                    if data.error
                        msg = "There was a problem automatically connecting \
                        your Clef account: #{data.error}."
                        @displayMessage msg, "error"

        displayMessage: (msg, opts) ->
            @$msgContainer.find('p').text(msg)
            @$msgContainer.addClass(opts.type).slideDown()
            if opts.fade
                setTimeout (() -> @$msgContainer.slideUp()), 3000




    SettingsView =  AjaxSettingsView.extend
        addEvents: 
            "click .generate-override": "generateOverride"

        constructor: (opts) ->
            @events = _.extend @events, @addEvents
            SettingsView.__super__.constructor.call(this, opts)

        initialize: (opts) ->
            @modelClass = SettingsModel
            SettingsView.__super__.initialize.call(this, opts)

            @formView = new FormVisualization( model: @model )
            @xmlEl = @model
                .cFindInput('clef_password_settings_xml_allowed')
                .parents('.input-container')

            @overrideContainer = @$el.find '.override-settings'
            @overrideButtonContainer = @$el.find '.override-buttons'
            @setOverrideLink()

            @badgePreviewContainer = @$el.find '.support-settings .footer-preview'

            window.onbeforeunload = (e) =>
                if @isSaving()
                    "Settings are being saved. Still want to navigate away?"

        updated: (obj, data) ->
            SettingsView.__super__.updated.call(this, obj, data)
            @setOverrideLink()

        render: () ->
            SettingsView.__super__.render.call this
            passwordsDisabled = @model.passwordsDisabled()

            @xmlEl.toggle passwordsDisabled
            @toggleOverrideContainer passwordsDisabled

            @overrideButtonContainer.toggle @model.overrideIsSet()

            @renderSupportBadge()

            if @$el.is(':not(:visible)')
                @$el.fadeIn()

        toggleInputs: (e) ->
            @formView.toggleForm(!!parseInt(e.currentTarget.value))

        toggleOverrideContainer: (show) ->
            @overrideContainer.toggle show

        generateOverride: () ->
            rnd = Math.random().toString(36).slice(2)
            @model.save 'wpclef[clef_override_settings_key]': rnd

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

        isConfigured: () ->
            @model.isConfigured()


    SettingsModel = AjaxSettingsModel.extend
        cFindInput: (name) ->
            @findInput "wpclef[#{name}]"

        cget: (key) ->
            @get "wpclef[#{key}]"

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

        isConfigured: () ->
            !!(@cget('clef_settings_app_id') &&
                @cget('clef_settings_app_secret'))

        configure: (data) ->
            @save
                'wpclef[clef_settings_app_id]': data.appID
                'wpclef[clef_settings_app_secret]': data.appSecret

    FormVisualization = Backbone.View.extend
        el: $("#login-form-view")
        template: _.template($('#form-template').html())

        initialize: (@opts) ->
            @model = @opts.model
            @listenTo @model, 'change', @toggleForm
            @render()

        render: () ->
            @$el.html(@template)
            @$el.find('input[type="submit"]').on 'click', 
                (e) -> e.preventDefault()
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