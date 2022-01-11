<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Cartflows Pro Optin Form Extend file
 *
 * @since 1.6.13
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CartFlows_Pro_BB_Optin_Form_Extend' ) ) {

	/**
	 * This class initializes Cartflows Pro Optin Form
	 *
	 * @class CartFlows_Pro_BB_Optin_Form_Extend
	 */
	final class CartFlows_Pro_BB_Optin_Form_Extend {

		/**
		 * Initializes Optin Form.
		 *
		 * @since 1.6.13
		 * @return void
		 */
		public static function init() {

			// Apply dynamic option filters.
			add_action( 'cartflows_bb_optin_options_filters', __CLASS__ . '::option_dynamic_filters', 10, 2 );

		}

		/**
		 * Dynamic filters.
		 *
		 * @param array $settings Settings array.
		 *
		 * @since 1.6.13
		 */
		public static function option_dynamic_filters( $settings ) {

			add_filter(
				'cartflows_optin_meta_wcf-input-fields-skins',
				function ( $value ) use ( $settings ) {

					$value = $settings->input_skins;

					return $value;
				},
				10,
				1
			);
		}
	}

	CartFlows_Pro_BB_Optin_Form_Extend::init();
}
