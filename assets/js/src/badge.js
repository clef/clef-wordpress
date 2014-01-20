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

        $prompt.find('.add-link').click(function(e) {
            e.preventDefault();

            if (sending) { return; }
            sending = true;

            var data = $.extend( { enable: "link" }, ajaxData);

            $prompt.slideUp();
            $.post(ajaxurl, data, function(data) {}, 'json');
        });

        $prompt.find('.no-badge').click(function() {
            $prompt.find('.badge-fade').fadeOut(function() {
                $prompt.find('.link-fade').fadeIn();
            });
        });

        $prompt.find('.no-link, .dismiss').click(function(e) {
            e.preventDefault();

            if (sending) { return; }
            sending = true;

            var data = $.extend( { disable: true }, ajaxData);

            $prompt.slideUp();
            $.post(ajaxurl, data, function(data) {}, 'json');
        });
    }); 

}).call(this, jQuery);


    