<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Description of class-rt-person
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Person' ) ) {
	class Rt_Person extends Rt_Contacts_Abstrast_Entity {
		public function __construct() {

		}
	}
}