<?php
/**
 * Cartflows Admin Helper.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Admin_Helper.
 */
class Cartflows_Pro_Admin_Helper {

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
	 * Get steps.
	 *
	 * @param int $flow_id Flow ID.
	 * @param int $step_id Step ID.
	 */
	public static function get_opt_steps( $flow_id, $step_id ) {

		$flow_steps    = get_post_meta( $flow_id, 'wcf-steps', true );
		$control_steps = array();
		$exclude_id    = $step_id;

		$optgroup = array(
			'upsell'   => esc_html__( 'Upsell ( Woo )', 'cartflows-pro' ),
			'downsell' => esc_html__( 'Downsell ( Woo )', 'cartflows-pro' ),
			'thankyou' => esc_html__( 'Thankyou ( Woo )', 'cartflows-pro' ),
		);

		$steps = array();
		if ( is_array( $flow_steps ) ) {
			foreach ( $flow_steps as $f_index => $f_data ) {
				$control_steps[] = intval( $f_data['id'] );
			}
		}

		$cartflows_steps_args = array(
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_type'      => 'cartflows_step',
			'post_status'    => 'publish',
			'post__not_in'   => array( $exclude_id ),
			/** 'fields'           => 'ids', */
		);
		array_push(
			$steps,
			array(
				'value' => '',
				'label' => 'Default',
			)
		);

		foreach ( $optgroup as $optgroup_key => $optgroup_value ) {
			$cartflows_steps_args['tax_query'] = array( // phpcs:ignore
				'relation' => 'AND',
				array(
					'taxonomy' => 'cartflows_step_type',
					'field'    => 'slug',
					'terms'    => $optgroup_key,
				),
				array(
					'taxonomy' => 'cartflows_step_flow',
					'field'    => 'slug',
					'terms'    => 'flow-' . $flow_id,

				),
			);
			$cartflows_steps_query = new WP_Query( $cartflows_steps_args );
			$cartflows_steps       = $cartflows_steps_query->posts;

			if ( ! empty( $cartflows_steps ) ) {

				array_push(
					$steps,
					array(
						'isopt' => true,
						'title' => $optgroup_value,
					)
				);
				foreach ( $cartflows_steps as $key => $cf_step ) {

					if ( ! in_array( $cf_step->ID, $control_steps, true ) ) {
						continue;
					}

					array_push(
						$steps,
						array(
							'value' => $cf_step->ID,
							'label' => esc_attr( $cf_step->post_title ),
						)
					);

				}
			}
		}

		return $steps;

	}

	/**
	 * Get product price.
	 *
	 * @param object $product product data.
	 */
	public static function get_product_original_price( $product ) {

		$custom_price = '';

		if ( $product->is_type( 'variable' ) ) {

			$default_attributes = $product->get_default_attributes();

			if ( ! empty( $default_attributes ) ) {

				foreach ( $product->get_children() as $c_in => $variation_id ) {

					if ( 0 === $c_in ) {
						$product_id = $variation_id;
					}

					$single_variation = new WC_Product_Variation( $variation_id );

					if ( $default_attributes == $single_variation->get_attributes() ) {

						$product_id = $variation_id;
						break;
					}
				}
			} else {

				$product_childrens = $product->get_children();

				if ( is_array( $product_childrens ) ) {

					foreach ( $product_childrens  as $c_in => $c_id ) {

						$product_id = $c_id;
						break;
					}
				}
			}

			if ( ! empty( $product_id ) ) {
				$product = wc_get_product( $product_id );
			}
		}

		if ( $product ) {
			$custom_price = $product->get_price( 'edit' );
		}

		return $custom_price;
	}

	/**
	 * Loop through the input and sanitize each of the values.
	 *
	 * @param array $input_settings input settings.
	 * @return array
	 */
	public static function sanitize_form_inputs( $input_settings = array() ) {
		$new_settings = array();
		foreach ( $input_settings as $key => $val ) {

			if ( is_array( $val ) ) {
				foreach ( $val as $k => $v ) {
					$new_settings[ $key ][ $k ] = ( isset( $val[ $k ] ) ) ? sanitize_text_field( $v ) : '';
				}
			} else {
				$new_settings[ $key ] = ( isset( $input_settings[ $key ] ) ) ? sanitize_text_field( $val ) : '';
			}
		}
		return $new_settings;
	}

	/**
	 * Update admin settings.
	 *
	 * @param string $key key.
	 * @param bool   $value key.
	 * @param bool   $network network.
	 */
	public static function update_admin_settings_option( $key, $value, $network = false ) {

		// Update the site-wide option since we're in the network admin.
		if ( $network && is_multisite() ) {
			update_site_option( $key, $value );
		} else {
			update_option( $key, $value );
		}

	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Admin' exist.
 *  Kicking this off by calling 'get_instance()' method
 *  Cartflows_Pro_Admin_Helper::get_instance();
 */
