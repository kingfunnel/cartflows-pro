<?php
/**
 * Cartflow default options.
 *
 * @package Cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Default_Meta {


	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var offer_fields
	 */
	private static $offer_fields = null;

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
	 *  Constructor
	 */
	public function __construct() {

		add_filter( 'cartflows_admin_save_meta_field_values', array( $this, 'fetch_meta_value' ), 10, 4 );
		add_filter( 'cartflows_flow_meta_options', array( $this, 'fetch_flow_meta_value' ), 10, 1 );
	}

	/**
	 * Get flow data.
	 *
	 * @param array $flow_meta flow data.
	 */
	public function fetch_flow_meta_value( $flow_meta ) {

		$flow_meta['wcf-enable-analytics'] = array(
			'default'  => 'yes',
			'sanitize' => 'FILTER_DEFAULT',
		);

		return $flow_meta;
	}

	/**
	 * Get checkout editor meta data.
	 *
	 * @param string  $meta_value defaulkt meta value.
	 * @param integer $post_id post id.
	 * @param string  $key meta key.
	 * @param string  $filter_type filter type.
	 * @return array
	 */
	public function fetch_meta_value( $meta_value, $post_id, $key, $filter_type ) {

		switch ( $filter_type ) {

			case 'FILTER_CARTFLOWS_PRO_CHECKOUT_PRODUCT_OPTIONS':
				if ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) { //phpcs:ignore
					foreach ( $_POST[ $key ] as $unique_id => $po_data ) { // phpcs:ignore

						if ( is_array( $po_data ) ) {
							$meta_value[ $unique_id ] = array_map( 'sanitize_text_field', $po_data );
						} else {
							$po_data                  = array();
							$meta_value[ $unique_id ] = $po_data;
						}
					}
				}
				break;

			case 'FILTER_SANITIZE_NUMBER_FLOAT':
				$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
				break;
			case 'FILTER_CARTFLOWS_PRO_CHECKOUT_RULES':
				$sanitized_data = array();

				if ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) { //phpcs:ignore
					foreach ( $_POST[ $key ] as $group_index => $group_data ) { // phpcs:ignore

						if ( is_array( $group_data ) && is_array( $group_data['rules'] ) ) {

							$rules = $this->sanitize_rules( $group_data['rules'] );

							$sanitized_data[ $group_index ]['group_id'] = sanitize_text_field( $group_data['group_id'] );
							$sanitized_data[ $group_index ]['step_id']  = sanitize_text_field( $group_data['step_id'] );
							$sanitized_data[ $group_index ]['rules']    = $rules;
						}
					}
					$meta_value = $sanitized_data;
				}

				break;
		}

		return $meta_value;

	}


	/**
	 * Sanitize rules.
	 *
	 * @param array $rules rules.
	 */
	public function sanitize_rules( $rules ) {

		$sanitized_rules = array();
		foreach ( $rules as $index => $rule_data ) {

			if ( ! empty( $rule_data['value'] ) ) {
				$sanitized_rules[] = $this->sanitize_rule_data( $rule_data );
			}
		}

		return $sanitized_rules;

	}

	/**
	 * Sanitize rule data.
	 *
	 * @param array $rule_data rule data.
	 */
	public static function sanitize_rule_data( $rule_data ) {

		$sanitized_input = '';

		if ( is_array( $rule_data ) ) {

			$sanitized_input = array();

			foreach ( $rule_data as $key => $value ) {
				$sanitized_key                     = sanitize_text_field( $key );
				$sanitized_input[ $sanitized_key ] = self::sanitize_rule_data( $value );
			}
		} else {
			$sanitized_input = sanitize_text_field( $rule_data );
		}

		return $sanitized_input;
	}

	/**
	 *  Offer Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	public function get_offer_fields( $post_id ) {

		if ( null === self::$offer_fields ) {

			self::$offer_fields = array(
				'wcf-offer-product'                   => array(
					'default'  => array(),
					'sanitize' => 'FILTER_CARTFLOWS_ARRAY',
				),
				'wcf-offer-quantity'                  => array(
					'default'  => 1,
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-offer-discount'                  => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-offer-discount-value'            => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_FLOAT',
				),
				'wcf-offer-flat-shipping-value'       => array(
					'default'  => 0,
					'sanitize' => 'FILTER_SANITIZE_NUMBER_FLOAT',
				),
				'wcf-enable-offer-product-variation'  => array(
					'default'  => 'no',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-offer-product-variation-options' => array(
					'default'  => 'inline',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-enable-offer-product-quantity'   => array(
					'default'  => 'no',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-custom-script'                   => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-no-next-step'                    => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-yes-next-step'                   => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-replace-main-order'              => array(
					'default'  => 'no',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-step-note'                       => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
			);
		}

		return apply_filters( 'cartflows_offer_meta_options', self::$offer_fields );
	}

	/**
	 *  Get checkout meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @param mix    $default options default value.
	 * @return string
	 */
	public function get_offers_meta_value( $post_id, $key, $default = false ) {

		$value = wcf()->options->get_save_meta( $post_id, $key );

		if ( ! $value ) {

			if ( $default ) {

				$value = $default;
			} else {

				$fields = $this->get_offer_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {

					$value = $fields[ $key ]['default'];
				}
			}
		}

		return $value;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Default_Meta::get_instance();
