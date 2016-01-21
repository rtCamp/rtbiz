jQuery(document).ready(function () {
	var rtmailbox = {
		init: function () {
			rtmailbox.default_ui();
			rtmailbox.ui_render();
			rtmailbox.imap_connect_ajax();
			rtmailbox.imap_folder_ajax();
			rtmailbox.mailbox_update_ajax();
			rtmailbox.mailbox_remove_ajax();
			rtmailbox.mailbox_add_ajax();
		},
		getParameterByName: function (name) {
			name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
				results = regex.exec(location.search);
			return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
		},
		default_ui: function () {
			// hide IMAP form by default
			if ('custom' !== jQuery('.rtmailbox_provider:checked').val()) {
				jQuery('#rtmailbox-imap-server-container').hide();
			} else {
				jQuery('#rtmailbox-imap-server-container').show();
			}

		},
		ui_render: function () {
			// provider
			jQuery(document).on('click', '.rtmailbox_provider', function (event) {
				if (jQuery(this).val() === 'custom') {
					jQuery('#rtmailbox-imap-server-container').show();
				} else {
					jQuery('#rtmailbox-imap-server-container').hide();
				}
			});

			//populate default port
			jQuery(document).on('click', '#rtmailbox-incoming_ssl', function (event) {
				if (jQuery(this).is(':checked')) {
					jQuery('#rtmailbox-incoming_port').val(jQuery('#rtmailbox-incoming_ssl_port').val());
				} else {
					jQuery('#rtmailbox-incoming_port').val(jQuery('#rtmailbox-incoming_tls_port').val());
				}
			});
			jQuery(document).on('click', '#rtmailbox-outgoing_ssl', function (event) {
				if (jQuery(this).is(':checked')) {
					jQuery('#rtmailbox-outgoing_port').val(jQuery('#rtmailbox-outgoing_ssl_port').val());
				} else {
					jQuery('#rtmailbox-outgoing_port').val(jQuery('#rtmailbox-outgoing_tls_port').val());
				}
			});

		},
		imap_connect_ajax: function () {
			//imap connect ajax request
			jQuery(document).on('click', '#rtmailbox-connect', function (event) {

				if (rtmailbox.IsEmptyCheck(jQuery('#rtmailbox-email').val())) {
					alert('please enter email address.');
					return;
				}

				if (!rtmailbox.IsEmail(jQuery('#rtmailbox-email').val())) {
					alert('please enter valid email address.');
					return;
				}

				if (rtmailbox.IsEmptyCheck(jQuery('#rtmailbox-password').val())) {
					alert('please enter password.');
					return;
				}

				if (jQuery('.rtmailbox_provider').val() === 'custom') {
					if (rtmailbox.IsEmptyCheck(jQuery('#rtmailbox-provider_name').val()) || rtmailbox.IsEmptyCheck(jQuery('#rtmailbox-incoming_server').val()) || rtmailbox.IsEmptyCheck(jQuery('#rtmailbox-outgoing_server').val())) {
						alert('please enter server required fields.');
						return;
					}
				}

				jQuery(this).after('<img id="mailbox-spinner" class="mailbox-spinner" src="' + adminurl + 'images/spinner.gif"/>');
				var requestArray = {};
				requestArray.data = jQuery('#rtmailbox-wrap').find('select,textarea, input').serialize();
				requestArray.action = 'rtmailbox_imap_connect';

				jQuery.ajax({
					url: ajaxurl,
					dataType: 'json',
					type: 'post',
					data: requestArray,
					beforeSend: function () {

					},
					success: function (data) {
						if (data.status) {
							//var page = rtmailbox.getParameterByName('page');
							// exception if mailbox page is helpesk page then after success in connection reload page.
							if (typeof reload_url !== 'undefined') {
								window.location.replace(decodeURI(reload_url));
							}
							jQuery('#rtmailbox-wrap').html(data.html);
							jQuery('#mailbox-' + data.moduleid).remove();
							if (jQuery('#mailbox-list>.rtmailbox-row').length === 0) {
								jQuery('#mailbox-list').html(data.html_list);
							} else {
								jQuery('#mailbox-list').append(data.html_list);
							}
						} else {
							alert(data.error);
						}
						jQuery('img#mailbox-spinner').remove();
					},
					error: function () {
						alert('Something goes wrong. Please try again.');
						jQuery('img#mailbox-spinner').remove();
					}
				});
				event.preventDefault();
			});
		},
		imap_folder_ajax: function () {
			//imap connect ajax request
			jQuery(document).on('click', '#rtmailbox-save', function (event) {

				if (jQuery('.mailbox-folder-list input:checked').length <= 0) {
					alert('Please select Folder for mailbox reading.');
					return;
				}

				jQuery(this).after('<img id="mailbox-spinner" class="mailbox-spinner" src="' + adminurl + 'images/spinner.gif"/>');
				mailboxid = jQuery(this).data('mailboxid');
				var requestArray = {};
				requestArray.data = jQuery('#mailbox-folder-' + mailboxid).find('select,textarea, input').serialize();
				requestArray.action = 'rtmailbox_folder_update';

				jQuery.ajax({
					url: ajaxurl,
					dataType: 'json',
					type: 'post',
					data: requestArray,
					beforeSend: function () {
						//alert('before send');
					},
					success: function (data) {
						if (data.status) {
							jQuery('#mailbox-folder-' + mailboxid).hide();
						} else {
							alert(data.error);
						}
						jQuery('img#mailbox-spinner').remove();
					},
					error: function () {
						alert('Something goes wrong. Please try again.');
						jQuery('img#mailbox-spinner').remove();
					}
				});
				event.preventDefault();
			});
		},
		mailbox_update_ajax: function () {
			jQuery(document).on('click', '#rtmailbox-update-mailbox', function (event) {
				jQuery(this).after('<img id="mailbox-spinner" class="mailbox-spinner" src="' + adminurl + 'images/spinner.gif"/>');
				var requestArray = {};
				mailboxid = jQuery(this).data('mailboxid');
				if (jQuery('#mailbox-folder-' + mailboxid).is(':visible')) {
					jQuery('#mailbox-folder-' + mailboxid).hide();
				} else {
					jQuery('#mailbox-folder-' + mailboxid).show();
				}
				jQuery('img#mailbox-spinner').remove();
				event.preventDefault();
			});
		},
		mailbox_remove_ajax: function () {
			jQuery(document).on('click', '.remove-mailbox', function (event) {
				if (confirm('Are you sure you want to remove this mailbox ?')) {
					jQuery(this).after('<img id="mailbox-spinner" class="mailbox-spinner" src="' + adminurl + 'images/spinner.gif"/>');
					var requestArray = {};
					requestArray.mailboxid = jQuery(this).data('mailboxid');
					requestArray.email = jQuery(this).data('email');
					requestArray.module = jQuery(this).data('module');
					requestArray.action = 'rtmailbox_mailbox_remove';

					jQuery.ajax({
						url: ajaxurl,
						dataType: 'json',
						type: 'post',
						data: requestArray,
						beforeSend: function () {
							//alert('before send');
						},
						success: function (data) {
							if (data.status) {
								if (typeof reload_url !== 'undefined') {
									window.location.replace(decodeURI(reload_url));
								}
								jQuery('#mailbox-' + data.moduleid).remove();
								if (jQuery('#mailbox-list>.rtmailbox-row').length === 0) {
									jQuery('#mailbox-list').html('<p>No mailbox Found! Please connect mailbox with helpdesk.</p>');
								}
							} else {
								alert(data.error);
							}
							jQuery('img#mailbox-spinner').remove();
						},
						error: function () {
							alert('Something goes wrong. Please try again.');
							jQuery('img#mailbox-spinner').remove();
						}
					});
					event.preventDefault();
				}
			});
		},
		mailbox_add_ajax: function () {
			jQuery(document).on('click', '#rtmailbox-add', function (event) {
				jQuery(this).after('<img id="mailbox-spinner" class="mailbox-spinner" src="' + adminurl + 'images/spinner.gif"/>');
				var requestArray = {};
				requestArray.module = jQuery(this).data('module');
				requestArray.action = 'rtmailbox_mailbox_add';

				jQuery.ajax({
					url: ajaxurl,
					dataType: 'json',
					type: 'post',
					data: requestArray,
					beforeSend: function () {
						//alert('before send');
					},
					success: function (data) {
						if (data.status) {
							jQuery('#rtmailbox-wrap').html(data.html);
							rtmailbox.default_ui();
						} else {
							alert(data.error);
						}
						jQuery('img#mailbox-spinner').remove();
					},
					error: function () {
						alert('Something goes wrong. Please try again.');
						jQuery('img#mailbox-spinner').remove();
					}
				});
				event.preventDefault();
			});
		},
		IsEmail: function (email) {
			var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
			return pattern.test(email);
		},
		IsEmptyCheck: function (variable) {
			if (variable) {
				return false;
			}
			return true;
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
