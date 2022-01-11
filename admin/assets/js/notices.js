( function ( $ ) {
	var migrate_to_new_order_bump = function () {
		$( 'a.migrate-to-new-ob' ).on( 'click', function ( e ) {
			e.preventDefault();

			let content = $( this ).closest( '.wcf-notice-content' ),
				text = CartFlows_Pro_Common_Vars.ob_notice_text;

			var data = {
				action: 'cartflows_migrate_order_bump',
				security: CartFlows_Pro_Common_Vars.ob_migration_nonce,
			};

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: data,

				success: function ( response ) {
					if ( response.success ) {
						console.log(
							'Action scheduled for order bump migration.'
						);
						content.html( text );
					}
				},
			} );
		} );
	};

	$( function () {
		migrate_to_new_order_bump();
	} );
} )( jQuery );
