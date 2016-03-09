(($) ->
    $(document).ready ->
        $prompt = $(".clef-badge-prompt")
        ajaxData = action: "clef_badge_prompt"
        sending = false

        $prompt.find(".add-badge").click (e) ->
            e.preventDefault()

            return  if sending
            sending = true

            data = {}
            $prompt.find('input').each ->
                data[$(this).attr('name')] = $(this).val()

            data.enable = 'badge'
            $.extend data, ajaxData
            $prompt.slideUp()
            $.post ajaxurl, data, (() ->)

        $prompt.find(".no-badge, .dismiss").click (e) ->
            e.preventDefault()

            return  if sending
            sending = true

            data = {}
            $prompt.find('input').each ->
                data[$(this).attr('name')] = $(this).val()

            data.disable = true
            $.extend data, ajaxData
            $.post ajaxurl, data, (() ->)
            $prompt.slideUp()


).call this, jQuery
