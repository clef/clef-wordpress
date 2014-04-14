(($, Backbone) ->
    InviteUsersView = Backbone.View.extend
        el: '#invite-users-settings'
        events:
            "click a[name='invite-users-button']": 'inviteUsers'
        messageTemplate:
            _.template "<div class='<%=type%> invite-users-message'>\
                          <%=message%>\
                        </div>"
        showMessage: (data) ->
            $messageEl = @$el.find('.invite-users-message')
            $messageEl.remove() if $messageEl.length
            @$el.find('.button').first().before(@messageTemplate(data))

        template: -> _.template($('#invite-users-template').html())
        initialize: (@opts) ->
            if @opts.el
                @setElement(@opts.el)
            
        inviteUsersAction: ajaxurl + "?action=clef_invite_users"
        inviteUsers: (e) ->
            e.preventDefault()
            data =
                _wpnonce: @opts.nonces.inviteUsers
                roles: $("select[name='invite-users-role']").val()
                networkAdmin: @opts.isNetworkSettings

            failure = (msg) =>
                @showMessage
                    message: _.template(
                        clefTranslations.messages.error.invite
                    )(error: msg)
                    type: "error"

            $.post @inviteUsersAction, data
                .success (data) =>
                    if data.success
                        @trigger "invited"
                        @showMessage
                            message: clefTranslations.messages.success.invite
                            type:"updated"
                    else
                        failure ClefUtils.getErrorMessage data
                .fail (res) -> failure res.responseText

        hideButton: () ->
            @$el.find('.button').hide()
            
        render: () ->
            @$el.html(@template)

    this.InviteUsersView = InviteUsersView

).call(this, jQuery, Backbone)
