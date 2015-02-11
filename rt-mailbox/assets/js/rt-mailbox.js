jQuery( document ).ready(function(){
	jQuery( document ).on( 'click', '.rthd-edit-server', function ( e ) {
		e.preventDefault();
		var server_id = jQuery( this ).data( 'server-id' );
		jQuery( '#rtmailbox_imap_server_' + server_id ).toggleClass( 'rtmailbox-hide-row' ).toggleClass( 'rtmailbox-show-row' );
	} );
	jQuery( document ).on( 'click', '#rtmailbox_add_imap_server', function ( e ) {
		e.preventDefault();
		jQuery( '#rthd_new_imap_server' ).toggleClass( 'rtmailbox-hide-row' ).toggleClass( 'rtmailbox-show-row' );
	} );
	jQuery( document ).on( 'click', '.rtmailbox-remove-server', function ( e ) {
		e.preventDefault();
		var flag = confirm( 'Are you sure you want to remove this server ?' );
		var server_id = jQuery( this ).data( 'server-id' );
		if ( flag ) {
			jQuery( '#rtmailbox_imap_server_' + server_id ).remove();
			jQuery( this ).parent().parent().remove();
		}
	} );
	jQuery( document ).on( 'click', '#rtmailbox_add_personal_email', function ( e ) {
		e.preventDefault();
		jQuery( '#rtmailbox_email_acc_type_container' ).toggleClass( 'rtmailbox-hide-row' ).toggleClass( 'rtmailbox-show-row' );
		if ( jQuery( '#rtmailbox_email_acc_type_container' ).hasClass( 'rtmailbox-hide-row' ) ) {
			jQuery( '#rthd_goauth_container' ).removeClass( 'rtmailbox-show-row' ).addClass( 'rtmailbox-hide-row' );
			jQuery( '#rtmailbox_add_imap_acc_form' ).removeClass( 'rtmailbox-show-row' ).addClass( 'rtmailbox-hide-row' );
			jQuery( '#rtmailbox_select_email_acc_type' ).val( '' ).change();
		}
	} );
	jQuery( document ).on( 'change', '#rtmailbox_select_email_acc_type', function () {
		if ( jQuery( this ).val() === 'goauth' ) {
			jQuery( '#rthd_goauth_container' ).removeClass( 'rtmailbox-hide-row' ).addClass( 'rtmailbox-show-row' );
			jQuery( '#rtmailbox_add_imap_acc_form' ).removeClass( 'rtmailbox-show-row' ).addClass( 'rtmailbox-hide-row' );
			jQuery( '#rtmailbox_add_imap_acc_form #rtmailbox_add_imap_acc_fields input[type=email]' ).remove();
			jQuery( '#rtmailbox_add_imap_acc_form #rtmailbox_add_imap_acc_fields input[type=password]' ).remove();
		} else if ( jQuery( this ).val() === 'imap' ) {
			jQuery( '#rtmailbox_add_imap_acc_form' ).removeClass( 'rtmailbox-hide-row' ).addClass( 'rtmailbox-show-row' );
			jQuery( '#rthd_goauth_container' ).removeClass( 'rtmailbox-show-row' ).addClass( 'rtmailbox-hide-row' );
			jQuery( '#rtmailbox_add_imap_acc_form #rtmailbox_add_imap_acc_fields' ).append( '<input type="email" autocomplete="off" id="rtmailbox_imap_user_email" name="rtmailbox_imap_user_email" placeholder="Email"/>' );
			jQuery( '#rtmailbox_add_imap_acc_form #rtmailbox_add_imap_acc_fields' ).append( '<input type="password" autocomplete="off" id="rtmailbox_imap_user_pwd" name="rtmailbox_imap_user_pwd" placeholder="Password"/>' );
		} else {
			jQuery( '#rthd_goauth_container' ).removeClass( 'rtmailbox-show-row' ).addClass( 'rtmailbox-hide-row' );
			jQuery( '#rtmailbox_add_imap_acc_form' ).removeClass( 'rtmailbox-show-row' ).addClass( 'rtmailbox-hide-row' );
			jQuery( '#rtmailbox_add_imap_acc_form #rtmailbox_add_imap_acc_fields input[type=email]' ).remove();
			jQuery( '#rtmailbox_add_imap_acc_form #rtmailbox_add_imap_acc_fields input[type=password]' ).remove();
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

	jQuery( '.rtMailbox-hide-mail-folders' ).click( function ( e ) {
		e.preventDefault();
		jQuery( this ).parent().parent().next( 'table' ).toggleClass( 'rtmailbox-hide-row' );
	});

	jQuery( '#rtmailbox_add_imap' ).click( function ( e ) {
		if ( ! jQuery( "#rtmailbox_imap_server" ).val()) {
			alert('Please select mail server!');
			e.preventDefault();
			return false;
		}
		if ( ! jQuery( "#rtmailbox_imap_user_email" ).val()) {
			alert('Please enter Email address!');
			e.preventDefault();
			return false;
		}
		if ( ! jQuery( "#rtmailbox_imap_user_pwd" ).val()) {
			alert('Please enter Email address!');
			e.preventDefault();
			return false;
		}
	});

});
