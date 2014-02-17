(($) ->
    Backbone.emulateHTTP = true

    AppView = Backbone.View.extend
        el: $('#clef-settings-container')
        initialize: (@opts) ->
            @$msgContainer = @$el.find('.message')
            @settings = new SettingsView (
                _.extend { options_name: "wpclef" }, @opts
            )
            
            @tutorial = new SetupTutorialView _.extend {}, @opts

            if @opts.isNetworkSettings
                delete @opts['formSelector']
                @multisiteOptionsView = new MultisiteOptionsView(@opts)

            @listenTo @settings, 'message', @displayMessage
            @listenTo @tutorial, 'message', @displayMessage

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

        displayMessage: (opts) ->
            @$msgContainer.find('p').text(opts.message)
            @$msgContainer.addClass(opts.type).slideDown()
            if opts.fade
                setTimeout (() -> @$msgContainer.slideUp()), 3000

        hideTutorial: () ->
            if @settings.isConfigured()
                @displayMessage clefTranslations.messages.success.configured
                type: "updated"

            @tutorial.hide()
            @settings.show()

    SettingsView =  AjaxSettingsView.extend
        errorTemplate: _.template "<div class='error form-error'>\
                                    <%=message%>\
                                   </div>"
        genericErrorMessage: clefTranslations.messages.error.generic
        addEvents:
            "click .generate-override": "generateOverride"
            "click input[type='submit']:not(.ajax-ignore)": "saveForm"
            "click a.show-support-html": "showSupportHTML"

        constructor: (opts) ->
            @events = _.extend @events, @addEvents
            SettingsView.__super__.constructor.call(this, opts)

        initialize: (@opts) ->
            @modelClass = SettingsModel
            SettingsView.__super__.initialize.call(this, opts)

            @inviteUsersView = new InviteUsersView(opts)
            @formView = new FormVisualization( model: @model )
            @xmlEl = @model
                .cFindInput('clef_password_settings_xml_allowed')
                .parents('.input-container')

            @overrideContainer = @$el.find '.override-settings'
            @overrideButtonContainer = @$el.find '.override-buttons'
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

            @overrideButtonContainer.toggle @model.overrideIsSet()

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

        clearErrors: (model, data) ->
            # when a model changes, if it previously was error'd, clear them
            # so we can reshow them (or do away with them) on model save
            for inputName, v of model.changed
                inp = @model.findInput(inputName).parents '.input-container'
                if inp.hasClass 'error'
                    inp.removeClass('error').next('.error.form-error').remove()

        error: (model, data) ->
            # no error messages, add a generic error message
            if !data.responseJSON.errors
                @trigger 'message', message: @genericErrorMessage, type: 'error'
                $('html, body').animate scrollTop: 0, "show"
                return

            # loop over all error messages and display them
            for inputName, msg of data.responseJSON.errors
                inp = @model.cFindInput(inputName).parents '.input-container'
                if inp.hasClass 'error'
                    inp.next('.error.form-error').html msg
                else
                    inp.addClass('error').after(@errorTemplate(message: msg))

        saveForm: (e) ->
            e.preventDefault()

            @model.save {},
                success: () =>
                    @trigger 'message',
                        message: "Settings saved.",
                        type: 'updated'
                    $('html, body').animate scrollTop: 0, "show"

                error: @model.saveError.bind(@model)

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

    this.AppView = AppView

    $.fn.serializeObject = (form) ->
        serialized = {}
        for obj in $(this).serializeArray()
            serialized[obj.name] = obj.value
        serialized


).call(this, jQuery)
