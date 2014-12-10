jQuery( document ).ready(function(){
	jQuery( document ).on( 'click', '.rthd-edit-server', function ( e ) {
		e.preventDefault();
		var server_id = jQuery( this ).data( 'server-id' );
		jQuery( '#rthd_imap_server_' + server_id ).toggleClass( 'rthd-hide-row' ).toggleClass( 'rthd-show-row' );
	} );
	jQuery( document ).on( 'click', '#rthd_add_imap_server', function ( e ) {
		e.preventDefault();
		jQuery( '#rthd_new_imap_server' ).toggleClass( 'rthd-hide-row' ).toggleClass( 'rthd-show-row' );
	} );
	jQuery( document ).on( 'click', '.rthd-remove-server', function ( e ) {
		e.preventDefault();
		var flag = confirm( 'Are you sure you want to remove this server ?' );
		var server_id = jQuery( this ).data( 'server-id' );
		if ( flag ) {
			jQuery( '#rthd_imap_server_' + server_id ).remove();
			jQuery( this ).parent().parent().remove();
		}
	} );
	jQuery( document ).on( 'click', '#rthd_add_personal_email', function ( e ) {
		e.preventDefault();
		jQuery( '#rthd_email_acc_type_container' ).toggleClass( 'rthd-hide-row' ).toggleClass( 'rthd-show-row' );
		if ( jQuery( '#rthd_email_acc_type_container' ).hasClass( 'rthd-hide-row' ) ) {
			jQuery( '#rthd_goauth_container' ).removeClass( 'rthd-show-row' ).addClass( 'rthd-hide-row' );
			jQuery( '#rthd_add_imap_acc_form' ).removeClass( 'rthd-show-row' ).addClass( 'rthd-hide-row' );
			jQuery( '#rthd_select_email_acc_type' ).val( '' ).change();
		}
	} );
	jQuery( document ).on( 'change', '#rthd_select_email_acc_type', function () {
		if ( jQuery( this ).val() === 'goauth' ) {
			jQuery( '#rthd_goauth_container' ).removeClass( 'rthd-hide-row' ).addClass( 'rthd-show-row' );
			jQuery( '#rthd_add_imap_acc_form' ).removeClass( 'rthd-show-row' ).addClass( 'rthd-hide-row' );
			jQuery( '#rthd_add_imap_acc_form input[type=email]' ).remove();
			jQuery( '#rthd_add_imap_acc_form input[type=password]' ).remove();
		} else if ( jQuery( this ).val() === 'imap' ) {
			jQuery( '#rthd_add_imap_acc_form' ).removeClass( 'rthd-hide-row' ).addClass( 'rthd-show-row' );
			jQuery( '#rthd_goauth_container' ).removeClass( 'rthd-show-row' ).addClass( 'rthd-hide-row' );
			jQuery( '#rthd_add_imap_acc_form' ).append( '<input type="email" autocomplete="off" name="rthd_imap_user_email" placeholder="Email"/>' );
			jQuery( '#rthd_add_imap_acc_form' ).append( '<input type="password" autocomplete="off" name="rthd_imap_user_pwd" placeholder="Password"/>' );
		} else {
			jQuery( '#rthd_goauth_container' ).removeClass( 'rthd-show-row' ).addClass( 'rthd-hide-row' );
			jQuery( '#rthd_add_imap_acc_form' ).removeClass( 'rthd-show-row' ).addClass( 'rthd-hide-row' );
			jQuery( '#rthd_add_imap_acc_form input[type=email]' ).remove();
			jQuery( '#rthd_add_imap_acc_form input[type=password]' ).remove();
		}
	});

	jQuery( '.remove-google-ac' ).click( function ( e ) {
		var r = confirm( 'Are you sure you want to remove this email A/C ?' );
		if ( r === true ) {

		} else {
			e.preventDefault();
			return false;
		}
	} );

	jQuery('.rtMailbox-hide-mail-folders' ).click( function ( e ) {
		e.preventDefault();
		jQuery(this ).parent().parent().next('tr').toggleClass( 'rthd-hide-row' );
	});

});
