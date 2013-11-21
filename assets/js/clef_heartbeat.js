/*! Clef for WordPress - v1.8.1.1
 * http://getclef.com
 * Copyright (c) 2013; * Licensed GPLv2+ */
var clefheartbeat={};jQuery(document).ready(function(){wp.heartbeat.interval("fast"),wp.heartbeat.enqueue("clef","cleflogout",!0),jQuery(document).on("heartbeat-tick.wp-auth-check",function(e,t){t&&!t["wp-auth-check"]?window.location.reload():wp.heartbeat.enqueue("clef","cleflogout",!0)})});