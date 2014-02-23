<p><?php _e('Hi there,', 'clef'); ?></p>
<p><?php _e('The administrator for', 'clef') ?> <?php echo get_bloginfo('name'); _e(' just set up <a href="https://getclef.com">Clef</a>, which means you can now log in without passwords.', "clef"); ?></p>
<p><?php _e('Click', 'clef'); ?> <a href="<?php 
if(!empty($invite_link)) { 
    echo $invite_link; 
} else if (isset($options) && isset($options['connectClefUrl'])) { 
    echo $options['connectClefUrl']; 
} else {
    echo '#';
}
?>"><?php _e('here', 'clef'); ?></a> <?php _e("and log in with your username and password (for the last time) to get started.", "clef"); ?></p>
<p><?php _e("Want a video walkthrough of setting up Clef? We've created one for you <a href='http://getclef.wistia.com/medias/8mnrh6og39'>here</a>.", 'clef'); ?></p>
<p><?php _e('Thanks!', 'clef'); ?></p>
