<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of Rt_Biz_Attributes
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Biz_Attributes' ) ) {

	class Rt_Biz_Attributes {

		var $attributes_page_slug = 'rtpm-attributes';

		public function __construct() {
			add_action( 'init', array( $this, 'init_attributes' ) );
		}

		function init_attributes() {
			global $rt_biz_rt_attributes;
			$rt_biz_rt_attributes = new RT_Attributes( RT_BIZ_TEXT_DOMAIN );

			$admin_cap = rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'admin' );
			$editor_cap = rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'editor' );

			$terms_caps = array(
				'manage_terms' => $editor_cap,
				'edit_terms'   => $editor_cap,
				'delete_terms' => $editor_cap,
				'assign_terms' => $editor_cap,
			);

			$rt_biz_rt_attributes->add_attributes_page( $this->attributes_page_slug, Rt_Biz::$dashboard_slug, '', $admin_cap, $terms_caps, $render_type = false, $storage_type = false, $orderby = false );
		}

	}

}
