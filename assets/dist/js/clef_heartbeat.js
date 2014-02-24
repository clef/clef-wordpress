jQuery(document).ready(function() {
  wp.heartbeat.interval("fast");
  wp.heartbeat.enqueue("clef", "cleflogout", true);
  return jQuery(document).on("heartbeat-tick", function(e, data) {
    if (data && (data.cleflogout || !data["wp-auth-check"])) {
      return window.location.reload();
    } else {
      return wp.heartbeat.enqueue("clef", "cleflogout", true);
    }
  });
});
