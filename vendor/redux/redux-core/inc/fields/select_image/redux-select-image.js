/*global redux*/

(function( $ ) {
	'use strict';

	// Simple URL sanitizer for image src attributes.
	// Allows http(s), protocol-relative URLs, and root / relative paths.
	function sanitizeImageSrc( src ) {
		if ( 'string' !== typeof src ) {
			return '';
		}

		src = src.trim();

		if ( '' === src ) {
			return '';
		}

		// Disallow javascript:, data:, and other dangerous schemes.
		var lower = src.toLowerCase();
		if ( lower.indexOf( 'javascript:' ) === 0 || lower.indexOf( 'data:' ) === 0 ) {
			return '';
		}

		// Allow absolute http/https URLs.
		if ( lower.indexOf( 'http://' ) === 0 || lower.indexOf( 'https://' ) === 0 ) {
			return src;
		}

		// Allow protocol-relative URLs.
		if ( src.indexOf( '//' ) === 0 ) {
			return src;
		}

		// Allow root-relative and simple relative paths.
		if ( src.indexOf( '/' ) === 0 || src.indexOf( './' ) === 0 || src.indexOf( '../' ) === 0 ) {
			return src;
		}

		// Fallback: treat as relative path segment.
		return src;
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

				preview.attr( 'src', sanitizeImageSrc( value ) );

				el.find( '.redux-select-images' ).on(
					'change',
					function() {
						var preview = $( this ).parents( '.redux-field:first' ).find( '.redux-preview-image' );

						if ( '' === $( this ).val() ) {
							preview.fadeOut(
								'medium',
								function() {
									preview.attr( 'src', '' );
								}
							);
						} else {
							preview.attr( 'src', sanitizeImageSrc( $( this ).val() ) );
							preview.fadeIn().css( 'visibility', 'visible' );
						}
					}
				);
			}
		);
	};
})( jQuery );
