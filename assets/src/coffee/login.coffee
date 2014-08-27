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
            loaded = false
            $spinnerContainer = $('.spinner-container')
            $iframe = $embedContainer.find('iframe')

            $iframe.load ->
                loaded = true
                $spinnerContainer.hide()
                setTimeout -> $embedContainer.slideDown()

            if not loaded
                $embedContainer.hide()
                $spinnerContainer.show()

).call this, jQuery