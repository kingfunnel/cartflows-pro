<?php
/**
 * CartFlows Admin Menu.
 *
 * @package CartFlows
 */

namespace CartflowsProAdmin\AdminCore\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Admin_Menu.
 */
class AdminMenu {

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
	 * Instance
	 *
	 * @access private
	 * @var string Class object.
	 * @since 1.0.0
	 */
	private $menu_slug;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->menu_slug = 'cartflowws';

		$this->initialize_hooks();
	}

	/**
	 * Init Hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function initialize_hooks() {
		add_filter( 'cartflows_react_admin_localize', array( $this, 'localize_required_vars' ), 10, 1 );

		add_action( 'cartflows_save_step_meta', array( $this, 'save_step_actions' ), 10, 1 );
	}

	/**
	 * Save step pro action.
	 *
	 * @param int $step_id step id.
	 */
	public function save_step_actions( $step_id ) {

		delete_post_meta( $step_id, 'wcf-pro-dynamic-css' );
	}

	/**
	 * Get payment gateways.
	 *
	 * @param array $localize localized variables.
	 */
	public function localize_required_vars( $localize ) {

		if ( ! wcf_pro()->is_woo_active ) {
			return $localize;
		}

		$supported_gateways = \Cartflows_Pro_Gateways::get_instance()->get_supported_gateways();

		$woo_available_gateways = WC()->payment_gateways->get_available_payment_gateways();

		$available_gateways = array();

		foreach ( $woo_available_gateways as  $key => $value ) {
			$available_gateways[ $key ]['method_title'] = $value->method_title;
		}

		$localize['supported_payment_gateways'] = $supported_gateways;

		$localize['available_payment_gateways'] = $available_gateways;

		$localize['is_order_bump_migrated'] = get_option( 'wcf_order_bump_migrated', false );

		return $localize;
	}
}

AdminMenu::get_instance();
