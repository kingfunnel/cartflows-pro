( function ( $ ) {
	/**
	 * Initialize selectWoo
	 */
	var wcf_initialize_selectwoo = function () {
		var select_fields = jQuery( '.form-row select' );

		$( select_fields ).each( function ( index ) {
			$( this ).selectWoo();
		} );
	};

	/**
	 * Floating Labels Animation
	 */
	var wcf_floating_labels = function () {
		if ( $( '.wcf-field-floating-labels' ).length < 1 ) {
			return;
		}

		var wcf_anim_field_label = function () {
			var wrapper = $( '.wcf-field-floating-labels' );
			var $inputs = wrapper.find( 'input' );
			var $select_input = wrapper.find( '.select2' );
			var $textarea = wrapper.find( 'textarea' );

			//Add focus class on clicked on input types
			$inputs.on( 'focus', function () {
				var $this = $( this ),
					field_row = $this.closest( '.form-row' );
				has_class = field_row.hasClass( 'wcf-anim-label' );
				field_value = $this.val();

				if ( field_value === '' ) {
					field_row.addClass( 'wcf-anim-label' );
				}
			} );

			//Remove focus class on clicked outside/other input types
			$inputs.on( 'focusout', function () {
				var $this = $( this ),
					field_row = $this.closest( '.form-row' );
				has_class = field_row.hasClass( 'wcf-anim-label' );
				field_value = $this.val();

				if ( field_value === '' ) {
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
				field_value = $this
					.find( '.select2-selection__rendered' )
					.text();

				if ( field_value === '' ) {
					field_row.addClass( 'wcf-anim-label' );
				}
			} );

			//Remove focus class on clicked outside/another Select or fields
			$select_input.on( 'focusout', function () {
				var $this = $( this ),
					field_row = $this.closest( '.form-row' );
				has_class = field_row.hasClass( 'wcf-anim-label' );
				field_value = $this
					.find( '.select2-selection__rendered' )
					.text();

				if ( field_value === '' ) {
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

				if ( field_value === '' ) {
					field_row.addClass( 'wcf-anim-label' );
				}
			} );

			//Remove focus class on clicked outside/another textarea or fields
			$textarea.on( 'focusout', function () {
				var $this = $( this ),
					field_row = $this.closest( '.form-row' );
				has_class = field_row.hasClass( 'wcf-anim-label' );
				field_value = $this.val();

				if ( field_value === '' ) {
					field_row.removeClass( 'wcf-anim-label' );
				} else {
					field_row.addClass( 'wcf-anim-label' );
				}
			} );
		};

		var wcf_anim_field_label_event = function () {
			var wrapper = $( '.wcf-field-floating-labels' );

			//Add focus class automatically if value is present in input
			var $all_inputs = wrapper.find( 'input' );

			$( $all_inputs ).each( function ( index ) {
				var $this = $( this ),
					field_type = $this.attr( 'type' ),
					form_row = $this.closest( '.form-row' ),
					input_elem_value = $this.val();

				$this.attr( 'placeholder', '' );

				_add_anim_class( input_elem_value, field_type, form_row );
			} );

			//Add focus class automatically if value is present in selects
			var $all_selects = wrapper.find( 'select' );

			$( $all_selects ).each( function ( index ) {
				var $this = $( this ),
					form_row = $this.closest( '.form-row' ),
					field_type = 'select',
					input_elem_value = $this.val();

				_add_anim_class( input_elem_value, field_type, form_row );
			} );

			// Common function to add wcf-anim-label
			function _add_anim_class( input_elem_value, field_type, form_row ) {
				if (
					input_elem_value !== '' ||
					( input_elem_value !== ' ' && 'select' === field_type )
				) {
					form_row.addClass( 'wcf-anim-label' );
				}

				if ( field_type === 'checkbox' ) {
					form_row.removeClass( 'wcf-anim-label' );
				}

				if ( field_type === 'hidden' ) {
					form_row.removeClass( 'wcf-anim-label' );
					form_row.addClass( 'wcf-anim-label-fix' );
				}
			}
		};

		wcf_anim_field_label();
		wcf_anim_field_label_event();
	};

	$( function () {
		wcf_initialize_selectwoo();
		wcf_floating_labels();
	} );
} )( jQuery );
