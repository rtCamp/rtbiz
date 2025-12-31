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
		// Treat null/undefined as safe "no URL".
		if ( url === null || url === undefined ) {
			return true;
		}

		// Normalize to string and trim whitespace.
		var trimmed = $.trim( String( url ) );

		// Empty string is used as "no image" elsewhere and is safe.
		if ( trimmed === '' ) {
			return true;
		}

		var lower = trimmed.toLowerCase();

		// Disallow common scriptable and browser-internal schemes.
		if ( lower.indexOf( 'javascript:' ) === 0 ||
			lower.indexOf( 'data:' ) === 0 ||
			lower.indexOf( 'vbscript:' ) === 0 ||
			lower.indexOf( 'file:' ) === 0 ||
			lower.indexOf( 'about:' ) === 0 ||
			lower.indexOf( 'chrome:' ) === 0 ) {
			return false;
		}

		// Allow http(s) URLs explicitly.
		if ( lower.indexOf( 'http://' ) === 0 || lower.indexOf( 'https://' ) === 0 ) {
			return true;
		}

		// Allow protocol-relative URLs (e.g. //example.com/image.png).
		if ( lower.indexOf( '//' ) === 0 ) {
			return true;
		}

		// For all other cases, allow only relative URLs without a scheme.
		// If there is a colon before any slash, query, or hash, treat it as a scheme and reject.
		var firstSlash = lower.indexOf( '/' );
		var firstQuery = lower.indexOf( '?' );
		var firstHash  = lower.indexOf( '#' );

		var limit = lower.length;
		if ( firstSlash !== -1 && firstSlash < limit ) {
			limit = firstSlash;
		}
		if ( firstQuery !== -1 && firstQuery < limit ) {
			limit = firstQuery;
		}
		if ( firstHash !== -1 && firstHash < limit ) {
			limit = firstHash;
		}

		var schemeSeparator = lower.indexOf( ':' );

		// No colon before the first special character => no explicit scheme => treat as relative URL.
		if ( schemeSeparator === -1 || schemeSeparator > limit ) {
			return true;
		}

		// Any other explicit scheme is considered unsafe by default.
		return false;
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
