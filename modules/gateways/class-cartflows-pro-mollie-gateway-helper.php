<?php
/**
 * Cod Gateway helper functions.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_mollie_Gateway_Helper .
 */
class Cartflows_Pro_Mollie_Gateway_Helper {

	/**
	 * Get user mollie customer id from order.
	 *
	 * @param object $order order object.
	 * @param bool   $test_mode test mode.
	 * @return null|string
	 */
	public function get_user_mollie_customer_id( $order, $test_mode ) {

		$order_customer_id = $order->get_customer_id();

		$mollie_customer_id = Mollie_WC_Plugin::getDataHelper()->getUserMollieCustomerId( $order_customer_id, $test_mode );

		if ( null === $mollie_customer_id ) {
			$mollie_customer_id = $order->get_meta( '_wcf_mollie_customer_id', true );

		}

		return $mollie_customer_id;
	}

	/**
	 * Get return url.
	 *
	 * @param int    $step_id step id.
	 * @param int    $order_id order id.
	 * @param string $order_key order key.
	 * @param string $session_key session key.
	 *
	 * @return string
	 */
	public function get_return_url( $step_id, $order_id, $order_key, $session_key ) {

		$url = get_permalink( $step_id );

		$args = array(
			'wcf-order'         => $order_id,
			'wcf-key'           => $order_key,
			'wcf-sk'            => $session_key,
			'wcf-mollie-return' => true,
		);

		return add_query_arg( $args, $url );
	}

	/**
	 * Redirect location after successfully completing process_payment
	 *
	 * @param \Mollie_WC_Payment_Order|\Mollie_WC_Payment_Payment $payment_object payment object.
	 *
	 * @return string
	 */
	public function get_process_payment_redirect( $payment_object ) {
		/*
		 * Redirect to payment URL
		 */
		return $payment_object->getCheckoutUrl();
	}

	/**
	 * Get WooCommerce payment geteways.
	 *
	 * @return array
	 */
	public function get_wc_gateway() {

		global $woocommerce;

		$gateways = $woocommerce->payment_gateways->payment_gateways();

		return $gateways[ $this->key ];
	}

	/**
	 * Save mollie customer id for order.
	 *
	 * @param object $order order data.
	 * @param string $customer_id customer id.
	 */
	public function set_mollie_customer_id( $order, $customer_id ) {

		if ( ! empty( $customer_id ) ) {

			try {

				$order->update_meta_data( '_mollie_customer_id', $customer_id );
				$order->update_meta_data( '_wcf_mollie_customer_id', $customer_id );
				$order->save();

				wcf()->logger->log( __FUNCTION__ . ': Stored Mollie customer ID ' . $customer_id . ' with order ' . $order->get_id() );
			} catch ( Exception $e ) {

				wcf()->logger->log( __FUNCTION__ . ": Couldn't load (and save) WooCommerce customer based on order ID " . $order->get_id() );
			}
		}
	}

	/**
	 * Maybe get mollie customer id from prev order for non logged-in user.
	 *
	 * @param string $billing_email user email.
	 *
	 * @return null|string
	 */
	public function maybe_get_mollie_customer_id_from_order( $billing_email ) {

		$mollie_customer_id = null;

		$prev_orders_by_meta = new WP_Query(
			array(
				'post_type'   => 'shop_order',
				'post_status' => 'any',
				'meta_query'  => array( //phpcs:ignore
					'relation' => 'AND',
					array(
						'key'     => '_billing_email',
						'value'   => $billing_email,
						'compare' => '=',
					),
					array(
						'key'     => '_mollie_customer_id',
						'compare' => 'EXISTS',
					),
				),
				'fields'      => 'ids',
				'order'       => 'ASC',
			)
		);

		if ( is_array( $prev_orders_by_meta->posts ) && count( $prev_orders_by_meta->posts ) > 0 ) {

			$prev_order_id = $prev_orders_by_meta->posts[0];

			$prev_order = wc_get_order( $prev_order_id );

			$mollie_customer_id = $prev_order->get_meta( '_mollie_customer_id', true );
		}

		return $mollie_customer_id;
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

		$order_id       = $offer_data['order_id'];
		$transaction_id = $offer_data['transaction_id'];
		$refund_amount  = number_format( $offer_data['refund_amount'], 2 );
		$refund_reason  = $offer_data['refund_reason'];
		$order_currency = $order->get_currency( $order );

		$response_id = false;

		if ( ! is_null( $refund_amount ) && isset( $transaction_id ) ) {

			$mollie = new \Mollie\Api\MollieApiClient();

			$settings  = Mollie_WC_Plugin::getSettingsHelper();
			$test_mode = $settings->isTestModeEnabled();
			$api_key   = $settings->getApiKey( $test_mode );

			$mollie->setApiKey( $api_key );
			$payment = $mollie->payments->get( $transaction_id );

			if ( $payment->canBeRefunded() && $payment->amountRemaining->currency === $order_currency && $payment->amountRemaining->value >= $refund_amount ) { //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

				// https://docs.mollie.com/reference/v2/refunds-api/create-refund.
				$refund      = $payment->refund(
					array(
						'amount' => array(
							'currency' => $order_currency,
							'value'    => $refund_amount, // You must send the correct number of decimals, thus we enforce the use of strings.
						),
					)
				);
				$response_id = $refund->id;

			}
		}

		return $response_id;
	}
}
