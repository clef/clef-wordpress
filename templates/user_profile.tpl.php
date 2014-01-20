<?php if (!$connected) { ?>
    <h3 id="clef"><?php _e( "Connect your Clef account", 'clef'); ?></h3>
    <p class="description"><?php _e( "Click the Clef button and sync your phone to enable Clef logins for this WordPress account.", 'clef'); ?></p>
    <div style="margin-top:20px; margin-left: 10px;">
    <?php
    include dirname( __FILE__ )."/button.tpl.php";
    ?>
    </div>
<?php } else { ?>
    <h3><?php _e( "Disconnect Clef account", 'clef'); ?></h3>
    <p class="description"><?php _e( "Click the checkbox and update your profile to disconnect the current Clef account.", 'clef'); ?></p>
    <table class="form-table">
        <tbody>
            <tr id="clef">
                <th><label for="remove_clef"><?php _e( "Disconnect Clef account", 'clef'); ?></label></th>
                <td>
                    <input type="checkbox" name="remove_clef">
                </td>
            </tr>
        </tbody>
    </table>
<?php } ?>
