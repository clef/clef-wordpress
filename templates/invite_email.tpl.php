<?php
if(empty($invite_link)) {
    if (isset($options) && isset($options['connectClefUrl'])) {
        $invite_link = $options['connectClefUrl'];
    } else {
        $invite_link = '#';
    }
}
if (empty($site_name)) $site_name = get_bloginfo('name');
?>
<p><?php _e('Hi there,', 'clef'); ?></p>
<p><?php printf( __('The administrator for %s just set up <a href="https://getclef.com">Clef</a>, which means you can now log in without passwords.', "clef"), $site_name); ?></p>
<p><?php printf(__('Click <a href="%s">here</a> and log in with your username and password (for the last time) to get started.', "clef"), $invite_link); ?> </p>
<p><?php _e("Want a video walkthrough of setting up Clef? We've created one for you <a href='http://getclef.wistia.com/medias/8mnrh6og39'>here</a>.", 'clef'); ?></p>
<p><?php _e('Thanks!', 'clef'); ?></p>
