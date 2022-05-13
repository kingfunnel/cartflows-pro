<?php
/**
 * Checkout Plugins - Stripe Gateway.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Gateway_Cpsw_Stripe.
 */
class Cartflows_Pro_Gateway_Cpsw_Stripe {

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
	public $key = 'cpsw_stripe';

	/**
	 * Refund supported variable
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

		add_filter( 'cpsw_force_save_card', array( $this, 'tokenize_if_required' ) );

		add_action( 'cpsw_redirect_order_url', array( $this, 'redirect_using_wc_function' ), 10, 2 );

		add_action( 'wp_ajax_wcf_cpsw_create_payment_intent', array( $this, 'create_payment_intent' ) );
		add_action( 'wp_ajax_nopriv_wcf_cpsw_create_payment_intent', array( $this, 'create_payment_intent' ) );

		add_action( 'cartflows_offer_subscription_created', array( $this, 'add_subscription_payment_meta' ), 10, 3 );

		add_action( 'cartflows_offer_child_order_created_' . $this->key, array( $this, 'add_required_meta_to_child_order' ), 10, 3 );
	}

	/**
	 * After payment process.
	 *
	 * @param object $order order data.
	 * @param array  $product product data.
	 * @return array
	 */
	public function process_offer_payment( $order, $product ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$is_successful = false;

		if ( isset( $_POST['cpsw_intent_id'] ) ) {

			$stored_intent_id = get_post_meta( $order->get_id(), 'wcf_cpsw_intent_id_' . $product['step_id'], true );
			$intent_id        = filter_input( INPUT_POST, 'cpsw_intent_id', FILTER_SANITIZE_STRING );

			$confirm_intent = ( $intent_id === $stored_intent_id ) ? true : false;

			if ( $confirm_intent ) {

				$is_successful  = true;
				$payment_method = filter_input( INPUT_POST, 'cpsw_payment_method', FILTER_SANITIZE_STRING );

				$order->update_meta_data( 'cartflows_offer_txn_resp_' . $product['step_id'], $intent_id );
				$order->update_meta_data( '_cartflows_offer_txn_cpsw_source_id_' . $product['step_id'], $payment_method );
				$order->update_meta_data( '_cartflows_offer_txn_cpsw_customer_id_' . $product['step_id'], $order->get_meta( '_cpsw_customer_id', true ) );
				$order->save();

				if ( ! wcf_pro()->utils->is_separate_offer_order() ) {
					$this->update_payout_details( $order, $intent_id, true );
				}
			}
		}

		return $is_successful;
	}

	/**
	 * Verify 3DS and create intent accordingly.
	 *
	 * @return bool
	 */
	public function create_payment_intent() {

		$nonce = filter_input( INPUT_POST, '_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'wcf_cpsw_create_payment_intent' ) ) {
			return;
		}

		$variation_id = '';
		$input_qty    = '';

		if ( isset( $_POST['variation_id'] ) ) {
			$variation_id = intval( $_POST['variation_id'] );
		}

		if ( isset( $_POST['input_qty'] ) && ! empty( $_POST['input_qty'] ) ) {
			$input_qty = intval( $_POST['input_qty'] );
		}

		$step_id       = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : 0;
		$order_id      = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : 0;
		$order         = wc_get_order( $order_id );
		$offer_product = wcf_pro()->utils->get_offer_data( $step_id, $variation_id, $input_qty, $order_id );

		if ( isset( $offer_product['price'] ) && ( floatval( 0 ) === floatval( $offer_product['price'] )
				|| '' === trim( $offer_product['price'] ) ) ) {
			wp_send_json(
				array(
					'result'  => 'fail',
					'message' => '0 value product',
				)
			);
		} else {

			$gateway  = $this->get_wc_gateway();
			$request  = $this->create_payment_intent_request_args( $order, $offer_product );
			$response = $gateway->create_payment_for_saved_payment_method( $request );

			wcf()->logger->log( 'CPSW Stripe payment intent api response ' . print_r( $response, true ) ); //phpcs:ignore

			if ( $response['success'] && isset( $response['data'] ) ) {

				$response = $response['data'];

				update_post_meta( $order_id, 'wcf_cpsw_intent_id_' . $step_id, $response->id );

				$client_secret = $response->client_secret;

				$payment_mode    = get_option( 'cpsw_mode' );
				$testmode        = $payment_mode && 'test' === $payment_mode ? true : false;
				$publishable_key = $testmode ? get_option( 'cpsw_test_pub_key' ) : get_option( 'cpsw_pub_key' );

				wcf()->logger->log( 'CPSW Stripe payment intent client secret key ' . $client_secret );

				wp_send_json(
					array(
						'result'        => 'success',
						'client_secret' => $client_secret,
						'cpsw_pk'       => $publishable_key,
					)
				);
			} else {
				wp_send_json(
					array(
						'result'  => 'fail',
						'message' => 'Payment Failed',
					)
				);
			}
		}
	}


	/**
	 * Tokenize to save source of payment if required
	 *
	 * @param bool $save_source force save source.
	 */
	public function tokenize_if_required( $save_source ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
		$flow_id     = wcf()->utils->get_flow_id_from_post_data();

		if ( $checkout_id && $flow_id ) {

			$wcf_step_obj      = wcf_pro_get_step( $checkout_id );
			$next_step_id      = $wcf_step_obj->get_next_step_id();
			$wcf_next_step_obj = wcf_pro_get_step( $next_step_id );

			if ( $next_step_id && $wcf_next_step_obj->is_offer_page() ) {

				$save_source = true;
				wcf()->logger->log( 'Force save source enabled' );
			}
		}

		return $save_source;
	}


