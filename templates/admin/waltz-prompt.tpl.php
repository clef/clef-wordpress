<div class="waltz <?php if (!empty($class)) echo $class; ?>">
    <img src="<?php echo CLEF_URL . "assets/dist/img/waltz.png" ?>" alt="Waltz">
    <div class="text">
        <h1<?php _e(">Want to use Clef everywhere?", "wpclef"); ?></h1>
        <p><?php _e("<a target='_blank' href='http://getwaltz.com'>Waltz</a> is a chrome extension that lets you log in to sites like Facebook, Gmail, and Twitter using Clef. It takes 5 seconds to install and will give you the full Clef experience.", "wpclef"); ?></p>
    </div>
    <div class="buttons">
        <a class="button button-primary button-hero" href="http://getwaltz.com" target="_blank"><?php _e("Try Waltz", "wpclef"); ?></a>
        <a href="<?php echo $next_href; ?>" class="button button-hero next"><?php echo $next_text; ?></a>
    </div>
    <?php wp_nonce_field(ClefAdmin::DISMISS_WALTZ_ACTION); ?>
</div>
<div class="clear"></div>
