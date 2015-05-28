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

		}
	}
}
