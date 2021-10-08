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
class Cartflows_Pro_Optin_Meta_Data {

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

		add_filter( 'cartflows_get_optin_custom_fields_data', array( $this, 'custom_fields_data' ), 10, 2 );

		add_filter( 'cartflows_optin_step_meta_fields', array( $this, 'filter_values' ), 10, 2 );
	}

	/**
	 * Filter optin values.
	 *
	 * @param  array $options options.
	 * @param  int   $step_id step id.
	 */
	public function filter_values( $options, $step_id ) {

		if ( isset( $options['wcf-optin-fields-billing'] ) ) {
			$options['wcf-optin-fields-billing'] = $this->get_field_settings( $step_id, 'billing', '' );
		}

		return $options;

	}
	/**
	 * Add custom meta fields
	 *
	 * @param array $post_id post id.
	 * @param array $options options.
	 */
	public function custom_fields_data( $post_id, $options ) {

		$billing_fields = $this->get_field_settings( $post_id, 'billing', '' );

		$new_options = $this->get_new_field_options();

		$custom_fields = array(
			'extra_fields'   => array(
				'fields' => array(
					'enable-optin-field-editor' => array(
						'type'  => 'checkbox',
						'label' => __( 'Enable Custom Field Editor', 'cartflows-pro' ),
						'name'  => 'wcf-optin-enable-custom-fields',
						'value' => $options['wcf-optin-enable-custom-fields'],
					),
				),
			),
			'billing_fields' => array(
				'fields' => $billing_fields,
			),
			'custom_editor'  => array(
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
					'type'  => 'text',
					'class' => 'wcf-cpf-default',
					'label' => __( 'Default', 'cartflows-pro' ),
					'name'  => 'wcf-checkout-custom-fields[' . $p_key . '][default]',
					'value' => '',
				),
				'placeholder-to-field' => array(
					'type'  => 'text',
					'class' => 'wcf-cpf-placeholder',
					'label' => __( 'Placeholder', 'cartflows-pro' ),
					'name'  => 'wcf-checkout-custom-fields[' . $p_key . '][placeholder]',
					'value' => '',
				),
				'options-field'        => array(
					'type'  => 'text',
					'class' => 'wcf-cpf-options',
					'label' => __( 'Options', 'cartflows-pro' ),
					'name'  => 'wcf-checkout-custom-fields[' . $p_key . '][label]',
					'value' => '',
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

			);
		}
		return $options;
	}

	/**
	 * Add custom meta fields
	 *
	 * @param string $post_id post id.
	 * @param array  $fields fields.
	 * @param array  $new_fields new fields.
	 */
	public static function get_field_settings( $post_id, $fields, $new_fields ) {

		$ordered_billing_fields = wcf()->options->get_optin_meta_value( $post_id, 'wcf-optin-fields-billing' );

		if ( isset( $ordered_billing_fields ) && ! empty( $ordered_billing_fields ) ) {
			$billing_fields = $ordered_billing_fields;

		} else {
			$billing_fields = Cartflows_Pro_Helper::get_optin_fields( 'billing', $post_id );
		}

		if ( isset( $billing_fields ) && ! empty( $billing_fields ) ) {
			$data_array = $billing_fields;
		}

		if ( isset( $new_fields ) && ! empty( $new_fields ) && is_array( $new_fields ) ) {
			$data_array = $new_fields;
		}
		$field_args = array();

		foreach ( $data_array as $key => $value ) {
			$optin_meta = Cartflows_Pro_Optin_Meta_Helper::get_instance();
			$field_args = $optin_meta->prepare_field_arguments( $key, $value, $post_id, $fields );

			foreach ( $field_args as $arg_key => $arg_val ) {

				if ( ! in_array( $arg_key, $value, true ) ) {

					$data_array[ $key ][ $arg_key ] = $arg_val;
				}
			}

			$name = 'wcf-optin-fields-billing[' . $key . ']';

			$is_checkbox = false;
			$is_require  = false;
			$is_select   = false;
			$display     = 'none';

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
						'type'    => 'checkbox',
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

			if ( false == $is_checkbox || false == $is_select ) {
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

		}

		return $data_array;
	}

		/**
		 * Prepare new custom meta fields
		 *
		 * @param array  $new_field new field.
		 * @param string $post_id post id.
		 * @param string $type post id.
		 */
	public static function prepare_field_settings( $new_field, $post_id, $type ) {

		$key        = $new_field['key'];
		$optin_meta = Cartflows_Pro_Optin_Meta_Helper::get_instance();
		$field_args = $optin_meta->prepare_field_arguments( $key, $new_field, $post_id, $type );

		$name = 'wcf-optin-fields-billing[' . $key . ']';

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

		return $new_field;
	}


}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Optin_Meta_Data::get_instance();
