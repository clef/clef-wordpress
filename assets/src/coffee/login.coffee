(($) ->
    closeOverlay = ->
        $('.clef-login-form.clef-login-form-embed').addClass 'clef-closed'
        false
    openOverlay = (e) ->
        $('.clef-login-form.clef-login-form-embed').removeClass 'clef-closed'
        false

    $ ->
        $('.close-overlay').click closeOverlay
        $('.open-overlay').click openOverlay
        $('.overlay-info .open').click ->
            $('.overlay-info').removeClass 'closed'

        if $('.clef-embed-container').length
            $('iframe').on 'load', ->
                if $(this).attr('src').match('clef\.io/iframes/qr')
                    $('.spinner-container').hide()
                    setTimeout -> $('.clef-embed-container').slideDown()

).call this, jQuery