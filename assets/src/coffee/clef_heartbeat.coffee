clefheartbeat = {}

jQuery(document).ready ->
    wp.heartbeat.interval "fast"
    wp.heartbeat.enqueue "clef", "cleflogout", true

    jQuery(document).on "heartbeat-tick.wp-auth-check", (e, data) ->
        if data and not data["wp-auth-check"]
            window.location.reload()
        else
            wp.heartbeat.enqueue "clef", "cleflogout", true

