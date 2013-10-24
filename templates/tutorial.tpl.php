<div class="clef">
    <div class="tutorial">
        <div class="header">
            <h1>Setup Wizard</h1>
            <p>Something not working? Try the guide
                <a href="<?php echo CLEF_BASE ?>/iframes/wordpress?domain=<?php echo $site_domain ?>&name=<?php echo $site_name ?><?php if (Clef::woo_active()) { echo '&payments_enabled=1'; }?>">here</a>
                or email 
                <a href="mailto:support@getclef.com">support@getclef.com.</a>
            </p>
        </div>
        <iframe src="<?php echo CLEF_BASE ?>/iframes/wordpress?domain=<?php echo $site_domain ?>&name=<?php echo $site_name ?><?php if (Clef::woo_active()) { echo '&payments_enabled=1'; }?>" width="525" height="350"></iframe>
    </div>
</div>