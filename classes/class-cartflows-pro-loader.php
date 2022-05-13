<?php
/**
 * Cartflows Loader.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Loader' ) ) {

	/**
	 * Class Cartflows_Pro_Loader.
	 */
	final class Cartflows_Pro_Loader {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Member Variable
		 *
		 * @var utils
		 */
		public $utils = null;

		/**
		 * Member Variable
		 *
		 * @var wc_common
		 */
		public $wc_common = null;

		/**
		 * Member Variable
		 *
		 * @var session
		 */
		public $session = null;

		/**
		 * Member Variable
		 *
		 * @var order
		 */
		public $order = null;

		/**
		 * Member Variable
		 *
		 * @var options
		 */
		public $options = null;

		/**
		 * Member Variable
		 *
		 * @var assets_vars
		 */
		public $assets_vars = null;


		/**
		 * Member Variable
		 *
		 * @var front
		 */
		public $front = null;

		/**
		 * Member Variable
		 *
		 * @var flow
		 */
		public $flow = null;

		/**
		 * Member Variable
		 *
		 * @var offer
		 */
		public $offer = null;

		/**
		 * Member Variable
		 *
		 * @var is_woo_active
		 */
		public $is_woo_active = true;

		/**
		 *  Member Variable
		 *
		 *  @var wcf_step_objs
		 */

		public $wcf_step_objs = array();

		/**
		 *  Initiator
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) ) {

				self::$instance = new self();

				do_action( 'cartflows_pro_loaded' );
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->define_constants();
			$this->licence_setup();

			// Activation hook.
			register_activation_hook( CARTFLOWS_PRO_FILE, array( $this, 'activation_reset' ) );

			// deActivation hook.
			register_deactivation_hook( CARTFLOWS_PRO_FILE, array( $this, 'deactivation_reset' ) );

			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
			add_action( 'plugins_loaded', array( $this, 'load_plugin' ), 100 );

			add_filter( 'plugin_action_links_' . CARTFLOWS_PRO_BASE, array( $this, 'add_action_links' ) );
		}

		/**
		 * Show action on plugin page.
		 *
		 * @param  array $links links.
		 * @return array
		 */
		public function add_action_links( $links ) {

			if ( ! defined( 'CARTFLOWS_SETTINGS' ) ) {
				return $links;
			}

			$setting_url = admin_url( 'admin.php?page=' . CARTFLOWS_SETTINGS );

			if ( ! CARTFLOWS_LEGACY_ADMIN ) {

				$setting_url = add_query_arg(
					array(
						'page' => CARTFLOWS_SLUG,
						'path' => 'settings',
					),
					admin_url()
				);
			}

			$mylinks = array(
				'<a href="' . $setting_url . '">' . __( 'Settings', 'cartflows-pro' ) . '</a>',
				'<a target="_blank" href="' . esc_url( 'https://cartflows.com/docs' ) . '">' . __( 'Docs', 'cartflows-pro' ) . '</a>',
			);
			return array_merge( $links, $mylinks );
		}

		/**
		 * Defines all constants
		 *
		 * @since 1.0.0
		 */
		public function define_constants() {

			define( 'CARTFLOWS_PRO_DISPLAY_TITLE', 'CartFlows Pro' );
			define( 'CARTFLOWS_PRO_PRODUCT_TITLE', 'CartFlows' ); // Don't change `CartFlows`. It is used as product title on server side.
			define( 'CARTFLOWS_PRO_BASE', plugin_basename( CARTFLOWS_PRO_FILE ) );
			define( 'CARTFLOWS_PRO_DIR', plugin_dir_path( CARTFLOWS_PRO_FILE ) );
			define( 'CARTFLOWS_PRO_URL', plugins_url( '/', CARTFLOWS_PRO_FILE ) );
			define( 'CARTFLOWS_PRO_VER', '1.9.2' );
			define( 'CARTFLOWS_PRO_SLUG', 'cartflows-pro' );
			define( 'CARTFLOWS_PRO_PLUGIN_TYPE', 'pro' );
			define( 'CARTFLOWS_PRO_LICENSE_URL', admin_url( 'plugins.php?cartflows-license-popup' ) );

			define( 'CARTFLOWS_PRO_VISITS_TABLE', 'cartflows_visits' );
			define( 'CARTFLOWS_PRO_VISITS_META_TABLE', 'cartflows_visits_meta' );
			define( 'CARTFLOWS_PRO_REQ_CF_VER', '1.9.0' );

			if ( ! defined( 'CARTFLOWS_SERVER_URL' ) ) {
				define( 'CARTFLOWS_SERVER_URL', 'https://my.cartflows.com/' );
			}

			$cookie_prefix = '';

			if ( defined( 'CARTFLOWS_COOKIE_PREFIX' ) ) {
				$cookie_prefix = CARTFLOWS_COOKIE_PREFIX;
			}

			define( 'CARTFLOWS_SESSION_COOKIE', $cookie_prefix . 'cartflows_session_' );

			define( 'CARTFLOWS_VISITED_FLOW_COOKIE', $cookie_prefix . 'wcf-visited-flow-' );

			define( 'CARTFLOWS_VISITED_STEP_COOKIE', $cookie_prefix . 'wcf-step-visited-' );

			define( 'CARTFLOWS_AB_TEST_COOKIE', $cookie_prefix . 'cartflows-ab-test-' );

			if ( ! defined( 'CARTFLOWS_HTTPS' ) ) {
				define( 'CARTFLOWS_HTTPS', is_ssl() ? true : false );
			}
		}

		/**
		 * Loads plugin files.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_plugin() {

			if ( ! did_action( 'cartflows_init' ) ) {
				add_action( 'admin_notices', array( $this, 'fails_to_load' ) );
				return;
			}

			/* Required version of CartFlow */

			if ( ! version_compare( CARTFLOWS_VER, CARTFLOWS_PRO_REQ_CF_VER, '>=' ) ) {
				add_action( 'admin_notices', array( $this, 'fail_load_out_of_date' ) );
				return;
			}

			$this->load_helper_files_components();
			$this->load_core_files();
			$this->load_core_components();

			add_action( 'wp_loaded', array( $this, 'initialize' ) );

			/**
			 * Cartflows Init.
			 *
			 * Fires when Cartflows is instantiated.
			 *
			 * @since 1.0.0
			 */
			do_action( 'cartflows_pro_init' );
		}



		/**
		 * Load Helper Files and Components.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_helper_files_components() {

			$this->is_woo_active = function_exists( 'WC' );

			/* Public Utils */
			include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-utils.php';

			/* Helper File */
			include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-helper.php';

			/* Admin Helper File */
			include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-admin-helper.php';

			/* Public Global Namespace Function */
			include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-functions.php';

			/* WC Common Public */
			include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-wc-common.php';

			/* Meta Default Values */
			include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-default-meta.php';

			/* Public Session */
			include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-session.php';

			include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-action-schedular.php';

			$this->utils     = Cartflows_Pro_Utils::get_instance();
			$this->wc_common = Cartflows_Pro_Wc_Common::get_instance();
			$this->options   = Cartflows_Pro_Default_Meta::get_instance();
			$this->session   = Cartflows_Pro_Session::get_instance();
		}

		/**
		 * Init hooked function.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function initialize() {
			$this->assets_vars = $this->utils->get_assets_path();
		}

		/**
		 * Load Core Files.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_core_files() {

			/* Update compatibility. */
			require_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-update.php';
		}

		/**
		 * Load Core Components.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_core_components() {

			/* Admin */
			include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-admin.php';
			include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-licence.php';
			include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-wp-cli.php';

			/* Frontend Global */
			include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-step-factory.php';
			include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-frontend.php';
			include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-flow-frontend.php';

			$this->front = Cartflows_Pro_Frontend::get_instance();
			$this->flow  = Cartflows_Pro_Flow_Frontend::get_instance();

			require_once CARTFLOWS_PRO_DIR . 'pro-admin-loader.php';

			/* Start-Pro-Feature */
			if ( file_exists( CARTFLOWS_PRO_DIR . 'modules/ab-test/class-cartflows-pro-ab-test.php' ) ) {
				// A/B Split Testing loader.
				include_once CARTFLOWS_PRO_DIR . 'modules/ab-test/class-cartflows-pro-ab-test.php';
			}
			/* End-Pro-Feature */

			if ( $this->is_woo_active ) {

				/* Modules */
				include_once CARTFLOWS_PRO_DIR . 'modules/shortcodes/class-cartflows-pro-shortcodes.php';
				include_once CARTFLOWS_PRO_DIR . 'modules/optin/class-cartflows-pro-optin.php';
				include_once CARTFLOWS_PRO_DIR . 'modules/checkout/class-cartflows-pro-checkout.php';
				include_once CARTFLOWS_PRO_DIR . 'modules/thankyou/class-cartflows-pro-thankyou.php';

				/* Start-Plus-Feature */
				include_once CARTFLOWS_PRO_DIR . 'modules/offer/class-cartflows-pro-base-offer.php';
				$this->offer = Cartflows_Pro_Base_Offer::get_instance();

				include_once CARTFLOWS_PRO_DIR . 'modules/upsell/class-cartflows-pro-upsell.php';
				include_once CARTFLOWS_PRO_DIR . 'modules/downsell/class-cartflows-pro-downsell.php';

				include_once CARTFLOWS_PRO_DIR . 'modules/orders/class-cartflows-pro-orders.php';
				$this->order = Cartflows_Pro_Orders::get_instance();

				/* Payment Gateways */
				include_once CARTFLOWS_PRO_DIR . 'modules/gateways/class-cartflows-pro-api-base.php';
				include_once CARTFLOWS_PRO_DIR . 'modules/gateways/class-cartflows-pro-gateway.php';
				include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-gateways.php';

				$this->gateways = Cartflows_Pro_Gateways::get_instance();
				/* End-Plus-Feature */

				// Social tracking loader.
				include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-tracking.php';
			}

			include_once CARTFLOWS_PRO_DIR . 'modules/gutenberg/classes/class-cartflows-pro-block-loader.php';

			if ( class_exists( '\Elementor\Plugin' ) ) {
				// Load the widgets.
				include_once CARTFLOWS_PRO_DIR . 'modules/elementor/class-cartflows-pro-el-widgets-loader.php';
			}

			if ( class_exists( 'FLBuilder' ) ) {

				include_once CARTFLOWS_PRO_DIR . 'modules/beaver-builder/class-cartflows-pro-bb-modules-loader.php';
			}

			if ( class_exists( 'Affiliate_WP' ) ) {
				include_once CARTFLOWS_PRO_DIR . 'compatibilities/class-cartflows-pro-affiliate-wp.php';
			}

			if ( class_exists( 'WOOMC\API' ) ) {
				include_once CARTFLOWS_PRO_DIR . 'compatibilities/class-cartflows-pro-woo-multicurrency.php';
			}

			/* Start-Pro-Feature */
			include_once CARTFLOWS_PRO_DIR . 'modules/tracking/class-cartflows-pro-analytics-tracking.php';
			include_once CARTFLOWS_PRO_DIR . 'modules/tracking/class-cartflows-pro-analytics-db.php';

			// Tracking files.
			if ( is_admin() ) {
				include_once CARTFLOWS_PRO_DIR . 'modules/tracking/class-cartflows-pro-analytics-reports.php';
			}
			/* End-Pro-Feature */
		}

		/**
		 * License Setup
		 *
		 * @since 1.1.16 Updated API manager library.
		 *
		 * @return void
		 */
		public function licence_setup() {

			// Load WC_AM_Client class if it exists.
			if ( ! class_exists( 'WC_AM_Client_25' ) ) {
				include_once CARTFLOWS_PRO_DIR . 'classes/class-wc-am-client.php';
			}

			// Instantiate WC_AM_Client class object if the WC_AM_Client class is loaded.
			if ( class_exists( 'WC_AM_Client_25' ) ) {
				/**
				 * This file is only an example that includes a plugin header, and this code used to instantiate the client object. The variable $wcam_lib
				 * can be used to access the public properties from the WC_AM_Client class, but $wcam_lib must have a unique name. To find data saved by
				 * the WC_AM_Client in the options table, search for wc_am_client_{product_id}, so in this example it would be wc_am_client_13.
				 *
				 * All data here is sent to the WooCommerce API Manager API, except for the $software_title, which is used as a title, and menu label, for
				 * the API Key activation form the client will see.
				 *
				 * ****
				 * NOTE
				 * ****
				 * If $product_id is empty, the customer can manually enter the product_id into a form field on the activation screen.
				 *
				 * @param string $file             Must be __FILE__ from the root plugin file, or theme functions, file locations.
				 * @param int    $product_id       Must match the Product ID number (integer) in the product.
				 * @param string $software_version This product's current software version.
				 * @param string $plugin_or_theme  'plugin' or 'theme'
				 * @param string $api_url          The URL to the site that is running the API Manager. Example: https://www.toddlahman.com/
				 * @param string $software_title   The name, or title, of the product. The title is not sent to the API Manager APIs, but is used for menu titles.
				 *
				 * Example:
				 *
				 * $wcam_lib = new WC_AM_Client_25( $file, $product_id, $software_version, $plugin_or_theme, $api_url, $software_title );
				 */

				// Example of empty string product_id.
				global $wcam_lib;
				$wcam_lib = new WC_AM_Client_25( CARTFLOWS_PRO_FILE, CARTFLOWS_PRO_PRODUCT_TITLE, CARTFLOWS_PRO_VER, 'plugin', CARTFLOWS_SERVER_URL, 'cartflows' );
			}

		}

		/**
		 * Fires admin notice when CartFlows is not installed and activated.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function fails_to_load() {

			$screen = get_current_screen();

			if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
				return;
			}

			$plugin = 'cartflows/cartflows.php';
			if ( _is_cartflows_installed() ) {
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
				}
				$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
				/* translators: %s: html tags */
				$message  = '<p>' . sprintf( __( 'The %1$s CartFlows Pro %2$s plugin requires %1$s CartFlows %2$s plugin to be activated.', 'cartflows-pro' ), '<strong>', '</strong>' ) . '</p>';
				$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, __( 'Activate CartFlows Now', 'cartflows-pro' ) ) . '</p>';
			} else {
				if ( ! current_user_can( 'install_plugins' ) ) {
					return;
				}
				$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=cartflows' ), 'install-plugin_cartflows' );
				/* translators: %s: html tags */
				$message  = '<p>' . sprintf( __( 'The %1$s CartFlows Pro %2$s plugin requires %1$s CartFlows %2$s plugin to be installed.', 'cartflows-pro' ), '<strong>', '</strong>' ) . '</p>';
				$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Install CartFlows Now', 'cartflows-pro' ) ) . '</p>';
			}
			echo '<div class="error"><p>' . $message . '</p></div>';

		}

		/**
		 * Fires admin notice when CartFlows version is not up to data.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function fail_load_out_of_date() {

			if ( ! current_user_can( 'update_plugins' ) ) {
				return;
			}

			$file_path = 'cartflows/cartflows.php';

			$upgrade_link = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file_path, 'upgrade-plugin_' . $file_path );
			/* translators: %s: required cartflows version */
			$message  = '<p>' . sprintf( __( 'You are using an older version of CartFlows. To keep things running smoothly with CartFlows Pro, please update CartFlows version %s or greater.', 'cartflows-pro' ), CARTFLOWS_PRO_REQ_CF_VER ) . '</p>';
			$message .= '<p>' . sprintf( '<a href="%s" class="button-primary" >%s</a>', $upgrade_link, __( 'Update CartFlows Now', 'cartflows-pro' ) ) . '</p>';

			echo '<div class="error wcf-notice">' . $message . '</div>';
		}

		/**
		 * Load CartFlows Pro Text Domain.
		 * This will load the translation textdomain depending on the file priorities.
		 *      1. Global Languages /wp-content/languages/cartflows-pro/ folder
		 *      2. Local dorectory /wp-content/plugins/cartflows-pro/languages/ folder
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function load_textdomain() {

			// Default languages directory for CartFlows Pro.
			$lang_dir = CARTFLOWS_PRO_DIR . 'languages/';

			/**
			 * Filters the languages directory path to use for CartFlows Pro.
			 *
			 * @param string $lang_dir The languages directory path.
			 */
			$lang_dir = apply_filters( 'cartflows_pro_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter.
			global $wp_version;

			$get_locale = get_locale();

			if ( $wp_version >= 4.7 ) {
				$get_locale = get_user_locale();
			}

			/**
			 * Language Locale for CartFlows Pro
			 *
			 * @var $get_locale The locale to use.
			 * Uses get_user_locale()` in WordPress 4.7 or greater,
			 * otherwise uses `get_locale()`.
			 */
			$locale = apply_filters( 'plugin_locale', $get_locale, 'cartflows-pro' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'cartflows-pro', $locale );

			// Setup paths to current locale file.
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/cartflows-pro/ folder.
				load_textdomain( 'cartflows-pro', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/cartflows-pro/languages/ folder.
				load_textdomain( 'cartflows-pro', $mofile_local );
			} else {
				// Load the default language files.
				load_plugin_textdomain( 'cartflows-pro', false, $lang_dir );
			}
		}

		/**
		 * Activation Reset
		 */
		public function activation_reset() {

			// Load analtics DB class.
			include_once CARTFLOWS_PRO_DIR . 'modules/tracking/class-cartflows-pro-analytics-db.php';
			$cartflows_db = Cartflows_Pro_Analytics_Db::get_instance();
			$cartflows_db->create_db_tables();
		}

		/**
		 * Deactivation Reset
		 */
		public function deactivation_reset() {
		}

	}

	/**
	 *  Prepare if class 'Cartflows_Pro_Loader' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Pro_Loader::get_instance();
}

/**
 * Get global class.
 *
 * @return object
 */
function wcf_pro() {
	return Cartflows_Pro_Loader::get_instance();
}

if ( ! function_exists( '_is_woo_installed' ) ) {

	/**
	 * Is woocommerce plugin installed.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	function _is_woo_installed() {

		$path    = 'woocommerce/woocommerce.php';
		$plugins = get_plugins();

		return isset( $plugins[ $path ] );
	}
}

if ( ! function_exists( '_is_cartflows_installed' ) ) {

	/**
	 * Is woocommerce plugin installed.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	function _is_cartflows_installed() {

		$path    = 'cartflows/cartflows.php';
		$plugins = get_plugins();

		return isset( $plugins[ $path ] );
	}
}
