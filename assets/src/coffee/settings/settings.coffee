(($) ->
    AppView = Backbone.View.extend
        el: $('#clef-settings-container')
        initialize: (@opts) ->
            @$msgContainer = @$el.find('.message')
            @settings = new SettingsView (
                _.extend { options_name: "wpclef" }, @opts
            )
            @settings.hide()

            if !@settings.isConfigured()
                @tutorial = new SetupTutorialView _.extend {}, @opts
                @tutorial.hide()
                @listenTo @tutorial, 'message', @displayMessage


            if @opts.isNetworkSettings
                delete @opts['formSelector']
                @multisiteOptionsView = new MultisiteOptionsView(@opts)

            @listenTo @settings, 'message', @displayMessage

            @render()

        render: ->
            if @opts.isUsingIndividualSettings or
            (@opts.isNetworkSettings && @opts.isNetworkSettingsEnabled)
                @multisiteOptionsView.show() if @multisiteOptionsView
                if @settings.isConfigured()
                    @settings.show()
                else
                    @tutorial.show()
                    @listenToOnce @tutorial, 'applicationCreated', @configure
                    @listenToOnce @tutorial, 'done', @hideTutorial

            @$el.fadeIn()

        configure: (data) ->
            @settings.model.configure(data)
            @settings.render()

        displayMessage: (opts) ->
            @$msgContainer.find('p').text(opts.message)
            @$msgContainer.addClass(opts.type).slideDown()
            if opts.fade
                setTimeout (() -> @$msgContainer.slideUp()), 3000

        hideTutorial: () ->
            if @settings.isConfigured()
                @displayMessage clefTranslations.messages.success.configured
                type: "updated"

            @tutorial.slideUp()
            @settings.show()

    SettingsView =  AjaxSettingsView.extend
        errorTemplate: _.template "<div class='error form-error'>\
                                    <%=message%>\
                                   </div>"
        genericErrorMessage: clefTranslations.messages.error.generic
        addEvents:
            "click .generate-override": "generateOverride"
            "click .clef-settings__saveButton": "saveForm"
            "click .clef-settings__resetButton": "resetForm"
            "click a.show-support-html": "showSupportHTML"

        constructor: (opts) ->
            @events = _.extend @events, @addEvents
            SettingsView.__super__.constructor.call(this, opts)

        initialize: (@opts) ->
            @modelClass = SettingsModel
            SettingsView.__super__.initialize.call(this, opts)

            @pro = new ClefProView(opts, @model)
            @inviteUsersView = new InviteUsersView(opts)
            @formView = new FormVisualization( model: @model )
            @xmlEl = @model
                .cFindInput('clef_password_settings_xml_allowed')
                .parents('.input-container')

            @overrideContainer = @$el.find '.override-settings'
            @setOverrideLink()

            @badgePreviewContainer = @$el.find '.support-settings .ftr-preview'

            @listenTo @model, "change", @clearErrors
            @listenTo @model, "error", @error
            window.onbeforeunload = (e) =>
                if @isSaving()
                    clefTranslations.messages.saving

            @render()

        updated: (obj, data) ->
            SettingsView.__super__.updated.call(this, obj, data)
            @setOverrideLink()

        render: () ->
            SettingsView.__super__.render.call this
            passwordsDisabled = @model.passwordsDisabled()

            $('#clef-settings-header').show()

            @xmlEl.toggle passwordsDisabled
            @toggleOverrideContainer passwordsDisabled

            @overrideContainer.toggleClass 'set', @model.overrideIsSet()

            @inviteUsersView.render()

            @renderSupportBadge()

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

            button = @overrideContainer.find('a.button')
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

        saveForm: (e) ->
            e.preventDefault()

            @model.save {},
                success: () =>
                    @trigger 'message',
                        message: "Settings saved.",
                        type: 'updated'
                    $('html, body').animate scrollTop: 0, "slow"

                error: @model.saveError.bind(@model)

        resetForm: (e) ->
            e.preventDefault()
            if confirm("Are you sure you want to clear your settings?")
                @model.reset success: -> window.location = window.location

        showSupportHTML: (e) ->
            e.preventDefault()
            $('.support-html-container').slideDown()

    SettingsModel = AjaxSettingsModel.extend
        cFindInput: (name) ->
            @findInput "wpclef[#{name}]"

        cget: (key) ->
            @get "wpclef[#{key}]"

        passwordsDisabled: () ->
            !!parseInt(@cget('clef_password_settings_disable_passwords')) ||
            @cget('clef_password_settings_disable_certain_passwords') != "" ||
            @passwordsFullyDisabled()

        passwordsFullyDisabled: () ->
            !!parseInt @cget('clef_password_settings_force')

        loginIsEmbedded: ->
            !!parseInt @cget('clef_form_settings_embed_clef')

        overrideIsSet: () ->
            !!@overrideKey()

        overrideKey: () ->
            @cget('clef_override_settings_key')

        badgeSetting: () ->
            @cget('support_clef_badge').toLowerCase()

        isConfigured: () ->
            !!(@cget('clef_settings_app_id') &&
                @cget('clef_settings_app_secret'))

        reset: (options={}) ->
            @configure { appID: "", appSecret: "" }, options

        configure: (data, options={}) ->
            toSave = {
                'wpclef[clef_settings_app_id]': data.appID
                'wpclef[clef_settings_app_secret]': data.appSecret
            }

            if data.configuration
                for k, v of data.configuration
                    toSave["wpclef[#{k}]"] = v

            @save toSave, options



    FormVisualization = Backbone.View.extend
        el: $("#login-form-view")
        template: () -> _.template($('#form-template').html())

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
            @$el.toggleClass('embed-clef', @model.loginIsEmbedded())

    this.AppView = AppView

    $.fn.serializeObject = (form) ->
        serialized = {}
        for obj in $(this).serializeArray()
            serialized[obj.name] = obj.value
        serialized


).call(this, jQuery)
