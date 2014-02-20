jQuery(document).ready ->
    wp.heartbeat.interval "fast"
    wp.heartbeat.enqueue "clef", "cleflogout", true

    jQuery(document).on "heartbeat-tick", (e, data) ->
        if data and (data.cleflogout or not data["wp-auth-check"])
            window.location.reload()
        else
            wp.heartbeat.enqueue "clef", "cleflogout", true

