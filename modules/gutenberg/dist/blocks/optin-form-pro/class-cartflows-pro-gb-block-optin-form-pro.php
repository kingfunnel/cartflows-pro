<?php
/**
 * WCFPB - Optin Form Pro.
 *
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Gb_Block_Optin_Form_Pro' ) ) {

	/**
	 * Class Cartflows_Pro_Gb_Block_Optin_Form_Pro.
	 */
	class Cartflows_Pro_Gb_Block_Optin_Form_Pro {

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

			add_filter( 'cartflows_gutenberg_optin_attributes_filters', array( $this, 'optin_form_pro_attributes' ) );
			add_action( 'cartflows_gutenberg_optin_options_filters', array( $this, 'dynamic_filters' ), 10, 2 );
		}

		/**
		 * Adds Optin From Pro Attributes.
		 *
		 * @param array $attributes Attributes array.
		 *
		 * @since 1.6.13
		 */
		public function optin_form_pro_attributes( $attributes ) {
			$attributes['input_skins'] = array(
				'type'    => 'string',
				'default' => '',
			);
			return $attributes;
		}

		/**
		 * Settings
		 *
		 * @since 1.6.13
		 * @var object $settings
		 */
		public static $settings;

		/**
		 * Dynamic filters.
		 *
		 * @param array $settings Settings array.
		 *
		 * @since 1.6.13
		 */
		public function dynamic_filters( $settings ) {
			self::$settings = $settings;

			$optin_fields = array(

				// Input Fields.
				array(
					'filter_slug'  => 'wcf-input-fields-skins',
					'setting_name' => 'input_skins',
				),
			);

			if ( isset( $optin_fields ) && is_array( $optin_fields ) ) {

				foreach ( $optin_fields as $key => $field ) {

					$setting_name = $field['setting_name'];

					add_filter(
						'cartflows_optin_meta_' . $field['filter_slug'],
						function ( $value ) use ( $setting_name ) {

							$value = self::$settings[ $setting_name ];

							return $value;
						},
						10,
						1
					);
				}
			}

		}

	}

	/**
	 *  Prepare if class 'Cartflows_Pro_Gb_Block_Optin_Form_Pro' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Pro_Gb_Block_Optin_Form_Pro::get_instance();
}
