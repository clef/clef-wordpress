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
            @$el.find('.invite-role-button').first()
                .before(@messageTemplate(data))

        template: -> _.template($('#invite-users-template').html())
        initialize: (@opts) ->
            if @opts.el
                @setElement(@opts.el)

        inviteUsersAction: "clef_invite_users"
        inviteUsers: (e) ->
            e.preventDefault()

            $(e.target).attr('disabled', 'disabled')

            data =
                _wpnonce: @opts.nonces.inviteUsers
                roles: $("select[name='invite-users-role']").val()
                networkAdmin: @opts.isNetworkSettings
                action: @inviteUsersAction

            failure = (data) =>
                msg = ClefUtils.getErrorMessage(data)
                $(e.target).removeAttr('disabled')
                @showMessage
                    message: _.template(
                        clefTranslations.messages.error.invite
                    )(error: msg)
                    type: "error"

            $.post "#{ajaxurl}?action=#{@inviteUsersAction}", data
                .success (data) =>
                    $(e.target).removeAttr('disabled')
                    if data.success
                        @trigger "invited"
                        @showMessage
                            message: data.message
                            type:"updated"
                    else
                        failure data
                .fail (res) -> failure res.responseText
        hideButton: () ->
            @$el.find('.button').hide()
        render: () ->
            @$el.html(@template)

    this.InviteUsersView = InviteUsersView

).call(this, jQuery, Backbone)
