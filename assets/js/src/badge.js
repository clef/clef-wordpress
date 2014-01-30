(function($) {

    $(document).ready(function() {
        var $prompt = $('.clef-badge-prompt'),
            ajaxData = { action: 'clef_badge_prompt' },
            sending = false;

        $prompt.find('.add-badge').click(function(e) {
            e.preventDefault();

            if (sending) { return; }
            sending = true;

            var data = $.extend( { enable: "badge" }, ajaxData);

            $prompt.slideUp();
            $.post(ajaxurl, data, function(data) {}, 'json');
        });

        $prompt.find('.no-badge, .dismiss').click(function() {
            var data = $.extend( { disable: true }, ajaxData);
            $.post(ajaxurl, data, function(data) {}, 'json');
            $prompt.slideUp();
        });
    }); 

}).call(this, jQuery);


    