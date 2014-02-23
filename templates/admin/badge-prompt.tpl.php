<div class="updated clef-badge-prompt">
    <div class="badge-fade">
        <?php if ($had_clef_before_onboarding) { ?>
            <h3><?php _e("You just logged in securely with Clef!", "clef"); ?></h3>
        <?php } else { ?>
            <h3><?php _e("You just completed your first login with Clef!", "clef"); ?></h3>
        <?php } ?>
        <p><?php _e("Let your visitors know that you are protected with the Clef login by adding a Clef badge to the footer of your page. </br> This helps your visitors know they are browsing a secure site and helps us spread the word about Clef.", "clef"); ?></p>
        <?php wp_nonce_field('clef_badge_prompt'); ?>
        <a href="#" class="button button-primary button-hero add-badge"><?php _e("Add protected by Clef badge", "clef"); ?></a>
        <a href="#" class="button no-badge button-hero"><?php _e("No thanks!", "clef"); ?></a>
    </div>
    <p></p>
    <a href="#" class="dismiss">&times;</a>
</div>
