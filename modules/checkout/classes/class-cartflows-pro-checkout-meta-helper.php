<?php
/**
 * Checkout meta helper
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Meta Boxes setup
 */
class Cartflows_Pro_Checkout_Meta_Helper {

	/**
	 * Instance
	 *
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Meta Option
	 *
	 * @var $meta_option
	 */
	private static $meta_option = null;

	/**
	 * Your Product Meta
	 *
	 * @var $your_product_meta
	 */
	private static $your_product_meta = array();

	/**
	 * Initiator
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
	}

	/**
	 * Fetch default width of checkout fields by key.
	 *
	 * @param string $checkout_field_key field key.
	 * @return int
	 */
	public function get_default_checkout_field_width( $checkout_field_key ) {

		$default_width = 100;
		switch ( $checkout_field_key ) {
			case 'billing_first_name':
			case 'billing_last_name':
			case 'billing_address_1':
			case 'billing_address_2':
			case 'shipping_first_name':
			case 'shipping_last_name':
			case 'shipping_address_1':
			case 'shipping_address_2':
				$default_width = 50;
				break;

			case 'billing_city':
			case 'billing_state':
			case 'billing_postcode':
			case 'shipping_city':
			case 'shipping_state':
			case 'shipping_postcode':
				$default_width = 33;
				break;

			default:
				$default_width = 100;
				break;
		}

		return $default_width;
	}


	/**
	 * Prepare HTML data for billing and shipping fields.
	 *
	 * @param string  $field checkout field key.
	 * @param string  $field_data checkout field object.
	 * @param integer $post_id chcekout post id.
	 * @param string  $type checkout field type.
	 * @return array
	 */
	public function prepare_field_arguments( $field, $field_data, $post_id, $type ) {

		if ( isset( $field_data['label'] ) ) {
			$field_name = $field_data['label'];
		} elseif ( 'shipping_address_2' == $field || 'billing_address_2' == $field ) {
			$field_name = 'Street address line 2';
		}

		if ( isset( $field_data['width'] ) ) {
			$width = $field_data['width'];
		} else {
			$width = get_post_meta( $post_id, 'wcf-field-width_' . $field, true );
			if ( ! $width ) {
				$width = $this->get_default_checkout_field_width( $field );
			}
		}

		if ( isset( $field_data['enabled'] ) ) {
			$is_enabled = true === $field_data['enabled'] ? 'yes' : 'no';
		} else {
			$is_enabled = get_post_meta( $post_id, 'wcf-' . $field, true );

			if ( '' === $is_enabled ) {
				$is_enabled = 'yes';
			}
		}

		$field_args = array(
			'type'        => ( isset( $field_data['type'] ) && ! empty( $field_data['type'] ) ) ? $field_data['type'] : '',
			'label'       => $field_name,
			'name'        => 'wcf-' . $field,
			'placeholder' => isset( $field_data['placeholder'] ) ? $field_data['placeholder'] : '',
			'width'       => $width,
			'enabled'     => $is_enabled,
			'after'       => 'Enable',
			'section'     => $type,
			'default'     => isset( $field_data['default'] ) ? $field_data['default'] : '',
			'required'    => ( isset( $field_data['required'] ) && true == $field_data['required'] ) ? 'yes' : 'no',
			'optimized'   => ( isset( $field_data['optimized'] ) && true == $field_data['optimized'] ) ? 'yes' : 'no',
			'options'     => ( isset( $field_data['options'] ) && ! empty( $field_data['options'] ) ) ? implode( ',', $field_data['options'] ) : '',
		);

		if ( 'shipping' === $type ) {
			if ( isset( $field_data['custom'] ) && $field_data['custom'] ) {
				$field_args['after_html']  = '<span class="wcf-cpf-actions" data-type="shipping" data-key="' . $field . '"> | ';
				$field_args['after_html'] .= '<a class="wcf-pro-custom-field-remove"><span class="dashicons dashicons-trash"></span></a>';
				$field_args['after_html'] .= '</span>';
			}
		}

		if ( 'billing' === $type ) {
			if ( isset( $field_data['custom'] ) && $field_data['custom'] ) {
				$field_args['after_html']  = '<span class="wcf-cpf-actions" data-type="billing" data-key="' . $field . '">';
				$field_args['after_html'] .= '<a class="wcf-pro-custom-field-remove wp-ui-text-notification">' . __( 'Remove', 'cartflows-pro' ) . '</a>';
				$field_args['after_html'] .= '</span>';
			}
		}

		return $field_args;
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Checkout_Meta_Helper::get_instance();
