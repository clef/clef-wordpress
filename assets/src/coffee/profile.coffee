(($) ->

    class ClefProfile
        selector: '#clef-profile'
        disconnectURL: ajaxurl + "?action=disconnect_clef_account"
        constructor: (@options) ->
            @$el = $(@selector)
            @render()
            @attachHandlers()
        render: ->
            @$el.toggleClass 'has-clef', @options.connected
        attachHandlers: ->
            @$el.find('#disconnect').click @disconnectClefAccount.bind(this)
            


    window.ClefProfile = ClefProfile

).call this, jQuery