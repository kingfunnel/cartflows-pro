( function ( $ ) {
	var cartflows_offer_refund_init = function () {
		$( '.button.wcf-offer-refund' ).on( 'click', function ( e ) {
			e.preventDefault();

			var refund_reason = prompt(
				'Enter refund reason:',
				'CartFlows Offer Refund'
			);

			if ( '' === refund_reason ) {
				return alert( 'Please enter valid refund reason', false );
			} else if ( null === refund_reason ) {
				return false;
			}

			var $this = $( this ),
				main_order_id = $this.attr( 'data-order-id' ),
				step_id = $this.attr( 'data-step-id' ),
				offer_product_id = $this.attr( 'data-item-id' ),
				offer_product_qty = $this.attr( 'data-item-quantity' ),
				offer_product_amt = $this.attr( 'data-item-amount' ),
				transaction_id = $this.attr( 'data-transaction-id' ),
				data_api_refund = true,
				nonce = $( 'input[name="wcf_admin_refund_offer_nonce"]' ).val(),
				offer_product_qty = {};

			// Display the loading icon.
			$( '#wcf-offer-refund-metabox' ).block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6,
				},
			} );

			offer_product_qty[ offer_product_id ] = offer_product_qty;

			var refund_data = {
				action: 'wcf_admin_refund_offer',
				order_id: main_order_id,
				step_id: step_id,
				offer_amt: offer_product_amt,
				offer_id: offer_product_id,
				refund_reason: refund_reason,
				api_refund: data_api_refund,
				transaction_id: transaction_id,
				restock_refunded_items: true,
				cartflows_refund: true,
				security: nonce,
			};

			// Send data for the refund process.
			jQuery.ajax( {
				type: 'POST',
				dataType: 'json',
				url: ajaxurl,
				data: refund_data,
				success: function ( response ) {
					if ( true === response.success ) {
						console.log( response.msg );

						alert( 'Your offer product is refunded successfully' );

						$( '#wcf-offer-refund-metabox' ).unblock();
						window.location.reload();
					} else {
						console.log( response );

						alert( response.msg );

						$( '#wcf-offer-refund-metabox' ).unblock();
					}
				},
			} );
		} );
	};

	$( function () {
		cartflows_offer_refund_init();
	} );
} )( jQuery );
