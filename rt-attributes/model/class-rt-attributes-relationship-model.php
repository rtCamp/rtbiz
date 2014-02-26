<?php
/**
 * Created by PhpStorm.
 * User: faishal
 * Date: 24/02/14
 * Time: 3:47 PM
 */
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Description of RT_WP_Attributes_Relationship_Model
 * Model class for Attributes Relationship with other post types & CRUD Operations.
 *
 * @author Udit Desai <desaiuditd@gmail.com>
 */

if ( ! class_exists( 'RT_Attributes_Relationship_Model' ) ) {
	/**
	 * Class RT_WP_Attributes_Relationship_Model
	 */
	class RT_Attributes_Relationship_Model extends RT_DB_Model
	{
		public function __construct()
		{
			parent::__construct( 'wp_attributes_relationship' );
		}

		function get_relations_by_post_type( $post_type, $attr_id = '' )
		{
			$args = array( 'post_type' => $post_type );
			if ( ! empty( $attr_id ) ) {
				$args[ 'attr_id' ] = $attr_id;
			}

			return parent::get( $args );
		}

		function add_relation( $data )
		{
			return parent::insert( $data );
		}

		function delete_relation( $where )
		{
			return parent::delete( $where );
		}

		function update_relation( $data, $where )
		{
			return parent::update( $data, $where );
		}
	}
}