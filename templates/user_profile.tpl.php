<?php if (!$connected) { ?>
    <h3>Connect your Clef account</h3>
    <p class="description">Click the Clef button and sync your phone to enable Clef logins for this WordPress account.</p>
    <div style="margin-top:20px; margin-left: 10px;">
    <?php
    include dirname( __FILE__ )."/button.tpl.php";
    ?>
    </div>
<?php } else { ?>
    <h3>Disconnect Clef account</h3>
    <p class="description">Click the checkbox and update your profile to disconnect the current Clef account.</p>
    <table class="form-table">
        <tbody>
            <tr id="clef">
                <th><label for="remove_clef">Disconnect Clef account</label></th>
                <td>
                    <input type="checkbox" name="remove_clef">
                </td>
            </tr>
        </tbody>
    </table>
<?php } ?>