<?php
/**
 * AB test markup.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * AB test Markup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Ab_Test_Markup {


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
	 *  Constructor
	 */
	public function __construct() {

		add_action( 'cartflows_wp', array( $this, 'process_ab_test' ) );
	}

	/**
	 * Process ab test
	 *
	 * @param int $step_id Step id.
	 */
	public function process_ab_test( $step_id ) {

		$ab_test = wcf_get_ab_test( $step_id );

		if ( $ab_test->is_ab_test_enable() ) {
			$ab_test->run_ab_test();
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Ab_Test_Markup::get_instance();
