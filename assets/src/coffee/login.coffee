(($) ->
    closeOverlay = ->
        $('.clef-login-form.clef-login-form-embed').addClass 'clef-closed'
        false
    openOverlay = (e) ->
        $('.clef-login-form.clef-login-form-embed').removeClass 'clef-closed'
        false

    $ ->
        $embedContainer = $('.clef-embed-container')
        $('.close-overlay').click closeOverlay
        $('.open-overlay').click openOverlay
        $('.overlay-info .open').click ->
            $('.overlay-info').removeClass 'closed'

        if $embedContainer.length
            $spinnerContainer = $('.spinner-container')

            $embedContainer.hide()
            $spinnerContainer.show()

            $('iframe').on 'load', ->
                if $(this).attr('src').match('clef\.io/iframes/qr')
                    $spinnerContainer.hide()
                    setTimeout -> $embedContainer.slideDown()

).call this, jQuery