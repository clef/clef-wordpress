var clefheartbeat = {};
jQuery(document).ready(function() {
    wp.heartbeat.interval('fast');
    wp.heartbeat.enqueue('clef', 'cleflogout', true);

    jQuery(document).on('heartbeat-tick.wp-auth-check', function(e, data) {
        if (data && !data['wp-auth-check']) {
            window.location.reload();
        } else {
            wp.heartbeat.enqueue('clef', 'cleflogout', true);   
        }
    });
});