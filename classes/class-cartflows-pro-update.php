<?php
/**
 * Update Compatibility
 *
 * @package CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Update' ) ) :

	/**
	 * CartFlows Update initial setup
	 *
	 * @since 1.0.0
	 */
	class Cartflows_Pro_Update {

		/**
		 * Class instance.
		 *
		 * @access private
		 * @var $instance Class instance.
		 */
		private static $instance;

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
		 *  Constructor
		 */
		public function __construct() {
			add_action( 'admin_init', __CLASS__ . '::init' );
		}

		/**
		 * Init
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function init() {

			do_action( 'cartflows_pro_update_before' );

			// Get auto saved version number.
			$saved_version = get_option( 'cartflows-pro-version', false );

			// Update auto saved version number.
			if ( ! $saved_version ) {
				update_option( 'cartflows-pro-version', CARTFLOWS_PRO_VER );
				return;
			}

			// If equals then return.
			if ( version_compare( $saved_version, CARTFLOWS_PRO_VER, '=' ) ) {
				return;
			}

			// Update to older version than 1.5.5 version.
			if ( version_compare( $saved_version, '1.5.5', '<' ) ) {
				self::v_1_5_5();
			}

			if ( version_compare( $saved_version, '1.7.0', '<' ) ) {
				update_option( 'wcf_order_bump_migrated', 'no' );
			}

			// For older order bump beta only.
			if ( '1.7.0-beta-1' === $saved_version ) {
				if ( function_exists( 'as_enqueue_async_action' ) ) {
					as_enqueue_async_action( 'cartflows_beta_migrate_order_bump_format' );
					update_option( 'wcf-beta-order-bump-page', 1 );
				}
			}

			// Update auto saved version number.
			update_option( 'cartflows-pro-version', CARTFLOWS_PRO_VER );

			do_action( 'cartflows_pro_update_after' );
		}

		/**
		 * Offer orders option.
		 *
		 * @since 1.5.5
		 * @return void
		 */
		public static function v_1_5_5() {

			/* Backward compatible option */
			$old_data = array(
				'separate_offer_orders' => 'merge',
			);

			Cartflows_Helper::update_admin_settings_option( '_cartflows_offer_global_settings', $old_data, false );
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Pro_Update::get_instance();

endif;
