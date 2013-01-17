jQuery(document).ready( function($) {
    $('#menu-settings').pointer({
        content: '<h3>Configure WPClef</h3><p>Connect your site to your Clef account to start using Clef.</p>',
        position: {
            edge: 'left',
            align: 'center'
        },
        close: function() {
            $.post( ajaxurl, {
                pointer: 'wpclef_configure',
                action: 'dismiss-wp-pointer'
            });
        }
    }).pointer('open');
});