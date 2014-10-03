/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function install_post2post_plugin( plugin_slug,action,rtm_nonce ) {
	jQuery('.post2post-not-installed-error').removeClass('error');
	jQuery('.post2post-not-installed-error').addClass('updated');
	jQuery('.post2post-not-installed-error p').html('<b>rtBiz :</b> Post2Post will be installed and activated. Please wait...');
	var param = {
		action: action,
		plugin_slug: plugin_slug,
		_ajax_nonce: rtm_nonce
	};
	jQuery.post( rtbiz_ajax_url, param,function( data ){
		data = data.trim();
		if(data == "true") {
			jQuery('.post2post-not-installed-error p').html('<b>rtBiz  :</b> Post2Post installed and activated successfully.');
			location.reload();
		} else {
			jQuery('.post2post-not-installed-error p').html('<b>rtBiz  :</b> There is some problem. Please try again.');
		}
	});
}

function activate_post2post_plugins( path, action, rtm_nonce ) {
	jQuery('.post2post-not-installed-error').removeClass('error');
	jQuery('.post2post-not-installed-error').addClass('updated');
	jQuery('.post2post-not-installed-error p').html('<b>rtBiz  :</b> Post2Post will be activated now. Please wait.');
	var param = {
		action: action,
		path: path,
		_ajax_nonce: rtm_nonce
	};
	jQuery.post( rtbiz_ajax_url, param,function(data){
		data = data.trim();
		if(data == "true") {
			jQuery('.post2post-not-installed-error p').html('<b>rtBiz  :</b> Post2Post activated.');
			location.reload();
		} else {
			jQuery('.post2post-not-installed-error p').html('<b>rtBiz  :</b> There is some problem. Please try again.');
		}
	});
}
