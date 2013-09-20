<?php
$force = WPClef::setting( 'clef_password_settings_force' );
if (!$force || !empty($_GET['override']) && $_GET['override'] == WPClef::setting('clef_password_settings_override_key')) { ?>
    <input type="hidden" value="<?php if (isset($_GET['override'])) echo $_GET['override'] ?>" name="override"/>
    <div style="position: relative">
        <div style="border-bottom: 1px solid #EEE; width: 90%; margin: 0 5%; z-index: 1; top: 50%; position: absolute;"></div>
        <h2 style="color: #666; margin: 0 auto 20px auto; padding: 3px 0; text-align:center; background: white; width: 20%; position:relative; z-index: 2;">or</h2>
    </div>
    <div style="margin-left: 35px;margin-bottom: 27px;">
        <?php
        include dirname( __FILE__ )."/button.tpl.php";
        ?>
    </div>
<?php } else { ?>
    <div style="margin-left: 35px;margin-top: 25px;">
        <?php
        include dirname( __FILE__ )."/button.tpl.php";
        ?>
    </div>
<?php } ?>