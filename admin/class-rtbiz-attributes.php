<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of Rtbiz_Attributes
 *
 * @author udit
 */
if ( ! class_exists( 'Rtbiz_Attributes' ) ) {

	class Rtbiz_Attributes {

		public static $page_slug = 'rtbiz-attributes';

		public function __construct() {
			Rt_Biz::$loader->add_action( 'init', $this, 'rt_biz_init_attributes' );
			Rt_Biz::$loader->add_action( 'restrict_manage_posts', $this, 'rt_biz_restrict_entity_by_attributes' );
			Rt_Biz::$loader->add_action( 'parse_query', $this, 'rt_biz_convert_term_id_to_term_slug_for_attributes_filter' );
		}

		public function rt_biz_init_attributes() {
			global $rt_biz_rt_attributes;
			$rt_biz_rt_attributes = new RT_Attributes( RTBIZ_TEXT_DOMAIN );

			$editor_cap = rtbiz_get_access_role_cap( RTBIZ_TEXT_DOMAIN, 'editor' );
			$author_cap = rtbiz_get_access_role_cap( RTBIZ_TEXT_DOMAIN, 'author' );

			$terms_caps = array(
				'manage_terms' => $editor_cap,
				'edit_terms'   => $editor_cap,
				'delete_terms' => $editor_cap,
				'assign_terms' => $author_cap,
			);

			$rt_biz_rt_attributes->add_attributes_page( self::$page_slug, Rtbiz_Dashboard::$page_slug, '', $editor_cap, $terms_caps, $render_type = false, $storage_type = false, $orderby = false );
		}

		public function rt_biz_convert_term_id_to_term_slug_for_attributes_filter( $query ) {
			global $pagenow, $typenow, $rt_biz_rt_attributes;
			$qv = &$query->query_vars;

			$post_types = array( rtbiz_get_contact_post_type(), rtbiz_get_company_post_type() );
			if ( 'edit.php' != $pagenow || ! in_array( $typenow, $post_types ) ) {
				return;
			}

			$rt_biz_attributes_model = new RT_Attributes_Model();
			$rt_biz_attributes_relationship_model = new RT_Attributes_Relationship_Model();
			$relations = $rt_biz_attributes_relationship_model->get_relations_by_post_type( $typenow );
			$taxonomies = array();

			foreach ( $relations as $r ) {
				$attr = $rt_biz_attributes_model->get_attribute( $r->attr_id );
				if ( 'taxonomy' == $attr->attribute_store_as ) {
					$taxonomies[] = $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name );
					if ( ! empty( $qv[ $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ) ] ) ) {
						$qv['taxonomy'] = $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name );
						$qv['term_id'] = $qv[ $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ) ];
						$term = get_term_by( 'id', $qv['term_id'], $qv['taxonomy'] );
						$qv['term'] = $term->slug;
						unset( $qv[ $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ) ] );
					} else {
						unset( $qv[ $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ) ] );
					}
				}
			}
		}

		public function rt_biz_restrict_entity_by_attributes() {
			global $typenow, $wp_query, $rt_biz_rt_attributes;

			$post_types = array( rtbiz_get_contact_post_type(), rtbiz_get_company_post_type() );

			if ( in_array( $typenow, $post_types ) ) {
				$rt_biz_attributes_model = new RT_Attributes_Model();
				$rt_biz_attributes_relationship_model = new RT_Attributes_Relationship_Model();
				$relations = $rt_biz_attributes_relationship_model->get_relations_by_post_type( $typenow );
				foreach ( $relations as $r ) {
					$attr = $rt_biz_attributes_model->get_attribute( $r->attr_id );
					if ( 'taxonomy' == $attr->attribute_store_as ) {
						$tax = get_taxonomy( $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ) );
						if ( ! empty( $tax ) ) {
							$args = array(
								'show_option_all' => __( "Show All {$tax->label}" ),
								'taxonomy' => $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ),
								'name' => $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ),
								'orderby' => 'name',
								'hierarchical' => true,
								'depth' => 3,
								'show_count' => false, // Show # listings in parens
								'hide_empty' => true, // Don't show businesses w/o listings
							);
							if ( isset( $wp_query->query[ $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ) ] ) ) {
								$args['selected'] = $wp_query->query[ $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name ) ];
							}
							wp_dropdown_categories( $args );
						}
					}
				}
			}
		}

	}

}
