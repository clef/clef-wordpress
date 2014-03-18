<div id="clef-pro-section">
    <div id="clef-pro-customization" class="clef-settings settings-section pro-section">
        <div class="inputs-container">
            <h3><?php _e("Customize the Clef login screen", "clef"); ?></h3>
            <p><?php _e("Add a custom logo and message to the Clef overlay to make your visitors feel at home."); ?></p>
            <div class="input-container">
                <label for=""><?php _e("Custom logo", "clef"); ?></label>
                <div class="button" id="clef-custom-logo-clear">Remove</div>
                <div class="button button-primary" id="clef-custom-logo-upload">Upload a file</div>
                <?php $form->getSection('customization')->getField('logo')->render(); ?>
            </div>
            <div class="input-container">
                <label for=""><?php _e("Custom message", "clef"); ?></label>
                <?php $form->getSection('customization')->getField('message')->render(); ?>
            </div>
        </div>
        <div class="preview-container">
            <div id="custom-login-view"></div>
        </div>
    </div>
</div>

<script id="clef-customization-template" type="text/template">
    <h4><?php _e("Preview of your customizations", "clef"); ?></h4>
    <img class='logo-preview' src="<%= image %>">
    <% if (message) { %>
    <p class='message-preview'><%= message %></p>
    <% } %>
</script>