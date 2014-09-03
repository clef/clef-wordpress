<script id="invite-users-template" type="text/template">
    <div class="invite-users">
        <div class="inputs-container">
            <div class="header">
                <h3><?php _e('Invite your users', "clef"); ?></h3>
                <h4 class='subheader'><?php _e('Your site is only as secure as its weakest password.', "clef"); ?></h4>
            </div>
            <p><?php _e("We've made it really easy to get all of your site's users up and running with Clef. Click the button below and we'll send an email inviting your users to set up their Clef account on your site. The invite email has <b>step-by-step instructions</b> and a video walkthrough.", "clef"); ?></p>

            <div class="input-container">
                <label for=""><?php _e("Invite all users with roles <b>greater than or equal</b> to ", "clef"); ?></label>
                <select class="ajax-ignore" name="invite-users-role">
                    <option selected value="Everyone"><?php _e("Everyone", "clef"); ?></option>
                    <option value="Contributor"><?php _e("Contributor", "clef"); ?></option>
                    <option value="Author"><?php _e("Author", "clef"); ?></option>
                    <option value="Editor"><?php _e("Editor", "clef"); ?></option>
                    <option value="Administrator"><?php _e("Administrator", "clef"); ?></option>
                    <option value="Super Administrator"><?php _e("Super Administrator", "clef"); ?></option>
                </select>
            </div>
            <a href="#" name="invite-users-button" class="button button-primary"><?php _e("Invite Users", "clef"); ?></a>
            <div class="button next" style="display:none;"><?php _e("Continue and finish setup", "clef"); ?></div>
        </div>

        <div class="preview-container">
            <h4><?php _e("Email preview", "clef"); ?></h4>
            <div class="email">
                <?php include CLEF_TEMPLATE_PATH . "invite_email.tpl.php"; ?>
            </div>
        </div>
    </div>
</script>