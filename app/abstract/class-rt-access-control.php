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
 * Description of class-rt-access-control
 *
 * @author udit
 */
if( ! class_exists('Rt_Access_Control') ) {
	class Rt_Access_Control {

		public static $permissions;

		public static $modules;

		public function __construct() {
			add_action( 'init', array( $this, 'init_acl' ) );
		}

		function init_acl() {
			/**
			 * $biz_module = array( RT_BIZ_TEXT_DOMAIN => __( 'rtBiz' ) );
			 */
			self::$modules = apply_filters( 'rt_biz_modules', array() );

			self::$permissions = array(
				'no_access' => array(
					'value' => 0,
					'name' => __( 'No Access' ),
				),
				'author' => array(
					'value' => 10,
					'name' => __( 'Author - Read/Write (Self)' ),
				),
				'editor' => array(
					'value' => 20,
					'name' => __( 'Editor - Read/Write (Everything)' ),
				),
				'admin' => array(
					'value' => 30,
					'name' => __( 'Admin - Read/Write (Everything) + Settings' ),
				),
			);
		}

		function get_capability_from_access_role( $module_key, $role = 'no_access' ) {
			$module_key = rt_biz_sanitize_module_key( $module_key );
			if ( isset( self::$permissions[ $role ] ) ) {
				return $module_key . '_' . $role;
			}
			return '';
		}

		function save_acl_settings() {
			if ( ! isset( $_POST['rt_biz_acl_permissions'] ) ) {
				return;
			}
			if ( ! isset( $_POST['rt_biz_module_permissions'] ) || ! is_array( $_POST['rt_biz_module_permissions'] ) ) {
				return;
			}
			$module_permissions = $_POST['rt_biz_module_permissions'];
			update_site_option( 'rt_biz_module_permissions', $module_permissions );
		}

		function acl_settings_ui() {

			$this->save_acl_settings();

			rt_biz_get_template( 'acl-settings.php' );
		}
	}
}
