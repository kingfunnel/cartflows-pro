<?php
/**
 * Cartflows Helper.
 *
 * @package CARTFLOWS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Helper.
 */
class Cartflows_Pro_Helper {

	/**
	 * Offer settings data
	 *
	 * @var zapier
	 */
	private static $offer_settings = null;

	/**
	 * Get Optin fields.
	 *
	 * @return array.
	 */
	public static function get_optin_default_fields() {

		$optin_fields = array(
			'billing_first_name' => array(
				'label'        => __( 'First name', 'cartflows-pro' ),
				'required'     => true,
				'class'        => array(
					'form-row-first',
				),
				'autocomplete' => 'given-name',
				'priority'     => 10,
			),
			'billing_last_name'  => array(
				'label'        => __( 'Last name', 'cartflows-pro' ),
				'required'     => true,
				'class'        => array(
					'form-row-last',
				),
				'autocomplete' => 'family-name',
				'priority'     => 20,
			),
			'billing_email'      => array(
				'label'        => __( 'Email address', 'cartflows-pro' ),
				'required'     => true,
				'type'         => 'email',
				'class'        => array(
					'form-row-wide',
				),
				'validate'     => array(
					'email',
				),
				'autocomplete' => 'email username',
				'priority'     => 30,
			),
		);

		return $optin_fields;
	}

	/**
	 * Get Optin field.
	 *
	 * @param string $key Field key.
	 * @param int    $post_id Post id.
	 * @return array.
	 */
	public static function get_optin_fields( $key, $post_id ) {

		$saved_fields = get_post_meta( $post_id, 'wcf_fields_' . $key, true );

		if ( ! $saved_fields ) {
			$saved_fields = array();
		}

		$fields = array_filter( $saved_fields );

		if ( empty( $fields ) ) {
			if ( 'billing' === $key ) {

				$fields = self::get_optin_default_fields();

				update_post_meta( $post_id, 'wcf_fields_' . $key, $fields );
			}
		}

		return $fields;
	}

	/**
	 * Get zapier settings.
	 *
	 * @return  array.
	 */
	public static function get_offer_global_settings() {

		if ( null === self::$offer_settings ) {

			$settings_default = apply_filters(
				'cartflows_offer_global_settings',
				array(
					'separate_offer_orders' => 'separate',
				)
			);

			$offer_settings = Cartflows_Helper::get_admin_settings_option( '_cartflows_offer_global_settings', false, false );

			$offer_settings = wp_parse_args( $offer_settings, $settings_default );

			if ( ! did_action( 'wp' ) ) {
				return $offer_settings;
			} else {
				self::$offer_settings = $offer_settings;
			}
		}

		return self::$offer_settings;
	}

	/**
	 * Create Edit page link for the widgets.
	 *
	 * @since 1.6.13
	 * @param string $tab The Tab which has to display.
	 * @access public
	 */
	public static function get_current_page_edit_url( $tab ) {

		$url = add_query_arg(
			array(
				'wcf-tab' => $tab,
			),
			get_edit_post_link()
		);

		return $url;
	}

	/**
	 * Create setting page URL.
	 *
	 * @since 1.6.13
	 * @access public
	 */
	public static function get_setting_page_url() {

		$admin_url = add_query_arg(
			array(
				'page' => 'cartflows_settings',
			),
			admin_url( 'admin.php' )
		);

		return $admin_url;
	}

	/**
	 * Add Checkout field.
	 *
	 * @param string $type Field type.
	 * @param string $field_key Field key.
	 * @param int    $post_id Post id.
	 * @param array  $field_data Field data.
	 * @return  boolean.
	 */
	public static function add_checkout_field( $type, $field_key, $post_id, $field_data = array() ) {

		$fields = Cartflows_Helper::get_checkout_fields( $type, $post_id );

		$fields[ $field_key ] = $field_data;

		update_post_meta( $post_id, 'wcf_fields_' . $type, $fields );

		return true;
	}

	/**
	 * Delete checkout field.
	 *
	 * @param string $type Field type.
	 * @param string $field_key Field key.
	 * @param int    $post_id Post id.
	 * @return  array.
	 */
	public static function delete_checkout_field( $type, $field_key, $post_id ) {

		$fields = Cartflows_Helper::get_checkout_fields( $type, $post_id );

		if ( isset( $fields[ $field_key ] ) ) {
			unset( $fields[ $field_key ] );
		}

		update_post_meta( $post_id, 'wcf_fields_' . $type, $fields );

		return true;
	}
}
