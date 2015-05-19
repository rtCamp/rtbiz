<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
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
			if ( strcmp( RT_BIZ_VERSION , '1.2.13' ) >= 0 ) {
				$acl_migration = new Rt_Biz_ACL_Migration();
			}

			if ( strcmp( RT_BIZ_VERSION , '1.2.14' ) >= 0 ) {
				$offering_migration = new Rt_Biz_Offering_Migration();
			}

			if ( strcmp( RT_BIZ_VERSION , '1.2.16' ) >= 0 ) {
				$mailbox_migration = new Rt_Biz_Mailbox_Migration();
			}
		}
	}
}
