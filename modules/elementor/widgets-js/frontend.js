( function ( $ ) {
	/**
	 * Search widget JS
	 */

	var WidgetCartflowsCheckoutForm = function ( $scope ) {
		if ( 'undefined' == typeof $scope ) return;

		var $wrapper = $scope.find( '.cartflows-elementor__checkout-form' );

		var $offer_wrap = $( 'body' ).find( '#wcf-pre-checkout-offer-modal' );

		var settings_data = $wrapper.data( 'settings-data' );

		var is_offer_enable = settings_data.enable_checkout_offer;

		var checkout_settings = {
			offer_title: [
				settings_data.title_text,
				'.wcf-content-modal-title h1',
			],
			offer_subtitle: [
				settings_data.subtitle_text,
				'.wcf-content-modal-sub-title span',
			],
			offer_product_name: [
				settings_data.product_name,
				'.wcf-pre-checkout-offer-product-title h1',
			],
			offer_product_desc: [
				settings_data.product_desc,
				'.wcf-pre-checkout-offer-desc span',
			],
			offer_accept_button: [
				settings_data.accept_button_text,
				'.wcf-pre-checkout-offer-btn-action.wcf-pre-checkout-add-cart-btn button',
			],
			offer_skip_button: [
				settings_data.skip_button_text,
				'.wcf-pre-checkout-offer-btn-action.wcf-pre-checkout-skip-btn .wcf-pre-checkout-skip',
			],
		};

		if ( 'yes' === is_offer_enable ) {
			$.each( checkout_settings, function ( key, value ) {
				var $control_name = value[ 0 ];
				var $selector = value[ 1 ];

				if ( '' !== $control_name ) {
					$offer_wrap.find( $selector ).html( $control_name );
				}
			} );
		}
	};

	$( window ).on( 'elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction(
			'frontend/element_ready/checkout-form.default',
			WidgetCartflowsCheckoutForm
		);
	} );
} )( jQuery );
