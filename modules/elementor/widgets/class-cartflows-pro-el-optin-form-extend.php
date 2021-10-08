<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Elementor Classes.
 *
 * @package cartflows
 */

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Checkout Form Widget
 *
 * @since 1.6.13
 */
class CartFlows_Pro_Optin_Form_Extend {

	/**
	 * Member Variable
	 *
	 * @var object instance
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
	 * Setup actions and filters.
	 *
	 * @since 1.6.13
	 */
	private function __construct() {

		// Apply dynamic option filters.
		add_action( 'cartflows_elementor_optin_options_filters', array( $this, 'dynamic_filters' ), 10, 2 );
	}

	/**
	 * Settings
	 *
	 * @since 1.6.13
	 * @var object $settings
	 */
	public static $settings;

	/**
	 * Register Billing fields Controls.
	 *
	 * @param object $element Widget settings object.
	 * @param array  $args    Args array.
	 */
	public function register_billing_fields_controls( $element, $args ) {

		$all_billing_fields = array();
		$optin_id           = get_the_id();

		if ( $optin_id && _is_wcf_optin_custom_fields( $optin_id ) ) {

			$all_billing_fields = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-optin-fields-billing' );
		}

		if ( ! empty( $all_billing_fields ) ) {

			$element->add_control(
				'billing_fields_heading',
				array(
					'label' => __( 'Fields Width', 'cartflows-pro' ),
					'type'  => Controls_Manager::HEADING,
				)
			);

			foreach ( $all_billing_fields as $field_key => $field ) {

				if ( is_array( $field ) ) {

					$element->add_control(
						$field_key,
						array(
							'label'   => $field['label'],
							'type'    => Controls_Manager::SELECT,
							'default' => '',
							'options' => array(
								''    => esc_html__( 'Default', 'cartflows-pro' ),
								'33'  => esc_html__( '33%', 'cartflows-pro' ),
								'50'  => esc_html__( '50%', 'cartflows-pro' ),
								'100' => esc_html__( '100%', 'cartflows-pro' ),
							),
						)
					);
				}
			}
		} else {

			$element->add_control(
				'billing_fields_disabled',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						/* translators: %1$1s, %2$2s Link to meta */
						__( 'Please enable "Custom Field Editor" from %1$1smeta settings%2$2s to to edit options.', 'cartflows-pro' ),
						'<a href="' . Cartflows_Pro_Helper::get_current_page_edit_url( 'custom-field' ) . '" target="_blank">',
						'</a>'
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				)
			);
		}
	}

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
 * Initiate the class.
 */
CartFlows_Pro_Optin_Form_Extend::get_instance();
