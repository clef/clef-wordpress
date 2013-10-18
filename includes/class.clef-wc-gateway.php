<?php
    class WC_Gateway_Clef extends WC_Payment_Gateway {

        static $INITIATION_PATH = "pay/initiate";
        static $SHIPPING_PATH = "pay/shipping/options";
        static $CHARGE_AUTHORIZE_PATH = "pay/authorize";
        static $CHARGE_PATH = "pay/charge";
        static $CART_TABLE_NAME = "clef_woo_carts";

        public static function init() {
            add_filter( 'woocommerce_payment_gateways', array(__CLASS__, "add_gateway") );

            add_action( 'woocommerce_after_cart_table', array(__CLASS__, "add_clef_button" ) );
            add_action( 'woocommerce_api_clef_initiation', array(__CLASS__, "handle_initiation") );
            add_action( 'woocommerce_api_clef_shipping', array(__CLASS__, "handle_shipping") );
            add_action( 'woocommerce_api_clef_checkout', array(__CLASS__, "handle_checkout" ) );
        }

        public function __construct() {
            $this->id = "clef";
            $this->icon = 'https://getclef.com/static/images/logos/icon_gray.png';
            $this->has_fields = false;
            $this->title = "Clef";
            $this->method_title = "Clef";
            $this->method_description = "Clef is the easiest way to pay online.";

            $this->init_form_fields();
            $this->init_settings();

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __( 'Enable/Disable', 'woocommerce' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enable Pay with Clef', 'woocommerce' ),
                    'default' => 'yes'
                )
            );
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

        public static function add_gateway( $methods ) {
            $methods[] = 'WC_Gateway_Clef';
            return $methods;
        }

        public static function add_clef_button() {
            global $woocommerce;
            $reference = htmlentities(self::serialize_cart($woocommerce->cart));
            $app_id =  Clef::setting( 'clef_settings_app_id' );

            $redirect_url = add_query_arg(
                array( 'wc-api' => "clef_checkout", "_wpnonce" => wp_create_nonce('woocommerce-process_checkout') ), 
                home_url()
            );

            $initiation_url = add_query_arg('wc-api', "clef_initiation", home_url());
            include CLEF_TEMPLATE_PATH . "woocommerce/pay_button.tpl.php";
        }

        public static function handle_initiation() {
            global $woocommerce;

            if (!isset($_GET['reference'])) {
                self::jsonError("No reference provided.");
            }

            $reference = $_GET["reference"];
            $amount = '100';

            $cart = self::deserialize_cart($reference);

            // calculate the total
            define( 'WOOCOMMERCE_CART', true);
            $cart->calculate_totals();
            $amount = $cart->total * 100;

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
                self::apiURL(self::$INITIATION_PATH), 
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
            $cart = self::deserialize_cart($_GET["reference"]);

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
                self::apiURL(self::$SHIPPING_PATH), 
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

                if ( sizeof( $woocommerce->cart->get_cart() ) == 0 ) {
                    wp_redirect( get_permalink( woocommerce_get_page_id( 'cart' ) ) );
                    exit;
                }

                if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) )
                    define( 'WOOCOMMERCE_CHECKOUT', true );

                $data = array(
                    'payment_id' => $_GET['payment_id'],
                    'app_id' => Clef::setting("clef_settings_app_id"),
                    'app_secret' => Clef::setting("clef_settings_app_secret")
                );

                $response = wp_remote_post( 
                    self::apiURL(self::$CHARGE_AUTHORIZE_PATH),
                    array(
                        'body' => $data,
                        'timeout' => 20
                    )
                );

                $charge_token = json_decode($response['body'])->charge_token;

                $data = array(
                    'charge_token' => $charge_token
                );

                $response = wp_remote_post( 
                    self::apiURL(self::$CHARGE_PATH),
                    array(
                        'body' => $data,
                        'timeout' => 20
                    )
                );

                $body = json_decode($response['body']);

                $shipping_info = $body->shipping_info;

                $split_name = explode(" ", $shipping_info->name, 2);

                $_POST['billing_first_name'] = $split_name[0];
                $_POST['billing_last_name'] = $split_name[1];
                $_POST['billing_address_1'] = $shipping_info->address_1;
                $_POST['billing_address_2'] = $shipping_info->address_2;
                $_POST['billing_city'] = $shipping_info->city;
                $_POST['billing_state'] = $shipping_info->state;
                $_POST['billing_postcode'] = $shipping_info->zip_code;
                $_POST['billing_country'] = "US";
                $_POST['billing_email'] = $body->email;
                $_POST['ship_to_different_address'] = 0;
                $_POST['shipping_method'] = $body->shipping_option;
                $_POST['shiptobilling'] = 1;

                // backward compatibility
                $_REQUEST['_n'] = $_POST['_wpnonce'] = $_GET['_wpnonce'];
                $_POST["payment_method"] = "clef";

                $woocommerce_checkout = $woocommerce->checkout();
                $woocommerce_checkout->process_checkout();
            }
        }

        public static function apiURL($path) {
            return CLEF_API_BASE . $path;
        }

        // public static function update($version) {
        //     if ($version == "0.0.1") {
        //         $migration = "(
        //             id INT NOT NULL AUTO_INCREMENT,
        //             value LONGTEXT,
        //             primary key (id)
        //         );";
        //         Clef::create_table(self::$CART_TABLE_NAME, $migration);
        //         Clef::setting('woo_version', $version);
        //     }
        // }

        public static function serialize_cart($cart) {
            $cart_hash = array(
                'i' => array(),
                'c' => array()
            );

            foreach ($cart->get_cart() as $id => $data) {
                $cart_hash['i'][$data['product_id']] = $data['quantity'];
            }

            if ($cart->coupons_enabled()) {
                foreach ($cart->get_applied_coupons() as $id => $name) {
                    $cart_hash['c'][] = $name;
                }
            }

            return base64_encode(json_encode($cart_hash));
        }

        public static function deserialize_cart($string_cart) {
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
    }
?>