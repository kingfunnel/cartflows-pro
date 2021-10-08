<?php
/**
 * Bump order
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order Bump Product
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Order_Bump_Product {

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
		add_action( 'cartflows_checkout_form_before', array( $this, 'load_actions' ) );
		/* For wc ajax actions to filter it's value user priortiy before 499 */
		add_action( 'cartflows_woo_checkout_update_order_review_init', array( $this, 'dynamic_order_bump' ), 499 );
		/* Add or Cancel Bump Product */
		add_action( 'wp_ajax_wcf_bump_order_process', array( $this, 'order_bump_process' ) );
		add_action( 'wp_ajax_nopriv_wcf_bump_order_process', array( $this, 'order_bump_process' ) );
		add_shortcode( 'cartflows_bump_product_title', array( $this, 'bump_product_title' ) );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'custom_price_to_cart_item' ), 9999 );
	}

	/**
	 * To show order bump dynamically after update order review.
	 */
	public function dynamic_order_bump() {
		add_action( 'cartflows_checkout_before_shortcode', array( $this, 'load_actions' ) );

	}

	/**
	 * Load Actions
	 *
	 * @param int $checkout_id checkout id.
	 */
	public function load_actions( $checkout_id ) {

		if ( empty( $checkout_id ) && is_admin() && isset( $_POST['id'] ) ) {
			$checkout_id = intval( $_POST['id'] );// phpcs:ignore
		}

		$multi_ob = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bumps' );

		// For backword compatibility until users migrate old order bump to new one.
		// @todo Remove this block of code after v1.7.3 release.
		// Start.
		if ( empty( $multi_ob ) && 'yes' !== get_post_meta( $checkout_id, 'wcf-order-bump-migrated', true ) && 'yes' === wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump' ) ) {

			$product       = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-product' );
			$product_image = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-image' );

			$show_image_mobile = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-show-bump-image-mobile' );
			$show_image_mobile = 'yes' === $show_image_mobile ? 'no' : 'yes';

			$old_ob = array(
				'status'            => true,
				'product'           => isset( $product[0] ) ? $product[0] : '',
				'product_image'     => $product_image,
				'quantity'          => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-product-quantity' ),
				'discount_type'     => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-discount' ),
				'discount_value'    => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-discount-value' ),
				'discount_coupon'   => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-discount-coupon' ),
				'product_img_obj'   => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-image-obj' ),
				'checkbox_label'    => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-label' ),

				'title_text'        => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-title' ),
				'hl_text'           => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-hl-text' ),
				'desc_text'         => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-desc' ),
				'replace_product'   => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-replace' ),
				'next_step'         => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-ob-yes-next-step' ),
				'position'          => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-position' ),

				'enable_show_image' => ! empty( $product_image ) ? 'yes' : 'no',
				'ob_image_position' => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-image-position' ),
				'ob_image_width'    => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-image-width' ),
				'show_image_mobile' => $show_image_mobile,

				'style'             => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-style' ),
				'border_color'      => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-border-color' ),
				'border_style'      => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-border-style' ),
				'bg_color'          => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-bg-color' ),
				'label_color'       => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-label-color' ),
				'label_bg_color'    => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-label-bg-color' ),
				'desc_text_color'   => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-desc-text-color' ),
				'hl_text_color'     => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-bump-hl-text-color' ),
				'show_arrow'        => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-show-bump-arrow' ),
				'show_animation'    => wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-show-bump-animate-arrow' ),
			);
			array_push( $multi_ob, $old_ob );
		}

		// End.

		// If order bump exist in multiple order bumps.
		if ( is_array( $multi_ob ) && ! empty( $multi_ob ) ) {

			$obs_before_checkout = array();
			$obs_after_customer  = array();
			$obs_after_order     = array();
			$obs_after_payment   = array();

			foreach ( $multi_ob as $index => $order_bump_data ) {

				// If no product assigned then continue to next order bump.
				if ( false === $order_bump_data['status'] || empty( $order_bump_data['product'] ) ) {
					continue;
				}

				if ( 'before-checkout' === $order_bump_data['position'] ) {
					array_push( $obs_before_checkout, $order_bump_data );
				}
				if ( 'after-customer' === $order_bump_data['position'] ) {
					array_push( $obs_after_customer, $order_bump_data );
				}
				if ( 'after-order' === $order_bump_data['position'] ) {
					array_push( $obs_after_order, $order_bump_data );
				}
				if ( 'after-payment' === $order_bump_data['position'] ) {
					array_push( $obs_after_payment, $order_bump_data );
				}
			}

			$this->render_before_checkout_order_bumps( $obs_before_checkout );
			$this->render_after_customer_order_bumps( $obs_after_customer );
			$this->render_after_order_order_bumps( $obs_after_order );
			$this->render_after_payment_order_bumps( $obs_after_payment );

		}

		add_action( 'woocommerce_checkout_after_order_review', array( $this, 'add_order_bump_hidden_fields' ), 99 );
	}

	/**
	 * Render order bump.
	 *
	 * @param array $order_bumps order bumps.
	 */
	public function render_before_checkout_order_bumps( $order_bumps ) {

		add_action(
			'woocommerce_checkout_before_customer_details',
			function() use ( $order_bumps ) {
				$this->bump_order( $order_bumps, 'before-checkout' );
			}
		);
	}

	/**
	 * Render order bump.
	 *
	 * @param array $order_bumps order bumps.
	 */
	public function render_after_customer_order_bumps( $order_bumps ) {

		add_action(
			'woocommerce_checkout_after_customer_details',
			function() use ( $order_bumps ) {
				$this->bump_order( $order_bumps, 'after-customer' );
			}
		);
	}

	/**
	 * Render order bump.
	 *
	 * @param array $order_bumps order bumps.
	 */
	public function render_after_order_order_bumps( $order_bumps ) {

		add_action(
			'woocommerce_checkout_order_review',
			function() use ( $order_bumps ) {
				$this->bump_order( $order_bumps, 'after-order' );
			}
		);
	}

	/**
	 * Render order bump.
	 *
	 * @param array $order_bumps order bumps.
	 */
	public function render_after_payment_order_bumps( $order_bumps ) {

		add_action(
			'woocommerce_review_order_before_submit',
			function() use ( $order_bumps ) {
				$this->bump_order( $order_bumps, 'after-payment' );
			}
		);
	}

	/**
	 *  Display bump offer box html.
	 */
	public function add_order_bump_hidden_fields() {
		echo '<input type="hidden" name="_wcf_bump_products" value="">';
	}

	/**
	 * Get order bump hidden data.
	 *
	 * @param int     $product_id product id.
	 * @param boolean $order_bump_checked checked value.
	 */
	public function get_order_bump_hidden_data( $product_id, $order_bump_checked ) {

		echo '<input type="hidden" name="wcf_bump_product_id" class="wcf-bump-product-id" value="' . $product_id . '">';
	}

	/**
	 * Display bump offer box html.
	 *
	 * @param array  $order_bumps order bump values.
	 * @param string $position order bump position.
	 */
	public function bump_order( $order_bumps, $position ) {

		global $post;

		$checkout_id = 0;

		if ( $post ) {
			$checkout_id = $post->ID;
		} elseif ( is_admin() && isset( $_POST['id'] ) ) {
			$checkout_id = intval( $_POST['id'] );// phpcs:ignore
		}

		$output = '';

		if ( is_array( $order_bumps ) && ! empty( $order_bumps ) ) {

			$default_meta    = Cartflows_Pro_Checkout_Default_Meta::get_instance()->order_bump_default_meta();
			$ob_default_meta = array();
			foreach ( $default_meta as $key => $value ) {
				$ob_default_meta[ $key ] = $value['default'];
			}

			$output .= "<div class='wcf-bump-order-grid-wrap wcf-" . $position . "'>";

			foreach ( $order_bumps as $key => $order_bump_data ) {

				$order_bump_data = wp_parse_args( $order_bump_data, $ob_default_meta );

				$order_bump_product_quantity = isset( $order_bump_data['quantity'] ) ? $order_bump_data['quantity'] : 1;

				$order_bump_product = $order_bump_data['product'];

				$ob_id = isset( $order_bump_data['id'] ) ? $order_bump_data['id'] : '';

				$bump_layout        = isset( $order_bump_data['style'] ) ? $order_bump_data['style'] : 'style-2';
				$bump_layout_width  = isset( $order_bump_data['width'] ) ? $order_bump_data['width'] : '100';
				$order_bump_label   = isset( $order_bump_data['checkbox_label'] ) ? $order_bump_data['checkbox_label'] : '';
				$order_bump_title   = isset( $order_bump_data['title_text'] ) ? $order_bump_data['title_text'] : '';
				$order_bump_hl_text = isset( $order_bump_data['hl_text'] ) ? $order_bump_data['hl_text'] : '';
				$order_bump_desc    = $order_bump_data['desc_text'];

				$enabled_bump_image = isset( $order_bump_data['enable_show_image'] ) ? $order_bump_data['enable_show_image'] : 'no';

				$discount_type  = isset( $order_bump_data['discount_type'] ) ? $order_bump_data['discount_type'] : '';
				$discount_value = isset( $order_bump_data['discount_value'] ) ? $order_bump_data['discount_value'] : '';

				$order_bump_action_element = isset( $order_bump_data['action_element'] ) ? $order_bump_data['action_element'] : '';

				$bump_image_position  = 'left';
				$bump_image_width     = '';
				$bump_order_image_obj = '';
				$bump_order_image     = '';

				if ( 'yes' === $enabled_bump_image ) {

					$bump_image_position = isset( $order_bump_data['ob_image_position'] ) ? $order_bump_data['ob_image_position'] : 'left';

					$bump_image_width = isset( $order_bump_data['ob_image_width'] ) ? $order_bump_data['ob_image_width'] : '';

					$bump_order_image_obj = isset( $order_bump_data['product_img_obj'] ) ? $order_bump_data['product_img_obj'] : '';

					$bump_order_image = $this->get_order_bump_image_url( $bump_order_image_obj, $order_bump_data['product_image'] );

				}

				if ( empty( $order_bump_product ) ) {

					$flow_id = wcf()->utils->get_flow_id_from_step_id( $checkout_id );

					if ( wcf()->flow->is_flow_testmode( $flow_id ) ) {
						$order_bump_product = $this->get_bump_test_product( $checkout_id );
					} else {
						return;
					}
				}

				$product_id         = intval( $order_bump_product );
				$order_bump_checked = false;

				if ( ! empty( $_POST['post_data'] ) ) {

					$post_data = array();

					$post_raw_data = sanitize_text_field( wp_unslash( $_POST['post_data'] ) );

					parse_str( $post_raw_data, $post_data );

					if ( ! empty( $post_data['wcf-bump-order-cb'] ) ) {
						$order_bump_checked = true;
					}

					$post_data = null;
				}

				// Chcek if bump order already added in the cart.
				if ( $this->cart_has_product( $product_id, true ) ) {
					$order_bump_checked = true;
				}

				$bump_offer_arr = array(
					'ob_id'      => $ob_id,
					'product_id' => $product_id,
				);

				$_product = wc_get_product( $product_id );

				if ( ! empty( $_product ) ) {

					if ( $_product->is_type( 'variable' ) ) {

						$default_attributes = $_product->get_default_attributes();

						if ( ! empty( $default_attributes ) ) {

							foreach ( $_product->get_children() as $c_in => $variation_id ) {

								if ( 0 === $c_in ) {
									$bump_offer_arr['product_id'] = $variation_id;
								}

								$single_variation = new WC_Product_Variation( $variation_id );

								if ( $default_attributes == $single_variation->get_attributes() ) {

									$bump_offer_arr['product_id'] = $variation_id;
									break;
								}
							}
						} else {

							$product_childrens = $_product->get_children();

							if ( is_array( $product_childrens ) ) {

								foreach ( $product_childrens  as $c_in => $c_id ) {

									$bump_offer_arr['product_id'] = $c_id;
									break;
								}
							}
						}
					}
				}

				/* Set new ids based on variation */
				$product_id = $bump_offer_arr['product_id'];

				/* bump order blinking arrow */
				$is_order_bump_arrow_enabled      = $order_bump_data['show_arrow'];
				$is_order_bump_arrow_anim_enabled = $order_bump_data['show_animation'];

				$bump_order_blinking_arrow = '';
				$bump_order_arrow_animate  = '';

				if ( 'yes' === $is_order_bump_arrow_enabled ) {

					if ( 'yes' === $is_order_bump_arrow_anim_enabled ) {
						$bump_order_arrow_animate = 'wcf-blink';
					}

					$bump_order_blinking_arrow = '<svg version="1.1" class="wcf-pointing-arrow ' . $bump_order_arrow_animate . '" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="15px" fill="red" viewBox="310 253 90 70" enable-background="new 310 253 90 70" xml:space="preserve"><g><g><path d="M364.348,253.174c-0.623,0.26-1.029,0.867-1.029,1.54v18.257h-51.653c-0.919,0-1.666,0.747-1.666,1.666v26.658
									c0,0.92,0.747,1.666,1.666,1.666h51.653v18.327c0,0.673,0.406,1.28,1.026,1.54c0.623,0.257,1.34,0.116,1.816-0.36l33.349-33.238 c0.313-0.313,0.49-0.737,0.49-1.18c0-0.443-0.177-0.866-0.487-1.179l-33.349-33.335 C365.688,253.058,364.971,252.915,364.348,253.174z"/></g></g></svg>';
				}
				/* bump order blinking arrow */

				/* Execute */
				$order_bump_desc = do_shortcode( $order_bump_desc );

				// Get the product price and replace the vars with actual values.
				$product_object     = wc_get_product( $product_id );
				$product_price      = $product_object ? $product_object->get_price( 'edit' ) : '';
				$custom_price       = wcf_pro()->utils->get_calculated_discount( $discount_type, $discount_value, $product_price );
				$product_price_data = $this->get_taxable_product_price( $product_object, $product_price, $custom_price );

				$display_price = '';

				if ( ! empty( $custom_price ) ) {
					$display_price  = '<del class="wcf-regular-price">' . wc_price( $product_price_data['product_price'] ) . '</del>';
					$display_price .= ' <span class="wcf-discount-price">' . wc_price( $product_price_data['custom_price'] ) . '</span>';
				} else {
					$display_price = wc_price( $product_price_data['product_price'] );
				}

				// Adding this variable as a data attribute.
				$bump_offer_data = wp_json_encode( $bump_offer_arr );

				$to_replace = array(
					'{{product_name}}',
					'{{product_price}}',
					'{{quantity}}',
				);

				$with_replace = array(
					$product_object->get_name(),
					$display_price,
					$order_bump_product_quantity,
				);

				$order_bump_label = str_replace( $to_replace, $with_replace, $order_bump_label );

				$order_bump_title = str_replace( $to_replace, $with_replace, $order_bump_title );

				$order_bump_hl_text = str_replace( $to_replace, $with_replace, $order_bump_hl_text );
				$order_bump_desc    = str_replace( $to_replace, $with_replace, $order_bump_desc );

				$bump_order_style_file = '';

				ob_start();

				if ( ! empty( $bump_layout ) && '' !== $bump_layout ) {

					$bump_order_style_file = CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/bump-order/wcf-bump-order-' . $bump_layout . '.php';

					if ( file_exists( $bump_order_style_file ) ) {
						include $bump_order_style_file;
					}
				} else {
					include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/bump-order/wcf-bump-order-style-1.php';
				}

				$output .= ob_get_clean();
			}

			$output .= '</div>';
		}
		echo $output;
	}

		/**
		 * Get product price with tax.
		 *
		 * @param object $product_object product data.
		 * @param int    $product_price product price.
		 * @param int    $custom_price custom price.
		 */
	public function get_taxable_product_price( $product_object, $product_price, $custom_price ) {

		$display_type = get_option( 'woocommerce_tax_display_cart' );

		if ( 'excl' === $display_type ) {
			$product_price = wc_get_price_excluding_tax( $product_object, array( 'price' => $product_price ) );
			$custom_price  = wc_get_price_excluding_tax( $product_object, array( 'price' => $custom_price ) );
		} else {
			$product_price = wc_get_price_including_tax( $product_object, array( 'price' => $product_price ) );
			$custom_price  = wc_get_price_including_tax( $product_object, array( 'price' => $custom_price ) );
		}

		return array(
			'product_price' => $product_price,
			'custom_price'  => $custom_price,
		);
	}

	/**
	 * Process bump order.
	 *
	 * @param object $bump_image_obj image object.
	 * @param string $original_url image url.
	 */
	public function get_order_bump_image_url( $bump_image_obj, $original_url ) {

		$size = apply_filters( 'cartflows_order_bump_image_size', 'medium' );

		if ( is_array( $bump_image_obj ) && ! empty( $bump_image_obj ) && '' !== $size ) {

			$original_url = ! empty( $bump_image_obj['url'][ $size ] ) ? $bump_image_obj['url'][ $size ] : $original_url;
		}

		return $original_url;
	}

	/**
	 * Process bump order.
	 */
	public function order_bump_process() {

		$nonce = filter_input( INPUT_POST, 'security', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'wcf_bump_order_process' ) ) {
			return;
		}

		$post_data = $_POST;

		if ( ! isset( $post_data['_wcf_bump_product_action'] ) ||
			( isset( $post_data['_wcf_bump_product_action'] ) && empty( $post_data['_wcf_bump_product_action'] ) )
		) {
			return;
		}

		$checkout_id = intval( $post_data['_wcf_checkout_id'] );
		$bump_action = sanitize_text_field( $post_data['_wcf_bump_product_action'] );

		// Check if checkout page is global checkout.
		$common             = Cartflows_Helper::get_common_settings();
		$is_global_checkout = false;
		if ( intval( $common['global_checkout'] ) === $checkout_id ) {
			$is_global_checkout = true;
		}

		if ( 'add_bump_product' === $bump_action ) {
			$checked = true;
		} elseif ( 'remove_bump_product' === $bump_action ) {
			$checked = false;
		} else {
			return;
		}

		$order_bump_data = isset( $post_data['_bump_offer_data'] ) ? json_decode( wp_unslash( $post_data['_bump_offer_data'] ), true ) : array();

		if ( empty( $order_bump_data ) ) {
			return;
		}

		$required_ob_data = $this->get_order_bump_by_id( $checkout_id, $order_bump_data['ob_id'] );

		/* Set new ids based on variation */
		$product_id = intval( $required_ob_data['product'] );

		$parent_id = intval( $required_ob_data['parent_id'] );
		$_product  = wc_get_product( $product_id );

		$discount_coupon = $required_ob_data['discount_coupon'];
		$new_key         = '';

		if ( is_array( $discount_coupon ) && ! empty( $discount_coupon ) ) {
			$discount_coupon = reset( $discount_coupon );
		}

		$ob_data = array(
			'ob_id'           => sanitize_text_field( $order_bump_data['ob_id'] ),
			'checkout_id'     => $checkout_id,
			'product_id'      => $product_id,
			'parent_id'       => $parent_id,
			'is_variable'     => sanitize_text_field( $required_ob_data['is_variable'] ),
			'is_variation'    => sanitize_text_field( $required_ob_data['is_variation'] ),

			'_product'        => $_product,
			'_product_price'  => floatval( $_product->get_price( 'edit' ) ),

			'discount_type'   => $required_ob_data['discount_type'],
			'discount_value'  => floatval( $required_ob_data['discount_value'] ),
			'discount_coupon' => $discount_coupon,
			'custom_price'    => '',
			'order_bump_qty'  => intval( $required_ob_data['quantity'] ),
			'is_replace'      => $required_ob_data['replace_product'],
			'index'           => 0,
			'checked'         => $checked,
		);

		// If replace main product with order bump option is enabled.
		if ( 'yes' === $ob_data['is_replace'] && ! $is_global_checkout ) {
			$this->replace_main_product_with_order_bump( $ob_data );
		}

		// Loop over cart items.
		$found_data       = $this->get_item_key_for_order_bump( $ob_data );
		$found_item_key   = $found_data['found_item_key'];
		$found_item       = $found_data['found_item'];
		$discount_enabled = $found_data['discount_enabled'];

		// Bump offer product found in cart and we need to add it.
		if ( null != $found_item_key && $checked ) {
			$this->order_bump_found_in_cart( $ob_data, $found_item_key, $found_item, $discount_enabled );
		}

		// add - if not found, remove/reduce - if found.
		if ( $checked && null === $found_item_key ) {
			$this->order_bump_not_found_in_cart( $ob_data );

		} elseif ( ! $checked && null != $found_item_key ) {
			$this->order_bump_remove_or_reduce( $ob_data, $found_item_key, $found_item );
		}

		wcf_update_the_checkout_transient( $checkout_id );

		$data = array(
			'total_product_price' => $required_ob_data['total_product_price'],
		);
		wp_send_json( wcf_pro()->utils->get_fragments( $new_key, $data ) );
	}
	/**
	 * Order bump remove or reduce.
	 *
	 * @param array  $ob_data order bump data.
	 * @param string $found_item_key cart key.
	 * @param array  $found_item item data.
	 */
	public function order_bump_remove_or_reduce( $ob_data, $found_item_key, $found_item ) {

		$new_qty = $found_item['quantity'] - $ob_data['order_bump_qty'];

		WC()->cart->remove_cart_item( $found_item_key );

		// Removed order bump data is required for GA events hence store it on fragments.
		$this->add_order_bump_data_in_fragment( $found_item );

		do_action( 'wcf_order_bump_item_removed', $ob_data['product_id'] );

		if ( $new_qty > 0 ) {

			if ( 'yes' === $ob_data['is_variable'] || 'yes' === $ob_data['is_variation'] ) {
				WC()->cart->add_to_cart( $ob_data['parent_id'], $new_qty, $ob_data['product_id'] );
			} else {
				WC()->cart->add_to_cart( $ob_data['parent_id'], $new_qty );
			}
		}

		if ( ! empty( $ob_data['discount_coupon'] ) ) {
			if ( WC()->cart->has_discount( $ob_data['discount_coupon'] ) ) {
				WC()->cart->remove_coupon( $ob_data['discount_coupon'] );
			}
		}
	}

	/**
	 * Add order bump data in fragment.
	 *
	 * @param array $ob_data order bump data.
	 */
	public function add_order_bump_data_in_fragment( $ob_data ) {

		$ga_settings = Cartflows_Helper::get_google_analytics_settings();

		if ( 'enable' === $ga_settings['enable_google_analytics'] ) {
			add_filter(
				'woocommerce_update_order_review_fragments',
				function( $data ) use ( $ob_data ) {
					$data['removed_order_bump_data'] = $ob_data;
					return $data;
				}
			);
		}
	}

	/**
	 * If order bump not found in cart.
	 *
	 * @param array $ob_data order bump data.
	 */
	public function order_bump_not_found_in_cart( $ob_data ) {

		$custom_price = '';

		if ( 'coupon' === $ob_data['discount_type'] ) {
			$apply_coupon = wcf_pro()->utils->apply_discount_coupon( $ob_data['discount_type'], $ob_data['discount_coupon'] );
		} else {
			$custom_price = wcf_pro()->utils->get_calculated_discount( $ob_data['discount_type'], $ob_data['discount_value'], $ob_data['_product_price'] );
		}

		$cart_item_data = array(
			'cartflows_bump' => true,
		);

		if ( isset( $custom_price ) && ( '' !== $custom_price ) ) {

			$cart_item_data['custom_price'] = $custom_price;
		}

		if ( 'yes' === $ob_data['is_variable'] || 'yes' === $ob_data['is_variation'] ) {
			WC()->cart->add_to_cart( $ob_data['parent_id'], $ob_data['order_bump_qty'], $ob_data['product_id'], array(), $cart_item_data );
		} else {
			WC()->cart->add_to_cart( $ob_data['product_id'], $ob_data['order_bump_qty'], 0, array(), $cart_item_data );
		}

		do_action( 'wcf_order_bump_item_added', $ob_data['product_id'] );
	}

	/**
	 * If order bump found in cart..
	 *
	 * @param array  $ob_data order bump data.
	 * @param string $found_item_key key.
	 * @param array  $found_item item data.
	 * @param bool   $discount_enabled is discount.
	 */
	public function order_bump_found_in_cart( $ob_data, $found_item_key, $found_item, $discount_enabled ) {

		// Case for discount enabled bump offer product.
		if ( $discount_enabled && 'coupon' !== $ob_data['discount_type'] ) {

			$custom_price = wcf_pro()->utils->get_calculated_discount( $ob_data['discount_type'], $ob_data['discount_value'], $ob_data['_product_price'] );

			$cart_item_data = array(
				'cartflows_bump' => true,
			);

			if ( isset( $custom_price ) ) {

				$cart_item_data['custom_price'] = $custom_price;
			}
			$new_key = '';
			if ( 'yes' === $ob_data['is_variable'] || 'yes' === $ob_data['is_variation'] ) {
				WC()->cart->add_to_cart( $ob_data['parent_id'], $ob_data['order_bump_qty'], $ob_data['product_id'], array(), $cart_item_data );
			} else {
				WC()->cart->add_to_cart( $ob_data['product_id'], $ob_data['order_bump_qty'], 0, array(), $cart_item_data );
			}

			do_action( 'wcf_order_bump_item_added', $ob_data['product_id'] );

		} else {

			if ( $discount_enabled && 'coupon' === $ob_data['discount_type'] ) {
				$apply_coupon = wcf_pro()->utils->apply_discount_coupon( $ob_data['discount_type'], $ob_data['discount_coupon'] );
			}

			$quantity = isset( $found_item['quantity'] ) ? $found_item['quantity'] : 0;
			$new_qty  = $quantity + $ob_data['order_bump_qty'];

			// If item is already in cart, increase quantity for product in cart.
			WC()->cart->remove_cart_item( $found_item_key );

			if ( $ob_data['_product']->is_in_stock() ) {

				$cart_item_data = array(
					'cartflows_bump' => true,
				);

				if ( 'yes' === $ob_data['is_variable'] || 'yes' === $ob_data['is_variation'] ) {
					WC()->cart->add_to_cart( $ob_data['parent_id'], $new_qty, $ob_data['product_id'], array(), $cart_item_data );
				} else {
					WC()->cart->add_to_cart( $ob_data['product_id'], $new_qty, 0, array(), $cart_item_data );
				}

				do_action( 'wcf_order_bump_item_added', $ob_data['product_id'] );
			}
		}
	}

	/**
	 * Get the item keu for order bump.
	 *
	 * @param array $ob_data order bump data.
	 */
	public function get_item_key_for_order_bump( $ob_data ) {

		$discount_enabled = false;
		$found_item_key   = null;
		$found_item       = null;

		foreach ( WC()->cart->get_cart() as $key => $item ) {

			// For variable product.
			if ( 'yes' === $ob_data['is_variable'] || 'yes' === $ob_data['is_variation'] ) {

				// Check if bump product is variation OR variable.
				if ( ( $item['product_id'] === $ob_data['parent_id'] && $item['variation_id'] === $ob_data['product_id'] )
				|| ( $item['product_id'] === $ob_data['product_id'] && $item['variation_id'] === $ob_data['product_id'] ) ) {

					if ( ! $ob_data['checked'] ) {

						if ( isset( $item['cartflows_bump'] ) ) {

							$found_item_key = $key;
							$found_item     = $item;

							if ( ! empty( $ob_data['discount_type'] ) ) {
								$discount_enabled = true;
							}
							break;
						}
					} else {

						$found_item_key = $key;
						$found_item     = $item;

						if ( ! empty( $ob_data['discount_type'] ) ) {
							$discount_enabled = true;
						}

						break;
					}
				}
			} else {

				// if same product is already in cart.
				if ( $item['product_id'] === $ob_data['product_id'] ) {

					if ( ! $ob_data['checked'] ) {

						if ( isset( $item['cartflows_bump'] ) ) {

							$found_item_key = $key;
							$found_item     = $item;

							if ( ! empty( $ob_data['discount_type'] ) ) {
								$discount_enabled = true;
							}

							break;
						}
					} else {

						$found_item_key = $key;
						$found_item     = $item;

						if ( ! empty( $ob_data['discount_type'] ) ) {
							$discount_enabled = true;
						}

						break;
					}
				}
			}
		}

		$found_data = array(
			'found_item_key'   => $found_item_key,
			'found_item'       => $found_item,
			'discount_enabled' => $discount_enabled,

		);

		return $found_data;
	}

	/**
	 * Replace the main product with order bump.
	 *
	 * @param array $ob_data order bump data.
	 */
	public function replace_main_product_with_order_bump( $ob_data ) {

		$main_products    = Cartflows_Pro_Variation_Product::get_instance()->get_all_main_products( $ob_data['checkout_id'] );
		$first_product    = $main_products[ $ob_data['index'] ];
		$first_product_id = intval( $first_product['product'] );
		$_product_data    = wc_get_product( $first_product['product'] );
		$cart_item_data   = array();

		if ( $ob_data['checked'] ) {

			// remove first product.
			foreach ( WC()->cart->get_cart() as $key => $item ) {
				if ( $first_product_id === $item['product_id'] || $first_product_id === $item['variation_id'] ) {
					WC()->cart->remove_cart_item( $key );
				}
			}
		} else {

			// check if product is already in cart. If yes then return.
			foreach ( WC()->cart->get_cart() as $key => $item ) {
				if ( $first_product_id === $item['product_id'] || $first_product_id === $item['variation_id'] ) {
					return;
				}
			}

			// add first product.
			$custom_price = wcf_pro()->utils->get_calculated_discount( $first_product['discount_type'], $first_product['discount_value'], $_product_data->get_price() );

			if ( ! empty( $custom_price ) ) {

				$cart_item_data = array(
					'custom_price' => $custom_price,
				);
			}
			if ( true === $first_product['variable'] || true === $first_product['variation'] ) {

				if ( true === $first_product['variable'] ) {
					$children_ids = $_product_data->get_children();
					$child        = $children_ids[0];
					$new_key      = WC()->cart->add_to_cart( $ob_data['parent_id'], $first_product['quantity'], $child, array(), $cart_item_data );
				} else {
					$parent_id = $_product_data->get_parent_id();
					$new_key   = WC()->cart->add_to_cart( $parent_id, $first_product['quantity'], $first_product['product'], array(), $cart_item_data );
				}
			} else {
				$new_key = WC()->cart->add_to_cart( $first_product['product'], $first_product['quantity'], 0, array(), $cart_item_data );
			}
		}
	}

	/**
	 * Preserve the custom item price added by Variations & Quantity feature
	 *
	 * @param array $cart_object cart object.
	 * @since 1.0.0
	 */
	public function custom_price_to_cart_item( $cart_object ) {

		if ( wp_doing_ajax() && ! WC()->session->__isset( 'reload_checkout' ) ) {

			foreach ( $cart_object->cart_contents as $key => $value ) {

				if ( isset( $value['custom_price'] ) ) {

					$custom_price = floatval( $value['custom_price'] );
					$value['data']->set_price( $custom_price );
				}
			}
		}
	}

	/**
	 * Bump order product title shortcode.
	 *
	 * @param array $atts shortcode atts.
	 * @return string shortcode output.
	 * @since 1.0.0
	 */
	public function bump_product_title( $atts ) {

		$output = '';
		if ( _is_wcf_checkout_type() ) {

			global $post;

			$order_bump_product = get_post_meta( $post->ID, 'wcf-order-bump-product', true );

			if ( ! empty( $order_bump_product ) ) {

				$product_id = reset( $order_bump_product );

				$output = get_the_title( $product_id );
			}
		}

		return $output;
	}

	/**
	 * Bump order product title shortcode.
	 *
	 * @param int $step_id step id.
	 * @return array bump order product.
	 * @since 1.0.0
	 */
	public function get_bump_test_product( $step_id ) {

		$bump_product = array();

		$args = array(
			'posts_per_page' => 1,
			'orderby'        => 'rand',
			'post_type'      => 'product',
			'meta_query'     => array(// phpcs:ignore
				// Exclude out of stock products.
				array(
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => 'NOT IN',
				),
			),
			'tax_query'      => array( //phpcs:ignore
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => 'simple',
				),
			),
		);

		$random_product = get_posts( $args );

		if ( isset( $random_product[0]->ID ) ) {
			$bump_product = array(
				$random_product[0]->ID,
			);
		}

		return $bump_product;
	}

	/**
	 * Check in Cart if product exists.
	 *
	 * @since 1.1.5
	 * @param int  $product_id product_id.
	 * @param bool $is_bump is bump product.
	 * @return bool.
	 * */
	public function cart_has_product( $product_id, $is_bump = false ) {

		$get_cart = WC()->cart->get_cart();

		foreach ( $get_cart as $cart_item_key => $cart_item ) {

			if ( $cart_item['product_id'] == $product_id ) {

				if ( $is_bump ) {

					if ( isset( $cart_item['cartflows_bump'] ) && $cart_item['cartflows_bump'] ) {
						return true;
					}
				} else {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get order bump data by ob id.
	 *
	 * @param int    $checkout_id checkout_id.
	 * @param string $ob_id order bump id.
	 * */
	public function get_order_bump_by_id( $checkout_id, $ob_id ) {

		$order_bumps = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bumps' );

		$ob_data = array();

		if ( ! empty( $ob_id ) && ! empty( $order_bumps ) ) {

			foreach ( $order_bumps as $index => $data ) {
				if ( $ob_id === $data['id'] ) {
					$ob_data = $data;
					break;
				}
			}
		}

		$ob_data['is_variable']  = '';
		$ob_data['is_variation'] = '';
		$ob_data['parent_id']    = $ob_data['product'];

		if ( ! empty( $ob_data ) && ! empty( $ob_data['product'] ) ) {

			$_product = wc_get_product( $ob_data['product'] );

			if ( $_product && $_product->is_type( 'variable' ) ) {

				$ob_data['is_variable'] = 'yes';
				$ob_data['parent_id']   = $ob_data['product'];

				$default_attributes = $_product->get_default_attributes();

				if ( ! empty( $default_attributes ) ) {

					foreach ( $_product->get_children() as $c_in => $variation_id ) {

						if ( 0 === $c_in ) {
							$ob_data['product'] = $variation_id;
						}

						$single_variation = new WC_Product_Variation( $variation_id );

						if ( $default_attributes == $single_variation->get_attributes() ) {

							$ob_data['product'] = $variation_id;
							break;
						}
					}
				} else {

					$product_childrens = $_product->get_children();

					if ( is_array( $product_childrens ) ) {

						foreach ( $product_childrens  as $c_in => $c_id ) {

							$ob_data['product'] = $c_id;
							break;
						}
					}
				}
			}

			if ( $_product && $_product->is_type( 'variation' ) ) {

				$ob_data['is_variation'] = 'yes';
				$ob_data['parent_id']    = $_product->get_parent_id();
			}

			// Get the product price and replace the vars with actual values.
			$product_object     = wc_get_product( $ob_data['product'] );
			$product_price      = $product_object ? $product_object->get_price( 'edit' ) : '';
			$custom_price       = wcf_pro()->utils->get_calculated_discount( $ob_data['discount_type'], $ob_data['discount_value'], $product_price );
			$product_price_data = $this->get_taxable_product_price( $product_object, $product_price, $custom_price );

			$ob_data['total_product_price'] = wc_get_price_including_tax( $product_object, array( 'price' => $product_price_data['custom_price'] ) );
		}

		return $ob_data;
	}
}


/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Order_Bump_Product::get_instance();
