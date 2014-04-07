<?php

/**
 * Created by PhpStorm.
 * User: udit
 * Date: 7/4/14
 * Time: 7:36 PM
 */

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
 * Description of class-rt-biz-acl
 *
 * @author udit
 */
if( ! class_exists( 'Rt_Biz_ACL' ) ) {
	class Rt_Biz_ACL extends Rt_Access_Control {
		function __construct() {
			add_filter( 'rt_biz_modules', array( $this, 'register_rt_biz_module' ) );
		}

		function register_rt_biz_module( $modules ) {
			$modules[ rt_biz_sanitize_module_key( RT_BIZ_TEXT_DOMAIN ) ] = __( 'rtBiz' );
			return $modules;
		}
	}
}
