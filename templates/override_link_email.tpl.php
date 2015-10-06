<p><?php _e('Hi there,', 'wpclef'); ?></p>
<p><?php printf(__("Passwords have now been disabled on %s for all users who enable <a href='https://getclef.com'>Clef</a>. If you ever have an issue that causes you to be locked out of your site, we've created an override URL which will allow you to log in to your site without Clef. The link is: ", "wpclef"), $site_url) ?></p>
<p><a href="<?php echo $override_link ?>"><?php echo $override_link ?></a></p>
<p><?php _e("You can learn more about this functionality <a href='http://support.getclef.com/article/11-creating-a-secret-url-where-you-can-log-into-your-wordpress-site-with-a-password'>here</a>. If you have any issues, please visit <a href='http://support.getclef.com'>support.getclef.com</a>."); ?></p>
<p><?php _e('Thanks!', 'wpclef'); ?></p>
