<?php
/**
 * Checkout post meta
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Meta Boxes setup
 */
class Cartflows_Pro_Checkout_Meta_Data {

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

		add_filter( 'cartflows_react_checkout_meta_fields', array( $this, 'meta_fields_react' ), 10, 3 );
		add_filter( 'cartflows_react_checkout_design_fields', array( $this, 'design_fields_react' ), 10, 2 );

		add_filter( 'cartflows_get_checkout_custom_fields_data', array( $this, 'custom_fields_data' ), 10, 2 );

		add_filter( 'cartflows_checkout_step_meta_fields', array( $this, 'filter_values' ), 10, 2 );

		add_filter( 'cartflows_react_checkout_settings_fields', array( $this, 'add_checkout_settings_pro_fields' ), 10, 1 );

	}

	/**
	 * Add pro checkout settings.
	 *
	 * @param array $settings settings.
	 */
	public function add_checkout_settings_pro_fields( $settings ) {

		$settings['settings']['checkout-settings']['fields']['wcf-animate-browser-tab'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Browser Tab Animation', 'cartflows-pro' ),
			'name'  => 'wcf-animate-browser-tab',
		);

		$settings['settings']['checkout-settings']['fields']['wcf-animate-browser-tab-text'] = array(
			'type'       => 'text',
			'label'      => __( 'Title Text', 'cartflows-pro' ),
			'name'       => 'wcf-animate-browser-tab-title',
			'conditions' => array(
				'fields' => array(
					array(
						'name'     => 'wcf-animate-browser-tab',
						'operator' => '===',
						'value'    => 'yes',
					),
				),
			),
		);

		return $settings;
	}

	/**
	 * Filter checkout values
	 *
	 * @param  array $options options.
	 * @param  int   $step_id step id.
	 */
	public function filter_values( $options, $step_id ) {

		// @todo Remove this code after v1.7.4 update.
		// Start.
		if ( ! empty( $options['wcf-order-bump-product'][0] ) ) {

			$product_id  = intval( $options['wcf-order-bump-product'][0] );
			$product_obj = wc_get_product( $product_id );

			if ( $product_obj ) {
				$options['wcf-order-bump-product'] = array(
					'value'          => $product_id,
					'label'          => $product_obj->get_name() . ' (#' . $product_obj->get_id() . ')',
					'original_price' => Cartflows_Pro_Admin_Helper::get_product_original_price( $product_obj ),
				);
			}
		}

		if ( ! empty( $options['wcf-order-bump-discount-coupon'][0] ) ) {

			$all_discount_types = wc_get_coupon_types();

			$coupon_code   = $options['wcf-order-bump-discount-coupon'][0];
			$coupon_data   = new WC_Coupon( $coupon_code );
			$coupon_id     = $coupon_data->get_id();
			$discount_type = get_post_meta( $coupon_id, 'discount_type', true );

			if ( $discount_type ) {
				$options['wcf-order-bump-discount-coupon'] = array(
					'value' => $coupon_code,
					'label' => get_the_title( $coupon_id ) . ' (Type: ' . $all_discount_types[ $discount_type ] . ')',
				);
			}
		}
		// End.

		if ( ! empty( $options['wcf-pre-checkout-offer-product'][0] ) ) {

			$product_id  = intval( $options['wcf-pre-checkout-offer-product'][0] );
			$product_obj = wc_get_product( $product_id );

			if ( $product_obj ) {
				$options['wcf-pre-checkout-offer-product'] = array(
					'value'          => $product_id,
					'label'          => $product_obj->get_name() . ' (#' . $product_obj->get_id() . ')',
					'original_price' => Cartflows_Pro_Admin_Helper::get_product_original_price( $product_obj ),
				);
			}
		}

		if ( ! empty( $options['wcf-checkout-discount-coupon'][0] ) ) {

			$all_discount_types = wc_get_coupon_types();

			$coupon_code   = $options['wcf-checkout-discount-coupon'][0];
			$coupon_data   = new WC_Coupon( $coupon_code );
			$coupon_id     = $coupon_data->get_id();
			$discount_type = get_post_meta( $coupon_id, 'discount_type', true );

			if ( $discount_type ) {
				$options['wcf-checkout-discount-coupon'] = array(
					'value' => $coupon_code,
					'label' => get_the_title( $coupon_id ) . ' (Type: ' . $all_discount_types[ $discount_type ] . ')',
				);
			}
		}

		if ( isset( $options['wcf_field_order_billing'] ) ) {
			$options['wcf_field_order_billing'] = $this->get_field_settings( $step_id, 'billing', '' );
		}

		if ( isset( $options['wcf_field_order_shipping'] ) ) {
			$options['wcf_field_order_shipping'] = $this->get_field_settings( $step_id, 'shipping', '' );
		}

		return $options;
	}


	/**
	 * Fet_name
	 *
	 * @param int $id id.
	 */
	public static function get_name( $id ) {

		$product_object = wc_get_product( $id );

		$formatted_name = '';

		if ( is_object( $product_object ) ) {
			$formatted_name = rawurldecode( $product_object->get_formatted_name() );
		}

		return $formatted_name;

	}

	/**
	 * Add meta fields
	 *
	 * @param array $settings checkout fields.
	 * @param int   $step_id step id.
	 * @param array $options options.
	 */
	public function meta_fields_react( $settings, $step_id, $options ) {

		$flow_id = get_post_meta( $step_id, 'wcf-flow-id', true );

		$opt_steps = Cartflows_Pro_Admin_Helper::get_opt_steps( $flow_id, $step_id );

		$settings['settings']['coupon']['fields'] = array(
			'coupon'     => array(
				'type'        => 'coupon',
				'name'        => 'wcf-checkout-discount-coupon',
				'label'       => __( 'Select Coupon', 'cartflows-pro' ),
				'placeholder' => __( 'Type to search for a coupon...', 'cartflows-pro' ),
				'multiple'    => false,
				'allow_clear' => true,
			),
			'coupon-doc' => array(
				'type'    => 'doc',
				/* translators: %1$1s: link html start, %2$12: link html end*/
				'content' => sprintf( __( 'For more information about the CartFlows coupon please %1$1s Click here.%2$2s', 'cartflows-pro' ), '<a href="https://cartflows.com/docs/enable-coupons-on-cartflows-page/" target="_blank">', '</a>' ),
			),
		);

		$settings['settings']['product-options']['fields'] = array(
			'wcf-enable-product-options'    => array(
				'type'  => 'checkbox',
				'label' => __( 'Enable Product Options', 'cartflows-pro' ),
				'name'  => 'wcf-enable-product-options',
			),

			'wcf-get-product-option-fields' => array(
				'type'          => 'product-options',
				'label'         => __( 'Enable Product Options', 'cartflows-pro' ),
				'name'          => 'wcf-product-options-data',
				'products_data' => $this->get_product_option_fields( $step_id ),
				'conditions'    => array(
					'fields' => array(
						array(
							'name'     => 'wcf-enable-product-options',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),

			'wcf-product-options'           => array(
				'type'       => 'radio',
				'label'      => __( 'Product Options Conditions', 'cartflows-pro' ),
				'name'       => 'wcf-product-options',
				'options'    => array(
					array(
						'value' => 'force-all',
						'label' => __( 'Restrict user to purchase all products', 'cartflows-pro' ),
					),
					array(
						'value' => 'single-selection',
						'label' => __( 'Let user select one product from all options', 'cartflows-pro' ),
					),
					array(
						'value' => 'multiple-selection',
						'label' => __( 'Let user select multiple products from all options', 'cartflows-pro' ),
					),
				),
				'conditions' => array(
					'fields' => array(
						array(
							'name'     => 'wcf-enable-product-options',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),

			'enable-variation'              => array(
				'type'       => 'checkbox',
				'label'      => __( 'Enable Variations', 'cartflows-pro' ),
				'name'       => 'wcf-enable-product-variation',
				'conditions' => array(
					'fields' => array(
						array(
							'name'     => 'wcf-enable-product-options',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),
			'wcf-product-variation-options' => array(
				'type'        => 'radio',
				'label'       => '',
				'name'        => 'wcf-product-variation-options',
				'child_class' => 'wcf-child-field',
				'options'     => array(
					array(
						'value' => 'inline',
						'label' => __( 'Show variations inline', 'cartflows-pro' ),
					),
					array(
						'value' => 'popup',
						'label' => __( 'Show variations in popup', 'cartflows-pro' ),
					),
				),
				'conditions'  => array(
					'fields' => array(
						array(
							'name'     => 'wcf-enable-product-options',
							'operator' => '===',
							'value'    => 'yes',
						),
						array(
							'name'     => 'wcf-enable-product-variation',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),
			'wcf-enable-product-quantity'   => array(
				'type'       => 'checkbox',
				'label'      => __( 'Enable Quantity', 'cartflows-pro' ),
				'name'       => 'wcf-enable-product-quantity',
				'conditions' => array(
					'fields' => array(
						array(
							'name'     => 'wcf-enable-product-options',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),
			'product-option-doc'            => array(
				'type'       => 'doc',
				'conditions' => array(
					'fields' => array(
						array(
							'name'     => 'wcf-enable-product-options',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
				/* translators: %1$1s: link html start, %2$12: link html end*/
				'content'    => sprintf( __( 'For more information about the product option settings %1$1s Click here. %2$2s', 'cartflows-pro' ), '<a href="https://cartflows.com/docs/set-default-product-in-product-options/" target="_blank">', '</a>' ),
			),

		);

		// @todo Remove old order bump code after v1.7.4 update.
		if ( in_array( get_option( 'wcf_order_bump_migrated', false ), array( 'no', 'processing' ), true ) ) {

			$settings['settings']['order-bump']['fields'] = array(
				'wcf-enable-ob'           => array(
					'type'  => 'checkbox',
					'label' => __( 'Enable Order Bump', 'cartflows-pro' ),
					'name'  => 'wcf-order-bump',
				),

				'ob-product-heading'      => array(
					'type'       => 'heading',
					'label'      => esc_html__( 'Product', 'cartflows-pro' ),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

				'wcf-ob-product'          => array(
					'type'                   => 'product',
					'label'                  => __( 'Select Product', 'cartflows-pro' ),
					'name'                   => 'wcf-order-bump-product',
					'placeholder'            => __( 'Type to search for a product...', 'cartflows-pro' ),
					'excluded_product_types' => array( 'grouped' ),
					'include_product_types'  => array( 'braintree-subscription', 'braintree-variable-subscription' ),
					'conditions'             => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

				'wcf-ob-product-qty'      => array(
					'type'       => 'number',
					'label'      => __( 'Product Quantity', 'cartflows-pro' ),
					'name'       => 'wcf-order-bump-product-quantity',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

				'wcf-ob-discount'         => array(
					'type'       => 'select',
					'label'      => __( 'Discount Type', 'cartflows-pro' ),
					'name'       => 'wcf-order-bump-discount',
					'options'    => array(
						array(
							'value' => '',
							'label' => esc_html__( 'Original', 'cartflows-pro' ),
						),
						array(
							'value' => 'discount_percent',
							'label' => esc_html__( 'Discount Percentage', 'cartflows-pro' ),
						),
						array(
							'value' => 'discount_price',
							'label' => esc_html__( 'Discount Price', 'cartflows-pro' ),
						),
						array(
							'value' => 'coupon',
							'label' => esc_html__( 'Coupon', 'cartflows-pro' ),
						),
					),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),
				'wcf-ob-dicount-value'    => array(
					'type'       => 'number',
					'label'      => __( 'Discount Value', 'cartflows-pro' ),
					'name'       => 'wcf-order-bump-discount-value',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
							array(
								'name'     => 'wcf-order-bump-discount',
								'operator' => 'in',
								'value'    => array( 'discount_price', 'discount_percent' ),
							),
						),
					),
				),

				'wcf-ob-discount-coupon'  => array(
					'type'        => 'coupon',
					'label'       => __( 'Select Coupon', 'cartflows-pro' ),
					'placeholder' => __( 'Type to search for a coupon...', 'cartflows-pro' ),
					'name'        => 'wcf-order-bump-discount-coupon',
					'conditions'  => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
							array(
								'name'     => 'wcf-order-bump-discount',
								'operator' => '===',
								'value'    => 'coupon',
							),
						),
					),
				),

				'wcf-ob-original-price'   => array(
					'type'       => 'text',
					'label'      => __( 'Original Price', 'cartflows-pro' ),
					'name'       => 'wcf-order-bump-product[original_price]',
					'value'      => $this->get_bump_original_price( $options, $step_id ),
					'tooltip'    => __( 'This is the unit price of product', 'cartflows-pro' ),
					'readonly'   => true,
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

				'wcf-ob-sell-price'       => array(
					'type'       => 'text',
					'label'      => __( 'Sell Price', 'cartflows-pro' ),
					'name'       => 'wcf-order-bump-product[sell_price]',
					'value'      => $this->get_bump_discount_price( $options, $step_id ),
					'tooltip'    => __( 'This is the unit discounted price of product', 'cartflows-pro' ),
					'readonly'   => true,
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

				'product-image'           => array(
					'type'       => 'image-selector',
					'label'      => __( 'Product Image', 'cartflows-pro' ),
					'name'       => 'wcf-order-bump-image',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

				'ob-settings-heading'     => array(
					'type'       => 'heading',
					'label'      => esc_html__( 'Settings', 'cartflows-pro' ),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

				'wcf-ob-position'         => array(
					'type'       => 'select',
					'label'      => __( 'Order Bump Position', 'cartflows-pro' ),
					'name'       => 'wcf-order-bump-position',
					'value'      => $options['wcf-order-bump-position'],
					'options'    => array(
						array(
							'value' => 'before-checkout',
							'label' => esc_html__( 'Before Checkout', 'cartflows-pro' ),
						),
						array(
							'value' => 'after-customer',
							'label' => esc_html__( 'After Customer Details', 'cartflows-pro' ),
						),
						array(
							'value' => 'after-order',
							'label' => esc_html__( 'After Order', 'cartflows-pro' ),
						),
						array(
							'value' => 'after-payment',
							'label' => esc_html__( 'After Payment', 'cartflows-pro' ),
						),
					),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),
				'wcf-ob-label'            => array(
					'type'       => 'text',
					'label'      => __( 'Checkbox Label', 'cartflows-pro' ),
					'name'       => 'wcf-order-bump-label',
					'value'      => $options['wcf-order-bump-label'],
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),
				'wcf-ob-highlight-text'   => array(
					'type'       => 'text',
					'label'      => __( 'Highlight Text', 'cartflows-pro' ),
					'name'       => 'wcf-order-bump-hl-text',
					'value'      => $options['wcf-order-bump-hl-text'],
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),
				'wcf-ob-product-desc'     => array(
					'type'       => 'textarea',
					'label'      => __( 'Product Description', 'cartflows-pro' ),
					'name'       => 'wcf-order-bump-desc',
					'value'      => $options['wcf-order-bump-desc'],
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

				'ob-adv-settings-heading' => array(
					'type'       => 'heading',
					'label'      => esc_html__( 'Advanced Settings', 'cartflows-pro' ),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),
				'wcf-ob-replace'          => array(
					'type'       => 'checkbox',
					'label'      => __( 'Replace First Product', 'cartflows-pro' ),
					'name'       => 'wcf-order-bump-replace',

					// translators: %1$1s: link html start, %2$2s: link html end.
					'desc'       => sprintf(
						__( 'It will replace the first selected product (from checkout products) with the order bump product. %1$1sLearn More »%2$2s', 'cartflows-pro' ), //phpcs:ignore
						'<a href="https://cartflows.com/docs/replace-first-product-with-order-bump-checkout-page/" target="_blank">',
						'</a>'
					),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

				'ob-next-step'            => array(
					'type'            => 'select',
					'label'           => __( 'On Order Bump Purchase - Next Step', 'cartflows-pro' ),
					'name'            => 'wcf-ob-yes-next-step',
					'options'         => $opt_steps,
					'data-flow-id'    => $flow_id,
					'data-exclude-id' => $step_id,
					'desc'            => sprintf(
						/* translators: %s: content */
						__( 'Note: Select the step if you want to redirect to a different step on the order bump purchase. %1$1sLearn More »%2$2s', 'cartflows-pro' ),
						'<a href="https://cartflows.com/docs/order-bump-purchase-conditional-redirect/" target="_blank">',
						'</a>'
					),
					'conditions'      => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

				'ob-doc'                  => array(
					'type'       => 'doc',
					'content'    => sprintf(
						/* translators: %1$1s: link html start, %2$12: link html end*/
						__( 'For more information about the order bump settings please %1$1sClick here.%2$2s', 'cartflows-pro' ),
						'<a href="https://cartflows.com/docs/how-to-add-order-bumps-to-woocommerce-sales-funnel/" target="_blank">',
						'</a>'
					),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-order-bump',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),
			);

		} else {
			// Multiple order bump options arrays start.
			$settings['settings']['multiple-order-bump-product']['fields'] = array(

				'wcf-ob-product'         => array(
					'type'                   => 'product',
					'label'                  => __( 'Select Product', 'cartflows-pro' ),
					'name'                   => 'product',
					'placeholder'            => __( 'Type to search for a product...', 'cartflows-pro' ),
					'excluded_product_types' => array( 'grouped' ),
					'include_product_types'  => array( 'braintree-subscription', 'braintree-variable-subscription' ),
					'nameComp'               => true,
				),

				'wcf-ob-product-qty'     => array(
					'type'  => 'number',
					'label' => __( 'Product Quantity', 'cartflows-pro' ),
					'name'  => 'quantity',
					'min'   => '1',

				),

				'wcf-ob-discount'        => array(
					'type'    => 'select',
					'label'   => __( 'Discount Type', 'cartflows-pro' ),
					'name'    => 'discount_type',
					'options' => array(
						array(
							'value' => '',
							'label' => esc_html__( 'Original', 'cartflows-pro' ),
						),
						array(
							'value' => 'discount_percent',
							'label' => esc_html__( 'Discount Percentage', 'cartflows-pro' ),
						),
						array(
							'value' => 'discount_price',
							'label' => esc_html__( 'Discount Price', 'cartflows-pro' ),
						),
						array(
							'value' => 'coupon',
							'label' => esc_html__( 'Coupon', 'cartflows-pro' ),
						),
					),
				),
				'wcf-ob-dicount-value'   => array(
					'type'       => 'number',
					'label'      => __( 'Discount Value', 'cartflows-pro' ),
					'name'       => 'discount_value',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'discount_type',
								'operator' => 'in',
								'value'    => array( 'discount_price', 'discount_percent' ),
							),
						),
					),
				),

				'wcf-ob-discount-coupon' => array(
					'type'        => 'coupon',
					'label'       => __( 'Select Coupon', 'cartflows-pro' ),
					'placeholder' => __( 'Type to search for a coupon...', 'cartflows-pro' ),
					'name'        => 'discount_coupon',
					'nameComp'    => true,
					'conditions'  => array(
						'fields' => array(

							array(
								'name'     => 'discount_type',
								'operator' => '===',
								'value'    => 'coupon',
							),
						),
					),
				),

				'wcf-ob-original-price'  => array(
					'type'     => 'text',
					'label'    => __( 'Original Price', 'cartflows-pro' ),
					'name'     => 'original_price',
					'value'    => $this->get_bump_original_price( $options, $step_id ),
					'tooltip'  => __( 'This is the unit price of product', 'cartflows-pro' ),
					'readonly' => true,

				),

				'wcf-ob-sell-price'      => array(
					'type'     => 'text',
					'label'    => __( 'Sell Price', 'cartflows-pro' ),
					'name'     => 'sell_price',
					'value'    => $this->get_bump_discount_price( $options, $step_id ),
					'tooltip'  => __( 'This is the unit discounted price of product', 'cartflows-pro' ),
					'readonly' => true,

				),

			);

			$settings['settings']['multiple-order-bump-settings']['fields'] = array(

				'wcf-ob-replace-heading'   => array(
					'type'  => 'heading',
					'label' => esc_html__( 'Replace Checkout Product', 'cartflows-pro' ),
				),

				'wcf-ob-replace'           => array(
					'type'  => 'checkbox',
					'label' => __( 'Replace First Product', 'cartflows-pro' ),
					'name'  => 'replace_product',
					'desc'  => __( 'It will replace the first selected product (from checkout products) with the order bump product.', 'cartflows-pro' ),
					/* translators: %1$1s, %2$2s Link to meta */
					'desc'  => sprintf(
						__( 'It will replace the first selected product (from checkout products) with the order bump product. %1$1sLearn More »%2$2s', 'cartflows-pro' ), //phpcs:ignore
						'<a href="https://cartflows.com/docs/replace-first-product-with-order-bump-checkout-page/" target="_blank">',
						'</a>'
					),

				),

				'wcf-ob-next-step-heading' => array(
					'type'  => 'heading',
					'label' => esc_html__( 'Next Step Redirection', 'cartflows-pro' ),
				),

				'ob-next-step'             => array(
					'type'            => 'select',
					'label'           => __( 'On Order Bump Purchase - Next Step', 'cartflows-pro' ),
					'name'            => 'next_step',
					'options'         => $opt_steps,
					'data-flow-id'    => $flow_id,
					'data-exclude-id' => $step_id,

				),

				'redirection-doc'          => array(
					'type'    => 'doc',
					'label'   => __( 'Doc', 'cartflows-pro' ),
					'content' => sprintf(
						/* translators: %s: content */
						__( 'Note: Select the step if you want to redirect to a different step on the order bump purchase.%1$1sLearn More »%2$2s', 'cartflows-pro' ),
						'<a href="https://cartflows.com/docs/order-bump-purchase-conditional-redirect/" target="_blank">',
						'</a>'
					),

					'ob-doc'  => array(
						'type'    => 'doc',
						'content' => sprintf(
							/* translators: %1$1s: link html start, %2$12: link html end*/
							__( 'For more information about the order bump settings please %1$1sClick here.%2$2s', 'cartflows-pro' ),
							'<a href="https://cartflows.com/docs/how-to-add-order-bumps-to-woocommerce-sales-funnel/" target="_blank">',
							'</a>'
						),
					),
				),
			);

			$settings['settings']['multiple-order-bump-design']['fields'] = array(

				'heading-ob-bump-layout'            => array(
					'type'  => 'heading',
					'label' => esc_html__( 'Order Bump Layout', 'cartflows-pro' ),
				),

				'wcf-ob-skin'                       => array(
					'type'    => 'select',
					'label'   => __( 'Order Bump Skin', 'cartflows-pro' ),
					'name'    => 'style',
					'options' => array(
						array(
							'value' => 'style-1',
							'label' => esc_html__( 'Style 1', 'cartflows-pro' ),
						),
						array(
							'value' => 'style-2',
							'label' => esc_html__( 'Style 2', 'cartflows-pro' ),
						),
						array(
							'value' => 'style-3',
							'label' => esc_html__( 'Style 3', 'cartflows-pro' ),
						),
						array(
							'value' => 'style-4',
							'label' => esc_html__( 'Style 4', 'cartflows-pro' ),
						),
						array(
							'value' => 'style-5',
							'label' => esc_html__( 'Style 5', 'cartflows-pro' ),
						),
					),
				),
				'wcf-ob-width'                      => array(
					'type'    => 'select',
					'label'   => __( 'Width', 'cartflows-pro' ),
					'name'    => 'width',
					'options' => array(
						array(
							'value' => '50',
							'label' => esc_html__( '50%', 'cartflows-pro' ),
						),
						array(
							'value' => '100',
							'label' => esc_html__( '100%', 'cartflows-pro' ),
						),
					),
				),

				'heading-ob-bump-title-heading'     => array(
					'type'       => 'heading',
					'label'      => esc_html__( 'Title', 'cartflows-pro' ),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-3', 'style-4', 'style-5' ),
							),
						),
					),
				),

				'wcf-ob-title-color'                => array(
					'type'       => 'color-picker',
					'label'      => __( 'Text Color', 'cartflows-pro' ),
					'name'       => 'title_text_color',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-3', 'style-4', 'style-5' ),
							),
						),
					),
				),

				'heading-ob-bump-desc'              => array(
					'type'  => 'heading',
					'label' => esc_html__( 'Description', 'cartflows-pro' ),
				),

				'wcf-ob-desc-color'                 => array(
					'type'  => 'color-picker',
					'label' => __( 'Text Color', 'cartflows-pro' ),
					'name'  => 'desc_text_color',
				),

				'heading-ob-bump-label'             => array(
					'type'       => 'heading',
					'label'      => esc_html__( 'Checkbox Label', 'cartflows-pro' ),
					'conditions' => array(
						'relation' => 'or',
						'fields'   => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-1', 'style-2' ),
							),
							array(
								'relation' => 'and',
								'fields'   => array(
									array(
										'name'     => 'action_element',
										'operator' => '==',
										'value'    => 'checkbox',
									),
									array(
										'name'     => 'style',
										'operator' => '==',
										'value'    => 'style-5',
									),
								),
							),
						),
					),
				),

				'wcf-ob-label-color'                => array(
					'type'       => 'color-picker',
					'label'      => __( 'Text Color', 'cartflows-pro' ),
					'name'       => 'label_color',
					'conditions' => array(
						'relation' => 'or',
						'fields'   => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-1', 'style-2' ),
							),
							array(
								'relation' => 'and',
								'fields'   => array(
									array(
										'name'     => 'action_element',
										'operator' => '==',
										'value'    => 'checkbox',
									),
									array(
										'name'     => 'style',
										'operator' => '==',
										'value'    => 'style-5',
									),
								),
							),
						),
					),
				),

				'wcf-ob-label-bg-color'             => array(
					'type'       => 'color-picker',
					'label'      => __( 'Background Color', 'cartflows-pro' ),
					'name'       => 'label_bg_color',
					'conditions' => array(
						'relation' => 'or',
						'fields'   => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-1', 'style-2' ),
							),
							array(
								'relation' => 'and',
								'fields'   => array(
									array(
										'name'     => 'action_element',
										'operator' => '==',
										'value'    => 'checkbox',
									),
									array(
										'name'     => 'style',
										'operator' => '==',
										'value'    => 'style-5',
									),
								),
							),
						),
					),
				),

				'wcf-ob-label-border-style'         => array(
					'type'       => 'select',
					'label'      => __( 'Border Style', 'cartflows-pro' ),
					'name'       => 'label_border_style',
					'options'    => array(
						array(
							'value' => 'inherit',
							'label' => esc_html__( 'Default', 'cartflows-pro' ),
						),
						array(
							'value' => 'dashed',
							'label' => esc_html__( 'Dashed', 'cartflows-pro' ),
						),
						array(
							'value' => 'dotted',
							'label' => esc_html__( 'Dotted', 'cartflows-pro' ),
						),
						array(
							'value' => 'solid',
							'label' => esc_html__( 'Solid', 'cartflows-pro' ),
						),
						array(
							'value' => 'none',
							'label' => esc_html__( 'None', 'cartflows-pro' ),
						),

					),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-5' ),
							),
							array(
								'name'     => 'action_element',
								'operator' => '===',
								'value'    => 'checkbox',
							),
						),
					),
				),

				'wcf-ob-label-border-color'         => array(
					'type'       => 'color-picker',
					'label'      => __( 'Border Color', 'cartflows-pro' ),
					'name'       => 'label_border_color',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-5' ),
							),
							array(
								'name'     => 'action_element',
								'operator' => '===',
								'value'    => 'checkbox',
							),
						),
					),
				),

				'heading-ob-bump-hg-tx'             => array(
					'type'       => 'heading',
					'label'      => esc_html__( 'Highlight Text', 'cartflows-pro' ),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-1', 'style-2' ),
							),
						),
					),
				),

				'wcf-ob-highlight-text-color'       => array(
					'type'       => 'color-picker',
					'label'      => __( 'Text Color', 'cartflows-pro' ),
					'name'       => 'hl_text_color',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => '!in',
								'value'    => array( 'style-3', 'style-4', 'style-5' ),
							),
						),
					),
				),

				'heading-ob-bump-button'            => array(
					'type'       => 'heading',
					'label'      => esc_html__( 'Button', 'cartflows-pro' ),
					'conditions' => array(
						'relation' => 'or',
						'fields'   => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-4' ),
							),
							array(
								'relation' => 'and',
								'fields'   => array(
									array(
										'name'     => 'action_element',
										'operator' => '==',
										'value'    => 'button',
									),
									array(
										'name'     => 'style',
										'operator' => '==',
										'value'    => 'style-5',
									),
								),
							),
						),
					),
				),

				'wcf-ob-button-text-color'          => array(
					'type'       => 'color-picker',
					'label'      => __( 'Text Color', 'cartflows-pro' ),
					'name'       => 'button_text_color',
					'conditions' => array(
						'relation' => 'or',
						'fields'   => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-4' ),
							),
							array(
								'relation' => 'and',
								'fields'   => array(
									array(
										'name'     => 'action_element',
										'operator' => '==',
										'value'    => 'button',
									),
									array(
										'name'     => 'style',
										'operator' => '==',
										'value'    => 'style-5',
									),
								),
							),
						),
					),
				),

				'wcf-ob-button-text-hover-bg-color' => array(
					'type'       => 'color-picker',
					'label'      => __( 'Text Hover Color', 'cartflows-pro' ),
					'name'       => 'button_text_hover_color',
					'conditions' => array(
						'relation' => 'or',
						'fields'   => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-4' ),
							),
							array(
								'relation' => 'and',
								'fields'   => array(
									array(
										'name'     => 'action_element',
										'operator' => '==',
										'value'    => 'button',
									),
									array(
										'name'     => 'style',
										'operator' => '==',
										'value'    => 'style-5',
									),
								),
							),
						),
					),
				),

				'wcf-ob-button-text-bg-color'       => array(
					'type'       => 'color-picker',
					'label'      => __( 'Background Color', 'cartflows-pro' ),
					'name'       => 'button_color',
					'conditions' => array(
						'relation' => 'or',
						'fields'   => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-4' ),
							),
							array(
								'relation' => 'and',
								'fields'   => array(
									array(
										'name'     => 'action_element',
										'operator' => '==',
										'value'    => 'button',
									),
									array(
										'name'     => 'style',
										'operator' => '==',
										'value'    => 'style-5',
									),
								),
							),
						),
					),
				),

				'wcf-ob-button-hover-bg-color'      => array(
					'type'       => 'color-picker',
					'label'      => __( 'Background Hover Color', 'cartflows-pro' ),
					'name'       => 'button_hover_color',
					'conditions' => array(
						'relation' => 'or',
						'fields'   => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-4' ),
							),
							array(
								'relation' => 'and',
								'fields'   => array(
									array(
										'name'     => 'action_element',
										'operator' => '==',
										'value'    => 'button',
									),
									array(
										'name'     => 'style',
										'operator' => '==',
										'value'    => 'style-5',
									),
								),
							),
						),
					),
				),

				'heading-ob-bump-bg'                => array(
					'type'  => 'heading',
					'label' => esc_html__( 'Box Background', 'cartflows-pro' ),
				),

				'wcf-ob-bg-color'                   => array(
					'type'  => 'color-picker',
					'label' => __( 'Background Color', 'cartflows-pro' ),
					'name'  => 'bg_color',
				),

				'heading-ob-bump-border'            => array(
					'type'  => 'heading',
					'label' => esc_html__( 'Box Border', 'cartflows-pro' ),
				),

				'wcf-ob-border-style'               => array(
					'type'    => 'select',
					'label'   => __( 'Border Style', 'cartflows-pro' ),
					'name'    => 'border_style',
					'options' => array(
						array(
							'value' => 'inherit',
							'label' => esc_html__( 'Default', 'cartflows-pro' ),
						),
						array(
							'value' => 'dashed',
							'label' => esc_html__( 'Dashed', 'cartflows-pro' ),
						),
						array(
							'value' => 'dotted',
							'label' => esc_html__( 'Dotted', 'cartflows-pro' ),
						),
						array(
							'value' => 'solid',
							'label' => esc_html__( 'Solid', 'cartflows-pro' ),
						),
						array(
							'value' => 'none',
							'label' => esc_html__( 'None', 'cartflows-pro' ),
						),

					),
				),

				'wcf-ob-border-color'               => array(
					'type'  => 'color-picker',
					'label' => __( 'Border Color', 'cartflows-pro' ),
					'name'  => 'border_color',
				),

				'heading-ob-arrow'                  => array(
					'type'       => 'heading',
					'label'      => esc_html__( 'Order Bump Pointing Arrow', 'cartflows-pro' ),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => '!in',
								'value'    => array( 'style-4' ),
							),
						),
					),
				),

				'wcf-ob-arrow'                      => array(
					'type'       => 'checkbox',
					'label'      => __( 'Enable Arrow ', 'cartflows-pro' ),
					'name'       => 'show_arrow',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => '!in',
								'value'    => array( 'style-4' ),
							),
						),
					),
				),
				'wcf-ob-arrow-animation'            => array(
					'type'       => 'checkbox',
					'label'      => __( 'Enable Animation ', 'cartflows-pro' ),
					'name'       => 'show_animation',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'show_arrow',
								'operator' => '===',
								'value'    => 'yes',
							),
							array(
								'name'     => 'style',
								'operator' => '!in',
								'value'    => array( 'style-4' ),
							),
						),
					),
				),

			);
			$settings['settings']['multiple-order-bump-content']['fields'] = array(

				'heading-ob-bump-position'   => array(
					'type'  => 'heading',
					'label' => esc_html__( 'Position', 'cartflows-pro' ),
				),

				'wcf-ob-position'            => array(
					'type'    => 'select',
					'label'   => __( 'Position', 'cartflows-pro' ),
					'name'    => 'position',
					'value'   => $options['wcf-order-bump-position'],
					'options' => array(
						array(
							'value' => 'before-checkout',
							'label' => esc_html__( 'Before Checkout', 'cartflows-pro' ),
						),
						array(
							'value' => 'after-customer',
							'label' => esc_html__( 'After Customer Details', 'cartflows-pro' ),
						),
						array(
							'value' => 'after-order',
							'label' => esc_html__( 'After Order', 'cartflows-pro' ),
						),
						array(
							'value' => 'after-payment',
							'label' => esc_html__( 'After Payment', 'cartflows-pro' ),
						),
					),

				),

				'heading-ob-bump-action'     => array(
					'type'       => 'heading',
					'label'      => esc_html__( 'Action Element', 'cartflows-pro' ),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-5' ),
							),
						),
					),
				),

				'wcf-ob-action-element'      => array(
					'type'       => 'select',
					'label'      => __( 'Element', 'cartflows-pro' ),
					'name'       => 'action_element',
					'value'      => $options['wcf-order-bump-action-element'],
					'options'    => array(
						array(
							'value' => 'checkbox',
							'label' => esc_html__( 'Checkbox', 'cartflows-pro' ),
						),
						array(
							'value' => 'button',
							'label' => esc_html__( 'Button', 'cartflows-pro' ),
						),
					),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-5' ),
							),
						),
					),
				),

				'heading-ob-bump-content'    => array(
					'type'  => 'heading',
					'label' => esc_html__( 'Contents', 'cartflows-pro' ),
				),

				'wcf-ob-label'               => array(
					'type'       => 'text',
					'label'      => __( 'Checkbox Label', 'cartflows-pro' ),
					'name'       => 'checkbox_label',
					'value'      => $options['wcf-order-bump-label'],
					'conditions' => array(
						'relation' => 'or',
						'fields'   => array(
							array(
								'name'     => 'style',
								'operator' => 'in',
								'value'    => array( 'style-1', 'style-2' ),
							),
							array(
								'relation' => 'and',
								'fields'   => array(
									array(
										'name'     => 'action_element',
										'operator' => '==',
										'value'    => 'checkbox',
									),
									array(
										'name'     => 'style',
										'operator' => '==',
										'value'    => 'style-5',
									),
								),
							),
						),
					),
				),

				'wcf-ob-title'               => array(
					'type'       => 'text',
					'label'      => __( 'Title', 'cartflows-pro' ),
					'name'       => 'title_text',
					'value'      => $options['wcf-order-bump-title'],
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => '!in',
								'value'    => array( 'style-1', 'style-2' ),
							),
						),
					),
				),

				'wcf-ob-highlight-text'      => array(
					'type'       => 'text',
					'label'      => __( 'Highlight Text', 'cartflows-pro' ),
					'name'       => 'hl_text',
					'value'      => $options['wcf-order-bump-hl-text'],
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'style',
								'operator' => '!in',
								'value'    => array( 'style-3', 'style-4', 'style-5' ),
							),
						),
					),
				),
				'wcf-ob-product-desc'        => array(
					'type'  => 'textarea',
					'label' => __( 'Description', 'cartflows-pro' ),
					'name'  => 'desc_text',
					'value' => $options['wcf-order-bump-desc'],
					'rows'  => '7',
					'cols'  => '39',
				),

				'wcf-ob-dynamic-var'         => array(
					'type'    => 'doc',
					'content' => __( 'Use {{product_name}}, {{product_price}} & {{quantity}} to fetch respective product details.', 'cartflows-pro' ),
				),

				'heading-ob-bump-media'      => array(
					'type'  => 'heading',
					'label' => esc_html__( 'Media', 'cartflows-pro' ),
				),

				'wcf-ob-enable-image-option' => array(
					'type'  => 'checkbox',
					'label' => __( 'Enable Image Options', 'cartflows-pro' ),
					'name'  => 'enable_show_image',
				),

				'product-image'              => array(
					'type'        => 'image-selector',
					'label'       => __( 'Product Image', 'cartflows-pro' ),
					'name'        => 'product_image',
					'isNameArray' => true,
					'objName'     => 'product_img_obj',
					'conditions'  => array(
						'fields' => array(
							array(
								'name'     => 'enable_show_image',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

				'wcf-ob-image-position'      => array(
					'type'       => 'select',
					'label'      => __( 'Image Position', 'cartflows-pro' ),
					'name'       => 'ob_image_position',
					'options'    => array(
						array(
							'value' => 'left',
							'label' => esc_html__( 'Left', 'cartflows-pro' ),
						),
						array(
							'value' => 'top',
							'label' => esc_html__( 'Top', 'cartflows-pro' ),
						),
						array(
							'value' => 'right',
							'label' => esc_html__( 'Right', 'cartflows-pro' ),
						),
					),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'enable_show_image',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

				'wcf-ob-image-width'         => array(
					'type'       => 'number',
					'label'      => __( 'Image Width', 'cartflows-pro' ),
					'name'       => 'ob_image_width',
					'min'        => 1,
					'afterfield' => 'px',
					'width'      => '80px',
					'tooltip'    => __( 'Keep value empty for 100% width', 'cartflows-pro' ),
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'enable_show_image',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

				'wcf-ob-image-option'        => array(
					'type'       => 'checkbox',
					'label'      => __( 'Show Image on Tab and Mobile', 'cartflows-pro' ),
					'name'       => 'show_image_mobile',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'enable_show_image',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),
			);
			// Multiple order bump options arrays end.
		}

		$settings['settings']['checkout-offer']['fields'] = array(
			'wcf-enable-co'                   => array(
				'type'  => 'checkbox',
				'label' => __( 'Enable Checkout Offer', 'cartflows-pro' ),
				'name'  => 'wcf-pre-checkout-offer',
			),

			'co-product-heading'              => array(
				'type'       => 'heading',
				'label'      => esc_html__( 'Product', 'cartflows-pro' ),
				'conditions' => array(
					'fields' => array(
						array(
							'name'     => 'wcf-pre-checkout-offer',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),

			'wcf-co-product'                  => array(
				'type'                   => 'product',
				'label'                  => __( 'Select Product', 'cartflows-pro' ),
				'name'                   => 'wcf-pre-checkout-offer-product',
				'excluded_product_types' => array( 'grouped' ),
				'include_product_types'  => array( 'braintree-subscription', 'braintree-variable-subscription' ),
				'placeholder'            => __( 'Type to search for a product', 'cartflows-pro' ),
				'conditions'             => array(
					'fields' => array(
						array(
							'name'     => 'wcf-pre-checkout-offer',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),

			'wcf-co-discount'                 => array(
				'type'       => 'select',
				'label'      => __( 'Discount Type', 'cartflows-pro' ),
				'name'       => 'wcf-pre-checkout-offer-discount',
				'options'    => array(
					array(
						'value' => '',
						'label' => esc_html__( 'Original', 'cartflows-pro' ),
					),
					array(
						'value' => 'discount_percent',
						'label' => esc_html__( 'Discount Percentage', 'cartflows-pro' ),
					),
					array(
						'value' => 'discount_price',
						'label' => esc_html__( 'Discount Price', 'cartflows-pro' ),
					),
				),

				'conditions' => array(
					'fields' => array(
						array(
							'name'     => 'wcf-pre-checkout-offer',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),
			'wcf-co-dicount-value'            => array(
				'type'       => 'number',
				'label'      => __( 'Discount Value', 'cartflows-pro' ),
				'name'       => 'wcf-pre-checkout-offer-discount-value',
				'conditions' => array(
					'fields' => array(
						array(
							'name'     => 'wcf-pre-checkout-offer',
							'operator' => '===',
							'value'    => 'yes',
						),
						array(
							'name'     => 'wcf-pre-checkout-offer-discount',
							'operator' => 'in',
							'value'    => array( 'discount_price', 'discount_percent' ),
						),
					),
				),
			),

			'wcf-co-original-price'           => array(
				'type'       => 'text',
				'label'      => __( 'Original Price', 'cartflows-pro' ),
				'name'       => 'wcf-pre-checkout-offer-product[original_price]',
				'value'      => $this->get_pre_checkout_offer_original_price( $options, $step_id ),
				'tooltip'    => __( 'This is the unit price of product', 'cartflows-pro' ),
				'readonly'   => true,
				'conditions' => array(
					'fields' => array(
						array(
							'name'     => 'wcf-pre-checkout-offer',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),
			'wcf-co-sell-price'               => array(
				'type'       => 'text',
				'label'      => __( 'Sell Price', 'cartflows-pro' ),
				'name'       => 'wcf-pre-checkout-offer-product[sell_price]',
				'value'      => $this->get_pre_checkout_offer_discount_price( $options, $step_id ),
				'tooltip'    => __( 'This is the unit discounted price of product', 'cartflows-pro' ),
				'readonly'   => true,
				'conditions' => array(
					'fields' => array(
						array(
							'name'     => 'wcf-pre-checkout-offer',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),

			'co-settings-heading'             => array(
				'type'       => 'heading',
				'label'      => esc_html__( 'Settings', 'cartflows-pro' ),
				'conditions' => array(
					'fields' => array(
						array(
							'name'     => 'wcf-pre-checkout-offer',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),

			'checkout-offer-title'            => array(
				'type'        => 'text',
				'label'       => __( 'Title Text', 'cartflows-pro' ),
				'name'        => 'wcf-pre-checkout-offer-popup-title',
				'value'       => $options['wcf-pre-checkout-offer-popup-title'],
				'placeholder' => esc_html__( '{first_name}, Wait! Your Order Is Almost Complete...', 'cartflows-pro' ),
				'conditions'  => array(
					'fields' => array(
						array(
							'name'     => 'wcf-pre-checkout-offer',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),

			),
			'checkout-offer-subtitle'         => array(
				'type'       => 'text',
				'label'      => __( 'Sub-title Text', 'cartflows-pro' ),
				'name'       => 'wcf-pre-checkout-offer-popup-sub-title',
				'value'      => $options['wcf-pre-checkout-offer-popup-sub-title'],
				'conditions' => array(
					'fields' => array(
						array(
							'name'     => 'wcf-pre-checkout-offer',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),
			'checkout-offer-product-title'    => array(
				'type'       => 'text',
				'label'      => __( 'Product Title', 'cartflows-pro' ),
				'name'       => 'wcf-pre-checkout-offer-product-title',
				'help'       => esc_html__( 'Enter to override default product title.', 'cartflows-pro' ),
				'value'      => $options['wcf-pre-checkout-offer-product-title'],
				'conditions' => array(
					'fields' => array(
						array(
							'name'     => 'wcf-pre-checkout-offer',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),
			'checkout-offer-product-desc'     => array(
				'type'        => 'textarea',
				'label'       => __( 'Product Description', 'cartflows-pro' ),
				'name'        => 'wcf-pre-checkout-offer-desc',
				'value'       => $options['wcf-pre-checkout-offer-desc'],
				'placeholder' => esc_html__( 'Write a few words about this awesome product and tell shoppers why they must get it. You may highlight this as "one time offer" and make it irresistible.', 'cartflows-pro' ),
				'conditions'  => array(
					'fields' => array(
						array(
							'name'     => 'wcf-pre-checkout-offer',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),

			'checkout-offer-button-text'      => array(
				'type'        => 'text',
				'label'       => __( 'Order Button Text', 'cartflows-pro' ),
				'name'        => 'wcf-pre-checkout-offer-popup-btn-text',
				'value'       => $options['wcf-pre-checkout-offer-popup-btn-text'],
				'placeholder' => esc_html__( 'Yes, Add to My Order!', 'cartflows-pro' ),
				'conditions'  => array(
					'fields' => array(
						array(
							'name'     => 'wcf-pre-checkout-offer',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),
			'checkout-offer-skip-button-text' => array(
				'type'        => 'text',
				'label'       => __( 'Skip Button Text', 'cartflows-pro' ),
				'name'        => 'wcf-pre-checkout-offer-popup-skip-btn-text',
				'value'       => $options['wcf-pre-checkout-offer-popup-skip-btn-text'],
				'placeholder' => esc_html__( 'No, thanks!', 'cartflows-pro' ),
				'conditions'  => array(
					'fields' => array(
						array(
							'name'     => 'wcf-pre-checkout-offer',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),
			'checkout-offer-doc'              => array(
				'type'       => 'doc',
				/* translators: %1$1s: link html start, %2$12: link html end*/
				'content'    => sprintf( __( 'For more information about the pre-checkout offer please %1$1sClick here.%2$2s', 'cartflows-pro' ), '<a href="https://cartflows.com/docs/setup-pre-checkout-upsell/" target="_blank">', '</a>' ),
				'conditions' => array(
					'fields' => array(
						array(
							'name'     => 'wcf-pre-checkout-offer',
							'operator' => '===',
							'value'    => 'yes',
						),
					),
				),
			),

		);

		return $settings;
	}


	/**
	 * Add meta fields
	 *
	 * @param array $settings checkout fields.
	 * @param array $options options.
	 */
	public function design_fields_react( $settings, $options ) {

		// @todo Remove this code after v1.7.4 update.
		// Start.
		if ( in_array( get_option( 'wcf_order_bump_migrated', false ), array( 'no', 'processing' ), true ) ) {
			$settings['settings']['order-bump-design']['fields'] = array(
				'wcf-ob-skin'                 => array(
					'type'    => 'select',
					'label'   => __( 'Order Bump Skin', 'cartflows-pro' ),
					'name'    => 'wcf-order-bump-style',
					'value'   => $options['wcf-order-bump-style'],
					'options' => array(
						array(
							'value' => 'style-1',
							'label' => esc_html__( 'Style 1', 'cartflows-pro' ),
						),
						array(
							'value' => 'style-2',
							'label' => esc_html__( 'Style 2', 'cartflows-pro' ),
						),
					),
				),
				'wcf-ob-border-style'         => array(
					'type'    => 'select',
					'label'   => __( 'Border Style', 'cartflows-pro' ),
					'name'    => 'wcf-bump-border-style',
					'value'   => $options['wcf-bump-border-style'],
					'options' => array(
						array(
							'value' => 'inherit',
							'label' => esc_html__( 'Default', 'cartflows-pro' ),
						),
						array(
							'value' => 'dashed',
							'label' => esc_html__( 'Dashed', 'cartflows-pro' ),
						),
						array(
							'value' => 'dotted',
							'label' => esc_html__( 'Dotted', 'cartflows-pro' ),
						),
						array(
							'value' => 'solid',
							'label' => esc_html__( 'Solid', 'cartflows-pro' ),
						),
						array(
							'value' => 'none',
							'label' => esc_html__( 'None', 'cartflows-pro' ),
						),

					),
				),
				'wcf-ob-border-color'         => array(
					'type'  => 'color-picker',
					'label' => __( 'Border Color', 'cartflows-pro' ),
					'name'  => 'wcf-bump-border-color',
					'value' => $options['wcf-bump-border-color'],
				),
				'wcf-ob-bg-color'             => array(
					'type'  => 'color-picker',
					'label' => __( 'Background Color', 'cartflows-pro' ),
					'name'  => 'wcf-bump-bg-color',
					'value' => $options['wcf-bump-bg-color'],
				),
				'wcf-ob-label-color'          => array(
					'type'  => 'color-picker',
					'label' => __( 'Label Color', 'cartflows-pro' ),
					'name'  => 'wcf-bump-label-color',
					'value' => $options['wcf-bump-label-color'],
				),
				'wcf-ob-label-bg-color'       => array(
					'type'  => 'color-picker',
					'label' => __( 'Label Background Color', 'cartflows-pro' ),
					'name'  => 'wcf-bump-label-bg-color',
					'value' => $options['wcf-bump-label-bg-color'],
				),

				'wcf-ob-desc-color'           => array(
					'type'  => 'color-picker',
					'label' => __( 'Description Text Color', 'cartflows-pro' ),
					'name'  => 'wcf-bump-desc-text-color',
					'value' => $options['wcf-bump-desc-text-color'],
				),
				'wcf-ob-highlight-text-color' => array(
					'type'  => 'color-picker',
					'label' => __( 'Highlight Text Color', 'cartflows-pro' ),
					'name'  => 'wcf-bump-hl-text-color',
					'value' => $options['wcf-bump-hl-text-color'],
				),

				'heading-ob-arrow'            => array(
					'type'  => 'heading',
					'label' => esc_html__( 'Order Bump Pointing Arrow', 'cartflows-pro' ),
				),

				'wcf-ob-arrow'                => array(
					'type'  => 'checkbox',
					'label' => __( 'Enable Arrow ', 'cartflows-pro' ),
					'name'  => 'wcf-show-bump-arrow',
					'value' => $options['wcf-show-bump-arrow'],
				),
				'wcf-ob-arrow-animation'      => array(
					'type'       => 'checkbox',
					'label'      => __( 'Enable Animation ', 'cartflows-pro' ),
					'name'       => 'wcf-show-bump-animate-arrow',
					'value'      => $options['wcf-show-bump-animate-arrow'],
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-show-bump-arrow',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				),

			);
		}
		// End.

		$settings['settings']['checkout-two-step-design']['fields'] = array(

			'checkout-note'            => array(
				'type'  => 'checkbox',
				'label' => __( 'Enable Note Text', 'cartflows-pro' ),
				'name'  => 'wcf-checkout-box-note',
				'value' => $options['wcf-checkout-box-note'],
				'after' => esc_html__( 'Enable Checkout Note', 'cartflows-pro' ),
			),
			'checkout-note-text'       => array(
				'type'  => 'text',
				'label' => __( 'Note Text', 'cartflows-pro' ),
				'name'  => 'wcf-checkout-box-note-text',
				'value' => $options['wcf-checkout-box-note-text'],
			),
			'checkout-note-text-color' => array(
				'type'  => 'color-picker',
				'label' => __( 'Text Color', 'cartflows-pro' ),
				'name'  => 'wcf-checkout-box-note-text-color',
				'value' => $options['wcf-checkout-box-note-text-color'],
			),
			'checkout-note-bg-color'   => array(
				'type'  => 'color-picker',
				'label' => __( 'Note Box Background Color', 'cartflows-pro' ),
				'name'  => 'wcf-checkout-box-note-bg-color',
				'value' => $options['wcf-checkout-box-note-bg-color'],
			),

			'heading-steps'            => array(
				'type'  => 'heading',
				'label' => esc_html__( 'Steps', 'cartflows-pro' ),
			),

			'step-one-title'           => array(
				'type'  => 'text',
				'label' => __( 'Step One Title', 'cartflows-pro' ),
				'name'  => 'wcf-checkout-step-one-title',
				'value' => $options['wcf-checkout-step-one-title'],
			),
			'step-one-subtitle'        => array(
				'type'  => 'text',
				'label' => __( 'Step One Sub Title', 'cartflows-pro' ),
				'name'  => 'wcf-checkout-step-one-sub-title',
				'value' => $options['wcf-checkout-step-one-sub-title'],
			),
			'step-two-title'           => array(
				'type'  => 'text',
				'label' => __( 'Step Two Title', 'cartflows-pro' ),
				'name'  => 'wcf-checkout-step-two-title',
				'value' => $options['wcf-checkout-step-two-title'],
			),
			'step-two-subtitle'        => array(
				'type'  => 'text',
				'label' => __( 'Step Two Sub Title', 'cartflows-pro' ),
				'name'  => 'wcf-checkout-step-two-sub-title',
				'value' => $options['wcf-checkout-step-two-sub-title'],
			),
			'step-section-width'       => array(
				'type'  => 'number',
				'label' => __( 'Section Width', 'cartflows-pro' ),
				'name'  => 'wcf-checkout-two-step-section-width',
				'value' => $options['wcf-checkout-two-step-section-width'],
			),
			'step-border'              => array(
				'type'    => 'select',
				'label'   => __( 'Border', 'cartflows-pro' ),
				'name'    => 'wcf-checkout-two-step-section-border',
				'value'   => $options['wcf-checkout-two-step-section-border'],

				'options' => array(
					array(
						'value' => 'none',
						'label' => esc_html__( 'None', 'cartflows-pro' ),
					),
					array(
						'value' => 'solid',
						'label' => esc_html__( 'Solid', 'cartflows-pro' ),
					),
				),
			),

			'heading-offer-button'     => array(
				'type'  => 'heading',
				'label' => esc_html__( 'Offer Button', 'cartflows-pro' ),
			),

			'offer-button-title'       => array(
				'type'  => 'text',
				'label' => __( 'Offer Button Title', 'cartflows-pro' ),
				'name'  => 'wcf-checkout-offer-button-title',
				'value' => $options['wcf-checkout-offer-button-title'],
			),
			'offer-button-subtitle'    => array(
				'type'  => 'text',
				'label' => __( 'Offer Button Sub Title', 'cartflows-pro' ),
				'name'  => 'wcf-checkout-offer-button-sub-title',
				'value' => $options['wcf-checkout-offer-button-sub-title'],
			),

		);

		$settings['settings']['product-option-design']['fields'] = array(
			'product-options-title'              => array(
				'type'        => 'text',
				'label'       => __( 'Section Title', 'cartflows-pro' ),
				'name'        => 'wcf-product-opt-title',
				'value'       => $options['wcf-product-opt-title'],
				'placeholder' => esc_html__( 'Your Products', 'cartflows-pro' ),

			),
			'product-options-position'           => array(
				'type'    => 'select',
				'label'   => __( 'Section Position', 'cartflows-pro' ),
				'name'    => 'wcf-your-products-position',
				'value'   => $options['wcf-your-products-position'],
				'options' => array(
					array(
						'value' => 'before-customer',
						'label' => __( 'Before Checkout Section', 'cartflows-pro' ),
					),
					array(
						'value' => 'after-customer',
						'label' => __( 'After Customer Details', 'cartflows-pro' ),
					),
					array(
						'value' => 'before-order',
						'label' => __( 'Before Order Review', 'cartflows-pro' ),
					),
				),
			),
			'product-options-skin'               => array(
				'type'    => 'select',
				'label'   => __( 'Skins', 'cartflows-pro' ),
				'name'    => 'wcf-product-options-skin',
				'value'   => $options['wcf-product-options-skin'],
				'options' => array(
					array(
						'value' => 'classic',
						'label' => __( 'Classic', 'cartflows-pro' ),
					),
					array(
						'value' => 'cards',
						'label' => __( 'Cards', 'cartflows-pro' ),
					),
				),
			),
			'product-options-image'              => array(
				'type'    => 'checkbox',
				'label'   => __( 'Show Product Images', 'cartflows-pro' ),
				'name'    => 'wcf-show-product-images',
				'value'   => $options['wcf-show-product-images'],
				'tooltip' => __( 'It will add images on checkout page.', 'cartflows-pro' ),
			),

			'product-options-product-text-color' => array(
				'type'  => 'color-picker',
				'label' => __( 'Product Text Color', 'cartflows-pro' ),
				'name'  => 'wcf-yp-text-color',
				'value' => $options['wcf-yp-text-color'],
			),
			'product-options-product-bg-color'   => array(
				'type'  => 'color-picker',
				'label' => __( 'Product Background Color', 'cartflows-pro' ),
				'name'  => 'wcf-yp-bg-color',
				'value' => $options['wcf-yp-bg-color'],
			),

			'heading-hl-design'                  => array(
				'type'  => 'heading',
				'label' => esc_html__( 'Highlight Product Design', 'cartflows-pro' ),
			),

			'product-options-hl-text-color'      => array(
				'type'  => 'color-picker',
				'label' => __( 'Highlight Product Text Color', 'cartflows-pro' ),
				'name'  => 'wcf-yp-hl-text-color',
				'value' => $options['wcf-yp-hl-text-color'],
			),
			'product-options-hl-bg-color'        => array(
				'type'  => 'color-picker',
				'label' => __( 'Highlight Product Background Color', 'cartflows-pro' ),
				'name'  => 'wcf-yp-hl-bg-color',
				'value' => $options['wcf-yp-hl-bg-color'],
			),
			'product-options-hl-box-bg-color'    => array(
				'type'  => 'color-picker',
				'label' => __( 'Highlight Box Border Color', 'cartflows-pro' ),
				'name'  => 'wcf-yp-hl-border-color',
				'value' => $options['wcf-yp-hl-border-color'],
			),
			'product-options-hl-flag-text-color' => array(
				'type'  => 'color-picker',
				'label' => __( 'Highlight Flag Text Color', 'cartflows-pro' ),
				'name'  => 'wcf-yp-hl-flag-text-color',
				'value' => $options['wcf-yp-hl-flag-text-color'],
			),
			'product-options-hl-flag-bg-color'   => array(
				'type'  => 'color-picker',
				'label' => __( 'Highlight Flag Background Color', 'cartflows-pro' ),
				'name'  => 'wcf-yp-hl-flag-bg-color',
				'value' => $options['wcf-yp-hl-flag-bg-color'],
			),

		);

		$settings['settings']['checkout-offer-design']['fields'] = array(

			'checkout-offer-bg-color' => array(
				'type'  => 'color-picker',
				'label' => __( 'Background Color', 'cartflows-pro' ),
				'name'  => 'wcf-pre-checkout-offer-bg-color',
				'value' => $options['wcf-pre-checkout-offer-bg-color'],
			),
		);

		return $settings;
	}

	/**
	 * Add custom meta fields
	 *
	 * @param string $post_id post id.
	 * @param array  $options options.
	 */
	public function custom_fields_data( $post_id, $options ) {

		$billing_fields  = $this->get_field_settings( $post_id, 'billing', '' );
		$shipping_fields = $this->get_field_settings( $post_id, 'shipping', '' );

		$new_options = $this->get_new_field_options();

		$custom_fields = array(
			'extra_fields'    => array(
				'fields' => array(
					'enable-coupon-field'       => array(
						'type'  => 'checkbox',
						'label' => __( 'Enable Coupon Field', 'cartflows-pro' ),
						'name'  => 'wcf-show-coupon-field',
						'value' => $options['wcf-show-coupon-field'],
					),
					'collapse-coupon-field'     => array(
						'type'        => 'checkbox',
						'label'       => __( 'Collapsible Coupon Field', 'cartflows-pro' ),
						'name'        => 'wcf-optimize-coupon-field',
						'value'       => $options['wcf-optimize-coupon-field'],
						'child_class' => 'wcf-cfe-child',
						'conditions'  => array(
							'fields' => array(
								array(
									'name'     => 'wcf-show-coupon-field',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),

					'additional-field'          => array(
						'type'  => 'checkbox',
						'label' => __( 'Enable Additional Field', 'cartflows-pro' ),
						'name'  => 'wcf-checkout-additional-fields',
						'value' => $options['wcf-checkout-additional-fields'],
					),
					'collapse-additional-field' => array(
						'type'        => 'checkbox',
						'label'       => __( 'Collapsible Additional Field', 'cartflows-pro' ),
						'name'        => 'wcf-optimize-order-note-field',
						'value'       => $options['wcf-optimize-order-note-field'],
						'child_class' => 'wcf-cfe-child',
						'conditions'  => array(
							'fields' => array(
								array(
									'name'     => 'wcf-checkout-additional-fields',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),

					'shipping-field'            => array(
						'type'  => 'checkbox',
						'label' => __( 'Enable Ship To Different Address', 'cartflows-pro' ),
						'name'  => 'wcf-shipto-diff-addr-fields',
						'value' => $options['wcf-shipto-diff-addr-fields'],
					),

					'custom-field-editor'       => array(
						'type'  => 'checkbox',
						'label' => __( 'Enable Custom Field Editor', 'cartflows-pro' ),
						'name'  => 'wcf-custom-checkout-fields',
						'value' => $options['wcf-custom-checkout-fields'],
					),
				),
			),
			'billing_fields'  => array(
				'fields' => $billing_fields,
			),
			'shipping_fields' => array(
				'fields' => $shipping_fields,
			),

			'custom_editor'   => array(
				'fields' => $new_options,
			),

		);

		return $custom_fields;
	}

	/**
	 * Add custom meta fields
	 */
	public function get_new_field_options() {

		$value    = array();
		$value[0] = array(
			'add_to' => '',
			'type'   => '',
			'label'  => '',
			'name'   => '',
		);

		foreach ( $value as $p_key => $p_data ) {

			$options = array(
				'add-to-field'         => array(
					'type'    => 'select',
					'label'   => __( 'Add to', 'cartflows-pro' ),
					'name'    => 'wcf-checkout-custom-fields[' . $p_key . '][add_to]',
					'class'   => 'wcf-cpf-add_to',
					'options' => array(
						array(
							'value' => 'billing',
							'label' => 'Billing',
						),
						array(
							'value' => 'shipping',
							'label' => 'Shipping',
						),
					),
				),
				'type-of-field'        => array(
					'type'    => 'select',
					'type'    => 'select',
					'label'   => __( 'Type', 'cartflows-pro' ),
					'name'    => 'wcf-checkout-custom-fields[' . $p_key . '][type]',
					'class'   => 'wcf-cpf-type',
					'options' => array(
						array(
							'value' => 'text',
							'label' => esc_html__( 'Text', 'cartflows-pro' ),
						),
						array(
							'value' => 'textarea',
							'label' => esc_html__( 'TextArea', 'cartflows-pro' ),
						),
						array(
							'value' => 'checkbox',
							'label' => esc_html__( 'Checkbox', 'cartflows-pro' ),
						),
						array(
							'value' => 'select',
							'label' => esc_html__( 'Select', 'cartflows-pro' ),
						),
						array(
							'value' => 'hidden',
							'label' => esc_html__( 'Hidden', 'cartflows-pro' ),
						),
					),
				),
				'label-of-field'       => array(
					'type'  => 'text',
					'class' => 'wcf-cpf-label',
					'label' => __( 'Label', 'cartflows-pro' ),
					'name'  => 'wcf-checkout-custom-fields[' . $p_key . '][label]',
					'value' => '',
				),
				'default-field'        => array(
					'type'       => 'text',
					'class'      => 'wcf-cpf-default',
					'label'      => __( 'Default', 'cartflows-pro' ),
					'name'       => 'wcf-checkout-custom-fields[' . $p_key . '][default]',
					'value'      => '',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-checkout-custom-fields[' . $p_key . '][type]',
								'operator' => '===',
								'value'    => 'select',
							),
						),
					),
				),
				'placeholder-to-field' => array(
					'type'       => 'text',
					'class'      => 'wcf-cpf-placeholder',
					'label'      => __( 'Placeholder', 'cartflows-pro' ),
					'name'       => 'wcf-checkout-custom-fields[' . $p_key . '][placeholder]',
					'value'      => '',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-checkout-custom-fields[' . $p_key . '][type]',
								'operator' => '===',
								'value'    => 'text',
							),
						),
					),
				),
				'options-field'        => array(
					'type'       => 'text',
					'class'      => 'wcf-cpf-options',
					'label'      => __( 'Options', 'cartflows-pro' ),
					'name'       => 'wcf-checkout-custom-fields[' . $p_key . '][label]',
					'value'      => '',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-checkout-custom-fields[' . $p_key . '][type]',
								'operator' => '===',
								'value'    => 'select',
							),
						),
					),
				),
				'width-of-field'       => array(
					'type'    => 'select',
					'class'   => 'wcf-cpf-width',
					'label'   => __( 'Width', 'cartflows-pro' ),
					'name'    => 'wcf-checkout-custom-fields[' . $p_key . '][width]',
					'value'   => '',
					'options' => array(
						array(
							'value' => '33',
							'label' => esc_html__( '33%', 'cartflows-pro' ),
						),
						array(
							'value' => '50',
							'label' => esc_html__( '50%', 'cartflows-pro' ),
						),
						array(
							'value' => '100',
							'label' => esc_html__( '100%', 'cartflows-pro' ),
						),
					),
				),
				'required-field'       => array(
					'type'  => 'checkbox',
					'class' => 'wcf-cpf-required',
					'label' => __( 'Required', 'cartflows-pro' ),
					'name'  => 'wcf-checkout-custom-fields[' . $p_key . '][required]',
					'value' => '',
				),
				'optimised-field'      => array(
					'type'       => 'checkbox',
					'class'      => 'wcf-cpf-optimized',
					'label'      => __( 'Collapsible', 'cartflows-pro' ),
					'name'       => 'wcf-checkout-custom-fields[' . $p_key . '][optimized]',
					'value'      => '',
					'conditions' => array(
						'fields' => array(
							array(
								'name'     => 'wcf-checkout-custom-fields[' . $p_key . '][required]',
								'operator' => '===',
								'value'    => 'no',
							),
						),
					),
				),

			);
		}
		return $options;
	}

	/**
	 * Prepare new custom meta fields
	 *
	 * @param array  $new_field new field.
	 * @param string $post_id post id.
	 * @param string $type type.
	 */
	public static function prepare_field_settings( $new_field, $post_id, $type ) {

		$key           = $new_field['key'];
		$checkout_meta = Cartflows_Pro_Checkout_Meta_Helper::get_instance();
		$field_args    = $checkout_meta->prepare_field_arguments( $key, $new_field, $post_id, $type );

		if ( 'billing' === $type ) {
			$name = 'wcf_field_order_billing[' . $key . ']';
		} else {
			$name = 'wcf_field_order_shipping[' . $key . ']';
		}

		$is_checkbox = false;
		$is_require  = false;
		$is_select   = false;
		$display     = 'none';

		$type = $field_args['type'];

		if ( 'checkbox' == $field_args['type'] ) {
			$is_checkbox = true;
		}

		if ( 'yes' == $field_args['required'] ) {
			$is_require = true;
		}

		if ( 'yes' == $field_args['optimized'] ) {
			$is_optimized = true;
		}

		if ( 'select' == $field_args['type'] ) {
			$is_select = true;
			$display   = 'block';
		}

		$new_field['required']  = 'yes' === $new_field['required'] ? 'yes' : 'no';
		$new_field['optimized'] = 'yes' === $new_field['optimized'] ? 'yes' : 'no';

		$new_field['field_options'] = array(
			'enable-field'  => array(
				'type'  => 'checkbox',
				'label' => __( 'Enable Field', 'cartflows-pro' ),
				'name'  => $name . '[enabled]',
				'value' => $field_args['enabled'],
			),
			'select-width'  => array(
				'type'    => 'select',
				'label'   => __( 'Field Width', 'cartflows-pro' ),
				'name'    => $name . '[width]',
				'value'   => $field_args['width'],
				'options' => array(
					array(
						'value' => '33',
						'label' => esc_html__( '33%', 'cartflows-pro' ),
					),
					array(
						'value' => '50',
						'label' => esc_html__( '50%', 'cartflows-pro' ),
					),
					array(
						'value' => '100',
						'label' => esc_html__( '100%', 'cartflows-pro' ),
					),
				),

			),
			'field-label'   => array(
				'type'  => 'text',
				'label' => __( 'Field Label', 'cartflows-pro' ),
				'name'  => $name . '[label]',
				'value' => $field_args['label'],
			),

			'field-default' => $is_checkbox ?
				array(
					'type'    => 'select',
					'label'   => __( 'Default', 'cartflows-pro' ),
					'name'    => $name . '[default]',
					'value'   => $field_args['default'],
					'options' => array(
						array(
							'value' => '1',
							'label' => esc_html__( 'Checked', 'cartflows-pro' ),
						),
						array(
							'value' => '0',
							'label' => esc_html__( 'Un-Checked', 'cartflows-pro' ),
						),
					),
				) :

				array(
					'type'  => 'text',
					'label' => __( 'Default', 'cartflows-pro' ),
					'name'  => $name . '[default]',
					'value' => $field_args['default'],
				),
		);

		if ( $is_select ) {

			$new_field['field_options']['select-options'] = array(
				'type'  => 'text',
				'label' => __( 'Options', 'cartflows-pro' ),
				'name'  => $name . '[options]',
				'value' => $field_args['options'],
			);
		}

		if ( ! in_array( $type, array( 'checkbox', 'select' ), true ) ) {
			$new_field['field_options']['field-placeholder'] = array(
				'type'  => 'text',
				'label' => __( 'Placeholder', 'cartflows-pro' ),
				'name'  => $name . '[placeholder]',
				'value' => $field_args['placeholder'],
			);
		}

		$new_field['field_options']['required-field'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Required', 'cartflows-pro' ),
			'name'  => $name . '[required]',
			'value' => $field_args['required'],
		);

		$new_field['field_options']['collapsed-field'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Collapsible', 'cartflows-pro' ),
			'name'  => $name . '[optimized]',
			'value' => $field_args['optimized'],
		);

		return $new_field;
	}

	/**
	 * Add custom meta fields
	 *
	 * @param string $post_id post id.
	 * @param array  $fields fields.
	 * @param array  $new_fields new fields.
	 */
	public static function get_field_settings( $post_id, $fields, $new_fields ) {

		if ( 'billing' === $fields ) {
			$get_ordered_fields = wcf()->options->get_checkout_meta_value( $post_id, 'wcf_field_order_billing' );
		} else {
			$get_ordered_fields = wcf()->options->get_checkout_meta_value( $post_id, 'wcf_field_order_shipping' );
		}

		if ( isset( $get_ordered_fields ) && ! empty( $get_ordered_fields ) ) {
			$data_array = $get_ordered_fields;

		} else {
			$data_array = Cartflows_Helper::get_checkout_fields( $fields, $post_id );
		}

		if ( isset( $new_fields ) && ! empty( $new_fields ) && is_array( $new_fields ) ) {
			$data_array = $new_fields;
		}
		$field_args = array();

		foreach ( $data_array as $key => $value ) {
			$checkout_meta = Cartflows_Pro_Checkout_Meta_Helper::get_instance();
			$field_args    = $checkout_meta->prepare_field_arguments( $key, $value, $post_id, $fields );

			foreach ( $field_args as $arg_key => $arg_val ) {

				if ( ! in_array( $arg_key, $value, true ) ) {

					$data_array[ $key ][ $arg_key ] = $arg_val;
				}
			}

			if ( 'billing' === $fields ) {
				$name = 'wcf_field_order_billing[' . $key . ']';
			} else {
				$name = 'wcf_field_order_shipping[' . $key . ']';
			}

			$is_checkbox = false;
			$is_require  = false;
			$is_select   = false;
			$display     = 'none';

			$type = $field_args['type'];

			if ( 'checkbox' == $field_args['type'] ) {
				$is_checkbox = true;
			}

			if ( 'yes' == $field_args['required'] ) {
				$is_require = true;
			}

			if ( 'yes' == $field_args['optimized'] ) {
				$is_optimized = true;
			}

			if ( 'select' == $field_args['type'] ) {
				$is_select = true;
				$display   = 'block';
			}

			$data_array[ $key ]['field_options'] = array(
				'enable-field'  => array(
					'type'  => 'checkbox',
					'label' => __( 'Enable Field', 'cartflows-pro' ),
					'name'  => $name . '[enabled]',
					'value' => $field_args['enabled'],
				),
				'select-width'  => array(
					'type'    => 'select',
					'label'   => __( 'Field Width', 'cartflows-pro' ),
					'name'    => $name . '[width]',
					'value'   => $field_args['width'],
					'options' => array(
						array(
							'value' => '33',
							'label' => esc_html__( '33%', 'cartflows-pro' ),
						),
						array(
							'value' => '50',
							'label' => esc_html__( '50%', 'cartflows-pro' ),
						),
						array(
							'value' => '100',
							'label' => esc_html__( '100%', 'cartflows-pro' ),
						),
					),

				),
				'field-label'   => array(
					'type'  => 'text',
					'label' => __( 'Field Label', 'cartflows-pro' ),
					'name'  => $name . '[label]',
					'value' => $field_args['label'],
				),

				'field-default' => $is_checkbox ?
					array(
						'type'    => 'select',
						'label'   => __( 'Default', 'cartflows-pro' ),
						'name'    => $name . '[default]',
						'value'   => $field_args['default'],
						'options' => array(
							array(
								'value' => '1',
								'label' => esc_html__( 'Checked', 'cartflows-pro' ),
							),
							array(
								'value' => '0',
								'label' => esc_html__( 'Un-Checked', 'cartflows-pro' ),
							),
						),
					) :

					array(
						'type'  => 'text',
						'label' => __( 'Default', 'cartflows-pro' ),
						'name'  => $name . '[default]',
						'value' => $field_args['default'],
					),
			);

			if ( $is_select ) {

				$data_array[ $key ]['field_options']['select-options'] = array(
					'type'  => 'text',
					'label' => __( 'Options', 'cartflows-pro' ),
					'name'  => $name . '[options]',
					'value' => $field_args['options'],
				);
			}

			if ( ! in_array( $type, array( 'checkbox', 'select' ), true ) ) {
				$data_array[ $key ]['field_options']['field-placeholder'] = array(
					'type'  => 'text',
					'label' => __( 'Placeholder', 'cartflows-pro' ),
					'name'  => $name . '[placeholder]',
					'value' => $field_args['placeholder'],
				);
			}

			$data_array[ $key ]['field_options']['required-field'] = array(
				'type'  => 'checkbox',
				'label' => __( 'Required', 'cartflows-pro' ),
				'name'  => $name . '[required]',
				'value' => $field_args['required'],
			);

			$data_array[ $key ]['field_options']['collapsed-field'] = array(
				'type'  => 'checkbox',
				'label' => __( 'Collapsible', 'cartflows-pro' ),
				'name'  => $name . '[optimized]',
				'value' => $field_args['optimized'],
			);
		}

		return $data_array;
	}

	/**
	 * Add custom meta fields
	 *
	 * @param string $id id.
	 */
	public function get_product_option_fields( $id ) {

		$data = wcf()->options->get_checkout_meta_value( $id, 'wcf-product-options-data' );

		$checkout_products = wcf_pro()->utils->get_selected_product_options_data( $id, $data );

		$product_option_data = array();

		if ( ! empty( $checkout_products ) ) {
			foreach ( $checkout_products as $key => $value ) {

				if ( ! isset( $value['product'] ) || empty( $value['product'] ) ) {
					return;
				}

				$product = wc_get_product( $value['product'] );

				if ( ! is_object( $product ) ) {
					continue;
				}

				$product_id   = $product->get_id();
				$product_name = $product->get_name();
				$unique_id    = isset( $value['unique_id'] ) ? $value['unique_id'] : '';

				$selected_data = array(
					'product_name'           => $product_name,
					'product_id'             => $product_id,
					'key'                    => $key,
					'input_product_name'     => isset( $value['product_name'] ) ? $value['product_name'] : $product_name,
					'input_subtext'          => isset( $value['product_subtext'] ) ? $value['product_subtext'] : '',
					'input_enable_highlight' => isset( $value['enable_highlight'] ) ? $value['enable_highlight'] : '',
					'input_highlight_text'   => isset( $value['highlight_text'] ) ? $value['highlight_text'] : '',
					'add_to_cart'            => isset( $value['add_to_cart'] ) ? $value['add_to_cart'] : 'yes',
					'unique_id'              => $unique_id,
				);

				$product_option_data[ $key ] = $selected_data;
			}
		}

		return $product_option_data;

	}

	/**
	 * Get original price
	 *
	 * @param array $options options.
	 * @param int   $post_id post id.
	 * @return string
	 */
	public function get_pre_checkout_offer_original_price( $options, $post_id ) {

		$offer_product = $options['wcf-pre-checkout-offer-product'];

		$custom_price = __( 'Product is not selected', 'cartflows-pro' );

		if ( isset( $offer_product[0] ) ) {
			$custom_price = __( 'Product does not exist', 'cartflows-pro' );

			$product_id = $offer_product[0];

			$product = wc_get_product( $product_id );

			if ( is_object( $product ) ) {

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
				}

				$product = wc_get_product( $product_id );

				if ( is_object( $product ) ) {
					$custom_price = $product->get_regular_price();
					return $custom_price;
				}
			}
		}

		return $custom_price;
	}

	/**
	 * Get discount price
	 *
	 * @param array $options options.
	 * @param int   $post_id post id.
	 * @return string
	 */
	public function get_pre_checkout_offer_discount_price( $options, $post_id ) {

		$offer_product = $options['wcf-pre-checkout-offer-product'];

		$custom_price = __( 'Product is not selected', 'cartflows-pro' );

		if ( isset( $offer_product[0] ) ) {
			$custom_price = __( 'Product does not exist', 'cartflows-pro' );

			$product_id = $offer_product[0];

			$product = wc_get_product( $product_id );

			if ( is_object( $product ) ) {

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
				}

				$product = wc_get_product( $product_id );

				if ( is_object( $product ) ) {

					$product_sale_price = $product->get_sale_price();

					if ( ! empty( $product_sale_price ) ) {

						$custom_price = floatval( $product->get_sale_price() );
					} else {
						$custom_price = floatval( $product->get_regular_price() );
					}

					/* Offer Discount */
					$discount_type  = $options['wcf-pre-checkout-offer-discount'];
					$discount_value = floatval( $options['wcf-pre-checkout-offer-discount-value'] );

					if ( ! empty( $discount_type ) && $discount_value > 0 ) {
						$original_price = floatval( $product->get_regular_price() );

						if ( 'discount_percent' === $discount_type ) {

							$discount_custom_price = $original_price - ( ( $original_price * $discount_value ) / 100 );

						} elseif ( 'discount_price' === $discount_type ) {

							$discount_custom_price = $original_price - $discount_value;

						}
						$custom_price = $discount_custom_price;

					}

					return $custom_price;
				}
			}
		}

		return $custom_price;
	}

	/**
	 * Get original price
	 *
	 * @param array $options options.
	 * @param int   $post_id post id.
	 * @return string
	 */
	public function get_bump_original_price( $options, $post_id ) {

		$offer_product = $options['wcf-order-bump-product'];

		$custom_price = __( 'Product is not selected', 'cartflows-pro' );

		if ( isset( $offer_product[0] ) ) {
			$custom_price = __( 'Product does not exist', 'cartflows-pro' );

			$product_id = $offer_product[0];

			$product = wc_get_product( $product_id );

			if ( is_object( $product ) ) {

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
					$product = wc_get_product( $product_id );
				}
				$custom_price = $product->get_regular_price();

				return $custom_price;
			}
		}

		return $custom_price;
	}

	/**
	 * Get discount price
	 *
	 * @param array $options options.
	 * @param int   $post_id post id.
	 * @return string
	 */
	public function get_bump_discount_price( $options, $post_id ) {

		$offer_product = $options['wcf-order-bump-product'];

		$custom_price = __( 'Product is not selected', 'cartflows-pro' );

		if ( isset( $offer_product[0] ) ) {
			$custom_price = __( 'Product does not exist', 'cartflows-pro' );

			$product_id = $offer_product[0];

			$product = wc_get_product( $product_id );

			if ( is_object( $product ) ) {

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
					$product = wc_get_product( $product_id );
				}

				$product_sale_price = $product->get_sale_price();

				if ( ! empty( $product_sale_price ) ) {

					$custom_price = floatval( $product->get_sale_price() );
				} else {
					$custom_price = floatval( $product->get_regular_price() );
				}

				/* Offer Discount */
				$discount_type = $options['wcf-order-bump-discount'];

				if ( ! empty( $discount_type ) ) {

					$custom_price = floatval( $product->get_regular_price() );

					$discount_value = floatval( $options['wcf-order-bump-discount-value'] );

					if ( 'discount_percent' === $discount_type ) {
						if ( $discount_value > 0 ) {
							$custom_price = $custom_price - ( ( $custom_price * $discount_value ) / 100 );
						}
					} elseif ( 'discount_price' === $discount_type ) {
						if ( $discount_value > 0 ) {
							$custom_price = $custom_price - $discount_value;
						}
					} elseif ( 'coupon' === $discount_type ) {
						$discount_coupon = $options['wcf-order-bump-discount-coupon'];

						if ( is_array( $discount_coupon ) && ! empty( $discount_coupon ) ) {
							$discount_coupon = reset( $discount_coupon );
						}

						return __( 'Coupon will be applied on checkout', 'cartflows-pro' );
					}
				}

				return $custom_price;
			}
		}

		return $custom_price;
	}

}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Checkout_Meta_Data::get_instance();
