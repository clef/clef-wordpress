/*! Clef for WordPress - v1.7.0
 * http://getclef.com
 * Copyright (c) 2013; * Licensed GPLv2+ */
jQuery(document).ready(function(e){e("#menu-settings").pointer({content:"<h3>Configure Clef</h3><p>Connect your site to your Clef account to start using Clef.</p>",position:{edge:"left",align:"center"},close:function(){e.post(ajaxurl,{pointer:"wpclef_configure",action:"dismiss-wp-pointer"})}}).pointer("open")});