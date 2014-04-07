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
 * Description of class-rt-biz-access-control
 *
 * @author udit
 */
if( ! class_exists( 'Rt_Biz_Access_Control' ) ) {
	class Rt_Biz_Access_Control {

		var $permissions = array(
			''
		);

		public function __construct() {
			add_action( 'init', array( $this, 'init_acl' ) );
		}

		function init_acl() {

		}

		static function acl_settings_ui() {
			rt_biz_get_template( 'acl-settings.php' );
		}
	}
}
