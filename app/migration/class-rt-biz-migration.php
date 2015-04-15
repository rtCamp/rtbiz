<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

if ( ! class_exists( 'Rt_Biz_Migration' ) ) {

	/*
	 *
	 */
	class Rt_Biz_Migration {

		/*
		 * construct
		 */
		public function __construct() {
			$this->init();
		}

		/*
		 * load current migration class
		 */
		public function init() {
			if ( strcmp ( RT_BIZ_VERSION , '1.2.12' ) < 1 ) {
				$acl_migration = new Rt_Biz_ACL_Migration();
			}
		}
	}
}