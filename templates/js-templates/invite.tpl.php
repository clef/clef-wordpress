<script id="invite-users-template" type="text/template">
    <div class="invite-users">
        <a class="next button button-primary" style="display:none;"><?php _e("Skip and finish setup", "wpclef"); ?></a>
        <div class="clear"></div>
        <div class="inputs-container">
            <div class="invite-users__individually-container">
                <h3>Invite users individually or in bulk</h3>
                <div>
                    <img src="<?php echo CLEF_URL ?>assets/dist/img/invite.png" alt="invite individual users" class="invite-users__img">
                    <div class="invite-users__individually">
                        <p><?php _e("To invite users individually or in bulk, go to the users page and use the dropdown.", "wpclef"); ?></p>
                        <a href="<?php echo admin_url('users.php') ?>" class="button button-primary" target="_blank"><?php _e("Go to users page", "wpclef"); ?></a>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>


            <h3>Invite users by role</h3>
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
            <a href="#" name="invite-users-button" class="button button-primary invite-role-button"><?php _e("Invite users by role", "wpclef"); ?></a>
        </div>

        <div class="preview-container">
            <h4><?php _e("Email preview", "wpclef"); ?></h4>
            <div class="email">
                <?php include CLEF_TEMPLATE_PATH . "invite_email.tpl.php"; ?>
            </div>
        </div>
    </div>
</script>
