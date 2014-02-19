<?php 
$contents = __('Hi there,', 'clef') . PHP_EOL . PHP_EOL; 
$contents .= __('The administrator for ', 'clef') . get_bloginfo('name') . ' ('. get_site_url() . ') ';
$contents .= __('just set up Clef (https://getclef.com), which means you can now log in without passwords.', 'clef');
$contents .= PHP_EOL . PHP_EOL;
$contents .= __('Click this link and log in with your username and password (for the last time) to get started:', 'clef') . PHP_EOL;
$contents .= PHP_EOL;
$contents .= !empty($invite_link) ? $invite_link : '#';
$contents .= PHP_EOL;
$contents .= PHP_EOL;
$contents .= __('Thanks!', 'clef');

echo $contents;
?>
