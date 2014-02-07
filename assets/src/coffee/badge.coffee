(($) ->
    $(document).ready ->
        $prompt = $(".clef-badge-prompt")
        ajaxData = action: "clef_badge_prompt"
        sending = false

        $prompt.find(".add-badge").click (e) ->
            e.preventDefault()

            return  if sending
            sending = true

            data = $.extend { enable: "badge" }, ajaxData
            $prompt.slideUp()
            $.post ajaxurl, data, (() ->), "json"

        $prompt.find(".no-badge, .dismiss").click ->
            e.preventDefault()

            return  if sending
            sending = true

            data = $.extend { disable: true }, ajaxData
            $.post ajaxurl, data, (() ->), "json"
            $prompt.slideUp()


).call this, jQuery