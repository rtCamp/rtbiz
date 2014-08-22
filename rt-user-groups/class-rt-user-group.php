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
 * Description of class-rt-user-groups
 *
 * @author udit
 */
add_action( 'after_setup_theme', array( 'RT_User_Group', 'load' ), 1 );

if ( ! class_exists( 'RT_User_Group' ) ) {

	class RT_User_Group {

		static function load() {
			global $RT_User_Group;
			$RT_User_Group = new RT_User_Group();
		}

		public function __construct() {

		}

	}

}