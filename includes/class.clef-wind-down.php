<?php

class ClefWindDown {
  static function init() {
    // Don't bother doing anything for anyone except admins
    if ( ! current_user_can( 'manage_options' ) ) {
      return;
    }

    // Hook into global admin notices
    add_action( 'admin_notices', array( 'ClefWindDown', 'show_notice' ) );

    // Handle activation etc requests (make sure priority is after ClefWindDown)
    add_action( 'init', array( 'ClefWindDown', 'handle_requests' ), 2 );
  }

  public static function show_notice() {
    // Show the message on certain pages only.
    // Remove this block if you'd prefer to just blanket-display it.
    if (
      ! in_array(
        basename( $_SERVER['PHP_SELF'] ),
        array(
          'index.php',
          'options-general.php',
          'admin.php' // pretty broad visibility
        )
      )
    ) {
      return;
    }

    // Don't show on the Jetpack page itself, that gets confusing
    if ( stristr( $_SERVER['REQUEST_URI'], 'admin.php?page=jetpack' ) ) {
      return;
    }

    ?>
    <php? */ move this css to whereever we need */ ?>
        <style>
          #message.clef-sunset-msg {
            background-color: #1DB2DF;
            color: #fff;
        }
        </style>
    <div id="message" class="clef-sunset-msg updated error notice-error">
      <p><?php printf( __( "Unfortunately, we're discontinuing support for Clef. <a href='%s' target='_blank'>Read more here</a>.", 'wpclef' ), 'https://blog.getclef.com/discontinuing-support-for-clef-6c89febef5f3#.ejv4vcu89' ); ?></p>
      <?php echo self::get_jetpack_prompt(); ?>
    </div><?php
  }

  private static function get_jetpack_prompt() {
    // If Jetpack is not installed at all; prompt to install it.
    // This is a pretty rough way of doing things, but it works.
    // Including the class_exists check as a safeguard against weird file paths.
    if ( ! class_exists( 'Jetpack' ) && ! file_exists( WP_CONTENT_DIR . '/plugins/jetpack/jetpack.php' ) ) {
      add_thickbox();
      wp_enqueue_script( 'plugin-install' );
      ?>
      <p><?php _e( 'To continue securing your site, we recommend Jetpack\'s, "Sign on with WordPress.com" feature, with Two-Step Authentication.', 'wpclef' ); ?></p>
      <p>
        <a href="plugin-install.php?tab=plugin-information&plugin=jetpack&from=plugins&TB_iframe=true&width=640&height=666" class="button-primary thickbox"><?php _e( 'Install Jetpack', 'wpclef' ); ?></a>
      </p>
      <?php
      return;
    }

    // If Jetpack is installed but not active, give them a button to activate it right here
    if ( ! defined( 'JETPACK__VERSION' ) || ! class_exists( 'Jetpack' ) ) {
      $url = admin_url( 'admin.php?page=clef&jetpack=activate' );
      ?>
      <p><?php _e( 'It looks like you have the Jetpack plugin installed, but not activated. Activate it now so that you can use their "Sign on with WordPress.com" feature to require Two-Step Authentication.', 'wpclef' ); ?></p>
      <p><a href="<?php echo esc_url( $url ); ?>" class="button-primary"><?php _e( 'Activate Jetpack', 'wpclef' ); ?></a></p>
      <?php
      return;
    }

    // If Jetpack isn't connected yet, prompt them to connect.
    if ( ! Jetpack::is_active() ) {
      $url = admin_url( 'admin.php?page=clef&jetpack=connect' );
      ?>
      <p><?php _e( 'It looks like you have Jetpack installed, but have not connected to WordPress.com yet. Once you are connected, you can enable "Sign on with WordPress.com" for your site, and require Two-Step Authentication.', 'wpclef' ); ?></p>
      <p><a href="<?php echo esc_url( $url ); ?>" class="button-primary">Connect Jetpack</a></p>
      <?php
      return;
    }

    // Jetpack is installed, active, and connected. If SSO isn't active, prompt them.
    if ( ! Jetpack::is_module_active( 'sso' ) ) {
      $url = admin_url( 'admin.php?page=clef&jetpack=enable-sso' );
      ?>
      <p><?php _e( 'You have Jetpack installed already, so you can enable "Sign on with WordPress.com" for your site, and require Two-Step Authentication via Jetpack.', 'wpclef' ); ?></p>
      <p><a href="<?php echo esc_url( $url ); ?>" class="button-primary">Enable Sign on with WordPress.com</a></p>
      <?php
      return;
    }

    // If the 2FA requirement isn't active, prompt them.
    if ( '1' != get_option( 'jetpack_sso_require_two_step' ) ) {
      $url = admin_url( 'admin.php?page=clef&jetpack=require-2fa' );
      ?>
      <p><?php _e( 'Since you already have Jetpack\'s "Sign on with WordPress.com" feature enabled, you just need to make sure you have Two-Step Authentication activated on your WordPress.com account, and then you can require it for all log ins on this site.', 'wpclef' ); ?></p>
      <p><a href="https://wordpress.com/me/security/two-step" target="_blank"><?php _e( 'Configure Two-Step Authentication on your WordPress.com account', 'wpclef' ); ?></a></p>
      <p><a href="<?php echo esc_url( $url ); ?>" class="button-primary"><?php _e( 'Require Two-Step Authentication-protected WordPress.com accounts', 'wpclef' ); ?></a></p>
      <?php
      return;
    }

    // Everything is set up and good to go
    add_filter( 'jetpack_sso_bypass_login_forward_wpcom', '__return_true' );
    add_filter( 'jetpack_remove_login_form', '__return_true' );

    ?><p><?php _e( "You're all set up to use Jetpack's Two-Step Authentication via WordPress.com accounts though, so carry on.", 'wpclef' ); ?></p><?php
  }

  public static function handle_requests() {
    // All our actions are via the jetpack parameter
    if ( ! isset( $_REQUEST['jetpack'] ) ) {
      return;
    }

    // Verify nonce against requested action
    // @todo

    switch ( $_REQUEST['jetpack'] ) {
    case 'activate':
      activate_plugin(
        'jetpack/jetpack.php',
        admin_url( 'plugins.php' ) // redirect to Plugins screen to get the best prompt to connect
      );
      break;

    case 'connect':
      $url = Jetpack::build_connect_url( true, true, 'wpclef' );
      wp_redirect( $url );
      exit;
      break;

    case 'enable-sso':
      Jetpack::activate_module( 'sso' );
      break;

    case 'require-2fa':
      update_option( 'jetpack_sso_require_two_step', '1' );
      break;
    }
  }
}
