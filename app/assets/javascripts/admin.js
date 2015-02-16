/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function($) {
	/**
	 * WordPress Menu Hack for Departments Menu Page ( Taxonomy Page )
	 */
	if ( typeof rt_biz_dashboard_screen !== 'undefined' && typeof rt_biz_department_url !== 'undefined' ) {
		$('#menu-posts').removeClass('wp-menu-open wp-has-current-submenu').addClass('wp-not-current-submenu');
		$('#menu-posts a.wp-has-submenu').removeClass('wp-has-current-submenu wp-menu-open menu-top');
		$('#'+rt_biz_dashboard_screen).addClass('wp-has-current-submenu wp-menu-open menu-top menu-top-first').removeClass('wp-not-current-submenu');
		$('#'+rt_biz_dashboard_screen+' a.wp-has-submenu').addClass('wp-has-current-submenu wp-menu-open menu-top');
		$(window).resize();
	}

	/**
	 * WordPress Menu Hack for Offerings Menu Page ( Taxonomy Page )
	 */
	if ( typeof rt_biz_dashboard_screen !== 'undefined' && typeof rt_biz_menu_url !== 'undefined' ) {
		$('#menu-posts').removeClass('wp-menu-open wp-has-current-submenu').addClass('wp-not-current-submenu');
		$('#menu-posts a.wp-has-submenu').removeClass('wp-has-current-submenu wp-menu-open menu-top');
		$('#'+rt_biz_dashboard_screen).addClass('wp-has-current-submenu wp-menu-open menu-top menu-top-first').removeClass('wp-not-current-submenu');
		$('#'+rt_biz_dashboard_screen+' a.wp-has-submenu').addClass('wp-has-current-submenu wp-menu-open menu-top');
		$('li.'+rt_biz_dashboard_screen+' ul li').removeClass('current');
		$('li.'+rt_biz_dashboard_screen+' ul li a').removeClass('current');
		$('li.'+rt_biz_dashboard_screen+' ul li a').each(function(e) {
			if ( this.href === rt_biz_menu_url ) {
				$(this).parent().addClass('current');
				$(this).addClass('current');
			}
		});
		$(window).resize();
	}

	try {
		if (arr_assign_user !== undefined) {
			jQuery('#assign_user_ac').autocomplete({
				                                           source: function (request, response) {
					                                           var term = jQuery.ui.autocomplete.escapeRegex(request.term), startsWithMatcher = new RegExp('^' + term, 'i'), startsWith = jQuery.grep(arr_assign_user, function (value) {
						                                           return startsWithMatcher.test(value.label || value.value || value);
					                                           }), containsMatcher = new RegExp(term, 'i'), contains = jQuery.grep(arr_assign_user, function (value) {
						                                           return jQuery.inArray(value, startsWith) < 0 && containsMatcher.test(value.label || value.value || value);
					                                           });

					                                           response(startsWith.concat(contains));
				                                           },
				                                           focus: function (event, ui) {

				                                           },
				                                           select: function (event, ui) {
					                                           if (jQuery('#assign-auth-' + ui.item.id).length < 1) {
						                                           jQuery('#divAssignList').html('<li id="assign-auth-' + ui.item.id + '" class="contact-list" >' + ui.item.imghtml + '<a href="#removeAssign" class="delete_row">×</a><br/><a class="assign-title heading" target="_blank" href="' + ui.item.user_edit_link + '">' + ui.item.label + '</a><input type="hidden" name="assign_to" value="' + ui.item.id + '" /></li>');
					                                           }
					                                           jQuery('#assign_user_ac').val('');
					                                           return false;
				                                           }
			                                           }).data('ui-autocomplete')._renderItem = function (ul, item) {
				return jQuery('<li></li>').data('ui-autocomplete-item', item).append('<a class="ac-assign-selected">' + item.imghtml + '&nbsp;' + item.label + '</a> ').appendTo(ul);
			};

			jQuery(document).on('click', 'a[href=#removeAssign]', function (e) {
				e.preventDefault();
				jQuery(this).parent().remove();
			});

		}
	} catch (e) {

	}

	jQuery('.rtbiz_offering_dissmiss' ).click( function (e ) {
		e.preventDefault();
		var param = {
			action: 'rtbiz_hide_offering_notice'
		};
		jQuery.post( rtbiz_ajax_url_admin, param,function(data){
			data = data.trim();
			if(data === 'true') {
				jQuery('.rtbiz-offering-notice' ).hide();
			}
		});
	});

	jQuery('.rtbiz-export' ).click(function(e){
		var that = jQuery(this).parent();
		e.preventDefault();
		var id = jQuery(this ).data('id');
		var nonce= jQuery(this ).next().val();
		var param = {
			action: 'rtbiz_export_contact',
			id: id,
			nonce: nonce
		};
		jQuery.post( rtbiz_ajax_url_admin, param,function(data){
			if( data.status ) {
				that.html(data.html);
				console.log(that.parent());
			}
		}, 'json' );

	});

	jQuery('.rtbiz-export-button' ).click(function(e){
		var nonce= jQuery('#rtbiz-contact-import-nonce').val();
		var param = {
			action: 'rtbiz_export_all_contacts',
			nonce: nonce
		};
		jQuery('#rtbiz-import-spinner' ).show();
		jQuery.post( rtbiz_ajax_url_admin, param,function(data){
			if ( data.status ) {
				jQuery( '#rtbiz-import-message' ).html( data.message );
				jQuery( '#rtbiz-import-message' ).removeClass( 'rtbiz-error' ).addClass( 'rtbiz-success' );
			}
			else{
				jQuery( '#rtbiz-import-message' ).html( 'Error: Import not success!' );
				jQuery( '#rtbiz-import-message' ).removeClass( 'rtbiz-success').addClass( 'rtbiz-error' );
			}
			jQuery('#rtbiz-import-spinner' ).hide();
		}, 'json');
	});

});
