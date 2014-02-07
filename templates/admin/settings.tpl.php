<div id="clef-settings-container">
    <div id="clef-tutorial" style="display:none;">
        <div class="sub">
            <h1>Welcome to Clef!</h1>
            <p>Clef is the coolest thing in the World. You'll really like it.</p>
            <div class="next button">next</div>
            <div class="previous button">previous</div>
        </div>        
        <div class="sub">
            <h1>Scan to set up your blog</h1>
            <iframe data-src="<?php echo CLEF_BASE . '/iframes/wave' ?>" src="" frameborder="0"></iframe>
            <div class="next button">next</div>
            <div class="previous button">previous</div>
        </div>
        <div class="sub">
            <h1>You're all configured</h1>
        </div>
    </div>
    <div id="clef-settings">
        <h1><?php _e("Clef", "clef"); ?></h1>
        <div id="fb-root"></div>
        <div class="fb-like" data-href="https://www.facebook.com/getclef" data-width="200" data-layout="button_count" data-action="like" data-show-faces="true" data-share="false"></div>
        <a href="https://twitter.com/getclef" class="twitter-follow-button" data-show-count="false" data-dnt="true">Follow @getclef</a>
        <script>(function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=241719455859280";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
        <?php include CLEF_TEMPLATE_PATH . 'admin/form.tpl.php'; ?>
    </div>
</div>
<script type="text/javascript"> var options = <?php echo json_encode($options); ?></script>
