jQuery(document).ready(function() {
  if (wp.heartbeat) {
    wp.heartbeat.interval("fast");
    wp.heartbeat.enqueue("clef", "cleflogout", true);
    return jQuery(document).on("heartbeat-tick", function(e, data) {
      return wp.heartbeat.enqueue("clef", "cleflogout", true);
    });
  }
});
