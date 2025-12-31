/*global redux*/

(function( $ ) {
	'use strict';

	/**
	 * Basic safety check for image src values to avoid dangerous URL schemes.
	 *
	 * @param {string} url
	 * @returns {boolean}
	 */
	function isSafeImageSrc( url ) {
		if ( ! url ) {
			return true;
		}

		var trimmed = $.trim( String( url ) );
		var lower   = trimmed.toLowerCase();

		// Disallow common scriptable schemes.
		if ( lower.indexOf( 'javascript:' ) === 0 ||
			lower.indexOf( 'data:' ) === 0 ||
			lower.indexOf( 'vbscript:' ) === 0 ||
			lower.indexOf( 'file:' ) === 0 ) {
			return false;
		}

		return true;
	}

	redux.field_objects              = redux.field_objects || {};
	redux.field_objects.select_image = redux.field_objects.select_image || {};

	redux.field_objects.select_image.init = function( selector ) {
		selector = $.redux.getSelector( selector, 'select_image' );

		$( selector ).each(
			function() {
				var value;
				var preview;

				var el     = $( this );
				var parent = el;

				if ( ! el.hasClass( 'redux-field-container' ) ) {
					parent = el.parents( '.redux-field-container:first' );
				}

				if ( parent.is( ':hidden' ) ) {
					return;
				}

				if ( parent.hasClass( 'redux-field-init' ) ) {
					parent.removeClass( 'redux-field-init' );
				} else {
					return;
				}

				el.find( 'select.redux-select-images' ).select2();

				value   = el.find( 'select.redux-select-images' ).val();
				preview = el.find( 'select.redux-select-images' ).parents( '.redux-field:first' ).find( '.redux-preview-image' );

				if ( isSafeImageSrc( value ) ) {
					preview.attr( 'src', value );
				} else {
					preview.attr( 'src', '' );
				}

				el.find( '.redux-select-images' ).on(
					'change',
					function() {
						var preview = $( this ).parents( '.redux-field:first' ).find( '.redux-preview-image' );
						var value   = $( this ).val();

						if ( '' === value || ! isSafeImageSrc( value ) ) {
							preview.fadeOut(
								'medium',
								function() {
									preview.attr( 'src', '' );
								}
							);
						} else {
							preview.attr( 'src', value );
							preview.fadeIn().css( 'visibility', 'visible' );
						}
					}
				);
			}
		);
	};
})( jQuery );
