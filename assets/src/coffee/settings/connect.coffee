(($, Backbone) ->

    ConnectView = Backbone.View.extend
        el: "#connect-clef-account"
        events:
            "click #disconnect": "disconnectClefAccount"
        disconnectURL: ajaxurl + "?action=disconnect_clef_account"
        messageTemplate:
            _.template "<div class='<%=type%> connect-clef-message'>\
                          <%=message%>\
                        </div>"
        initialize: (@opts) ->
            @tutorial = new ConnectTutorialView _.clone(@opts)
            @disconnect = @$el.find '.disconnect-clef'
            @render()
        show: ->
            @$el.fadeIn()
        render: ->
            @tutorial.render()

            if not @opts.connected
                @disconnect.hide()
                @tutorial.show()
            else
                @tutorial.hide()
                @disconnect.show()
        disconnectClefAccount: (e) ->
            e.preventDefault()
            $.post @disconnectURL, { _wpnonce: @opts.nonces.disconnectClef },
                (data) =>
                    if data.success
                        @opts.connected = false
                        @render()
                        msg = clefTranslations.messages.success.disconnect
                        @showMessage
                            message: msg
                            type: "updated"
                    else
                        @showMessage
                            message: ClefUtils.getErrorMessage(data)
                            type: "error"

        showMessage: (data) ->
            @message.remove() if @message
            @message = $(@messageTemplate data).hide()
            @message.prependTo(@$el).slideDown()

    window.ConnectView = ConnectView

).call(this, jQuery, Backbone)
