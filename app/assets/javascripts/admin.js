/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function($) {

	if ( typeof rt_biz_dashboard_screen != 'undefined' && typeof rt_biz_my_team_url != 'undefined' ) {
		console.log(rt_biz_dashboard_screen);
		console.log(rt_biz_my_team_url);
		$('li.'+rt_biz_dashboard_screen+' ul li').removeClass('current');
		$('li.'+rt_biz_dashboard_screen+' ul li a').removeClass('current');
		$('li.'+rt_biz_dashboard_screen+' ul li a').each(function(e) {
			console.log(this.href);
			if ( this.href == rt_biz_my_team_url ) {
				$(this).parent().addClass("current");
	            $(this).addClass('current');
			}
		});
	}
});