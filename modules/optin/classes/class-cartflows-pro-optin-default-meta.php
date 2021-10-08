<?php
/**
 * Optin post meta
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Meta Boxes setup
 */
class Cartflows_Pro_Optin_Default_Meta {

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

		add_filter( 'cartflows_optin_meta_options', array( $this, 'meta_fields' ), 10, 2 );
	}

	/**
	 * Optin pro default field
	 *
	 * @param array $fields checkout fields.
	 * @param int   $post_id post ID.
	 */
	public function meta_fields( $fields, $post_id ) {

		if ( ! cartflows_pro_is_active_license() && is_admin() ) {
			return $fields;
		}

		/* Custom Fields Options*/
		$fields['wcf-optin-enable-custom-fields'] = array(
			'default'  => 'no',
			'sanitize' => 'FILTER_SANITIZE_STRING',
		);

		$fields['wcf-optin-fields-billing'] = array(
			'default'  => Cartflows_Pro_Helper::get_optin_default_fields(),
			'sanitize' => 'FILTER_CARTFLOWS_PRO_OPTIN_FIELDS',
		);

		return $fields;
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Optin_Default_Meta::get_instance();
