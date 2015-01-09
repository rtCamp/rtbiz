<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rtlib_Gravity_Fields_Mapping_Model' ) ) {
	/**
	 * Class Rt_HD_Gravity_Fields_Mapping_Model
	 * This Uses to access database for Gravity Fields
	 *
	 * @since rt-Helpdesk 0.1
	 *
	 */
	class Rtlib_Gravity_Fields_Mapping_Model extends RT_DB_Model {
		/**
		 * constructor
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function __construct() {
			parent::__construct( 'wp_rtlib_gravity_fields_mapping' );
		}

		/**
		 * returns all gravity field mapping
		 *
		 * @return array
		 *
		 * @since rt-Helpdesk 0.1
		 *
		 */
		function get_all_mappings() {
			$gravity_mappings = parent::get( array() );

			return $gravity_mappings;
		}

		/**
		 * returns mapping of specific form
		 *
		 * @param string $form_id
		 *
		 * @return array
		 *
		 * @since rt-Helpdesk 0.1
		 *
		 */
		function get_mapping( $form_id = '' ) {
			$args = array();
			if ( ! empty( $form_id ) ) {
				$args['form_id'] = $form_id;
			}

			return parent::get( $args );
		}

		/**
		 * update mapping of gravity field
		 *
		 * @param $data
		 * @param $where
		 *
		 * @return mixed
		 *
		 *
		 */
		function update_mapping( $data, $where ) {
			return parent::update( $data, $where );
		}

		/**
		 * add mapping of gravity field
		 *
		 * @param $data
		 *
		 * @return int
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function add_mapping( $data ) {
			$data['create_date'] = current_time( 'mysql' );
			$data['enable']      = 'yes';

			return parent::insert( $data );
		}

		/**
		 *  delete mapping of gravity field
		 *
		 * @param $where
		 *
		 * @return int
		 *
		 * @since rt-Helpdesk 0.1
		 *
		 */
		function delete_mapping( $where ) {
			return parent::delete( $where );
		}
	}
}
