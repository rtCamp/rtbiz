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
			add_action( 'restrict_manage_posts', array( $this, 'restrict_entity_by_attributes' ) );
			add_action( 'parse_query', array( $this, 'convert_term_id_to_term_slug_for_attributes_filter' ) );
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
						$links[] = '<a href="' . add_query_arg( array( $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ) => $t->term_id ) ) . '">' . $t->name . '</a>';
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

		function convert_term_id_to_term_slug_for_attributes_filter( $query ) {
			global $pagenow, $typenow, $rt_biz_rt_attributes;
			$qv = &$query->query_vars;

			$post_types = array( rt_biz_get_person_post_type(), rt_biz_get_organization_post_type() );
			if ( $pagenow != 'edit.php' || ! in_array( $typenow, $post_types ) ) {
				return;
			}

			$rt_biz_attributes_model = new RT_Attributes_Model();
			$rt_biz_attributes_relationship_model = new RT_Attributes_Relationship_Model();
			$relations = $rt_biz_attributes_relationship_model->get_relations_by_post_type( $typenow );
			$taxonomies = array();

			foreach ( $relations as $r ) {
				$attr = $rt_biz_attributes_model->get_attribute( $r->attr_id );
				if ( $attr->attribute_store_as == 'taxonomy' ) {
					$taxonomies[] = $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name );
					if ( ! empty( $qv[ $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ) ] ) ) {
						$qv[ 'taxonomy' ] = $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name );
						$qv[ 'term_id' ] = $qv[ $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ) ];
						$term = get_term_by( 'id', $qv[ 'term_id' ], $qv[ 'taxonomy' ] );
						$qv[ 'term' ] = $term->slug;
						unset( $qv[ $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ) ] );
					} else {
						unset( $qv[ $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ) ] );
					}
				}
			}
		}

		function restrict_entity_by_attributes() {
			global $typenow, $wp_query, $rt_biz_rt_attributes;

			$post_types = array( rt_biz_get_person_post_type(), rt_biz_get_organization_post_type() );

			if ( in_array( $typenow, $post_types ) ) {
				$rt_biz_attributes_model = new RT_Attributes_Model();
				$rt_biz_attributes_relationship_model = new RT_Attributes_Relationship_Model();
				$relations = $rt_biz_attributes_relationship_model->get_relations_by_post_type( $typenow );
				foreach ( $relations as $r ) {
					$attr = $rt_biz_attributes_model->get_attribute( $r->attr_id );
					if ( $attr->attribute_store_as == 'taxonomy' ) {
						$tax = get_taxonomy( $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ) );
						wp_dropdown_categories( array(
							'show_option_all' => __( "Show All {$tax->label}" ),
							'show_option_none' => __( "No {$tax->label}" ),
							'taxonomy' => $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ),
							'name' => $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ),
							'orderby' => 'name',
							'selected' => $wp_query->query[ $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ) ],
							'hierarchical' => true,
							'depth' => 3,
							'show_count' => false, // Show # listings in parens
							'hide_empty' => true, // Don't show businesses w/o listings
						) );
					}
				}
			}
		}

	}

}
