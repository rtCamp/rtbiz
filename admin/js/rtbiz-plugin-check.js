/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function install_rtbiz_plugin(plugin_slug, action, rtm_nonce) {
	jQuery('.rtbiz-plugin-not-installed-error').removeClass('error');
	jQuery('.rtbiz-plugin-not-installed-error').addClass('updated');
	jQuery('.rtbiz-plugin-not-installed-error p').html('<b>rtBiz :</b> ' + plugin_slug + ' will be installed and activated. Please wait...<div class="spinner"> </div>');
	jQuery('div.spinner').show();
	var param = {
		action: action,
		plugin_slug: plugin_slug,
		_ajax_nonce: rtm_nonce
	};
	jQuery.post(rtbiz_ajax_url, param, function (data) {
		data = data.trim();
		if (data === 'true') {
			jQuery('.rtbiz-plugin-not-installed-error p').html('<b>rtBiz  :</b> ' + plugin_slug + ' installed and activated successfully.');
			location.reload();
		} else {
			jQuery('.rtbiz-plugin-not-installed-error p').html('<b>rtBiz  :</b> There is some problem. Please try again.');
		}
	});
}

function activate_rtbiz_plugin(path, action, rtm_nonce) {
	jQuery('.rtbiz-plugin-not-installed-error').removeClass('error');
	jQuery('.rtbiz-plugin-not-installed-error').addClass('updated');
	jQuery('.rtbiz-plugin-not-installed-error p').html('<b>rtBiz  :</b> ' + path + ' will be activated now. Please wait. <div class="spinner"> </div>');
	jQuery('div.spinner').show();
	var param = {
		action: action,
		path: path,
		_ajax_nonce: rtm_nonce
	};
	jQuery.post(rtbiz_ajax_url, param, function (data) {
		data = data.trim();
		if (data === 'true') {
			jQuery('.rtbiz-plugin-not-installed-error p').html('<b>rtBiz  :</b> Post2Post activated.');
			location.reload();
		} else {
			jQuery('.rtbiz-plugin-not-installed-error p').html('<b>rtBiz  :</b> There is some problem. Please try again.');
		}
	});
}
