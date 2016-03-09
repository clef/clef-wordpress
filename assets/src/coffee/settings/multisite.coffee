(($) ->
    MultisiteOptionsView = AjaxSettingsView.extend
        el: '#multisite-settings'
        initialize: (opts) ->
            @modelClass = MultisiteOptionsModel
            MultisiteOptionsView.__super__.initialize.call(this, opts)


    MultisiteOptionsModel = AjaxSettingsModel.extend
        parse: (data, options)->
            options.url = ajaxurl + '?action=clef_multisite_settings'
            MultisiteOptionsModel.__super__.parse.call(this, data, options)
        addActionToData: (data) ->
            data.action = "clef_multisite_settings"
            data


    this.MultisiteOptionsModel = MultisiteOptionsModel
    this.MultisiteOptionsView = MultisiteOptionsView

).call(this, jQuery)


