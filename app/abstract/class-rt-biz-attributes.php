<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of Rt_Biz_Attributes
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Biz_Attributes' ) ) {

	class Rt_Biz_Attributes {

		var $attributes_page_slug = 'rtpm-attributes';

		public function __construct() {
			add_action( 'init', array( $this, 'init_attributes' ) );
			add_filter( 'rt_entity_columns', array( $this, 'attributes_columns' ), 10, 2 );
			add_action( 'rt_entity_manage_columns', array( $this, 'manage_attributes_columns' ), 10, 3 );
		}

		function init_attributes() {
			global $rt_biz_rt_attributes;
			$rt_biz_rt_attributes = new RT_Attributes( RT_BIZ_TEXT_DOMAIN );

			$admin_cap = rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'admin' );
			$editor_cap = rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'editor' );

			$terms_caps = array(
				'manage_terms' => $editor_cap,
				'edit_terms'   => $editor_cap,
				'delete_terms' => $editor_cap,
				'assign_terms' => $editor_cap,
			);

			$rt_biz_rt_attributes->add_attributes_page( $this->attributes_page_slug, Rt_Biz::$dashboard_slug, '', $admin_cap, $terms_caps, $render_type = false, $storage_type = false, $orderby = false );
		}

		function attributes_columns( $columns, $entity_obj ) {
			$rt_biz_attributes_model = new RT_Attributes_Model();
			$rt_biz_attributes_relationship_model = new RT_Attributes_Relationship_Model();
			$relations = $rt_biz_attributes_relationship_model->get_relations_by_post_type( $entity_obj->post_type );
			foreach ( $relations as $r ) {
				$attr = $rt_biz_attributes_model->get_attribute( $r->attr_id );
				if ( $attr->attribute_store_as == 'taxonomy' ) {
					$columns[ $attr->attribute_name ] = $attr->attribute_label;
				}
			}
			return $columns;
		}

		function manage_attributes_columns( $column, $post_id, $entity_obj ) {
			global $rt_biz_rt_attributes;
			$rt_biz_attributes_model = new RT_Attributes_Model();
			$rt_biz_attributes_relationship_model = new RT_Attributes_Relationship_Model();
			$relations = $rt_biz_attributes_relationship_model->get_relations_by_post_type( $entity_obj->post_type );
			foreach ( $relations as $r ) {
				$attr = $rt_biz_attributes_model->get_attribute( $r->attr_id );
				if ( $attr->attribute_store_as == 'taxonomy' && $attr->attribute_name == $column ) {
					$terms = wp_get_post_terms( $post_id, $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ) );
					$links = array();
					foreach ( $terms as $t ) {
						$links[] = '<a href="' . add_query_arg(array( 'taxonomy' => $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ), 'term' => $t->slug )) . '">' . $t->name . '</a>';
					}

					if ( ! empty( $links ) ) {
						echo implode( ' , ', $links );
					} else {
						echo '-';
					}
					break;
				}
			}
		}

	}

}
