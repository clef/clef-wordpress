<div class="clef-register-container" style="clear:both;">
    <div style="position: relative" class="or-container">
        <div style="border-bottom: 1px solid #EEE; width: 90%; margin: 0 5%; z-index: 1; top: 50%; position: absolute;"></div>
        <h2 style="color: #666; margin: 0 auto 20px auto; padding: 3px 0; text-align:center; background: white; width: 20%; position:relative; z-index: 2;">or</h2>
    </div>
    <div class="clef-button-container">
        <?php do_action('clef_render_login_button', $redirect_url, $app_id, false, "register") ?>
    </div>
    <script>
        jQuery(document).ready(function() {
            var container = document.querySelector('.clef-register-container');
            container.parentNode.appendChild(container);
        })
    </script>
</div>

