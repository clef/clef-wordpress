<script id="invite-users-template" type="text/template">
    <div class="invite-users">
        <div class="inputs-container">
            <div class="header">
                <h3><?php _e('Invite your users', "wpclef"); ?></h3>
                <h4 class='subheader'><?php _e('Your site is only as secure as its weakest password.', "wpclef"); ?></h4>
            </div>
            <p><?php _e("We've made it really easy to get all of your site's users up and running with Clef. Click the button below and we'll send an email inviting your users to set up their Clef account on your site. The invite email has <b>step-by-step instructions</b> and a video walkthrough.", "wpclef"); ?></p>

            <div class="input-container">
                <label for=""><?php _e("Invite all users with roles <b>greater than or equal</b> to ", "wpclef"); ?></label>
                <select class="ajax-ignore" name="invite-users-role">
                    <option selected value="Everyone"><?php _e("Everyone", "wpclef"); ?></option>
                    <option value="Contributor"><?php _e("Contributor", "wpclef"); ?></option>
                    <option value="Author"><?php _e("Author", "wpclef"); ?></option>
                    <option value="Editor"><?php _e("Editor", "wpclef"); ?></option>
                    <option value="Administrator"><?php _e("Administrator", "wpclef"); ?></option>
                    <option value="Super Administrator"><?php _e("Super Administrator", "wpclef"); ?></option>
                </select>
            </div>
            <a href="#" name="invite-users-button" class="button button-primary"><?php _e("Invite Users", "wpclef"); ?></a>
            <div class="button next" style="display:none;"><?php _e("Continue and finish setup", "wpclef"); ?></div>
        </div>

        <div class="preview-container">
            <h4><?php _e("Email preview", "wpclef"); ?></h4>
            <div class="email">
                <?php include CLEF_TEMPLATE_PATH . "invite_email.tpl.php"; ?>
            </div>
        </div>
    </div>
</script>