<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-rt-contacts-attributes
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Contacts_Attributes' ) ) {
	class Rt_Contacts_Attributes {
		public function __construct() {
			global $rt_attributes, $rt_plugin_info, $rt_contacts_roles;
			$rt_plugin_info = new RT_Plugin_Info( RT_CONTACTS_PATH . 'index.php' );
			$rt_attributes = new RT_Attributes( $rt_plugin_info->name );
			$rt_attributes->add_attributes_page( Rt_Contacts::$menu_page_slug, '', $rt_contacts_roles->global_caps['manage_contacts'] );
		}
	}
}
