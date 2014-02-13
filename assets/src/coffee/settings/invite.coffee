(($) ->
    InviteUsersView = Backbone.View.extend
        el: '#invite-users-settings'
        events:
            "click a[name='invite-users-button']": 'inviteUsers'
        messageTemplate:
            _.template "<div class='<%=type%> invite-users-message'>\
                          <%=message%>\
                        </div>"
        showMessage: (type, message) ->
            $messageEl = @$el.find('.invite-users-message')
            $messageEl.remove() if $messageEl.length
            @$el.find('.button').first().before(@messageTemplate(
                type: type
                message: message
            ))

        template: _.template($('#invite-users-template').html())
        initialize: (@opts) ->
            if @opts.el
                @setElement(@opts.el)
            
        inviteUsersAction: ajaxurl + "?action=clef_invite_users"
        inviteUsers: (e) ->
            e.preventDefault()
            data =
                _wp_nonce: @opts.setup._wp_nonce_invite_users
                roles: $("select[name='invite-users-role']").val()
            $.post @inviteUsersAction,
                data,
                (data) =>
                    msg = ""
                    type = ""
                    if data.error
                        msg = "There was a problem sending invites: \
                        #{data.error}."
                        type = "error"
                    else if data.success
                        @trigger "invited"
                        msg = "Email invitations have been sent to your users."
                        type = "updated"
                    @showMessage type, msg

        hideButton: () ->
            @$el.find('.button').hide()
            
        render: () ->
            @$el.html(@template)

    this.InviteUsersView = InviteUsersView

).call(this, jQuery)
