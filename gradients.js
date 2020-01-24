jQuery( function( $ ) {
	// Handle on click per gradient.
	$( '#geg-gradients' ).on( 'click', '.geg-gradient', function( e ) {
		e.preventDefault();
		$button = $( this );
		if ( $button.hasClass( 'unchecked' ) ) {
			$button.removeClass( 'unchecked' ).addClass( 'checked' );
		} else {
			$button.removeClass( 'checked' ).addClass( 'unchecked' );
		}
	} );
	// Handle select all anchor.
	$( '#geg-gradients' ).on( 'click', '#geg-gradient-select-all', function( e ) {
		e.preventDefault();
		$( '.geg-gradient' ).removeClass( 'unchecked' ).addClass( 'checked' );
	} );
	// Handle deselect all anchor.
	$( '#geg-gradients' ).on( 'click', '#geg-gradient-deselect-all', function( e ) {
		e.preventDefault();
		$( '.geg-gradient' ).removeClass( 'checked' ).addClass( 'unchecked' );
	} );
	// Handle submit handler.
	$( '#geg-gradients-form' ).on( 'submit', function( e ) {
		e.preventDefault();
		var $submit_button = $( '#geg-save-gradients' );
		$submit_button.val( gutenberg_experimental_gradients.saving ).prop( 'disabled', 'disabled' );
		var selected = {};
		$('#geg-gradients .checked' ).each( function() {
			var $button = $( this );
			selected[ $button.data( 'title' ) ] = {
				name: $button.data( 'name' ),
				gradient: $button.data( 'style' )
			};
		} );
		$.post(
			ajaxurl,
			{
				action: 'geg_save_gradients',
				gradients: selected,
				nonce: $('#geg_ajax_gradients_nonce').val()
			},
			function( response ) {
				$submit_button.val( gutenberg_experimental_gradients.saved ).removeAttr( 'disabled' );
			}
		);
	} );
} );