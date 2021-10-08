<?php
/**
 * Cartflows_Pro_Checkout_Form
 *
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Checkout_Form' ) ) {

	/**
	 * Class Cartflows_Pro_Checkout_Form.
	 */
	class Cartflows_Pro_Checkout_Form {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Member Variable
		 *
		 * @since 1.6.13
		 * @var instance
		 */
		public static $block_list;

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

			/* Init ajax options */
			add_action( 'init', array( $this, 'dynamic_options_ajax_filters' ), 1 );

			add_filter( 'cartflows_gutenberg_cf_attributes_filters', array( $this, 'checkout_form_pro_attributes' ) );

			add_action( 'cartflows_gutenberg_checkout_options_filters', array( $this, 'dynamic_filters' ), 10, 2 );

		}

		/**
		 * Dynamic options ajax filters actions.
		 */
		public function dynamic_options_ajax_filters() {

			add_action(
				'cartflows_woo_checkout_update_order_review_init',
				function( $post_data ) {

					if ( ! empty( $post_data['_wcf_orderBumpSkin'] ) ) {

						$ob_options = array(
							// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
							// Order Bump.
							// array(
							// 'filter_slug'  => 'wcf-order-bump-position',
							// 'setting_name' => 'obposition',
							// ),.
							array(
								'filter_slug'  => 'wcf-order-bump-style',
								'setting_name' => 'orderBumpSkin',
							),
							// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
							// array(
							// 'filter_slug'  => 'wcf-order-bump-label',
							// 'setting_name' => 'orderBumpCheckboxLabel',
							// ),
							// array(
							// 'filter_slug'  => 'wcf-order-bump-hl-text',
							// 'setting_name' => 'orderBumpHighlightText',
							// ),
							// array(
							// 'filter_slug'  => 'wcf-order-bump-desc',
							// 'setting_name' => 'orderBumpCheckboxProductDescription',
							// ),.
							array(
								'filter_slug'  => 'wcf-show-bump-arrow',
								'setting_name' => 'orderBumpCheckboxArrow',
							),
							array(
								'filter_slug'  => 'wcf-show-bump-animate-arrow',
								'setting_name' => 'orderBumpCheckboxArrowAnimation',
							),
						);

						foreach ( $ob_options as $ob_option ) {

							$setting_name = '_wcf_' . $ob_option['setting_name'];

							if ( ! empty( $post_data[ $setting_name ] ) ) {

								$setting_value = $post_data[ $setting_name ];

								add_filter(
									'cartflows_checkout_meta_' . $ob_option['filter_slug'],
									function ( $value ) use ( $setting_value ) {

										$value = sanitize_text_field( wp_unslash( $setting_value ) );

										return $value;
									},
									10,
									1
								);
							}
						}
					}
				}
			);
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

			$checkout_id = get_the_id();

			$checkout_fields = array(
				array(
					'filter_slug'  => 'wcf-checkout-layout',
					'setting_name' => 'layout',
				),

				// Input Fields.
				array(
					'filter_slug'  => 'wcf-fields-skins',
					'setting_name' => 'inputSkins',
				),

				// Two step texts.
				array(
					'filter_slug'  => 'wcf-checkout-step-one-title',
					'setting_name' => 'stepOneTitleText',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-one-sub-title',
					'setting_name' => 'stepOneSubTitleText',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-two-title',
					'setting_name' => 'stepTwoTitleText',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-two-sub-title',
					'setting_name' => 'stepTwoSubTitleText',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-two-next-button-title-text',
					'setting_name' => 'offerButtonTitleText',
				),
				array(
					'filter_slug'  => 'wcf-checkout-step-two-next-button-subtitle-text',
					'setting_name' => 'offerButtonSubTitleText',
				),

			);

			if ( isset( $checkout_fields ) && is_array( $checkout_fields ) ) {

				foreach ( $checkout_fields as $key => $field ) {

					$setting_name = $field['setting_name'];

					if ( '' !== self::$settings[ $setting_name ] ) {

						add_filter(
							'cartflows_checkout_meta_' . $field['filter_slug'],
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

			add_filter(
				'cartflows_checkout_meta_wcf-checkout-box-note',
				function ( $is_note_enabled ) {

					$is_note_enabled = ( 'yes' === self::$settings['enableNote'] ) ? 'yes' : 'no';
					return $is_note_enabled;
				},
				10,
				1
			);

			if ( 'yes' === self::$settings['enableNote'] && '' !== self::$settings['noteText'] ) {

				add_filter(
					'cartflows_checkout_meta_wcf-checkout-box-note-text',
					function ( $checkout_note_text ) {

						$checkout_note_text = self::$settings['noteText'];
						return $checkout_note_text;
					},
					10,
					1
				);
			}

			// * Order bump fields */
				$order_bump_fields = array(
					// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
					// Order Bump.
					// array(
					// 'filter_slug'  => 'wcf-order-bump-position',
					// 'setting_name' => 'obposition',
					// ),.
					array(
						'filter_slug'  => 'wcf-order-bump-style',
						'setting_name' => 'orderBumpSkin',
					),
					// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
					// array(
					// 'filter_slug'  => 'wcf-order-bump-label',
					// 'setting_name' => 'orderBumpCheckboxLabel',
					// ),
					// array(
					// 'filter_slug'  => 'wcf-order-bump-hl-text',
					// 'setting_name' => 'orderBumpHighlightText',
					// ),
					// array(
					// 'filter_slug'  => 'wcf-order-bump-desc',
					// 'setting_name' => 'orderBumpCheckboxProductDescription',
					// ),.
					array(
						'filter_slug'  => 'wcf-show-bump-arrow',
						'setting_name' => 'orderBumpCheckboxArrow',
					),
					array(
						'filter_slug'  => 'wcf-show-bump-animate-arrow',
						'setting_name' => 'orderBumpCheckboxArrowAnimation',
					),
				);

				if ( isset( $order_bump_fields ) && is_array( $order_bump_fields ) ) {

					foreach ( $order_bump_fields as $key => $field ) {

						$setting_name = $field['setting_name'];

						if ( '' !== self::$settings[ $setting_name ] ) {
							add_filter(
								'cartflows_checkout_meta_' . $field['filter_slug'],
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

				add_action(
					'woocommerce_after_order_notes',
					function () {

						$ob_options = array(
							// 'obposition',
							'orderBumpSkin',
							// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
							// 'orderBumpCheckboxLabel',
							// 'orderBumpHighlightText',
							// 'orderBumpCheckboxProductDescription',
							'orderBumpCheckboxArrow',
							'orderBumpCheckboxArrowAnimation',
						);

						foreach ( $ob_options as $ob_option ) {
							if ( ! empty( self::$settings[ $ob_option ] ) ) {
								echo '<input type="hidden" class="input-hidden" name="_wcf_' . $ob_option . '" value="' . esc_attr( self::$settings[ $ob_option ] ) . '">';
							}
						}
					},
					99
				);

				// Checkout offer.

				$checkout_offer_fields = array(
					array(
						'filter_slug'  => 'wcf-checkout-offer-button-title',
						'setting_name' => 'offerButtonTitleText',
					),
					array(
						'filter_slug'  => 'wcf-checkout-offer-button-sub-title',
						'setting_name' => 'offerButtonSubTitleText',
					),
					// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
					// array(
					// 'filter_slug'  => 'wcf-pre-checkout-offer-popup-title',
					// 'setting_name' => 'preTitleText',
					// ),
					// array(
					// 'filter_slug'  => 'wcf-pre-checkout-offer-popup-sub-title',
					// 'setting_name' => 'preSubTitleText',
					// ),
					// array(
					// 'filter_slug'  => 'wcf-pre-checkout-offer-product-title',
					// 'setting_name' => 'PreProductTitleText',
					// ),
					// array(
					// 'filter_slug'  => 'wcf-pre-checkout-offer-desc',
					// 'setting_name' => 'PreProductDescText',
					// ),
					// array(
					// 'filter_slug'  => 'wcf-pre-checkout-offer-popup-btn-text',
					// 'setting_name' => 'PreOrderText',
					// ),
					// array(
					// 'filter_slug'  => 'wcf-pre-checkout-offer-popup-skip-btn-text',
					// 'setting_name' => 'PreSkipText',
					// ),.
				);

				if ( isset( $checkout_offer_fields ) && is_array( $checkout_offer_fields ) ) {

					foreach ( $checkout_offer_fields as $key => $field ) {

						$setting_name = $field['setting_name'];

						if ( '' !== self::$settings[ $setting_name ] ) {
							add_filter(
								'cartflows_checkout_meta_' . $field['filter_slug'],
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

				// Product options.

				$product_options_fields = array(

					array(
						'filter_slug'  => 'wcf-product-options-skin',
						'setting_name' => 'productOptionsSkin',
					),
					array(
						'filter_slug'  => 'wcf-show-product-images',
						'setting_name' => 'productOptionsImages',
					),
					array(
						'filter_slug'  => 'wcf-product-opt-title',
						'setting_name' => 'productOptionsSectionTitleText',
					),
					array(
						'filter_slug'  => 'wcf-your-products-position',
						'setting_name' => 'sectionposition',
					),
				);

				if ( isset( $product_options_fields ) && is_array( $product_options_fields ) ) {

					foreach ( $product_options_fields as $key => $field ) {

						$setting_name = $field['setting_name'];

						if ( '' !== self::$settings[ $setting_name ] ) {
							add_filter(
								'cartflows_checkout_meta_' . $field['filter_slug'],
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
		 * Registers the `core/latest-posts` block on server.
		 *
		 * @param array $attr Array of block attributes.
		 *
		 * @since 1.6.13
		 */
		public function checkout_form_pro_attributes( $attr ) {

				$attributes = array(
					'productBorderColor'              => array(
						'type'    => 'string',
						'default' => '',
					),
					'productBorderRadius'             => array(
						'type'    => 'string',
						'default' => '',
					),
					'productBorderWidth'              => array(
						'type'    => 'string',
						'default' => '',
					),
					'productBorderStyle'              => array(
						'type'    => 'string',
						'default' => 'none',
					),
					'productTextBgColor'              => array(
						'type'    => 'string',
						'default' => '',
					),
					'productTextColor'                => array(
						'type'    => 'string',
						'default' => '',
					),
					'productTitleTextColor'           => array(
						'type'    => 'string',
						'default' => '',
					),
					'orderbumpBorderColor'            => array(
						'type'    => 'string',
						'default' => '',
					),
					'orderbumpBorderRadius'           => array(
						'type'    => 'string',
						'default' => '',
					),
					'orderbumpBorderWidth'            => array(
						'type'    => 'string',
						'default' => '',
					),
					'orderbumpBorderStyle'            => array(
						'type'    => 'string',
						'default' => 'none',
					),
					'orderbumpTextBgColor'            => array(
						'type'    => 'string',
						'default' => '',
					),
					'orderbumpTextColor'              => array(
						'type'    => 'string',
						'default' => '',
					),
					'orderbumpcontentTextColor'       => array(
						'type'    => 'string',
						'default' => '',
					),
					'orderbumpcontentBgColor'         => array(
						'type'    => 'string',
						'default' => '',
					),
					'showprecheckoutoffer'            => array(
						'type'    => 'string',
						'default' => '',
					),
					'OverlayBackgroundColor'          => array(
						'type'    => 'string',
						'default' => '',
					),
					'ModalBackgroundColor'            => array(
						'type'    => 'string',
						'default' => '',
					),
					'TitleColor'                      => array(
						'type'    => 'string',
						'default' => '',
					),
					'SubtitleColor'                   => array(
						'type'    => 'string',
						'default' => '',
					),
					'DescriptionColor'                => array(
						'type'    => 'string',
						'default' => '',
					),
					// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
					// 'obposition'                          => array(
					// 'type'    => 'string',
					// 'default' => '',
					// ),.
					'orderBumpSkin'                   => array(
						'type'    => 'string',
						'default' => 'style-1',
					),
					'orderBumpCheckboxArrow'          => array(
						'type'    => 'string',
						'default' => 'no',
					),
					'orderBumpCheckboxArrowAnimation' => array(
						'type'    => 'string',
						'default' => 'no',
					),
					// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
					// 'orderBumpCheckboxLabel'              => array(
					// 'type'    => 'string',
					// 'default' => '',
					// ),
					// 'orderBumpHighlightText'              => array(
					// 'type'    => 'string',
					// 'default' => '',
					// ),
					// 'orderBumpCheckboxProductDescription' => array(
					// 'type'    => 'string',
					// 'default' => '',
					// ),.
					'sectionposition'                 => array(
						'type'    => 'string',
						'default' => '',
					),
					'twoStepBgColor'                  => array(
						'type'    => 'string',
						'default' => '',
					),
					'twoStepTextColor'                => array(
						'type'    => 'string',
						'default' => '',
					),
					'layout'                          => array(
						'type'    => 'string',
						'default' => 'two-column',
					),
					'productOptionsSkin'              => array(
						'type'    => 'string',
						'default' => '',
					),
					'productOptionsImages'            => array(
						'type'    => 'string',
						'default' => '',
					),
					'productOptionsSectionTitleText'  => array(
						'type'    => 'string',
						'default' => '',
					),
					// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
					// 'PreSkipText'                         => array(
					// 'type'    => 'string',
					// 'default' => '',
					// ),
					// 'PreOrderText'                        => array(
					// 'type'    => 'string',
					// 'default' => '',
					// ),
					// 'PreProductTitleText'                 => array(
					// 'type'    => 'string',
					// 'default' => '',
					// ),
					// 'preSubTitleText'                     => array(
					// 'type'    => 'string',
					// 'default' => '',
					// ),
					// 'preTitleText'                        => array(
					// 'type'    => 'string',
					// 'default' => '',
					// ),
					// 'PreProductDescText'                  => array(
					// 'type'    => 'string',
					// 'default' => '',
					// ),.
					'inputSkins'                      => array(
						'type'    => 'string',
						'default' => '',
					),
					'enableNote'                      => array(
						'type'    => 'string',
						'default' => 'yes',
					),
					'noteText'                        => array(
						'type'    => 'string',
						'default' => '',
					),
					'stepOneTitleText'                => array(
						'type'    => 'string',
						'default' => '',
					),
					'stepOneSubTitleText'             => array(
						'type'    => 'string',
						'default' => '',
					),
					'stepTwoTitleText'                => array(
						'type'    => 'string',
						'default' => '',
					),
					'stepTwoSubTitleText'             => array(
						'type'    => 'string',
						'default' => '',
					),
					'offerButtonTitleText'            => array(
						'type'    => 'string',
						'default' => '',
					),
					'offerButtonSubTitleText'         => array(
						'type'    => 'string',
						'default' => '',
					),
					'tstextFontSize'                  => array(
						'type'    => 'number',
						'default' => '',
					),
					'tstextFontSizeType'              => array(
						'type'    => 'string',
						'default' => 'px',
					),
					'tstextFontSizeTablet'            => array(
						'type' => 'number',
					),
					'tstextFontSizeMobile'            => array(
						'type' => 'number',
					),
					'tstextFontFamily'                => array(
						'type'    => 'string',
						'default' => 'Default',
					),
					'tstextFontWeight'                => array(
						'type' => 'string',
					),
					'tstextFontSubset'                => array(
						'type' => 'string',
					),
					'tstextLineHeightType'            => array(
						'type'    => 'string',
						'default' => 'em',
					),
					'tstextLineHeight'                => array(
						'type' => 'number',
					),
					'tstextLineHeightTablet'          => array(
						'type' => 'number',
					),
					'tstextLineHeightMobile'          => array(
						'type' => 'number',
					),
					'tstextLoadGoogleFonts'           => array(
						'type'    => 'boolean',
						'default' => false,
					),
				);

				return array_merge( $attr, $attributes );
		}
	}

	/**
	 *  Prepare if class 'Cartflows_Pro_Checkout_Form' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Pro_Checkout_Form::get_instance();
}
