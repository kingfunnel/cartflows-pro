<?php
/**
 * CartFlows Flows ajax actions.
 *
 * @package CartFlows
 */

namespace CartflowsProAdmin\AdminCore\Ajax;

use CartflowsProAdmin\AdminCore\Ajax\AjaxBase;
use CartflowsProAdmin\AdminCore\inc\AdminHelper;

/**
 * Class Flows.
 */
class MultipleOrderBump extends AjaxBase {

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
	 * Register_ajax_events.
	 *
	 * @return void
	 */
	public function register_ajax_events() {
		$ajax_events = array(
			'delete_order_bump',
			'add_order_bump',
			'clone_order_bump',
			'update_order_bump_title',
			'update_order_bump_status',
			'get_current_order_bump',
			'save_order_bump_settings',
		);

		$this->init_ajax_events( $ajax_events );
	}

	/**
	 * Save order bump settings.
	 *
	 * @return void
	 */
	public function save_order_bump_settings() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_save_order_bump_settings', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : '';
		$ob_id   = isset( $_POST['ob_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ob_id'] ) ) : '';

		if ( ! empty( $ob_id ) && ! empty( $step_id ) ) {

			$order_bumps = get_post_meta( $step_id, 'wcf-order-bumps', true );

			$new_data = isset( $_POST['ob_data'] ) ? json_decode( wp_unslash( $_POST['ob_data'] ), true ) : ''; //phpcs:ignore

			$default_data = \Cartflows_Pro_Checkout_Default_Meta::get_instance()->order_bump_default_meta();

			foreach ( $order_bumps as $index => $ob_data ) {

				if ( $ob_data['id'] === $ob_id ) {

					foreach ( $new_data as $data_key => $data_value ) {

						if ( ! in_array( $data_key, array_keys( $default_data ), true ) ) {
							continue;
						}

						$data_value                         = $this->sanitize_multiple_order_bump_values( $data_value, $default_data[ $data_key ]['sanitize'], $step_id, $order_bumps, $index, $new_data );
						$order_bumps[ $index ][ $data_key ] = $data_value;
					}

					update_post_meta( $step_id, 'wcf-order-bumps', $order_bumps );
					delete_post_meta( $step_id, 'wcf-pro-dynamic-css' );
					break;
				}
			}
		}

		wp_send_json_error( $response_data );

	}

	/**
	 * Sanitixe order bump values.
	 *
	 * @param string $data_value meta value.
	 * @param string $filter filter type.
	 * @param int    $step_id checkout id.
	 * @param array  $order_bumps order bumps.
	 * @param int    $index order bump index.
	 * @param int    $new_data order bump data.
	 */
	public function sanitize_multiple_order_bump_values( $data_value, $filter, $step_id, $order_bumps, $index, $new_data ) {

		switch ( $filter ) {

			case 'FILTER_SANITIZE_STRING':
				$meta_value = filter_var( $data_value, FILTER_SANITIZE_STRING );
				break;

			case 'FILTER_SANITIZE_URL':
				$meta_value = filter_var( $data_value, FILTER_SANITIZE_URL );
				break;

			case 'FILTER_SANITIZE_NUMBER_INT':
				$meta_value = filter_var( $data_value, FILTER_SANITIZE_NUMBER_INT );
				break;

			case 'FILTER_SANITIZE_NUMBER_FLOAT':
				$meta_value = filter_var( $data_value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
				break;

			case 'FILTER_WP_KSES_POST':
				$meta_value = wp_kses_post( $data_value ); // phpcs:ignore
				break;

			case 'FILTER_SANITIZE_COLOR':
				$meta_value = sanitize_hex_color( $data_value ); //phpcs:ignore
				break;

			case 'FILTER_CARTFLOWS_OB_IMAGE':
				$image_data = '';

				if ( ! empty( $data_value ) && ! is_serialized( $data_value ) ) {
					$image_obj = json_decode( stripcslashes( wp_unslash( $data_value ) ), true );
					$image_url = isset( $image_obj['sizes'] ) ? $image_obj['sizes'] : array();

					$image_id = isset( $image_obj['id'] ) ? intval( $image_obj['id'] ) : 0;

					if ( 0 !== $image_id ) {
						$image_data = array(
							'id'  => $image_id,
							'url' => array(
								'thumbnail' => isset( $image_url['thumbnail']['url'] ) ? esc_url_raw( $image_url['thumbnail']['url'] ) : '',
								'medium'    => isset( $image_url['medium']['url'] ) ? esc_url_raw( $image_url['medium']['url'] ) : '',
								'full'      => isset( $image_url['full']['url'] ) ? esc_url_raw( $image_url['full']['url'] ) : '',
							),
						);
					}
				}
				$meta_value = $image_data;
				break;

			default:
				$meta_value = filter_var( $data_value, FILTER_DEFAULT );
				break;
		}

		return $meta_value;
	}

	/**
	 * Get order bump.
	 *
	 * @return void
	 */
	public function get_current_order_bump() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_get_current_order_bump', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : '';
		$ob_id   = isset( $_POST['ob_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ob_id'] ) ) : 0;

		$current_ob = array();

		if ( ! empty( $ob_id ) && ! empty( $step_id ) ) {

			$order_bumps = get_post_meta( $step_id, 'wcf-order-bumps', true );

			foreach ( $order_bumps as $index => $ob_data ) {

				if ( $ob_id === $ob_data['id'] ) {

					if ( ! empty( $ob_data['product'] ) ) {
						$product_id  = intval( $ob_data['product'] );
						$product_obj = wc_get_product( $product_id );

						if ( $product_obj ) {
							$ob_data['product'] = array(
								'value'          => $product_id,
								'label'          => $product_obj->get_name() . ' (#' . $product_obj->get_id() . ')',
								'original_price' => \Cartflows_Pro_Admin_Helper::get_product_original_price( $product_obj ),
							);
						}
					}

					if ( ! empty( $ob_data['coupon'] ) ) {

						$all_discount_types = wc_get_coupon_types();
						$coupon_code        = $ob_data['coupon'];
						$coupon_data        = new WC_Coupon( $coupon_code );
						$coupon_id          = $coupon_data->get_id();
						$discount_type      = get_post_meta( $coupon_id, 'discount_type', true );

						if ( $discount_type ) {
							$ob_data['coupon'] = array(
								'value' => $coupon_code,
								'label' => get_the_title( $coupon_id ) . ' (Type: ' . $all_discount_types[ $discount_type ] . ')',
							);
						}
					}

					$current_ob = $ob_data;
					break;
				}
			}

			$response_data = array(
				'success'    => true,
				'messsage'   => __( 'Order Bump Data Retrived', 'cartflows-pro' ),
				'current_ob' => $current_ob,
			);
		} else {

			$response_data = array(
				'success'    => false,
				'messsage'   => __( 'No data.', 'cartflows-pro' ),
				'current_ob' => $current_ob,
			);
		}

		wp_send_json_success( $response_data );

	}

	/**
	 * Delete order bump.
	 *
	 * @return void
	 */
	public function update_order_bump_status() {
		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_update_order_bump_status', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		$post_id   = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$step_id   = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : '';
		$ob_id     = isset( $_POST['ob_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ob_id'] ) ) : 0;
		$ob_status = isset( $_POST['ob_status'] ) && 'true' === $_POST['ob_status'] ? true : false;

		if ( ! empty( $ob_id ) && ! empty( $step_id ) ) {

			$order_bumps = get_post_meta( $step_id, 'wcf-order-bumps', true );

			foreach ( $order_bumps as $index => $ob_data ) {

				if ( $ob_id === $ob_data['id'] ) {

					$order_bumps[ $index ]['status'] = $ob_status;

				}
			}

			update_post_meta( $step_id, 'wcf-order-bumps', $order_bumps );

			AdminHelper::clear_current_step_css( $step_id );

			$response_data = array(
				'success'  => true,
				'messsage' => __( 'Order Bump Status Updated Succesfully', 'cartflows-pro' ),
			);
			wp_send_json_success( $response_data );
		} else {

			$response_data = array(
				'success'  => false,
				'messsage' => __( 'Cant update order bump status!', 'cartflows-pro' ),
			);
			wp_send_json_error( $response_data );
		}
	}

	/**
	 * Delete order bump.
	 *
	 * @return void
	 */
	public function delete_order_bump() {

		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_delete_order_bump', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : '';
		$ob_id   = isset( $_POST['ob_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ob_id'] ) ) : 0;

		if ( ! empty( $ob_id ) && ! empty( $step_id ) ) {

			$order_bumps = get_post_meta( $step_id, 'wcf-order-bumps', true );
			$new_ob_data = array();

			foreach ( $order_bumps as $index => $ob_data ) {

				if ( $ob_id === $ob_data['id'] ) {

					unset( $order_bumps[ $index ] );
					$new_ob_data = array_values( $order_bumps );
					update_post_meta( $step_id, 'wcf-order-bumps', $new_ob_data );
					AdminHelper::clear_current_step_css( $step_id );
					break;
				}
			}

			$response_data = array(
				'success'     => true,
				'messsage'    => __( 'Order Bump Deleted Succesfully', 'cartflows-pro' ),
				'order_bumps' => $new_ob_data,
			);
			wp_send_json_success( $response_data );
		} else {

			$response_data = array(
				'success'  => false,
				'messsage' => __( 'Cant delete order bump!', 'cartflows-pro' ),
			);
			wp_send_json_error( $response_data );
		}

	}

	/**
	 * Add Order Bump.
	 *
	 * @return void
	 */
	public function add_order_bump() {
		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_add_order_bump', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : '';
		$ob_id   = isset( $_POST['ob_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ob_id'] ) ) : 0;

		$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : 'no title';

		if ( ! empty( $ob_id ) && ! empty( $step_id ) ) {

			$order_bumps = get_post_meta( $step_id, 'wcf-order-bumps', true );

			if ( ! is_array( $order_bumps ) ) {
				$order_bumps = array();
			}

			$new_ob_data = AdminHelper::add_default_order_bump_data( $ob_id, $title, $order_bumps );
			update_post_meta( $step_id, 'wcf-order-bumps', $new_ob_data );

			$response_data = array(
				'success'     => true,
				'messsage'    => __( 'Order Bump Added Succesfully', 'cartflows-pro' ),
				'order_bumps' => $new_ob_data,
			);
			wp_send_json_success( $response_data );
		} else {

			$response_data = array(
				'messsage' => __( 'Cant add order bump!', 'cartflows-pro' ),
				'success'  => false,
			);
			wp_send_json_error( $response_data );
		}
	}

	/**
	 * Clone Order Bump
	 *
	 * @return void
	 */
	public function clone_order_bump() {
		$response_data = array( 'messsage' => $this->get_error_msg( 'permission' ) );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_clone_order_bump', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : '';
		$ob_id   = isset( $_POST['ob_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ob_id'] ) ) : '';

		if ( ! empty( $ob_id ) && ! empty( $step_id ) ) {

			// Get all the order bumps for current step.
			$order_bumps = get_post_meta( $step_id, 'wcf-order-bumps', true );

			foreach ( $order_bumps as $index => $ob_data ) {

				if ( $ob_id === $ob_data['id'] ) {

					// Generate random ID for new order Bump.
					$new_ob_id = substr( md5( wp_rand() ), 2, 3 );

					$data_to_clone = $ob_data;

					// Replace/Change the ID and Title to new.
					$data_to_clone['id']     = $new_ob_id;
					$data_to_clone['title']  = $data_to_clone['title'] . __( ' Clone', 'cartflows-pro' );
					$data_to_clone['status'] = false;

					array_push( $order_bumps, $data_to_clone );

					update_post_meta( $step_id, 'wcf-order-bumps', $order_bumps );
					break;
				}
			}

			$response_data = array(
				'success'     => true,
				'messsage'    => __( 'Order Bump Cloned Succesfully', 'cartflows-pro' ),
				'order_bumps' => $order_bumps,
			);

			wp_send_json_success( $response_data );
		} else {

			$response_data = array(
				'messsage' => __( 'Can\'t clone the order bump! Order Bump ID or Page ID not found.', 'cartflows-pro' ),
				'success'  => false,
			);
			wp_send_json_error( $response_data );
		}
	}

	/**
	 * Update order bump title.
	 */
	public function update_order_bump_title() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'cartflows_pro_update_order_bump_title', 'security', false ) ) {
			$response_data = array( 'messsage' => $this->get_error_msg( 'nonce' ) );
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = array( 'messsage' => __( 'No post data found!', 'cartflows-pro' ) );
			wp_send_json_error( $response_data );
		}

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$step_id = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : '';
		$ob_id   = isset( $_POST['ob_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ob_id'] ) ) : 0;

		$new_title = isset( $_POST['new_title'] ) ? sanitize_text_field( wp_unslash( $_POST['new_title'] ) ) : 'no title';

		$order_bumps = get_post_meta( $step_id, 'wcf-order-bumps', true );

		foreach ( $order_bumps as $index => $ob_data ) {

			if ( $ob_id === $ob_data['id'] ) {

				$order_bumps[ $index ]['title'] = $new_title;
				break;
			}
		}
		update_post_meta( $step_id, 'wcf-order-bumps', $order_bumps );

		$response_data = array(
			'success'     => true,
			'messsage'    => __( 'Order Bump Title Updated Succesfully', 'cartflows-pro' ),
			'order_bumps' => $order_bumps,
		);
		wp_send_json_success( $response_data );
	}
}
