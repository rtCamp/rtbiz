<?php

/**
 * Created by PhpStorm.
 * User: spock
 * Date: 9/11/15
 * Time: 3:48 PM
 */

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'rtBiz_Settings_Migration' ) ) :

	class rtBiz_Settings_Migration {

		/**
		 * rtBiz_Settings_Migration constructor.
		 */
		public function __construct() {
			add_action( 'rt_db_update_finished', array( $this, 'setting_migration' ) );
		}

		public function setting_migration() {
			$flag = get_option( 'rtbiz_setting_migration_complete' );
			if ( empty( $flag ) ) {
				if ( version_compare( RTBIZ_VERSION, '1.4.1', '>=' ) ) {
					$redux = rtbiz_get_redux_settings();
					update_option( 'rtbiz_product_plugin', ! empty( $redux['product_plugin'] ) ? $redux['product_plugin'] : array() );
				}
				update_option( 'rtbiz_setting_migration_complete', 'yes' );
			}

		}
	}

endif;
