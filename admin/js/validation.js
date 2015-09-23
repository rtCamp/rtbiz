/**
 * Created by sai on 26/12/14.
 */
jQuery(document).ready(function ($) {
	jQuery('#submitdiv').on('click', '#publish', function (e) {
		// Contact & company title required
		if (jQuery('#titlewrap #title').length) {
			var eleTitle = jQuery('#titlewrap #title');
			var valTitle = jQuery.trim(eleTitle.val());
			if (( typeof valTitle === 'undefined' ) || valTitle === '') {
				jQuery(this).siblings('.spinner').hide();
				eleTitle.addClass('rtbiz-validation-error');
				jQuery('span[for="' + eleTitle.attr('id') + '"]').remove();
				eleTitle.after('<span for="' + eleTitle.attr('id') + '" style="color: red">Please Enter Title ...</span>');
				eleTitle.focus();
				e.preventDefault();
			}
		}
		$("input[name^='contact_meta'], input[name^='company_meta']").each(function () { /*jslint ignore:line*/
			if ($(this).val().length && $(this).data('type') !== undefined) {
				if ($(this).attr('type') === 'email') {
					if (!validateEmail($(this).val())) {
						e.preventDefault();
						$(this).next().next().html('Please Enter Valid Email ID.');
						$(this).next().next().addClass('rtbiz-error');
					} else {
						$(this).next().next().html('');
						$(this).next().next().removeClass('rtbiz-error');
					}
				}
				if ($(this).attr('type') === 'tel') {
					if (!validatePhone($(this).val())) {
						e.preventDefault();
						$(this).next().next().next().html('Please Enter Valid Phone Number.');
						$(this).next().next().next().addClass('rtbiz-error');
					} else {
						$(this).next().next().next().html('');
						$(this).next().next().next().removeClass('rtbiz-error');
					}
				}
			}
		});
	});

	// Title error hide on value change
	jQuery('#titlewrap').on('keyup', '#title', function (e) {
		var eleTitle = jQuery('#titlewrap #title');
		var valTitle = jQuery.trim(eleTitle.val());
		if (( typeof valTitle !== 'undefined' ) && valTitle !== '') {
			eleTitle.removeClass('rtbiz-validation-error');
			jQuery('span[for="' + eleTitle.attr('id') + '"]').remove();
		}
	});

});
function validateEmail(email) {
	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
}
function validatePhone(phone) {
	var regex = /^\d{3}-?\d{3}-?\d{4}$/g;
	return regex.test(phone);
}
