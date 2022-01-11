<?php
/**
 * Mollie Gateway. Credit Card method.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Gateway_Mollie_Credit_Card.
 */
class Cartflows_Pro_Gateway_Mollie_Credit_Card extends Cartflows_Pro_Mollie_Gateway_Helper {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Key name variable
	 *
	 * @var key
	 */
	public $key = 'mollie_wc_gateway_creditcard';

	/**
	 * Refund Supported
	 *
	 * @var is_api_refund
	 */
	public $is_api_refund = true;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		if ( class_exists( 'Mollie_WC_Plugin' ) ) {

			add_action( Mollie_WC_Plugin::PLUGIN_ID . '_customer_return_payment_success', array( $this, 'maybe_setup_upsell' ) );

			/* Create mollie customer id for non logged-in user - Credit card */
			add_filter( 'woocommerce_mollie_wc_gateway_creditcard_args', array( $this, 'maybe_create_mollie_customer_id' ), 10, 2 );
		}

		add_action( 'wp_ajax_wcf_mollie_creditcard_process', array( $this, 'process_credit_card' ) );
		add_action( 'wp_ajax_nopriv_wcf_mollie_creditcard_process', array( $this, 'process_credit_card' ) );

		/**
		 * Mollie CC webhook while creating payment
		 */
		add_action( 'woocommerce_api_cartflows_mollie_cc_webhook', array( $this, 'maybe_handle_mollie_cc_webhook' ) );

		add_action( 'cartflows_offer_child_order_created_' . $this->key, array( $this, 'store_mollie_meta_keys_for_refund' ), 10, 3 );

