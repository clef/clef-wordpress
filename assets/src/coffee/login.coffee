(($) ->
    closeOverlay = ->
        $('.clef-login-form.clef-overlay').addClass 'closed'
    openOverlay = ->
        $('.clef-login-form.clef-overlay').removeClass 'closed'

    $ ->
        $('.close-overlay').click closeOverlay
        $('.open-overlay').click openOverlay
        $('.overlay-info .open').click ->
            $('.overlay-info').removeClass 'closed'


).call this, jQuery