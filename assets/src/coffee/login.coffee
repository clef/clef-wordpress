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
            $iframe = $embedContainer.find('iframe')

            $iframe.load ->
                $spinnerContainer.hide()
                setTimeout -> $embedContainer.slideDown()

            if not $iframe.attr 'data-loaded'
                $embedContainer.hide()
                $spinnerContainer.show()

).call this, jQuery