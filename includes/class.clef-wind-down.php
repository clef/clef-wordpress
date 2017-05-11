<?php

class ClefWindDown {
  static function init() {
    // Don't bother doing anything for anyone except admins
    if ( ! current_user_can( 'manage_options' ) ) {
      return;
    }

	  if ( class_exists( 'Jetpack' ) && Jetpack::is_active() && ! get_option( 'clef_jetpack_connected', false ) ) {
		  JetpackTracking::record_user_event( 'test_clef_connection', array( 'used_cta' => get_option( 'clef_jetpack_cta_used' ) ) );
		  update_option( 'clef_jetpack_connected', true );
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
	        'options.php', // show immediately after clef is activated
          'plugins.php', // a likely place to be
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

	  $current_step = self::get_current_step();
	  $dismissed    = get_option( 'clef_jetpack_dismissal', - 1 );
	  //$dismissed = - 1;

	  if ( $current_step == $dismissed ) {
		  return;
	  }

    ?>
	  <?php /* move this css to whereever we need */ ?>
        <style>

          #message.clef-sunset-msg {
            position: relative;
            background-color: #1DB2DF;
            border-color: #1DB2DF;
            color: #fff;
            padding: 16px;
          }

          #message .clef-sunset-msg-img {
            width: 100px;
            margin: 0 14px 10px 6px;
          }

