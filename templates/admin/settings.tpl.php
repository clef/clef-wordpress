<div id="clef-settings-container">
    <div class="message"><p></p></div>
    <div id="clef-tutorial" style="display:none;">
        <?php include CLEF_TEMPLATE_PATH . 'admin/tutorial.tpl.php'; ?>
    </div>
    <div id="clef-settings">
        <?php include CLEF_TEMPLATE_PATH . 'admin/form.tpl.php'; ?>
    </div>
</div>

<script id="invite-users-template" type="text/template">
    <div class="invite-users">
        <div class="inputs-container">
            <div class="header">
                <h3>Invite your users</h3>
                <h4 class='subheader'>Your site is only as secure as its weakest password.</h4>
            </div>
            <p>We've made it really easy to get all of your site's users up and running with Clef. Click the button below and we'll send an email inviting your users to set up their Clef account on your site.</p>
            <p class='already-disabled'>If you've already disabled passwords site-wide, users can use their invite links to log in temporarily using passwords.</p>
            <div class="input-container">
                <label for="">Invite all users with privileges <b>greater than or equal</b> to </label>
                <select class="ajax-ignore" name="invite-users-role">
                    <option selected value="Everyone">Everyone</option>
                    <option value="Contributor">Contributor</option>
                    <option value="Author">Author</option>
                    <option value="Editor">Editor</option>
                    <option value="Administrator">Administrator</option>
                    <option value="Super Administrator">Super Administrator</option>
                </select>
            </div>
            <a href="#" name="invite-users-button" class="button button-primary">Invite Users</a>
        </div>
    </div>
</script>

<script type="text/javascript"> var options = <?php echo json_encode($options); ?></script>
