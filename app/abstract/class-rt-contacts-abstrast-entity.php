<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Description of rt-contacts-abstrast-entity
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Contacts_Abstrast_Entity' ) ) {
	class Rt_Contacts_Abstrast_Entity {

		public $enabled_post_types = array();

		public function __construct() {

		}

		function register_post_type( $name, $labels = array(), $menu_icon = '', $menu_position = '' ) {
			$args = array(
				'labels' => $labels,
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true, // Show the UI in admin panel
				'show_in_nav_menus' => true,
				'menu_icon' => $menu_icon,
				'menu_position' => $menu_position,
				'supports' => array( 'title', 'editor', 'author', 'comments', 'thumbnail', 'custom-fields' ),
				'capability_type' => $name,
			);
			register_post_type( $name, $args );
		}

		function get_posts_for_taxonomy( $post_type, $post_id, $key ) {
			return get_posts(
				array(
					'post_type' => $key,
					'post_status' => 'any',
					'connected_type' => $key.'_to_'.$post_type,
					'connected_items' => $post_id,
					'nopaging' => true,
					'suppress_filters' => false,
				)
			);
		}
	}
}