(($, Backbone) ->
    ConnectView = Backbone.View.extend
        el: "#connect-clef-account"
        events:
            "click #disconnect": "disconnectClefAccount"
        disconnectAction: "disconnect_clef_account"
        messageTemplate:
            _.template "<div class='<%=type%> connect-clef-message'>\
                          <%=message%>\
                        </div>"
        initialize: (@opts) ->
            @tutorial = new ConnectTutorialView _.clone(@opts)
            @disconnect = @$el.find '.disconnect-clef'

            @listenTo @tutorial, 'done', @finishTutorial

            @render()
        show: ->
            @$el.fadeIn()
        render: ->
            @tutorial.render()

            if not @opts.connected
                @disconnect.hide()
                @tutorial.show()
            else
                @tutorial.slideUp()
                @disconnect.show()
        disconnectClefAccount: (e) ->
            e.preventDefault()

            failure = (data) =>
                msg = ClefUtils.getErrorMessage(data)
                @showMessage
                    message: _.template(
                        clefTranslations.messages.error.disconnect
                    )(error: msg)
                    type: "error"

            data =
              action: @disconnectClefAction
              _wpnonce: @opts.nonces.disconnectClef

            $.post "#{ajaxurl}?action=#{@disconnectAction}", data
                .success (data) =>
                    if data.success
                        @opts.connected = false
                        @render()
                        msg = clefTranslations.messages.success.disconnect
                        @showMessage
                            message: msg
                            type: "updated"
                    else
                        failure data
                .fail (res) ->
                    failure res.responseText

        showMessage: (data) ->
            @message.remove() if @message
            @message = $(@messageTemplate data).hide()
            @message.prependTo(@$el).slideDown()
        finishTutorial: ->
            window.location = ''

    window.ConnectView = ConnectView

).call(this, jQuery, Backbone)
