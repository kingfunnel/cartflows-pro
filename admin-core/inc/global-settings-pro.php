<?php
/**
 * CartFlows Global Data.
 *
 * @package CartFlows
 */

namespace CartflowsProAdmin\AdminCore\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use CartflowsAdmin\AdminCore\Inc\AdminHelper;
/**
 * Class flowMeta.
 */
class GlobalSettingsPro {



	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.6.13
	 */
	public function __construct() {

		add_filter( 'cartflows_global_settings_data', array( $this, 'get_pro_global_settings' ), 10, 1 );

		add_filter( 'cartflows_global_data_options', array( $this, 'get_pro_gloabl_options' ), 10, 1 );
	}

	/**
	 * Get_pro_gloabl_options
	 *
	 * @param string $options options.
	 */
	public static function get_pro_gloabl_options( $options ) {

		$settings_default = apply_filters(
			'cartflows_offer_global_settings',
			array(
				'separate_offer_orders' => 'separate',
			)
		);

		$offer_settings = AdminHelper::get_admin_settings_option( '_cartflows_offer_global_settings', false, false );

		$offer_settings = wp_parse_args( $offer_settings, $settings_default );

		foreach ( $offer_settings as $key => $data ) {
			$options[ '_cartflows_offer_global_settings[' . $key . ']' ] = $data;
		}

		return $options;
	}


	/**
	 * Page Header Tabs.
	 *
	 * @param string $settings settings.
	 */
	public static function get_pro_global_settings( $settings ) {

		$settings['general']['fields']['ref-paypal-trns'] = array(
			'type'  => 'checkbox',
			'name'  => '_cartflows_common[paypal_reference_transactions]',
			'label' => __( 'Enable PayPal Reference Transactions.', 'cartflows-pro' ),
			/* translators: %1$1s: link html start, %2$12: link html end*/
			'desc'  => sprintf( __( 'This option will work with %1$1s PayPal Standard%2$2s & %3$3s PayPal Checkout%4$4s Gateways only. To know more about PayPal reference transactions %5$5s click here. %6$6s', 'cartflows-pro' ), '<a href="https://docs.woocommerce.com/document/paypal-standard/" target="_blank">', '</a>', '<a href="https://wordpress.org/plugins/woocommerce-gateway-paypal-express-checkout/" target="_blank">', '</a>', '<a href="https://cartflows.com/docs/how-to-enable-paypal-reference-transactions/" target="_blank">', '</a>' ),
		);

		$settings['offer-settings'] = array(
			'title'  => __( 'Offer Settings ', 'cartflows-pro' ),
			'fields' => array(
				'order-optoin' => array(
					'type'    => 'radio',
					'name'    => '_cartflows_offer_global_settings[separate_offer_orders]',
					'options' => array(
						array(
							'value' => 'separate',
							'label' => __( 'Create a new child order (Recommended)', 'cartflows-pro' ),
							'desc'  => __( 'This option create a new order for all accepted upsell/downsell offers. Main order will be parent order for them.', 'cartflows-pro' ),
						),
						array(
							'value' => 'merge',
							'label' => __( 'Add to main order', 'cartflows-pro' ),
							'desc'  => __( 'This option will merge all accepted upsell/downsell offers into main order.', 'cartflows-pro' ),
						),
					),
				),
				'order-doc'    => array(
					'type'    => 'doc',
					/* translators: %1$1s: link html start, %2$12: link html end*/
					'content' => sprintf( __( 'For more information about the offer settings please %1$1s Click here. %2$2s', 'cartflows-pro' ), '<a href="https://cartflows.com/docs/introducing-separate-orders-feature/" target="_blank">', '</a>' ),
				),
			),
		);

		return $settings;
	}
}
