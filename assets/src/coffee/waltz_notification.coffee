(($) ->
    dismissWaltzNotification = (e) ->
        e.preventDefault()
        $('.waltz-notification').remove()
        $.post(ajaxurl + '?action=clef_dismiss_waltz_notification')


    $(document).ready ->
        if window.waltzIsInstalled
            dismissWaltzNotification()
        else
            $('.waltz-notification .next').click(dismissWaltzNotification)

).call(this, jQuery)