	/**
	 * Redirection to order received URL.
	 *
	 * @param array  $url response data.
	 * @param object $order order data.
	 */
	public function redirect_using_wc_function( $url, $order ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		if ( 1 === did_action( 'cartflows_order_started' ) ) {

			$url = $order->get_checkout_order_received_url();
		}

		return $url;
	}

	/**
	 * Check if token is present.
	 *
	 * @param object $order order data.
	 */
	public function has_token( $order ) {

		$token = $order->get_meta( '_cpsw_source_id' );

		if ( ! empty( $token ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get WooCommerce payment geteway.
	 *
	 * @return object
	 */
	public function get_wc_gateway() {

		$gateways = wc()->payment_gateways->payment_gateways();

		return $gateways[ $this->key ];
	}


	/**
	 * Create payment intent args.
	 *
	 * @param WC_Order $order    The order that is being paid for.
	 * @param array    $product  The product data.
	 */
	public function create_payment_intent_request_args( $order, $product ) {

		$gateway = $this->get_wc_gateway();

		$order_source_id   = $order->get_meta( '_cpsw_source_id' );
		$order_customer_id = $order->get_meta( '_cpsw_customer_id' );

		if ( empty( $order_source_id ) || empty( $order_customer_id ) ) {

			$order_source = $gateway->prepare_order_source( $order );

			$order_source_id   = $order_source->source;
			$order_customer_id = $order_source->customer;
		}

		/* translators: %1s site name */
		$description = sprintf( __( '%1$s - Order %2$s_%3$s - One Click Payment', 'cartflows-pro' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $order->get_id(), $product['step_id'] );
		/* translators: %1s order number */
		$statement_descriptor = sprintf( __( 'Order %1$s_%2$s OTO', 'cartflows-pro' ), $order->get_order_number(), $product['step_id'] );

		return array(
			'payment_method'       => $order_source_id,
			'payment_method_types' => array( 'card' ),
			'amount'               => $gateway->get_formatted_amount( $product['price'] ),
			'currency'             => strtolower( $order->get_currency() ),
			'description'          => $description,
			'statement_descriptor' => $statement_descriptor,
			'customer'             => $order_customer_id,
			'confirm'              => false,
			'capture_method'       => $gateway->capture_method,

		);
	}

	/**
	 * Allow gateways to declare whether they support offer refund
	 *
	 * @return bool
	 */
	public function is_api_refund() {

		return $this->is_api_refund;
	}

	/**
	 * Process offer refund
	 *
	 * @param object $order Order Object.
	 * @param array  $offer_data offer data.
	 *
	 * @return string/bool.
	 */
	public function process_offer_refund( $order, $offer_data ) {

		$response_id = false;
		$gateway     = $this->get_wc_gateway();

		$transaction_id = $offer_data['transaction_id'];
		$refund_amount  = $offer_data['refund_amount'];
		$refund_reason  = $offer_data['refund_reason'];

		$response = $gateway->create_refund_request( $order, $refund_amount, $refund_reason, $transaction_id );

		if ( isset( $response['success'] ) && $response['success'] ) {
			$response_id = $response['data']->id;
			$this->update_payout_details( $order, $transaction_id, false );
		}

		return $response_id;
	}


	/**
	 * Setup the Payment data for Stripe's Automatic Subscription.
	 *
	 * @param WC_Subscription $subscription An instance of a subscription object.
	 * @param object          $order Object of order.
	 * @param array           $offer_product array of offer product.
	 */
	public function add_subscription_payment_meta( $subscription, $order, $offer_product ) {

		if ( 'cpsw_stripe' === $order->get_payment_method() ) {

			$subscription_id = $subscription->get_id();

			update_post_meta( $subscription_id, '_cpsw_source_id', $order->get_meta( '_cpsw_source_id', true ) );
			update_post_meta( $subscription_id, '_cpsw_customer_id', $order->get_meta( '_cpsw_customer_id', true ) );
		}
	}

	/**
	 * Save the parent payment meta to child order.
	 *
	 * @param object $parent_order Object of order.
	 * @param object $child_order Object of order.
	 * @param int    $transaction_id transaction id.
	 */
	public function add_required_meta_to_child_order( $parent_order, $child_order, $transaction_id ) {

		// In order to refund the upsell childe order, stripe checks if charge is captured or not.Hence need to add below key.
		update_post_meta( $child_order->get_id(), '_cpsw_charge_captured', 'yes' );

		$intent_secret = array(
			'id' => $transaction_id,
		);

		update_post_meta( $child_order->get_id(), '_cpsw_intent_secret', $intent_secret );

		$this->update_payout_details( $child_order, $transaction_id, true );
	}

	/**
	 * Save the parent payment meta to child order.
	 *
	 * @param object $order Object of order.
	 * @param string $intent_id id.
	 * @param bool   $initiate is intent initiate call.
	 */
	public function update_payout_details( $order, $intent_id, $initiate = false ) {
		$gateway = $this->get_wc_gateway();
		$gateway->update_stripe_balance( $order, $intent_id, $initiate );
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Cpsw_Stripe' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Cpsw_Stripe::get_instance();
