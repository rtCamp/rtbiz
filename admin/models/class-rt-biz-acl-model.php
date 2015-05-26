<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Description of RTBIZACLModel
 * Model for 'rt_wp_rtbiz_acl' table in DB
 */
if ( ! class_exists( 'RT_Biz_ACL_Model' ) ) {
	class RT_Biz_ACL_Model extends RT_DB_Model {

		public function __construct() {
			parent::__construct( 'wp_rtbiz_acl' );
		}

		/**
		 * get acl
		 *
		 * @param $where
		 *
		 * @return mixed
		 *
		 */
		function get_acl( $where ) {
			return parent::get( $where, false, false, 'userid asc' );
		}

		/**
		 * add acl entry
		 *
		 * @param $data
		 *
		 * @return int
		 */
		function add_acl( $data ) {
			return parent::insert( $data );
		}

		/**
		 * update acl entry
		 *
		 * @param $data
		 * @param $where
		 *
		 * @return mixed
		 *
		 */
		function update_acl( $data, $where ) {
			return parent::update( $data, $where );
		}

		/**
		 * remove ACL
		 *
		 * @param $where
		 *
		 * @return int
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function remove_acl( $where ) {
			return parent::delete( $where );
		}

		/**
		 * get acl records
		 * @param $querystr
		 *
		 * @return mixed
		 */
		function get_result_by_query( $querystr ) {
			global $wpdb;
			return $wpdb->get_results( $querystr );
		}
	}
}