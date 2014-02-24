(($) ->
    dismissWaltzNotification = (e) ->
        e.preventDefault() if e
        $el = $('.waltz-notification')

        data = {}
        $el.find('input').each ->
            data[$(this).attr('name')] = $(this).val()

        $el.remove()

        $.post(ajaxurl + '?action=clef_dismiss_waltz', data)


    $(document).ready ->
        setTimeout () ->
            if window.waltzIsInstalled
                dismissWaltzNotification()
        , 1000
        

        $('.waltz-notification .next').click(dismissWaltzNotification)

).call(this, jQuery)
