<?php
    class WC_Gateway_Clef extends WC_Payment_Gateway {

        static $INITIATION_PATH = "pay/initiate";
        static $SHIPPING_PATH = "pay/shipping/options";
        static $CHARGE_AUTHORIZE_PATH = "pay/authorize";
        static $CHARGE_PATH = "pay/charge";
        static $CART_TABLE_NAME = "clef_woo_carts";

        public static $_instance;

        public static function init() {
            add_filter( 'woocommerce_payment_gateways', array(__CLASS__, "add_gateway") );

            add_action( 'woocommerce_proceed_to_checkout', array(__CLASS__, "add_cart_button" ) );
            add_action( 'woocommerce_api_clef_initiation', array(__CLASS__, "handle_initiation") );
            add_action( 'woocommerce_api_clef_shipping', array(__CLASS__, "handle_shipping") );
            add_action( 'woocommerce_api_clef_checkout', array(__CLASS__, "handle_checkout" ) );

            add_action( 'update_option_' . CLEF_OPTIONS_NAME, array(__CLASS__, "update_clef_settings_hook") );

            if (self::instance()->get_option('product_page') == "yes") {
                add_action( 'woocommerce_after_add_to_cart_button', array(__CLASS__, "add_single_product_button") );
            }

            wp_register_style('wpclef_styles', CLEF_URL . 'assets/css/wpclef.css', FALSE, '1.0.0');
            wp_enqueue_style('wpclef_styles');
        }

        public static function instance() {
            if ( is_null( self::$_instance ) )
                self::$_instance = new self();
            return self::$_instance;
        }

        public function __construct() {
            $this->id = "clef";
            $this->icon = CLEF_URL . 'assets/i/clef-40.png';
            $this->has_fields = true;
            $this->title = "Clef";
            $this->method_title = "Clef";
            $this->method_description = "Clef is the easiest way to pay online.";

            $this->init_form_fields();
            $this->init_settings();


            add_action( 'woocommerce_settings_' . $this->id, array( $this, "display_setup_tutorial") );
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        function update($version) {
            $this->init_settings();

            $current_version = Clef::setting('woo_version');

            if (!$current_version) {

                // Check if Clef is already installed and has app ID and Secret
                $setting = Clef::setting('clef_settings_app_id');
                if ($setting) {
                   $this->settings['clef_app_id'] = $setting;
                }

                $setting = Clef::setting('clef_settings_app_secret');
                if ($setting) {
                    $this->settings['clef_app_secret'] = $setting;
                }

                if (version_compare($version, $current_version) >= 0) {
                    // nothing to upgrade right now

                }

                // update version
                Clef::setting('woo_version', $version);
            }

            $this->process_admin_options();
        }

        function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __( 'Enable/Disable', 'woocommerce' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enable Pay with Clef', 'woocommerce' ),
                    'default' => 'yes'
                ),
                'product_page' => array(
                    'title' => __('Enable/Disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable one click checkout from product page', 'woocommerce'),
                    'default' => 'yes'
                ),
                'clef_app_id' => array(
                    'title' => __( 'Clef application ID', 'woocommerce' ),
                    'type' => 'text',
                    'label' => 'The identifier for your Clef application'
                ),
                'clef_app_secret' => array(
                    'title' => __( 'Clef application secret', 'woocommerce' ),
                    'type' => 'text',
                    'label' => 'The secret for your Clef application'
                )
            );
        }

        function admin_options() {
            if (!isset($this->settings['clef_app_id']) || $this->settings['clef_app_id'] == "") {
                include CLEF_TEMPLATE_PATH . "tutorial.tpl.php";
                wp_register_script('wpclef_keys', CLEF_URL . 'assets/js/keys.js', array('jquery'), '1.0.0', TRUE );
                wp_enqueue_script('wpclef_keys');
            }
            parent::admin_options();
        }

        function process_admin_options() {
            if (isset($this->settings)) {

                if (isset($_POST["woocommerce_clef_clef_app_id"]) && $_POST["woocommerce_clef_clef_app_id"] != Clef::setting("clef_settings_app_id")) {
                    Clef::setting("clef_settings_app_id", $_POST["woocommerce_clef_clef_app_id"]);
                }

                if (isset($_POST["woocommerce_clef_clef_app_secret"]) && $_POST["woocommerce_clef_clef_app_secret"] != Clef::setting("clef_settings_app_secret")) {
                    Clef::setting("clef_settings_app_secret", $_POST["woocommerce_clef_clef_app_secret"]);
                }
            }

            parent::process_admin_options();
        }

        function display_setup_tutorial() {
            echo('hi');
        }

        function process_payment( $order_id ) {
            global $woocommerce;

            $order = new WC_Order( $order_id );

            $order->payment_complete();
            $order->reduce_order_stock();
            $woocommerce->cart->empty_cart();


            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order)
            );
        }

        function payment_fields() {
            global $woocommerce;
            $reference = htmlentities(self::serialize_reference($woocommerce->cart));
            self::add_clef_button($reference);
        }

        public static function update_clef_settings_hook() {
            $updated = false;
            $_this = self::instance();
            $_this->init_settings();

            if (isset($_POST["clef_settings_app_id"]) && $_POST["clef_settings_app_id"] != $_this->setting["clef_settings_app_id"]) {
                Clef::setting("clef_settings_app_id", $_POST["woocommerce_clef_clef_app_id"]);
                $updated = true;
            }

            if (isset($_POST["clef_settings_app_secret"]) && $_POST["woocommerce_clef_clef_app_secret"] != $_this->setting["clef_settings_app_secret"]) {
                Clef::setting("clef_settings_app_secret", $_POST["woocommerce_clef_clef_app_secret"]);
                $updated = true;
            }

            if ($updated) {
                $_this->process_admin_options();
            }

        }

        public static function add_gateway( $methods ) {
            $methods[] = 'WC_Gateway_Clef';
            return $methods;
        }

        public static function add_clef_button($reference) {
            $app_id =  Clef::setting( 'clef_settings_app_id' );

            $redirect_url = add_query_arg(
                array( 'wc-api' => "clef_checkout", "_wpnonce" => wp_create_nonce('woocommerce-process_checkout') ), 
                home_url()
            );

            $initiation_url = add_query_arg('wc-api', "clef_initiation", home_url());
            include CLEF_TEMPLATE_PATH . "woocommerce/pay_button.tpl.php";
        }

        public static function add_cart_button() {
            global $woocommerce;
            $reference = htmlentities(self::serialize_reference($woocommerce->cart));
            self::add_clef_button($reference);
        }

        public static function add_single_product_button() {
            global $product;

            wp_register_script('clef_single_product', CLEF_URL . 'assets/js/single_product.js', array('jquery'), '1.0.0', TRUE );
            wp_enqueue_script('clef_single_product');

            self::add_clef_button(self::serialize_reference($product->id));
        }

        public static function handle_initiation() {
            global $woocommerce;

            if (!isset($_GET['reference'])) {
                self::jsonError("No reference provided.");
            }

            $reference = $_GET["reference"];
            $amount = '100';

            $cart = self::deserialize_reference($reference);

            // calculate the total
            define( 'WOOCOMMERCE_CART', true);
            $cart->calculate_totals();
            $amount = ($cart->total - $cart->shipping_total) * 100;

            if (!$reference) {
                self::jsonError("Please provide a reference ID");
            }
            
            $data = array(
                'reference' => $reference,
                'amount' => $amount,
                'app_id' => Clef::setting("clef_settings_app_id"),
                'app_secret' => Clef::setting("clef_settings_app_secret"),
                'variable_shipping_url' => add_query_arg("wc-api", "clef_shipping", home_url("/"))
            );

            $response = wp_remote_request( 
                self::api_url(self::$INITIATION_PATH), 
                array(
                    'method' => 'POST',
                    'body' => json_encode($data),
                    'timeout' => 20,
                    'headers'  => array(
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json'
                    )
                )
            );

            header('Content-Type: application/json');
            print($response['body']);
            exit(1);
        }

        public static function handle_shipping() {
            global $woocommerce;

            if (!isset($_GET["payment_id"]) || !isset($_GET["state"]) || !isset($_GET["reference"]) || !isset($_GET["zipcode"])) {
                self::jsonError("Payment ID, state, and reference required.");   
            }

            $woocommerce->customer->set_shipping_location("US", $_GET["state"], $_GET['zipcode']);
            $cart = self::deserialize_reference($_GET["reference"]);

            $cart->calculate_shipping();
            $shipping_options = array();
            foreach ($woocommerce->shipping->get_available_shipping_methods() as $id => $method) {
                $shipping_options[] = array(
                    "id" => $id,
                    "description" => $method->label,
                    "price" => $method->cost * 100
                );
            }

            $data = array(
                'payment_id' => $_GET["payment_id"],
                'app_id' => Clef::setting("clef_settings_app_id"),
                'app_secret' => Clef::setting("clef_settings_app_secret"),
                'shipping_options' => $shipping_options
            );

            $response = wp_remote_request( 
                self::api_url(self::$SHIPPING_PATH), 
                array(
                    'method' => 'POST',
                    'body' => json_encode($data),
                    'timeout' => 20,
                    'headers'  => array(
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json'
                    )
                )
            );

            header("Content-type: application/json");
            echo json_encode(
                array(
                    'shipping_options' => $shipping_options,
                    'success' => true
                )
            );
            exit(); 
        }

        public static function handle_checkout() {

            if ( isset( $_GET['payment_id'] ) ) {
                global $woocommerce;

                add_filter( 'woocommerce_billing_fields', array(__CLASS__, "remove_fields"), 10, 1);

                if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) )
                    define( 'WOOCOMMERCE_CHECKOUT', true );

                $data = array(
                    'payment_id' => $_GET['payment_id']
                );

                // exchange payment_id for token
                $response = self::api_request(self::$CHARGE_AUTHORIZE_PATH, $data);

                if ($response) {
                    // charge the token
                    $data = array('charge_token' => $response->charge_token );
                    $response = self::api_request(self::$CHARGE_PATH, $data);

                    if ($response) {
                        $shipping_info = $response->shipping_info;

                        $split_name = explode(" ", $shipping_info->name, 2);

                        // jerry-rig this so WC thinks that we're coming
                        // from checkout with all the appropriate information
                        // filled in
                        $_POST['billing_first_name'] = $split_name[0];
                        $_POST['billing_last_name'] = $split_name[1];
                        $_POST['billing_address_1'] = $shipping_info->address_1;
                        $_POST['billing_address_2'] = $shipping_info->address_2;
                        $_POST['billing_city'] = $shipping_info->city;
                        $_POST['billing_state'] = $shipping_info->state;
                        $_POST['billing_postcode'] = $shipping_info->zip_code;
                        $_POST['billing_country'] = "US";
                        $_POST['billing_email'] = $response->email;
                        $_POST['ship_to_different_address'] = 0;
                        $_POST['shipping_method'] = $response->shipping_option;
                        $_POST['shiptobilling'] = 1;

                        // set the nonce with backward compatibility
                        $_REQUEST['_n'] = $_POST['_wpnonce'] = $_GET['_wpnonce'];

                        $_POST["payment_method"] = "clef";

                        self::deserialize_reference($response->reference);

                        // call the woocommerce checkout methods
                        $woocommerce_checkout = $woocommerce->checkout();
                        $woocommerce_checkout->process_checkout();
                    }
                }

                // case where there are errors
                wp_safe_redirect( $_SERVER["HTTP_REFERER"] );
            }
        }

        public static function api_url($path) {
            return CLEF_API_BASE . $path;
        }

        public static function serialize_reference($cart) {
            $cart_hash = array(
                'i' => array(),
                'c' => array()
            );

            if (gettype($cart) == "object") {
                foreach ($cart->get_cart() as $id => $data) {
                    $cart_hash['i'][$data['product_id']] = $data['quantity'];
                }

                if ($cart->coupons_enabled()) {
                    foreach ($cart->get_applied_coupons() as $id => $name) {
                        $cart_hash['c'][] = $name;
                    }
                }
            } else {
                $cart_hash['i'][$cart] = 1;
            }

            return base64_encode(json_encode($cart_hash));
        }

        public static function deserialize_reference($string_cart) {
            global $woocommerce;

            $woocommerce->cart->empty_cart();

            $json = json_decode(base64_decode($string_cart), true);

            foreach ($json['i'] as $id => $quantity) {
                $woocommerce->cart->add_to_cart($id, $quantity);
            }

            if (isset($json['c'])) {
                foreach ($json['c'] as $name) {
                    $woocommerce->cart->add_discount($name);
                }
            }
            
            return $woocommerce->cart;
        }

        public static function remove_fields( $fields ) {
            $fields['billing_phone']['required'] = false;
            return $fields;
        }

        public static function jsonError($message) {
            header('Content-type: application/json');
            print(array( "error" => $message));
            exit(1);
        }

        public static function api_request($path, $args = array(), $method = "POST" ) {
            global $woocommerce;

            $base_args = array(
                'app_id' => Clef::setting("clef_settings_app_id"),
                'app_secret' => Clef::setting("clef_settings_app_secret")
            );

            $complete_args = array_merge($base_args, $args);

            if ($method == "POST") {
                $response = wp_remote_post( 
                    self::api_url($path),
                    array(
                        'body' => $complete_args,
                        'timeout' => 20
                    )
                );
            } else {
                // TODO: implement
            }

            if ( is_wp_error($response)  ) {
                $error = $response->get_error_message();
            } else if ($response['response']['code'] != 200) {
                try {
                    $error = json_decode($response['body'])->error;
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            } 

            if (isset($error)) {
                $woocommerce->add_error( __("Something went wrong: " . $error));
                return false; 
            } else {
                return json_decode($response['body']);
            }

        }

        public static function update_options() {
            error_log('hi');
        }
    }
?>