          #message.clef-sunset-msg a {
            color: #fff;
          }

          #message.clef-sunset-msg .button {
            color: #555;
          }

          .clef-sunset-msg-dismiss {
            display: block;
            text-decoration: none !important;
            line-height: .5;
            float: right;
          }

          .clef-sunset-msg-dismiss:before {
            color: #fff;
            font: 400 16px/1 dashicons;
            content: '\f158';
          }

          @media (max-width: 480px) {
            .clef-sunset-msg-dismiss {
              float: none;
              position: absolute;
              top: 14px;
              right: 14px;
            }
          }

        </style>
    <div id="message" class="clef-sunset-msg updated error notice-error">
	  <?php
	  if ( $current_step > 2 ):
		  ?>
	    <a href="<?php echo admin_url( 'admin.php?page=clef&jetpack=dismiss' ); ?>" class="clef-sunset-msg-dismiss"></a>
		  <?php
	  endif;
	  ?>
      <img src="<?php echo esc_url( plugins_url( 'assets/src/img/clef-logo@2x.png', dirname( __FILE__ ) ) ); ?>" alt="Clef logo" class="clef-sunset-msg-img"/>
      <p><?php printf( __( "Unfortunately, we're discontinuing support for Clef. <a href='%s' target='_blank'>Read more here</a>.", 'wpclef' ), 'https://blog.getclef.com/discontinuing-support-for-clef-6c89febef5f3#.ejv4vcu89' ); ?></p>
	  <?php self::get_jetpack_prompt( $current_step ); ?>
    </div><?php
  }

	public static function get_current_step() {
		switch ( true ) {
			case ! class_exists( 'Jetpack' ) && ! file_exists( WP_PLUGIN_DIR . '/jetpack/jetpack.php' ):
				$current_step = 0;
				break;
			case ! defined( 'JETPACK__VERSION' ) || ! class_exists( 'Jetpack' ):
				$current_step = 1;
				break;
			case ! Jetpack::is_active():
				$current_step = 2;
				break;
			case ! Jetpack::is_module_active( 'sso' ):
				$current_step = 3;
				break;
			case '1' != get_option( 'jetpack_sso_require_two_step' ):
				$current_step = 4;
				break;
			default:
				$current_step = 5;
							update_option( 'clef_jetpack_integrated', true );
				break;
		}

		return $current_step;
	}

	private static function get_jetpack_prompt( $current_step ) {
    // If Jetpack is not installed at all; prompt to install it.
    // This is a pretty rough way of doing things, but it works.
    // Including the class_exists check as a safeguard against weird file paths.
		if ( $current_step === 0 ) {
      add_thickbox();
      wp_enqueue_script( 'plugin-install' );
      wp_enqueue_script('wp-util');
      wp_enqueue_script('updates');
      ?>
      <p><?php _e( 'To continue securing your site, we recommend Jetpack\'s, "Sign on with WordPress.com" feature, with Two-Step Authentication.', 'wpclef' ); ?></p>
      <p>
        <a href="<?php echo admin_url("/plugin-install.php?tab=plugin-information&plugin=jetpack&TB_iframe=true&width=600&height=550") ?>" class="install-now button"><?php _e( 'Install Jetpack', 'wpclef' ); ?></a>
      </p>
      <?php
      return;
    }

    // If Jetpack is installed but not active, give them a button to activate it right here
		if ( $current_step === 1 ) {
      $url = admin_url( 'admin.php?page=clef&jetpack=activate' );
      ?>
      <p><?php _e( 'It looks like you have the Jetpack plugin installed, but not activated. Activate it now so that you can use their "Sign on with WordPress.com" feature to require Two-Step Authentication.', 'wpclef' ); ?></p>
      <p><a href="<?php echo esc_url( $url ); ?>" class="button"><?php _e( 'Activate Jetpack', 'wpclef' ); ?></a></p>
      <?php
      return;
    }

    // If Jetpack isn't connected yet, prompt them to connect.
		if ( $current_step === 2 ) {
      $url = admin_url( 'admin.php?page=clef&jetpack=connect' );
      ?>
      <p><?php _e( 'It looks like you have Jetpack installed, but have not connected to WordPress.com yet. Once you are connected, you can enable "Sign on with WordPress.com" for your site, and require Two-Step Authentication.', 'wpclef' ); ?></p>
      <p><a href="<?php echo esc_url( $url ); ?>" class="button">Connect Jetpack</a></p>
      <?php
      return;
    }

    // Jetpack is installed, active, and connected. If SSO isn't active, prompt them.
		if ( $current_step === 3 ) {
      $url = admin_url( 'admin.php?page=clef&jetpack=enable-sso' );
      ?>
      <p><?php _e( 'You have Jetpack installed already, so you can enable "Sign on with WordPress.com" for your site, and require Two-Step Authentication via Jetpack.', 'wpclef' ); ?></p>
      <p><a href="<?php echo esc_url( $url ); ?>" class="button">Enable Sign on with WordPress.com</a></p>
      <?php
      return;
    }

    // If the 2FA requirement isn't active, prompt them.
		if ( $current_step === 4 ) {
      $url = admin_url( 'admin.php?page=clef&jetpack=require-2fa' );
      ?>
      <p><?php _e( 'Since you already have Jetpack\'s "Sign on with WordPress.com" feature enabled, you just need to make sure you have Two-Step Authentication activated on your WordPress.com account, and then you can require it for all log ins on this site.', 'wpclef' ); ?></p>
      <p><a href="https://wordpress.com/me/security/two-step" target="_blank"><?php _e( 'Configure Two-Step Authentication on your WordPress.com account', 'wpclef' ); ?></a></p>
      <p><a href="<?php echo esc_url( $url ); ?>" class="button"><?php _e( 'Require Two-Step Authentication-protected WordPress.com accounts', 'wpclef' ); ?></a></p>
      <?php
      return;
    }

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
	    case 'dismiss':
		    update_option( 'clef_jetpack_dismissal', self::get_current_step() );
		    break;

    case 'activate':
      activate_plugin(
        'jetpack/jetpack.php',
        admin_url( 'plugins.php' ) // redirect to Plugins screen to get the best prompt to connect
      );
      break;

    case 'connect':
	    update_option( 'clef_jetpack_cta_used', true );
	    $url = Jetpack::init()->build_connect_url( true, true, 'wpclef' );
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

// block access to regular login, once they've completed all the steps...
if ( get_option( 'clef_jetpack_integrated' ) ) {
	// Everything is set up and good to go
	add_filter( 'jetpack_sso_bypass_login_forward_wpcom', '__return_true' );
	add_filter( 'jetpack_remove_login_form', '__return_true' );
}

