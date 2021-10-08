<?php
/**
 * Cartflows Checkout Field Optimization.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Checkout_Field_Optimization.
 */
class Cartflows_Pro_Checkout_Field_Optimization {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

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

		add_filter( 'cartflows_show_additional_field', array( $this, 'show_hide_additional_field_on_checkout' ), 10, 2 );

		add_filter( 'wp_enqueue_scripts', array( $this, 'add_frontend_localize_optimized_scripts' ) );
		add_filter( 'wp_enqueue_scripts', array( $this, 'add_frontend_localize_animate_scripts' ) );

		add_filter( 'woocommerce_checkout_fields', array( $this, 'optimize_checkout_fields' ), 1001 );

		add_filter( 'cartflows_coupon_field_options', array( $this, 'optimize_coupon_field' ), 10 );

	}


	/**
	 * Optimize the checkout fields.
	 *
	 * @param array $checkout_fields checkout fields.
	 * @return mixed
	 */
	public function optimize_checkout_fields( $checkout_fields ) {

		if ( ! _is_wcf_checkout_type() && _is_wcf_doing_checkout_ajax() ) {
			$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
		} else {
			$checkout_id = _get_wcf_checkout_id();
		}

		$is_custom_fields_enabled = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

		if ( 'yes' === $is_custom_fields_enabled ) {

			foreach ( $checkout_fields as $field_type => $checkout_field ) {
				if ( isset( $checkout_fields[ $field_type ] ) ) {
					$fields = $checkout_fields[ $field_type ];
					foreach ( $fields as $field_name => $field ) {
						if ( isset( $field['optimized'] ) && $field['optimized'] && ! $field['required'] ) {
							$checkout_fields[ $field_type ][ $field_name ]['class'][] = 'wcf-hide-field';
						}
					}
				}
			}
		}

		$is_order_note_optimized = apply_filters( 'cartflows_show_additional_field', true, true );
		if ( $is_order_note_optimized ) {
			$checkout_fields['order']['order_comments']['class'][] = 'wcf-hide-field';
		}

		return $checkout_fields;
	}

	/**
	 * Optimize coupon field.
	 *
	 * @param array $coupon_field coupon fields.
	 * @return mixed
	 */
	public function optimize_coupon_field( $coupon_field ) {

		$optimize_coupon_field = apply_filters( 'cartflows_show_coupon_field', true, true );

		if ( $optimize_coupon_field ) {
			$coupon_field['class'] = 'wcf-hide-field';
		}

		return $coupon_field;
	}

	/**
	 *  Add localize script for animate title.
	 */
	public function add_frontend_localize_animate_scripts() {

		if ( ! _is_wcf_checkout_type() ) {
			return;
		}

		global $post;

		$checkout_id = $post->ID;

		$localize['enabled'] = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-animate-browser-tab' );
		$localize['title']   = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-animate-browser-tab-title' );

		$localize_script  = '<!-- script to print the admin localized variables -->';
		$localize_script .= '<script type="text/javascript">';
		$localize_script .= 'var cartflows_animate_tab_fields = ' . wp_json_encode( $localize ) . ';';
		$localize_script .= '</script>';

		echo $localize_script;
	}

	/**
	 * Add localize variables.
	 */
	public function add_frontend_localize_optimized_scripts() {

		if ( ! _is_wcf_checkout_type() ) {
			return;
		}

		global $post;

		$checkout_id = $post->ID;

		$is_custom_fields_enabled = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

		if ( 'yes' === $is_custom_fields_enabled ) {

			$get_ordered_billing_fields  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_billing' );
			$get_ordered_shipping_fields = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_shipping' );

			if ( ! is_array( $get_ordered_billing_fields ) ) {
				$get_ordered_billing_fields = array();
			}

			if ( ! is_array( $get_ordered_shipping_fields ) ) {
				$get_ordered_shipping_fields = array();
			}

			$order_checkout_fields = array_merge( $get_ordered_billing_fields, $get_ordered_shipping_fields );

			foreach ( $order_checkout_fields as $field_key => $order_checkout_field ) {

				$is_enabled   = isset( $order_checkout_field['enabled'] ) ? $order_checkout_field['enabled'] : false;
				$is_required  = isset( $order_checkout_field['required'] ) ? $order_checkout_field['required'] : false;
				$is_optimized = isset( $order_checkout_field['optimized'] ) ? $order_checkout_field['optimized'] : false;

				$localize[ $field_key . '_field' ] = array(
					'is_optimized' => ( $is_enabled && ! $is_required && $is_optimized && 'yes' === $is_custom_fields_enabled ),

					/* Translators: %s: Field Name */
					'field_label'  => sprintf( __( '<div class="dashicons dashicons-arrow-right"></div> Add %s', 'cartflows-pro' ), $order_checkout_field['label'] ),
				);
			}
		}

		$localize['order_comments_field'] = array(
			'is_optimized' => apply_filters( 'cartflows_show_additional_field', true, true ),
			'field_label'  => __( '<div class="dashicons dashicons-arrow-right"></div>Add Order Notes', 'cartflows-pro' ),
		);

		$localize['wcf_custom_coupon_field'] = array(
			'is_optimized' => apply_filters( 'cartflows_show_coupon_field', true, true ),
			'field_label'  => __( '<div class="dashicons dashicons-arrow-right"></div> Have a coupon?', 'cartflows-pro' ),
		);

		$localize_script  = '<!-- script to print the admin localized variables -->';
		$localize_script .= '<script type="text/javascript">';
		$localize_script .= 'var cartflows_optimized_fields = ' . wp_json_encode( $localize ) . ';';
		$localize_script .= '</script>';

		echo $localize_script;
	}




	/**
	 * Show/Hide coupon field on checkout page
	 *
	 * @param bool $is_field true.
	 * @param bool $optimized_field true.
	 * @return bool
	 */
	public function show_hide_additional_field_on_checkout( $is_field, $optimized_field = false ) {

		if ( _is_wcf_checkout_type() ) {

			global $post;

			$checkout_id = $post->ID;
		} else {

			if ( _is_wcf_doing_checkout_ajax() ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $is_field;
			}
		}

		$show = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-additional-fields' );

		if ( $optimized_field ) {
			$optimized_show = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-optimize-order-note-field' );

			if ( 'yes' === $show && 'yes' === $optimized_show ) {
				return true;
			}
		} else {
			if ( 'yes' === $show ) {
				return true;
			}
		}

		return false;
	}


}

/**
 *  Prepare if class 'Cartflows_Pro_Admin' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Checkout_Field_Optimization::get_instance();
