/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function($) {

	/**
	 * WordPress Menu Hack for My Team - Employees Menu Page
	 * */
	if ( typeof rt_biz_dashboard_screen != 'undefined' && typeof rt_biz_my_team_url != 'undefined' ) {
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

	/**
	 * WordPress Menu Hack for Departments Menu Page ( Taxonomy Page )
	 * */
	if ( typeof rt_biz_dashboard_screen != 'undefined' && typeof rt_biz_department_url != 'undefined' ) {
		$('#menu-posts').removeClass('wp-menu-open wp-has-current-submenu').addClass('wp-not-current-submenu');
		$('#menu-posts a.wp-has-submenu').removeClass('wp-has-current-submenu wp-menu-open menu-top');
		$('#'+rt_biz_dashboard_screen).addClass('wp-has-current-submenu wp-menu-open menu-top menu-top-first').removeClass('wp-not-current-submenu');
		$('#'+rt_biz_dashboard_screen+' a.wp-has-submenu').addClass('wp-has-current-submenu wp-menu-open menu-top');
	}
});