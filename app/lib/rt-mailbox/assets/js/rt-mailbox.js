jQuery( document ).ready(function(){
    var rtmailbox = {
        init: function () {
            rtmailbox.default_ui();
            rtmailbox.ui_render();
            rtmailbox.imap_connect_ajax();
            rtmailbox.imap_folder_ajax();
        },
        default_ui:function(){
            // hide IMAP form by default
            jQuery('#rtmailbox-imap-server-container').hide();
        },
        ui_render: function(){
            // provider select
            jQuery('.rtmailbox_provider').click(function(){
               if( jQuery(this).val() === 'custom' ){
                   jQuery('#rtmailbox-imap-server-container').show();
               }else{
                   jQuery('#rtmailbox-imap-server-container').hide();
               }
            });

            //populate default port
            jQuery('#rtmailbox-incoming_ssl').click(function(){
                if( jQuery(this).is(':checked') ){
                    jQuery('#rtmailbox-incoming_port').val( jQuery('#rtmailbox-incoming_ssl_port').val() );
                }else{
                    jQuery('#rtmailbox-incoming_port').val( jQuery('#rtmailbox-incoming_tls_port').val() );
                }
            });
            jQuery('#rtmailbox-outgoing_ssl').click(function(){
                if( jQuery(this).is(':checked') ){
                    jQuery('#rtmailbox-outgoing_port').val( jQuery('#rtmailbox-outgoing_ssl_port').val() );
                }else{
                    jQuery('#rtmailbox-outgoing_port').val( jQuery('#rtmailbox-outgoing_tls_port').val() );
                }
            });

        },
        imap_connect_ajax: function(){
            //imap connect ajax request
            jQuery('#rtmailbox-connect').click(function(){
                var requestArray = {};
                requestArray.data =  jQuery( '#rtmailbox-wrap input' ).serialize();
                requestArray.action = 'rtmailbox_imap_connect';

                jQuery.ajax({
                    url: ajaxurl,
                    dataType: 'json',
                    type: 'post',
                    data: requestArray,
                    beforeSend: function(){

                    },
                    success: function(data) {
                        if (data.status) {
                            jQuery( '#rtmailbox-wrap' ).html( data.html);
                        }else{
                            alert( data.error );
                        }
                    },
                    error: function(){
                        alert( 'Something goes wrong. Please try again.' );
                    }
                });
                event.preventDefault();
            });
        },
        imap_folder_ajax: function(){
            //imap connect ajax request
            jQuery(document).on('click', '#rtmailbox-save', function( event ) {
                var requestArray = {};
                requestArray.data =  jQuery( '#rtmailbox-wrap input' ).serialize();
                requestArray.action = 'rtmailbox_folder_update';

                jQuery.ajax({
                    url: ajaxurl,
                    dataType: 'json',
                    type: 'post',
                    data: requestArray,
                    beforeSend: function(){
                        //alert('before send');
                    },
                    success: function(data) {
                        alert(data);
                    },
                    error: function(){
                        alert( 'Something goes wrong. Please try again.' );
                    }
                });
                event.preventDefault();
            });
        }
    };

    rtmailbox.init();
});














/*
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

	jQuery( '.rtMailbox-hide-mail-folders' ).click( function ( e ) {
		e.preventDefault();
		that  = jQuery( this ).parent().parent().next( 'table' );
		if (that.is(':visible')){
			jQuery(this).text('Show');
		}else{
			jQuery(this).text('Hide');
		}
		that.toggleClass( 'rtmailbox-hide-row' );
	});

	function validateEmail(email) {
		var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(email);
	}

	jQuery( '#rtmailbox_add_imap' ).click( function ( e ) {
		if ( ! jQuery( '#rtmailbox_imap_server' ).val()) {
			jQuery(this ).next('.rt_mailbox_error' ).text('Please select mail server.');
			e.preventDefault();
			return false;
		}
		if ( ! jQuery( '#rtmailbox_imap_user_email' ).val()) {
			jQuery(this ).next('.rt_mailbox_error' ).text('Please enter Email address.');
			e.preventDefault();
			return false;
		} else if ( !validateEmail( jQuery( '#rtmailbox_imap_user_email' ).val() ) ) {
			jQuery(this ).next('.rt_mailbox_error' ).text('Please enter valid Email address!');
			e.preventDefault();
			return false;
		}
		if ( ! jQuery( '#rtmailbox_imap_user_pwd' ).val()) {
			jQuery(this ).next('.rt_mailbox_error' ).text('Please enter Password!');
			e.preventDefault();
			return false;
		}
	});

	jQuery( document ).on( 'click', '.remove-mailbox', function () {

		if ( confirm( 'Are you sure you want to remove this email A/C ?' ) ) {

			var requestArray = {};
			var mailboxID = jQuery(this).attr('data-mailboxid');

			requestArray.rtmailbox_submit_action =  'delete';
			requestArray.email =  jQuery(this).attr('data-email');
			requestArray.module_to_register =  jQuery(this).attr('data-module');
			requestArray.action = 'rtmailbox_remove_account';
			jQuery('#remove-mailbox-spinner'+mailboxID ).show();
			jQuery.ajax( {
				url: ajaxurl,
				dataType: 'json',
				type: 'post',
				data: requestArray,
				success: function ( data ) {
					if (data.status) {
						jQuery('#rtmailbox-container'+mailboxID ).next('hr' ).remove();
						jQuery('#rtmailbox-container'+mailboxID ).remove();
					}
					jQuery('#remove-mailbox-spinner'+mailboxID ).hide();
				},
				error: function(){
					jQuery('#remove-mailbox-spinner'+mailboxID ).hide();
					alert( 'Something goes wrong. Please try again.' );
				}
			});
		} else {
			return false;
		}
	});

});
*/
