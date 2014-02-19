<img src="<?php echo CLEF_URL . "assets/dist/img/waltz.png" ?>" alt="Waltz">
<h1<?php _e(">Want to use Clef everywhere?", "clef"); ?></h1>
<p><?php _e("<a href='http://getwaltz.com'>Waltz</a> is a chrome extension that lets you log in to sites like Facebook, Gmail, and Twitter using Clef. It takes 5 seconds to install and will give you the full Clef experience.", "clef"); ?></p>
<?php wp_nonce_field(ClefAdmin::DISMISS_WALTZ_ACTION); ?>
<a class="button button-primary button-hero" href="http://getwaltz.com" target="_blank"><?php _e("Try Waltz", "clef"); ?></a>
<a href="<?php echo $next_href; ?>" class="button button-hero next"><?php echo $next_text; ?></a>
