<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-rt-biz-attributes
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Biz_Attributes' ) ) {
	class Rt_Biz_Attributes {

		var $attributes_page_slug = 'rt-biz-attributes';

		public function __construct() {
			global $rt_attributes, $rt_plugin_info, $rt_biz_roles;
			$rt_plugin_info = new RT_Plugin_Info( RT_BIZ_PATH . 'index.php' );
			$rt_attributes = new RT_Attributes( $rt_plugin_info->name );
			$rt_attributes->add_attributes_page( $this->attributes_page_slug ,  Rt_Biz::$menu_page_slug, '', $rt_biz_roles->global_caps['manage_rt_biz'] );
		}
	}
}
