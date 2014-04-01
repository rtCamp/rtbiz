<?php
/**
 * Created by PhpStorm.
 * User: faishal
 * Date: 24/02/14
 * Time: 3:48 PM
 */
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Description of RT_WP_Attributes_Model
 * Model class for Attributes & CRUD Operations.
 *
 * @author Udit Desai <desaiuditd@gmail.com>
 */

if ( ! class_exists( 'RT_Attributes_Model' ) ) {
	/**
	 * Class RT_WP_Attributes_Model
	 */
	class RT_Attributes_Model extends RT_DB_Model {

		/**
		 *
		 */
		public function __construct() {
			parent::__construct( 'wp_attributes' );
		}

		/**
		 * @param $attribute_name
		 *
		 * @return bool
		 */
		function attribute_exists( $attribute_name ) {
			$attributes = $this->get_all_attributes();
			foreach ( $attributes as $attribute ) {
				if ( $attribute_name == $attribute->attribute_name ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * @return array
		 */
		function get_all_attributes() {
			return parent::get( array() );
		}

		/**
		 * @param $attribute_id
		 *
		 * @return bool
		 */
		function get_attribute( $attribute_id ) {
			$args      = array( 'id' => $attribute_id );
			$attribute = parent::get( $args );
			if ( empty( $attribute ) ) {
				return false;
			}

			return $attribute[ 0 ];
		}

		/**
		 * @param $attribute_id
		 *
		 * @return bool
		 */
		function get_attribute_name( $attribute_id ) {
			$attribute = $this->get_attribute( $attribute_id );
			if ( empty( $attribute ) ) {
				return false;
			}

			return $attribute->attribute_name;
		}

		/**
		 * @param $data
		 *
		 * @return int
		 */
		function add_attribute( $data ) {
			return parent::insert( $data );
		}

		/**
		 * @param $data
		 * @param $where
		 *
		 * @return mixed
		 */
		function update_attribute( $data, $where ) {
			return parent::update( $data, $where );
		}

		/**
		 * @param $where
		 *
		 * @return int
		 */
		function delete_attribute( $where ) {
			return parent::delete( $where );
		}
	}
}
