/**
 * Created by sai on 26/12/14.
 */
jQuery(document).ready(function($) {
	jQuery( '#submitdiv' ).on( 'click', '#publish', function( e ) {
		// Contact & company title required
		if ( jQuery( "#titlewrap #title" ).length ){
			var eleTitle = jQuery( "#titlewrap #title" );
			var valTitle = jQuery.trim( eleTitle.val() );
			if ( ( typeof valTitle === 'undefined' ) || valTitle === '' ) {
				jQuery( this ).siblings( '.spinner' ).hide();
				eleTitle.addClass('rtbiz-validation-error');
				jQuery( "span[for='"+ eleTitle.attr( 'id' ) +"']" ).remove();
				eleTitle.after('<span for="' + eleTitle.attr( 'id' ) + '" style="color: red">Please Enter Title ...</span>');
				eleTitle.focus();
				e.preventDefault();
			}
		}
	});

	// Title error hide on value change
	jQuery( '#titlewrap' ).on( 'keyup', '#title', function( e ) {
		var eleTitle = jQuery( "#titlewrap #title" );
		var valTitle = jQuery.trim( eleTitle.val() );
		if ( ( typeof valTitle !== 'undefined' ) && valTitle !== '' ){
			eleTitle.removeClass( 'rtbiz-validation-error' );
			jQuery( "span[for='"+ eleTitle.attr( 'id' ) +"']" ).remove();
		}
	});

});