(($) ->
    MultisiteOptionsView = AjaxSettingsView.extend
        el: '#clef-multisite-options'
        enabled_template: _.template($('#multisite-enabled-template').html())
        disabled_template: _.template($('#multisite-disabled-template').html())
        initialize: (opts) ->
            @modelClass = MultisiteOptionsModel
            MultisiteOptionsView.__super__.initialize.call(this, opts)
        render: ->
            if @opts.overridden_by_network_settings
                template = @enabled_template
            else
                template = @disabled_template

            @$el.html(template())

    MultisiteNetworkOptionsView = MultisiteOptionsView.extend
        render: ->
            console.log(@modelClass)
            if @opts.network_settings_enabled
                template = @enabled_template
            else
                template = @disabled_template

            @$el.html(template())

    MultisiteOptionsModel = AjaxSettingsModel.extend
        parse: (data, options)->
            options.url = ajaxurl + '?action=clef_multisite_options'
            MultisiteOptionsModel.__super__.parse.call(this, data, options)

    this.MultisiteOptionsModel = MultisiteOptionsModel
    this.MultisiteOptionsView = MultisiteOptionsView
    this.MultisiteNetworkOptionsView = MultisiteNetworkOptionsView

).call(this, jQuery)


