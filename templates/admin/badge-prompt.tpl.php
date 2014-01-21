<div class="updated clef-badge-prompt">
    <div class="badge-fade">
        <?php if ($had_clef_before_onboarding) { ?>
            <h3><?php _e("You just logged in securely with Clef!", "clef");</h3>
        <?php } else { ?>
            <h3><?php _e("You just completed your first login with Clef!", "clef"); ?></h3>
        <?php } ?>
        <p><?php _e("Let your visitors know that you are protected with the Clef login by adding a Clef badge to the footer of your page. </br> This helps your visitors know they are browsing a secure site and helps us spread the word about Clef.", "clef"); ?></p>
        <a href="#" class="button button-primary button-hero add-badge"><?php _e("Add protected by Clef badge", "clef"); ?></a>
        <a href="#" class="button no-badge button-hero"><?php _e("Not right now"); ?></a>
    </div>
    <div class="link-fade">
        <h3><?php _e("How about a small link?"); ?></h3>
        <p><?php _e("We're dedicated to protecting the WordPress community for <b>free, forever</b> and we'd really appreciate your support!", "clef") ?></p>
        <a href="#" class="button button-primary button-hero add-link"><?php _e("Add protected by Clef link", "clef"); ?></a>
        <a href="#" class="button no-link button-hero"><?php _e("I'll support Clef another way", "clef"); ?></a>
    </div>
    <p></p>
    <a href="#" class="dismiss">&times;</a>
</div>