		add_action( 'cartflows_offer_subscription_created', array( $this, 'add_subscription_payment_meta_for_mollie' ), 10, 3 );

	}

	/**
	 * May be setup upsell.
	 *
	 * @param object $order Order object.
	 *
	 * @return void
	 */
	public function maybe_setup_upsell( $order ) {

		Cartflows_Pro_Frontend::get_instance()->start_the_upsell_flow( $order );
	}

	/**
	 * Get webhook url.
	 *
	 * @param int    $step_id step id.
	 * @param int    $order_id order id.
	 * @param string $order_key order key.
	 *
	 * @return string
	 */
	public function get_webhook_url( $step_id, $order_id, $order_key ) {

		$url = WC()->api_request_url( 'cartflows_mollie_cc_webhook' );

		$args = array(
			'step_id'   => $step_id,
			'order_id'  => $order_id,
			'order_key' => $order_key,
		);

		return add_query_arg( $args, $url );
	}

	/**
	 * After payment process.
	 *
	 * @param array $order order data.
	 * @param array $product product data.
	 * @return array
	 */
	public function process_offer_payment( $order, $product ) {

		$is_successful = false;

		$tr_id = $order->get_meta( '_' . $product['step_id'] . '_tr_id', true );

		$response_data = array(
			'id' => $tr_id,
		);

		$this->store_offer_transaction( $order, $response_data, $product );

		if ( '' !== $tr_id ) {

			$is_successful = true;
		}

		return $is_successful;
	}

	/**
	 * Store Offer Trxn Charge.
	 *
	 * @param WC_Order $order            The order that is being paid for.
	 * @param Object   $response           The response that is send from the payment gateway.
	 * @param array    $product             The product data.
	 */
	public function store_offer_transaction( $order, $response, $product ) {

		$order_id = $order->get_id();

		wcf()->logger->log( 'Mollie Credit Card : Offer Transaction :: Transaction ID = ' . $response['id'] . ' Captured' );

		$order->update_meta_data( 'cartflows_offer_txn_resp_' . $product['step_id'], $response['id'] );
		$order->save();
	}

	/**
	 * Process credit card upsell payment.
	 *
	 * @return bool
	 */
	public function process_credit_card() {

		$nonce = filter_input( INPUT_POST, '_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $nonce, 'wcf_mollie_creditcard_process' ) ) {
			return;
		}

		$step_id      = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$flow_id      = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : 0;
		$order_id     = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : 0;
		$order_key    = isset( $_POST['order_key'] ) ? sanitize_text_field( wp_unslash( $_POST['order_key'] ) ) : '';
		$offer_type   = isset( $_POST['offer_type'] ) ? sanitize_text_field( wp_unslash( $_POST['offer_type'] ) ) : '';
		$offer_action = isset( $_POST['offer_action'] ) ? sanitize_text_field( wp_unslash( $_POST['offer_action'] ) ) : '';
		$session_key  = isset( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ CARTFLOWS_SESSION_COOKIE . $flow_id ] ) ) : '';
		$order        = wc_get_order( $order_id );
		$variation_id = '';
		$input_qty    = '';

		if ( isset( $_POST['variation_id'] ) && ! empty( $_POST['variation_id'] ) ) {
			$variation_id = intval( $_POST['variation_id'] );
		}
		if ( isset( $_POST['input_qty'] ) && ! empty( $_POST['input_qty'] ) ) {
			$input_qty = intval( $_POST['input_qty'] );
		}
		// May need to update the data if variation and quantity changes using offer shortcodes ).
		$product_data = array(
			'variation_id' => $variation_id,
			'input_qty'    => $input_qty,
		);

		$order->update_meta_data( 'wcf_offer_product_data_' . $step_id, $product_data );
		$order->save();

		$offer_product = wcf_pro()->utils->get_offer_data( $step_id, $variation_id, $input_qty, $order_id );

		if ( isset( $offer_product['price'] ) && ( floatval( 0 ) === floatval( $offer_product['price'] )
				|| '' === trim( $offer_product['price'] ) ) ) {
			wp_send_json(
				array(
					'result'  => 'fail',
					'message' => __( '0 value product', 'cartflows-pro' ),
				)
			);
		} else {

			$settings_helper = Mollie_WC_Plugin::getSettingsHelper();

			// Is test mode enabled?
			$test_mode   = $settings_helper->isTestModeEnabled();
			$customer_id = $this->get_user_mollie_customer_id( $order, $test_mode );

			if ( $customer_id ) {

				$mollie_api = Mollie_WC_Plugin::getApiHelper()->getApiClient( $test_mode );

				$data = array(
					'amount'      => array(
						'currency' => Mollie_WC_Plugin::getDataHelper()->getOrderCurrency( $order ),
						'value'    => Mollie_WC_Plugin::getDataHelper()->formatCurrencyValue( $offer_product['price'], Mollie_WC_Plugin::getDataHelper()->getOrderCurrency( $order ) ),
					),
					'description' => "One-click payment {$order_id}_{$step_id}",
					'redirectUrl' => $this->get_return_url( $step_id, $order_id, $order_key, $session_key ),
					'webhookUrl'  => $this->get_webhook_url( $step_id, $order_id, $order_key ),
					'method'      => 'creditcard',
					'metadata'    => array(
						'order_id' => $order_id,
					),
					'customerId'  => $customer_id,
					// 'testmode'	 =>
				);

				$payment_object = $mollie_api->payments->create( $data );

				wp_send_json(
					array(
						'result'   => 'success',
						'redirect' => $this->get_process_payment_redirect( $payment_object ),
					)
				);
			}

			wp_send_json(
				array(
					'result'  => 'fail',
					'message' => __( 'Customer id not found. Payment failed', 'cartflows-pro' ),
				)
			);
		}
	}

	/**
	 * Handle mollie payment webhook.
	 *
	 * @return void
	 */
	public function maybe_handle_mollie_cc_webhook() {

		// Webhook test by Mollie.
		if ( isset( $_GET['testByMollie'] ) ) {
			wcf()->logger->log( __METHOD__ . ': Webhook tested by Mollie.' );
			return;
		}

		if ( empty( $_GET['order_id'] ) || empty( $_GET['order_key'] ) || empty( $_GET['step_id'] ) ) {
			Mollie_WC_Plugin::setHttpResponseCode( 400 );
			wcf()->logger->log( __METHOD__ . ':  No order ID or order key provided.' );
			return;
		}

		$step_id   = sanitize_text_field( wp_unslash( $_GET['step_id'] ) );
		$order_id  = sanitize_text_field( wp_unslash( $_GET['order_id'] ) );
		$order_key = sanitize_text_field( wp_unslash( $_GET['order_key'] ) );

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			Mollie_WC_Plugin::setHttpResponseCode( 404 );
			wcf()->logger->log( __METHOD__ . ":  Could not find order $order_id." );
			return;
		}

		if ( ! $order->key_is_valid( $order_key ) ) {
			Mollie_WC_Plugin::setHttpResponseCode( 401 );
			wcf()->logger->log( __METHOD__ . ":  Invalid key $key for order $order_id." );
			return;
		}

		// No Mollie payment id provided.
		if ( empty( $_POST['id'] ) ) { //phpcs:ignore
			Mollie_WC_Plugin::setHttpResponseCode( 400 );
			wcf()->logger->log( __METHOD__ . ': No payment object ID provided.' );
			return;
		}

		$payment_object_id = sanitize_text_field( wp_unslash( $_POST['id'] ) );
		$test_mode         = $order->get_meta( '_mollie_payment_mode', true ) == 'test';

		// Load the payment from Mollie, do not use cache.
		try {
			$payment_object = Mollie_WC_Plugin::getPaymentFactoryHelper()->getPaymentObject(
				$payment_object_id
			);
		} catch ( ApiException $exception ) {
			Mollie_WC_Plugin::setHttpResponseCode( 400 );
			return;
		}

		$payment = $payment_object->getPaymentObject( $payment_object->data, $test_mode, $use_cache = false );

		// Payment not found.
		if ( ! $payment ) {
			Mollie_WC_Plugin::setHttpResponseCode( 404 );
			wcf()->logger->log( __METHOD__ . ": payment object $payment_object_id not found." );
			return;
		}

		if ( $order_id != $payment->metadata->order_id ) {
			Mollie_WC_Plugin::setHttpResponseCode( 400 );
			wcf()->logger->log( __METHOD__ . ": Order ID does not match order_id in payment metadata. Payment ID {$payment->id}, order ID $order_id" );
			return;
		}

		$order->update_meta_data( '_' . $step_id . '_tr_id', $payment_object_id );
		$order->save();

		$order->add_order_note( __( 'Mollie credit card payment processed.', 'cartflows-pro' ) );

		// Status 200.
	}

	/**
	 * Create mollie customer id for non logged-in user.
	 *
	 * @param array  $data payment args.
	 * @param object $order order data.
	 * @return array
	 */
	public function maybe_create_mollie_customer_id( $data, $order ) {

		if ( isset( $data['payment']['customerId'] ) && null !== $data['payment']['customerId'] ) {
			return $data;
		}

		$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
		$flow_id     = wcf()->utils->get_flow_id_from_post_data();

		if ( $checkout_id && $flow_id ) {

			// Try to create a new Mollie Customer.
			try {

				$billing_first_name = $order->get_billing_first_name();
				$billing_last_name  = $order->get_billing_last_name();
				$billing_email      = $order->get_billing_email();

				$customer_id = $this->maybe_get_mollie_customer_id_from_order( $billing_email );

				if ( null === $customer_id ) {

					// Get the best name for use as Mollie Customer name.
					$user_full_name = $billing_first_name . ' ' . $billing_last_name;

					$settings_helper = Mollie_WC_Plugin::getSettingsHelper();

					// Is test mode enabled?.
					$test_mode = $settings_helper->isTestModeEnabled();

					// Create the Mollie Customer.
					$customer = Mollie_WC_Plugin::getApiHelper()->getApiClient( $test_mode )->customers->create(
						array(
							'name'     => trim( $user_full_name ),
							'email'    => trim( $billing_email ),
							'metadata' => array( 'order_id' => $order->get_id() ),
						)
					);

					$customer_id = $customer->id;
				}

				$this->set_mollie_customer_id( $order, $customer_id );

				wcf()->logger->log( __FUNCTION__ . ": Created a Mollie Customer ($customer_id) for order with ID " . $order->get_id() );

				$data['payment']['customerId'] = $customer_id;

			} catch ( \Mollie\Api\Exceptions\ApiException $e ) {
				wcf()->logger->log( __FUNCTION__ . ': Could not create Mollie Customer for order with ID ' . $order->get_id() . ' (' . ( $test_mode ? 'test' : 'live' ) . '): ' . $e->getMessage() . ' (' . get_class( $e ) . ')' );
			}

			wcf()->logger->log( 'Force save source enabled' );
		}

		return $data;
	}

	/**
	 * Is gateway support offer refund?
	 *
	 * @return bool
	 */
	public function is_api_refund() {

		return $this->is_api_refund;
	}

	/**
	 * Save required meta keys to refund seperate order.
	 *
	 * @param object $parent_order Parent order Object.
	 * @param object $child_order Child order Object.
	 * @param string $transaction_id id.
	 */
	public function store_mollie_meta_keys_for_refund( $parent_order, $child_order, $transaction_id ) {

		if ( ! empty( $transaction_id ) ) {

			$payment_mode = $parent_order->get_meta( '_mollie_payment_mode' );
			$child_order->update_meta_data( '_mollie_order_id', $transaction_id );
			$child_order->update_meta_data( '_mollie_payment_id', $transaction_id );
			$child_order->update_meta_data( '_mollie_payment_mode', $payment_mode );
			$child_order->save();
		}
	}

	/**
	 * Setup the Payment data for mollie Automatic Subscription.
	 *
	 * @param WC_Subscription $subscription An instance of a subscription object.
	 * @param object          $order Object of order.
	 * @param array           $offer_product array of offer product.
	 */
	public function add_subscription_payment_meta_for_mollie( $subscription, $order, $offer_product ) {

		if ( 'mollie_wc_gateway_creditcard' === $order->get_payment_method() ) {

			$subscription_id = $subscription->get_id();

			update_post_meta( $subscription_id, '_mollie_payment_id', $order->get_meta( '_mollie_payment_id', true ) );
			update_post_meta( $subscription_id, '_mollie_payment_mode', $order->get_meta( '_mollie_payment_mode', true ) );
			update_post_meta( $subscription_id, '_mollie_customer_id', $order->get_meta( '_mollie_customer_id', true ) );
		}
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Mollie_Credit_Card' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Mollie_Credit_Card::get_instance();
