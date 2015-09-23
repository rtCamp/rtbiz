<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rtbiz_Migration' ) ) {

	/*
	 *
	 */

	class Rtbiz_Migration {

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
