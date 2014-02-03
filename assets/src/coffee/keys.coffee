(($) ->
    handleKeys = (data) ->
        return  unless /https:\/\/clef.io/.test(data.origin)

        appID = data.data.appID
        appSecret = data.data.appSecret
        oauthCode = data.data.oauthCode

        $("input[name=\"wpclef[clef_settings_app_id]\"]").val appID
        $("input[name=\"wpclef[clef_settings_app_secret]\"]").val appSecret
        $("input[name=\"wpclef[clef_settings_oauth_code]\"]").val oauthCode
        $(".wrap iframe").hide()
        $("form#wp_clef input[type=submit]").trigger "click"

    $(document).ready ->

        window.addEventListener "message", handleKeys

        setTimeout (()->
            # show logout error message after an amount of time
            $(".logout-hook-error").slideDown()
        ), 20000

).call this, jQuery