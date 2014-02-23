(($) ->
    dismissWaltzNotification = (e) ->
        e.preventDefault()
        $el = $('.waltz-notification')

        data = {}
        $el.find('input').each ->
            data[$(this).attr('name')] = $(this).val()

        $el.remove()

        $.post(ajaxurl + '?action=clef_dismiss_waltz', data)


    $(document).ready ->
        if window.waltzIsInstalled
            dismissWaltzNotification()
        else
            $('.waltz-notification .next').click(dismissWaltzNotification)

).call(this, jQuery)
