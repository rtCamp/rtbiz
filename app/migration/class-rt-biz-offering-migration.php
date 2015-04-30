<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

if ( ! class_exists( 'Rt_Biz_Offering_Migration' ) ) {

	/*
	 * Migration to store User permission in custom table
	 */
	class Rt_Biz_Offering_Migration {

		/*
		 * construct
		 */
		public function __construct() {
			if ( strcmp( RT_BIZ_VERSION , '1.2.14' ) >= 0 ) {
				add_action( 'admin_init', array( $this, 'migrate' ), 20 );
			}
		}

		public function migrate(){
			global $wpdb;
			$isMigrated = get_option( 'rtbiz_offering_migration_1.2.14' );
			if ( empty( $isMigrated ) ||  'false' === $isMigrated ){
				$sql = "INSERT INTO {$wpdb->taxonomymeta} (taxonomy_id,meta_key,meta_value) SELECT `taxonomy_id`, '_offering_import_from', 'edd' FROM {$wpdb->taxonomymeta}, {$wpdb->posts} WHERE `meta_key` LIKE '_offering_id' and id=`meta_value` and post_type = 'download'";
				$wpdb->query( $sql );
				$sql = "INSERT INTO {$wpdb->taxonomymeta} (taxonomy_id,meta_key,meta_value) SELECT `taxonomy_id`, '_offering_import_from', 'woocommerce' FROM {$wpdb->taxonomymeta}, {$wpdb->posts} WHERE `meta_key` LIKE '_offering_id' and id=`meta_value` and post_type = 'product'";
				$wpdb->query( $sql );
				update_option( 'rtbiz_offering_migration_1.2.14', 'true' );
			}

		}

	}
}
