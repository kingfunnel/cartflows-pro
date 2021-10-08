( function ( $ ) {
	var wcf_toggle_optimized_fields = function () {
		jQuery.each(
			cartflows_optimized_fields,
			function ( field, cartflows_optimized_field ) {
				if ( cartflows_optimized_field.is_optimized ) {
					jQuery( '#' + field ).prepend(
						'<a href="#" id="wcf_optimized_' +
							field +
							'">' +
							cartflows_optimized_field.field_label +
							'</a>'
					);
					jQuery( '#wcf_optimized_' + field ).on(
						'click',
						function ( e ) {
							e.preventDefault();
							jQuery( '#' + field ).removeClass(
								'wcf-hide-field'
							);
							// jQuery("#" + field).removeClass('mt20')
							var field_id = field.replace( /_field/g, '' );
							$( '#' + field_id ).trigger( 'focus' );
							jQuery( this ).remove();
						}
					);
				}
			}
		);
	};

	var wcf_page_title_notification = {
		vars: {
			originalTitle: document.title,
			interval: null,
		},
		On: function ( notification, intervalSpeed ) {
			var _this = this;
			_this.vars.interval = setInterval(
				function () {
					document.title =
						_this.vars.originalTitle == document.title
							? notification
							: _this.vars.originalTitle;
				},
				intervalSpeed ? intervalSpeed : 1000
			);
		},
		Off: function () {
			clearInterval( this.vars.interval );
			document.title = this.vars.originalTitle;
		},
	};

	var wcf_update_product_options_cart_item_key = function ( e ) {
		$( document ).on(
			'wcf_cart_data_restored',
			function ( e, wcf_cart_data ) {
				if ( 'yes' === cartflows.is_product_options && wcf_cart_data ) {
					$( '.wcf-qty-row' ).each( function () {
						var product_data = $( this ).data( 'options' );
						if ( product_data ) {
							if ( product_data.unique_id in wcf_cart_data ) {
								product_data.cart_item_key =
									wcf_cart_data[ product_data.unique_id ];
								$( this ).attr(
									'data-options',
									JSON.stringify( product_data )
								);
							}
						}
					} );
				}
			}
		);
	};

	var wcf_animate_browser_tab = function () {
		if ( 'yes' !== cartflows_animate_tab_fields.enabled ) {
			return;
		}

		$( window ).blur( function () {
			wcf_page_title_notification.On(
				cartflows_animate_tab_fields.title
			);
		} );

		$( window ).on( 'focus', function () {
			wcf_page_title_notification.Off();
		} );
	};

	// Scroll to top for the two step navigation.
	var wcf_scroll_to_top = function ( scrollTo ) {
		if ( scrollTo.length ) {
			event.preventDefault();
			$( 'html, body' )
				.stop()
				.animate(
					{
						scrollTop: scrollTo.offset().top - 50,
					},
					100
				);
		}
	};

	var wcf_display_spinner = function () {
		$(
			'.woocommerce-checkout-review-order-table, .wcf-product-option-wrap, .wcf-bump-order-wrap'
		).block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6,
			},
		} );
	};

	var wcf_remove_spinner = function ( rsp ) {
		if ( jQuery( '.wc_payment_methods' ).length ) {

			if ( rsp.hasOwnProperty( 'cart_total' ) ) {
				
				// Update the Fragements for order bump & product options add/remove.
				if ( rsp.hasOwnProperty( 'fragments' ) ) {
					$.each( rsp.fragments, function ( key, value ) {
						$( key ).replaceWith( value );
					} );
				}

				if ( parseFloat( rsp.cart_total ) > 0 ) {
					$(
						'.woocommerce-checkout-review-order-table, .wcf-product-option-wrap, .wcf-bump-order-wrap'
					).unblock();
				} else {
					$( 'body' ).trigger( 'update_checkout' );
					$(
						'.woocommerce-checkout-review-order-table, .wcf-product-option-wrap, .wcf-bump-order-wrap'
					).unblock();
				}
			}
		} else {
			$( 'body' ).trigger( 'update_checkout' );
			$(
				'.woocommerce-checkout-review-order-table, .wcf-product-option-wrap, .wcf-bump-order-wrap'
			).unblock();
		}
	};

	var wcf_product_quantity_var_options = function () {
		var wcf_variation_validation_trigger_click = function () {
			$( 'form.woocommerce-checkout' ).on(
				'checkout_place_order',
				function ( e ) {
					var invalid_var = $(
						'.wcf-select-variation-attribute.wcf-invalid-variation'
					);

					if ( invalid_var.length > 0 ) {
						e.preventDefault();
						wcf_scroll_to_top( invalid_var );
						return ! 1;
					}
				}
			);

			$( '.wcf-select-variation-attribute' ).on( 'click', function ( e ) {
				e.preventDefault();

				var wrap = $( this ).closest( '.wcf-qty-row' );

				wrap.find( '.wcf-item-choose-options a' ).trigger( 'click' );
			} );
		};

		/* Single Selection */
		$( document ).on( 'change', '.wcf-single-sel', function () {
			var $this = $( this );
			var wrap = $this.closest( '.wcf-qty-row' );
			var option = wrap.data( 'options' );
			var input = wrap.find( '.wcf-qty input' );
			var input_quantity = parseInt( input.val() );
			var checkout_id = $( '._wcf_checkout_id' ).val();

			if ( 0 >= input_quantity || isNaN( input_quantity ) ) {
				input.val( 1 );
				input_quantity = 1;
			}

			option[ 'input_quantity' ] = input_quantity;
			option[ 'checkout_id' ] = checkout_id;

			wcf_display_spinner();
			$.ajax( {
				url: cartflows.ajax_url,
				data: {
					action: 'wcf_single_selection',
					option: option,
					security: cartflows.wcf_single_selection_nonce,
				},
				dataType: 'json',
				type: 'POST',
				success: function ( response ) {
					if ( response.hasOwnProperty( 'cartflows_data' ) ) {
						wrap.find( '.wcf-display-quantity' ).html(
							response.cartflows_data.display_quantity
						);
						wrap.find( '.wcf-display-price' ).html(
							response.cartflows_data.display_price
						);
						wrap.find( '.wcf-display-discount-value' ).html(
							response.cartflows_data.display_discount_value
						);
						wrap.find( '.wcf-display-discount-percent' ).html(
							response.cartflows_data.display_discount_percent
						);

						wrap.find( '.wcf_subscription_price' ).html(
							response.cartflows_data.subscription_price
						);
						wrap.find( '.wcf_subscription_fee' ).html(
							response.cartflows_data.sign_up_fee
						);
						console.log( response );
					}

					wcf_remove_spinner( response );
					option[ 'cart_item_key' ] = response.cart_item_key;
					wrap.attr( 'data-options', JSON.stringify( option ) );

					// Re-calculate the cart total
					$( document.body ).trigger( 'updated_cart_totals' );
				},
				error: function ( error ) {
					$( '.woocommerce-checkout-review-order-table' ).unblock();
				},
			} );
		} );

		/* Multiple Selection */
		$( document ).on( 'change', '.wcf-multiple-sel', function () {
			var checked_cb = $( '.wcf-multiple-sel:checked' );
			var $this = $( this );
			var wrap = $this.closest( '.wcf-qty-row' );
			var option = wrap.data( 'options' );
			var input = wrap.find( '.wcf-qty input' );
			var input_quantity = parseInt( input.val() );
			var checkout_id = $( '._wcf_checkout_id' ).val();

			if ( 0 >= input_quantity || isNaN( input_quantity ) ) {
				input.val( 1 );
				input_quantity = 1;
			}

			if ( 0 == checked_cb.length ) {
				$this.prop( 'checked', true );
				$this.prop( 'disabled', true );
				return;
			}

			if ( 1 == checked_cb.length ) {
				checked_cb.prop( 'disabled', true );
			} else {
				checked_cb.removeAttr( 'disabled' );
			}

			option[ 'checkout_id' ] = checkout_id;
			option[ 'input_quantity' ] = input_quantity;
			option[ 'checked' ] = 'no';

			if ( $this.is( ':checked' ) ) {
				option[ 'checked' ] = 'yes';
			}

			wcf_display_spinner();

			$.ajax( {
				url: cartflows.ajax_url,
				data: {
					action: 'wcf_multiple_selection',
					option: option,
					security: cartflows.wcf_multiple_selection_nonce,
				},
				dataType: 'json',
				type: 'POST',
				success: function ( response ) {
					if ( response.hasOwnProperty( 'cartflows_data' ) ) {
						wrap.find( '.wcf-display-quantity' ).html(
							response.cartflows_data.display_quantity
						);
						wrap.find( '.wcf-display-price' ).html(
							response.cartflows_data.display_price
						);
						wrap.find( '.wcf-display-discount-value' ).html(
							response.cartflows_data.display_discount_value
						);
						wrap.find( '.wcf-display-discount-percent' ).html(
							response.cartflows_data.display_discount_percent
						);

						wrap.find( '.wcf_subscription_price' ).html(
							response.cartflows_data.subscription_price
						);
						wrap.find( '.wcf_subscription_fee' ).html(
							response.cartflows_data.sign_up_fee
						);
					}

					wcf_remove_spinner( response );
					option[ 'cart_item_key' ] = response.cart_item_key;
					wrap.attr( 'data-options', JSON.stringify( option ) );

					// Re-calculate the cart total
					$( document.body ).trigger( 'updated_cart_totals' );
				},
				error: function ( error ) {
					$( '.woocommerce-checkout-review-order-table' ).unblock();
				},
			} );
		} );

		/* Force All Selection */
		$( document ).on( 'change', '.wcf-var-sel', function () {
			var $this = $( this );
			var wrap = $this.closest( '.wcf-qty-row' );
			var input = wrap.find( '.wcf-qty input' );
			var option = wrap.data( 'options' );
			var input_quantity = parseInt( input.val() );
			var checkout_id = $( '._wcf_checkout_id' ).val();

			option[ 'checkout_id' ] = checkout_id;
			option[ 'input_quantity' ] = input_quantity;

			wcf_display_spinner();

			$.ajax( {
				url: cartflows.ajax_url,
				data: {
					action: 'wcf_variation_selection',
					option: option,
					security: cartflows.wcf_variation_selection_nonce,
				},
				dataType: 'json',
				type: 'POST',
				success: function ( response ) {
					if ( response.hasOwnProperty( 'cartflows_data' ) ) {
						wrap.find( '.wcf-display-quantity' ).html(
							response.cartflows_data.display_quantity
						);
						wrap.find( '.wcf-display-price' ).html(
							response.cartflows_data.display_price
						);
						wrap.find( '.wcf-display-discount-value' ).html(
							response.cartflows_data.display_discount_value
						);
						wrap.find( '.wcf-display-discount-percent' ).html(
							response.cartflows_data.display_discount_percent
						);

						wrap.find( '.wcf_subscription_price' ).html(
							response.cartflows_data.subscription_price
						);
						wrap.find( '.wcf_subscription_fee' ).html(
							response.cartflows_data.sign_up_fee
						);
					}

					wcf_remove_spinner( response );
					// Re-calculate the cart total
					$( document.body ).trigger( 'updated_cart_totals' );
				},
				error: function ( error ) {
					$( '.woocommerce-checkout-review-order-table' ).unblock();
				},
			} );
		} );

		/* Quantity Selection For All type */
		$( document ).on( 'change', '.wcf-qty-selection', function () {
			var $this = $( this ),
				wrap = $this.closest( '.wcf-qty-row' ),
				item_selector = wrap.find( '.wcf-item-selector' ),
				option = wrap.data( 'options' ),
				input_quantity = parseInt( $this.val() ),
				checkout_id = $( '._wcf_checkout_id' ).val();

			if ( item_selector.length > 0 ) {
				var selector_input = item_selector.find( 'input' );

				if (
					selector_input.length > 0 &&
					! selector_input.is( ':checked' )
				) {
					return;
				}
			}

			if ( 0 >= input_quantity || isNaN( input_quantity ) ) {
				$this.val( 1 );
				input_quantity = 1;
			}

			option[ 'input_quantity' ] = input_quantity;
			option[ 'checkout_id' ] = checkout_id;

			if ( typeof data != 'undefined' ) {
				option[ 'cart_item_key' ] = data[ 'cart_item_key' ];
			}

			wcf_display_spinner();

			$.ajax( {
				url: cartflows.ajax_url,
				data: {
					action: 'wcf_quantity_update',
					option: option,
					security: cartflows.wcf_quantity_update_nonce,
				},
				dataType: 'json',
				type: 'POST',
				success: function ( response ) {
					if ( response.hasOwnProperty( 'cartflows_data' ) ) {
						wrap.find( '.wcf-display-quantity' ).html(
							response.cartflows_data.display_quantity
						);
						wrap.find( '.wcf-display-price' ).html(
							response.cartflows_data.display_price
						);
						wrap.find( '.wcf-display-discount-value' ).html(
							response.cartflows_data.display_discount_value
						);
						wrap.find( '.wcf-display-discount-percent' ).html(
							response.cartflows_data.display_discount_percent
						);

						wrap.find( '.wcf_subscription_price' ).html(
							response.cartflows_data.subscription_price
						);
						wrap.find( '.wcf_subscription_fee' ).html(
							response.cartflows_data.sign_up_fee
						);
					}

					wcf_remove_spinner( response );
					// Re-calculate the cart total
					$( document.body ).trigger( 'updated_cart_totals' );
				},
				error: function ( error ) {
					$( '.woocommerce-checkout-review-order-table' ).unblock();
				},
			} );
		} );

		/* Variation attribute and click trigger */
		wcf_variation_validation_trigger_click();

		/* Variation Popup */
		wcf_quick_view();
	};

	var wcf_do_not_remove_single_product = function () {
		var checked_cb = $( '.wcf-multiple-sel:checked' );
		var $this = $( this );

		if ( 1 == checked_cb.length ) {
			checked_cb.prop( 'checked', true );
			checked_cb.prop( 'disabled', true );
			return;
		}
	};

	var wcf_remove_product = function () {
		$( document ).on( 'cartflows_remove_product', function ( e, p_id ) {
			jQuery( '.wcf-multiple-sel[value="' + p_id + '"]' ).prop(
				'checked',
				false
			);
			jQuery( '.wcf-single-sel[value="' + p_id + '"]' ).prop(
				'checked',
				false
			);
		} );
	};

	var wcf_order_bump_ajax = function () {
		var wcf_order_bump_clicked = false;

		$( document ).on( 'change', '.wcf-bump-order-cb', function ( e ) {
			if ( true === wcf_order_bump_clicked ) {
				return false;
			}

			wcf_order_bump_clicked = true;

			var $this = $( this );
			var product_id = $this.val();
			var checkout_id = $( '[name=_wcf_checkout_id]' ).val();
			var bump_offer_data = $this.attr( 'data-ob_data' ),
				ob_id = $this.data( 'ob_data' ).ob_id,
				product_price = $this.data( 'ob_data' ).total_product_price;

			var button_class = $this
				.closest( '.wcf-bump-order-field-wrap' )
				.find( '.wcf-bump-order-cb-button' );
			var bump_offer_wrap = $this.closest( '.wcf-bump-order-wrap' );

			var is_add_to_cart = '';

			var data = {
				security: cartflows.wcf_bump_order_process_nonce,
				_wcf_checkout_id: checkout_id,
				_wcf_product_id: product_id,
				_bump_offer_data: bump_offer_data ? bump_offer_data : '',
				action: 'wcf_bump_order_process',
			};

			var bump_product_ids = $( '[name=_wcf_bump_products]' ).val();

			if ( '' === bump_product_ids ) {
				bump_product_ids = {};
			} else {
				bump_product_ids = JSON.parse( bump_product_ids );
			}

			if ( $this.is( ':checked' ) ) {
				data._wcf_bump_product_action = 'add_bump_product';
				is_add_to_cart = true;
			} else {
				data._wcf_bump_product_action = 'remove_bump_product';
				is_add_to_cart = false;
			}

			// Display spinner for specific order bump.
			bump_offer_wrap.block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6,
				},
			} );

			$.ajax( {
				url: cartflows.ajax_url,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function ( response ) {
					wcf_remove_spinner( response );

					if ( button_class.length > 0 ) {
						if ( is_add_to_cart ) {
							button_class.toggleClass(
								'wcf-bump-add-to-cart wcf-bump-remove-from-cart'
							);
							button_class.text( 'Remove' );
						} else {
							button_class.toggleClass(
								'wcf-bump-remove-from-cart wcf-bump-add-to-cart'
							);
							button_class.text( 'Add' );
						}
					}

					if ( $this.is( ':checked' ) ) {
						bump_product_ids[ ob_id ] = {
							id: product_id,
							price: response.cartflows_data.total_product_price,
						};
					} else {
						delete bump_product_ids[ ob_id ];
					}
					$( '[name=_wcf_bump_products]' ).val(
						JSON.stringify( bump_product_ids )
					);
				},
				error: function ( error ) {
					$( '.woocommerce-checkout-review-order-table' ).unblock();
				},
			} );

			wcf_order_bump_clicked = false;

			return false;
		} );
	};

	var wcf_nav_tab_hide_show_events = function () {
		/* Ready */
		wcf_nav_tab_hide_show();

		$( '.wcf-embed-checkout-form-two-step .woocommerce' ).addClass(
			'step-one'
		);

		/* Change Custom Field*/
		$(
			'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps a'
		).on( 'click', function ( e ) {
			e.preventDefault();
			wcf_nav_tab_hide_show();
		} );

		/* Change on click of next button */
		$(
			'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav-btns a'
		).on( 'click', function ( e ) {
			e.preventDefault();

			// Check form validation before go to step two.

			wcf_nav_tab_hide_show_next_btn();
		} );
	};

	var wcf_nav_tab_hide_show_next_btn = function () {
		if ( wcf_two_step_validations() == 'true' ) {
			$(
				'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps div.wcf-current'
			).removeClass( 'wcf-current' );

			var selector = $(
					'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav-btns a'
				).attr( 'href' ),
				scrollTo = '';

			if ( selector == '#customer_details' ) {
				wcf_scroll_to_top( $( '.wcf-embed-checkout-form-nav' ) );
				$(
					'.wcf-embed-checkout-form-two-step .woocommerce'
				).removeClass( 'step-two' );
				$( '.wcf-embed-checkout-form-two-step .woocommerce' ).addClass(
					'step-one'
				);
				$( '.wcf-embed-checkout-form-two-step' )
					.find( '.step-one' )
					.addClass( 'wcf-current' );
			} else if ( selector == '#wcf-order-wrap' ) {
				wcf_scroll_to_top( $( '.wcf-embed-checkout-form-nav' ) );
				$(
					'.wcf-embed-checkout-form-two-step .woocommerce'
				).removeClass( 'step-one' );
				$( '.wcf-embed-checkout-form-two-step .woocommerce' ).addClass(
					'step-two'
				);
				$( '.wcf-embed-checkout-form-two-step' )
					.find( '.step-two' )
					.addClass( 'wcf-current' );
			}
		}
	};

	var wcf_nav_tab_hide_show = function () {
		$(
			'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps a'
		).on( 'click', function ( e ) {
			e.preventDefault();

			// Check form validation before go to step two.
			// if ( $("div").hasClass("wcf-embed-checkout-form-two-step") && ! $('form[name="checkout"]').valid() ) {
			// 	return false;
			// }

			if ( wcf_two_step_validations() == 'true' ) {
				var $this = $( this ),
					wrap = $this.closest(
						'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps div'
					);
				// 	validated = wcf_woocommerce_field_validate();

				// if(validated == false){
				// 	return false;
				// }

				$(
					'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps div.wcf-current'
				).removeClass( 'wcf-current' );

				wrap.addClass( 'wcf-current' );

				var selector = $this
					.closest(
						'.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps div a'
					)
					.attr( 'href' );
				if ( selector == '#customer_details' ) {
					$(
						'.wcf-embed-checkout-form-two-step .woocommerce'
					).removeClass( 'step-two' );
					$(
						'.wcf-embed-checkout-form-two-step .woocommerce'
					).addClass( 'step-one' );
				} else if ( selector == '#wcf-order-wrap' ) {
					$(
						'.wcf-embed-checkout-form-two-step .woocommerce'
					).removeClass( 'step-one' );
					$(
						'.wcf-embed-checkout-form-two-step .woocommerce'
					).addClass( 'step-two' );
				}
			}
		} );
	};

	/**
	 * Label Animation
	 */
	var wcf_anim_field_label = function () {
		var $inputs = $(
			'.wcf-field-style-one form.woocommerce-checkout, .wcf-field-style-one form.woocommerce-form-login'
		).find( 'input' );
		var $select_input = $(
			'.wcf-field-style-one form.woocommerce-checkout'
		).find( '.select2' );
		var $textarea = $(
			'.wcf-field-style-one form.woocommerce-checkout'
		).find( 'textarea' );

		//Add focus class on clicked on input types
		$inputs.on( 'focus', function () {
			var $this = $( this ),
				field_row = $this.closest( '.form-row' );
			has_class = field_row.hasClass( 'wcf-anim-label' );
			field_value = $this.val();

			if ( field_value == '' ) {
				field_row.addClass( 'wcf-anim-label' );
			}
		} );

		//Remove focus class on clicked outside/other input types
		$inputs.on( 'focusout', function () {
			var $this = $( this ),
				field_row = $this.closest( '.form-row' );
			has_class = field_row.hasClass( 'wcf-anim-label' );
			field_value = $this.val();

			if ( field_value == '' ) {
				field_row.removeClass( 'wcf-anim-label' );
			} else {
				field_row.addClass( 'wcf-anim-label' );
			}
		} );

		//Add focus class on clicked on Select
		$select_input.on( 'click', function () {
			var $this = $( this ),
				field_row = $this.closest( '.form-row' );
			has_class = field_row.hasClass( 'wcf-anim-label' );
			field_value = $this.find( '.select2-selection__rendered' ).text();

			if ( field_value == '' ) {
				field_row.addClass( 'wcf-anim-label' );
			}
		} );

		//Remove focus class on clicked outside/another Select or fields
		$select_input.on( 'focusout', function () {
			var $this = $( this ),
				field_row = $this.closest( '.form-row' );
			has_class = field_row.hasClass( 'wcf-anim-label' );
			field_value = $this.find( '.select2-selection__rendered' ).text();

			if ( field_value == '' ) {
				field_row.removeClass( 'wcf-anim-label' );
			} else {
				field_row.addClass( 'wcf-anim-label' );
			}
		} );

		//Add focus class on clicked on textarea
		$textarea.on( 'click', function () {
			var $this = $( this ),
				field_row = $this.closest( '.form-row' );
			has_class = field_row.hasClass( 'wcf-anim-label' );
			field_value = $this.val();

			if ( field_value == '' ) {
				field_row.addClass( 'wcf-anim-label' );
			}
		} );

		//Remove focus class on clicked outside/another textarea or fields
		$textarea.on( 'focusout', function () {
			var $this = $( this ),
				field_row = $this.closest( '.form-row' );
			has_class = field_row.hasClass( 'wcf-anim-label' );
			field_value = $this.val();

			if ( field_value == '' ) {
				field_row.removeClass( 'wcf-anim-label' );
			} else {
				field_row.addClass( 'wcf-anim-label' );
			}
		} );
	};

	var wcf_anim_field_label_event = function () {
		//Add focus class automatically if value is present in input
		var $all_inputs = $(
			'.wcf-field-style-one form.woocommerce-checkout'
		).find( 'input' );

		$( $all_inputs ).each( function ( index ) {
			var $this = $( this ),
				field_type = $this.attr( 'type' ),
				form_row = $this.closest( '.form-row' ),
				has_class = form_row.hasClass( 'mt20' ),
				input_elem_value = $this.val();

			_add_anim_class(
				has_class,
				input_elem_value,
				field_type,
				form_row
			);
		} );

		//Add focus class automatically if value is present in selects
		var $all_selects = $(
			'.wcf-field-style-one form.woocommerce-checkout'
		).find( 'select' );

		$( $all_selects ).each( function ( index ) {
			var $this = $( this ),
				form_row = $this.closest( '.form-row' ),
				field_type = 'select',
				has_class = form_row.hasClass( 'mt20' ),
				input_elem_value = $this.val();

			_add_anim_class(
				has_class,
				input_elem_value,
				field_type,
				form_row
			);
		} );

		// Common function to add wcf-anim-label
		function _add_anim_class(
			has_class,
			input_elem_value,
			field_type,
			form_row
		) {
			if ( has_class ) {
				form_row.removeClass( 'mt20' );
			}

			if (
				input_elem_value !== '' ||
				( input_elem_value !== ' ' && 'select' === field_type )
			) {
				form_row.addClass( 'wcf-anim-label' );
			}

			if ( field_type === 'hidden' ) {
				form_row.removeClass( 'wcf-anim-label' );
				form_row.addClass( 'wcf-anim-label-fix' );
			}
		}
	};

	/* Autocomplete Zip Code */
	var wcf_autocomplete_zip_data = function () {
		var zip_code_timeout;

		$( document.body ).on(
			'textInput input change keypress paste',
			'#billing_postcode, #shipping_postcode',
			function ( e ) {
				var $this = $( this ),
					type = $this.attr( 'id' ).split( '_' )[ 0 ],
					zip_code = $this.val().trim(),
					country = $( '#' + type + '_country' ).val();

				if ( '' === country ) {
					return;
				}

				if ( '' === zip_code ) {
					return;
				}

				clearTimeout( zip_code_timeout );

				zip_code_timeout = setTimeout( function () {
					if ( -1 === [ 'GB', 'CA' ].indexOf( country ) ) {
						get_zip_data_and_update( type, country, zip_code );
					}
				}, 800 );
			}
		);

		var get_zip_data_and_update = function ( type, country, zip_code ) {
			$.ajax( {
				url: 'https://api.zippopotam.us/' + country + '/' + zip_code,
				cache: ! 0,
				dataType: 'json',
				type: 'GET',
				success: function ( result, status ) {
					$.each( result.places, function ( e, t ) {
						$( '#' + type + '_city' )
							.val( this[ 'place name' ] )
							.trigger( 'change' );
						$( '[name="' + type + '_state"]:visible' )
							.val( this[ 'state abbreviation' ] )
							.trigger( 'change' );
						return false;
					} );
				},
				error: function ( e, t ) {},
			} );
		};
	};

	/**
	 * Quick View
	 */
	var wcf_quick_view = function () {
		var quick_view_btn = $( '.wcf-item-choose-options a' );

		var modal_wrap = $( '.wcf-quick-view-wrapper' );

		modal_wrap.appendTo( document.body );

		var wcf_quick_view_bg = modal_wrap.find( '.wcf-quick-view-bg' ),
			wcf_qv_modal = modal_wrap.find( '#wcf-quick-view-modal' ),
			wcf_qv_content = wcf_qv_modal.find( '#wcf-quick-view-content' ),
			wcf_qv_close_btn = wcf_qv_modal.find( '#wcf-quick-view-close' ),
			wcf_qv_wrapper = wcf_qv_modal.find( '.wcf-content-main-wrapper' ),
			wcf_qv_wrapper_w = wcf_qv_wrapper.width(),
			wcf_qv_wrapper_h = wcf_qv_wrapper.height();

		quick_view_btn.off( 'click' ).on( 'click', function ( e ) {
			e.preventDefault();

			var $this = $( this );

			/* Check if product is selected */
			var cls_wrap = $this.closest( '.wcf-item' );

			if (
				! cls_wrap.find( '.wcf-item-selector input' ).is( ':checked' )
			) {
				cls_wrap.find( '.wcf-item-selector input' ).trigger( 'click' );
			}

			var product_id = $this.data( 'product' );

			$this.addClass( 'wcf-variation-popup-open' );

			if ( ! wcf_qv_modal.hasClass( 'loading' ) ) {
				wcf_qv_modal.addClass( 'loading' );
			}

			if ( ! wcf_quick_view_bg.hasClass( 'wcf-quick-view-bg-ready' ) ) {
				wcf_quick_view_bg.addClass( 'wcf-quick-view-bg-ready' );
			}

			$( document ).trigger( 'wcf_quick_view_loading' );

			wcf_qv_ajax_call( $this, product_id );
		} );

		var wcf_qv_ajax_call = function ( t, product_id ) {
			wcf_qv_modal.css( 'opacity', 0 );

			$.ajax( {
				url: cartflows.ajax_url,
				data: {
					action: 'wcf_woo_quick_view',
					product_id: product_id,
				},
				dataType: 'html',
				type: 'POST',
				success: function ( data ) {
					wcf_qv_content.html( data );
					wcf_qv_content_height();
				},
			} );
		};

		var wcf_qv_content_height = function () {
			// Variation Form
			var form_variation = wcf_qv_content.find( '.variations_form' );

			form_variation.trigger( 'check_variations' );
			form_variation.trigger( 'reset_image' );

			if ( ! wcf_qv_modal.hasClass( 'open' ) ) {
				wcf_qv_modal.removeClass( 'loading' ).addClass( 'open' );

				var scrollbar_width = wcf_get_scrollbar_width();
				var $html = $( 'html' );

				$html.css( 'margin-right', scrollbar_width );
				$html.addClass( 'wcf-quick-view-is-open' );
			}

			if (
				form_variation.length > 0 &&
				'function' === typeof form_variation.wc_variation_form
			) {
				form_variation.wc_variation_form();
				form_variation.find( 'select' ).trigger( 'change' );
			}

			/*wcf_qv_content.imagesLoaded( function(e) {

				var image_slider_wrap = wcf_qv_modal.find('.wcf-qv-image-slider');

				if ( image_slider_wrap.find('li').length > 1 ) {
					image_slider_wrap.flexslider({
						animation: "slide",
						start: function( slider ){
							setTimeout(function() {
								wcf_update_summary_height( true );
							}, 300);
						},
					});
				}else{
					setTimeout(function() {
						wcf_update_summary_height( true );
					}, 300);
				}
			});*/

			var image_slider_wrap = wcf_qv_modal.find( '.wcf-qv-image-slider' );

			if ( image_slider_wrap.find( 'li' ).length > 1 ) {
				image_slider_wrap.flexslider( {
					animation: 'slide',
					start: function ( slider ) {
						setTimeout( function () {
							wcf_update_summary_height( true );
						}, 300 );
					},
				} );
			} else {
				setTimeout( function () {
					wcf_update_summary_height( true );
				}, 300 );
			}

			// stop loader
			$( document ).trigger( 'wcf_quick_view_loader_stop' );
		};

		var wcf_qv_close_modal = function () {
			// Close box by click overlay
			wcf_qv_wrapper.on( 'click', function ( e ) {
				if ( this === e.target ) {
					wcf_qv_close();
				}
			} );

			// Close box with esc key
			$( document ).on( 'keyup', function ( e ) {
				if ( e.keyCode === 27 ) {
					wcf_qv_close();
				}
			} );

			// Close box by click close button
			wcf_qv_close_btn.on( 'click', function ( e ) {
				e.preventDefault();
				wcf_qv_close();
			} );

			var wcf_qv_close = function () {
				wcf_quick_view_bg.removeClass( 'wcf-quick-view-bg-ready' );
				wcf_qv_modal.removeClass( 'open' ).removeClass( 'loading' );
				$( 'html' ).removeClass( 'wcf-quick-view-is-open' );
				$( 'html' ).css( 'margin-right', '' );

				quick_view_btn.removeClass( 'wcf-variation-popup-open' );

				setTimeout( function () {
					wcf_qv_content.html( '' );
				}, 600 );
			};
		};

		/*var	ast_qv_center_modal = function() {

			ast_qv_wrapper.css({
				'width'     : '',
				'height'    : ''
			});

			ast_qv_wrapper_w 	= ast_qv_wrapper.width(),
			ast_qv_wrapper_h 	= ast_qv_wrapper.height();

			var window_w = $(window).width(),
				window_h = $(window).height(),
				width    = ( ( window_w - 60 ) > ast_qv_wrapper_w ) ? ast_qv_wrapper_w : ( window_w - 60 ),
				height   = ( ( window_h - 120 ) > ast_qv_wrapper_h ) ? ast_qv_wrapper_h : ( window_h - 120 );

			ast_qv_wrapper.css({
				'left' : (( window_w/2 ) - ( width/2 )),
				'top' : (( window_h/2 ) - ( height/2 )),
				'width'     : width + 'px',
				'height'    : height + 'px'
			});
		};

		*/
		var wcf_update_summary_height = function ( update_css ) {
			var quick_view = wcf_qv_content,
				img_height = quick_view
					.find( '.product .wcf-qv-image-slider' )
					.first()
					.height(),
				summary = quick_view.find( '.product .summary.entry-summary' ),
				content = summary.css( 'content' );

			if (
				'undefined' != typeof content &&
				544 == content.replace( /[^0-9]/g, '' ) &&
				0 != img_height &&
				null !== img_height
			) {
				summary.css( 'height', img_height );
			} else {
				summary.css( 'height', '' );
			}

			if ( true === update_css ) {
				wcf_qv_modal.css( 'opacity', 1 );
			}
		};

		var wcf_get_scrollbar_width = function () {
			var div = $(
				'<div style="width:50px;height:50px;overflow:hidden;position:absolute;top:-200px;left:-200px;"><div style="height:100px;"></div>'
			);
			// Append our div, do our calculation and then remove it
			$( 'body' ).append( div );
			var w1 = $( 'div', div ).innerWidth();
			div.css( 'overflow-y', 'scroll' );
			var w2 = $( 'div', div ).innerWidth();
			$( div ).remove();

			return w1 - w2;
		};

		wcf_qv_close_modal();
		//wcf_update_summary_height();

		window.addEventListener( 'resize', function ( event ) {
			wcf_update_summary_height();
		} );

		/* Add to cart ajax */
		/**
		 * wcf_add_to_cart_ajax class.
		 */
		var wcf_add_to_cart_ajax = function () {
			modal_wrap
				.off(
					'click',
					'#wcf-quick-view-content .single_add_to_cart_button'
				)
				.off( 'wcf_added_to_cart' )
				.on(
					'click',
					'#wcf-quick-view-content .single_add_to_cart_button',
					this.onAddToCart
				)
				.on( 'wcf_added_to_cart', this.updateButton );
		};

		/**
		 * Handle the add to cart event.
		 */
		wcf_add_to_cart_ajax.prototype.onAddToCart = function ( e ) {
			e.preventDefault();
			var $form = $( this ).closest( 'form' );

			// If the form inputs are invalid
			if ( ! $form[ 0 ].checkValidity() ) {
				$form[ 0 ].reportValidity();
				return false;
			}

			var $thisbutton = $( this ),
				product_id =
					$form.find( 'input[name="product_id"]' ).val() || '',
				variation_id =
					$form.find( 'input[name="variation_id"]' ).val() || '',
				choose_var = $( '.wcf-variation-popup-open' ),
				qty_wrap = choose_var.closest( '.wcf-qty-row' ),
				qty_selection = qty_wrap.find( '.wcf-qty-selection' ),
				input_quantity = qty_selection.val() || 1,
				qty_options = qty_wrap.data( 'options' ),
				checkout_id = $( '._wcf_checkout_id' ).val(),
				item_wrap = qty_wrap.find( '.wcf-item-wrap' );

			qty_options[ 'input_quantity' ] = input_quantity;
			qty_options[ 'checkout_id' ] = checkout_id;

			if ( $thisbutton.is( '.single_add_to_cart_button' ) ) {
				$thisbutton.removeClass( 'added' );
				$thisbutton.addClass( 'loading' );

				// Ajax action.
				if ( variation_id != '' ) {
					jQuery.ajax( {
						url: cartflows.ajax_url,
						type: 'POST',
						data: {
							action: 'wcf_add_cart_single_product',
							form_data: $form.serialize(),
							product_id: product_id,
							variation_id: variation_id,
							quantity: input_quantity,
							option: qty_options,
							security: cartflows.wcf_quick_view_add_cart_nonce,
						},
						dataType: 'json',
						success: function ( response ) {
							console.log( response );

							if (
								response.hasOwnProperty( 'cartflows_data' ) &&
								'yes' === response.cartflows_data.added_to_cart
							) {
								result = response.cartflows_data;

								/* Update Attributes to Name in summary */
								choose_var
									.closest( '.wcf-item' )
									.find( '.wcf-display-attributes' )
									.html( result.display_attr );

								choose_var
									.closest( '.wcf-item' )
									.find( '.wcf-item-image' )
									.html( result.variation_image );

								/* Update Variaiton id in attributes */
								choose_var.attr(
									'data-variation',
									result.variation_id
								);
								qty_options.variation_id = result.variation_id;
								qty_options.original_price =
									result.original_price;
								qty_options.discounted_price =
									result.discounted_price;

								qty_options.subscription_price =
									result.subscription_price;
								qty_options.sign_up_fee = result.signup_fee;
								qty_wrap.attr(
									'data-options',
									JSON.stringify( qty_options )
								);

								/* Item selector */
								var var_selection = qty_wrap.find(
									'.wcf-item-selector'
								);

								if ( var_selection.length > 0 ) {
									var var_options = qty_wrap.data(
										'options'
									);

									var_options.variation_id =
										result.variation_id;

									qty_wrap.attr(
										'data-options',
										JSON.stringify( var_options )
									);
								}

								/* Set display data */
								qty_wrap
									.find( '.wcf-display-quantity' )
									.html( result.display_quantity );
								qty_wrap
									.find( '.wcf-display-price' )
									.html( result.display_price );
								qty_wrap
									.find( '.wcf-display-discount-value' )
									.html( result.display_discount_value );
								qty_wrap
									.find( '.wcf-display-discount-percent' )
									.html( result.display_discount_percent );
								item_wrap
									.find( '.wcf_subscription_price' )
									.html( result.display_subscription_price );
								item_wrap
									.find( '.wcf_subscription_period' )
									.html(
										result.display_subscription_details
									);
								item_wrap
									.find( '.wcf_subscription_fee' )
									.html( result.display_signup_fee );
								item_wrap
									.find( '.wcf_subscription_free_trial' )
									.html( result.trial_period_string );
							}

							// Trigger event so themes can refresh other areas.
							$( document.body ).trigger( 'wc_fragment_refresh' );

							modal_wrap.trigger( 'wcf_added_to_cart', [
								$thisbutton,
							] );
						},
					} );
				} /*else {
					jQuery.ajax ({
						url: cartflows.ajax_url,
						type:'POST',
						data:'action=wcf_add_cart_single_product&product_id=' + product_id + '&quantity=' + quantity,

						success:function(results) {
							// Trigger event so themes can refresh other areas.
							$( document.body ).trigger( 'wc_fragment_refresh' );
							//modal_wrap.trigger( 'wcf_added_to_cart', [ $thisbutton ] );

							$( "body" ).trigger( "update_checkout" );

							wcf_qv_close_btn.trigger( 'click' );
						}
					});
				}*/
			}
		};

		/**
		 * Update cart page elements after add to cart events.
		 */
		wcf_add_to_cart_ajax.prototype.updateButton = function ( e, button ) {
			$( 'body' ).trigger( 'update_checkout' );

			wcf_qv_close_btn.trigger( 'click' );

			return;

			button = typeof button === 'undefined' ? false : button;

			if ( $( button ) ) {
				$( button ).removeClass( 'loading' );
				$( button ).addClass( 'added' );

				// View cart text.
				if (
					! cartflows.is_cart &&
					$( button ).parent().find( '.added_to_cart' ).length ===
						0 &&
					cartflows.is_single_product
				) {
					$( button ).after(
						' <a href="' +
							cartflows.cart_url +
							'" class="added_to_cart wc-forward" title="' +
							cartflows.view_cart +
							'">' +
							cartflows.view_cart +
							'</a>'
					);
				}
			}
		};

		/**
		 * Init wcf_add_to_cart_ajax.
		 */
		new wcf_add_to_cart_ajax();
	};

	var wcf_two_step_validations = function () {
		var $billing_inputs,
			$billing_select,
			$shipping_inputs,
			$shipping_select = [];

		$billing_inputs = $(
			'.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-billing-fields, .wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-account-fields'
		).find(
			'input[type="text"], input[type="tel"], input[type="email"], input[type="password"]'
		);

		$billing_chekboxes = $(
			'.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-billing-fields, .wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-account-fields'
		).find( 'input[type="checkbox"]' );

		$billing_select = $(
			'.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-billing-fields'
		).find( '.select2' );

		$shipping_inputs = $(
			'.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-shipping-fields'
		).find(
			'input[type="text"], input[type="tel"], input[type="email"], input[type="password"]'
		);

		$shipping_chekboxes = $(
			'.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-shipping-fields .woocommerce-shipping-fields__field-wrapper'
		).find( 'input[type="checkbox"]' );

		$shipping_select = $(
			'.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-shipping-fields'
		).find( '.select2' );

		var is_ship_to_diff = $(
			'.wcf-embed-checkout-form-two-step form.woocommerce-checkout'
		)
			.find(
				'h3#ship-to-different-address input[type="checkbox"]:checked'
			)
			.val();

		//Add focus class on clicked on input types
		var access = 'true',
			field_focus = '';

		Array.from( $billing_inputs ).forEach( function ( $this ) {
			var type = $this.type,
				name = $this.name,
				field_row = $this.closest( '.form-row' ),
				has_class = field_row.classList.contains( 'validate-required' ),
				field_value = $.trim( $this.value );
			// whiteSpace  = /\s/g.test(field_value);

			if ( name == 'account_password' || name == 'account_username' ) {
				var create_acc_checkbox = document.getElementById(
					'createaccount'
				);

				if ( create_acc_checkbox ) {
					if ( $( create_acc_checkbox ).is( ':checked' ) ) {
						has_class = true;
					} else {
						has_class = false;
					}
				} else {
					has_class = true;
				}
			}

			if ( has_class && '' == field_value ) {
				$this.classList.add( 'field-required' );
				access = 'false';
				if ( '' == field_focus ) {
					field_focus = $this;
				}
			} else {
				if (
					'email' == type &&
					false ==
						/^([a-zA-Z0-9_\+\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,14})$/.test(
							field_value
						)
				) {
					$this.classList.add( 'field-required' );
					access = 'false';

					if ( '' == field_focus ) {
						field_focus = $this;
					}
				}

				$this.classList.remove( 'field-required' );
			}
		} );

		Array.from( $billing_chekboxes ).forEach( function ( $this ) {
			var field_row = $this.closest( '.form-row' ),
				has_class = field_row.classList.contains( 'validate-required' ),
				field_value = false;

			if ( $( $this ).is( ':checked' ) ) {
				field_value = true;
			}

			if ( has_class && false == field_value ) {
				$this.classList.add( 'field-required' );
				access = 'false';
				if ( '' == field_focus ) {
					field_focus = $this;
				}
			} else {
				$this.classList.remove( 'field-required' );
			}
		} );

		Array.from( $billing_select ).forEach( function ( $this ) {
			var field_row = $this.closest( '.form-row' ),
				has_class = field_row.classList.contains( 'validate-required' ),
				field_value = $.trim(
					field_row.querySelector(
						'.select2-selection__rendered[title]'
					)
				);
			name = field_row.querySelector( 'select' ).name;

			if ( has_class && '' == field_value ) {
				$this.classList.add( 'field-required' );
				access = 'false';
				if ( '' == field_focus ) {
					field_focus = $this;
				}
			} else {
				$this.classList.remove( 'field-required' );
			}
		} );

		if ( 1 == is_ship_to_diff ) {
			Array.from( $shipping_inputs ).forEach( function ( $this ) {
				var type = $this.type,
					name = $this.name,
					field_row = $this.closest( '.form-row' );
				has_class = field_row.classList.contains( 'validate-required' );

				field_value = $.trim( $this.value );

				if ( has_class && '' == field_value ) {
					$this.classList.add( 'field-required' );
					access = 'false';

					if ( '' == field_focus ) {
						field_focus = $this;
					}
				} else {
					if (
						'email' == type &&
						false ==
							/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(
								field_value
							)
					) {
						$this.classList.add( 'field-required' );
						access = 'false';

						if ( '' == field_focus ) {
							field_focus = $this;
						}
					}

					$this.classList.remove( 'field-required' );
				}
			} );

			Array.from( $shipping_select ).forEach( function ( $this ) {
				var field_row = $this.closest( '.form-row' ),
					has_class = field_row.classList.contains(
						'validate-required'
					),
					field_value = $.trim(
						field_row.querySelector(
							'.select2-selection__rendered[title]'
						)
					);
				name = field_row.querySelector( 'select' ).name;

				whiteSpace = /\s/g.test( field_value );

				if ( has_class && '' == field_value ) {
					$this.classList.add( 'field-required' );
					access = 'false';

					if ( '' == field_focus ) {
						field_focus = $this;
					}
				} else {
					$this.classList.remove( 'field-required' );
				}
			} );

			Array.from( $shipping_chekboxes ).forEach( function ( $this ) {
				var field_row = $this.closest( '.form-row' ),
					has_class = field_row.classList.contains(
						'validate-required'
					),
					field_value = false;

				if ( $( $this ).is( ':checked' ) ) {
					field_value = true;
				}

				if ( has_class && false == field_value ) {
					$this.classList.add( 'field-required' );
					access = 'false';
					if ( '' == field_focus ) {
						field_focus = $this;
					}
				} else {
					$this.classList.remove( 'field-required' );
				}
			} );
		}

		// Focus the errored field
		if ( '' != field_focus ) {
			field_focus.trigger( 'focus' );
		}

		return access;
	};

	/*$(".wcf-qty [type='number']").on('keyup mouseup', function (event) {
		var input = jQuery(this).val();
		var input2 = input+jQuery(this).text();
		var step = jQuery(this).attr("step");
		var min = jQuery(this).attr("min");

		if(!Number.isNaN(input2)){
			if(parseInt(input2) > min){
				
				var remainder = input2%step;
				if(remainder != 0){
					var new_value = parseInt(input2)  -  remainder;
					jQuery(this).val(new_value);
					
				}
			}
		}
	}).trigger('mouseup');

	jQuery(".wcf-qty [type='number']").on('focusout',function (evt) {
		
		// var input = String.fromCharCode(evt.which);
	
		var input = jQuery(this).val();
		
		// var input_text = jQuery(this).text();
		// if ((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105)) {
			
		var min = jQuery(this).attr("min");

		if(!Number.isNaN(input)){
			if(parseInt(input) < min){
				jQuery(this).val(min);
			}

		}
	});*/

	var wcf_order_bump_buttons = function () {
		$( document ).on( 'click', '.wcf-bump-order-cb-button', function ( e ) {
			var $this = $( this ),
				parent_class = $this.closest( '.wcf-bump-order-action' ),
				input = parent_class.find( 'input[type="checkbox"]' );

			if ( $this.hasClass( 'wcf-bump-add-to-cart' ) ) {
				input.attr( 'checked', true );
				$this.text( 'Adding' );
			} else {
				input.attr( 'checked', false );

				$this.text( 'Removing' );
			}

			parent_class.find( '.wcf-bump-order-cb' ).trigger( 'change' );
		} );
	};

	$( function ( $ ) {
		wcf_remove_product();

		wcf_toggle_optimized_fields();

		wcf_animate_browser_tab();

		if ( 'yes' === cartflows.allow_autocomplete_zipcode ) {
			wcf_autocomplete_zip_data();
		}

		wcf_product_quantity_var_options();
		wcf_do_not_remove_single_product();
		wcf_order_bump_ajax();
		wcf_anim_field_label_event();
		wcf_anim_field_label();
		wcf_order_bump_buttons();

		if ( $( '.wcf-embed-checkout-form-two-step' ).length > 0 ) {
			wcf_nav_tab_hide_show_events();
		}

		//In multi checkout case we need to update the cart item key of the data-option for the product options.
		wcf_update_product_options_cart_item_key();
	} );
} )( jQuery );
