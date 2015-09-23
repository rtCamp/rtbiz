<?php

if ( ! class_exists( 'Rtbiz_P2p' ) ) {

	class Rtbiz_P2p {

		public $p2p_mapping = array();

		public function __construct() {
			add_action( 'p2p_init', array( $this, 'create_connection' ) );
			add_filter( 'p2p_post_admin_column_link', array( $this, 'change_link_to_admin' ), 10, 2 );
		}

		public function change_link_to_admin( $link, $item ) {
			$cpt = rtbiz_get_contact_post_type();
			if ( $cpt == get_current_screen()->post_type && $cpt . '_to_user' === $item->p2p_type && current_user_can( 'edit_users' ) ) {
				return get_edit_user_link( $item->ID );
			}

			return $link;
		}

		public function init_connection( $from_post_type, $to_post_type, $args ) {
			$default_args                                          = array(
				'admin_column' => 'any',
				'title'        => 'Connection',
				'from_labels'  => array(
					'column_title' => 'From',
				),
				'to_labels'    => array(
					'column_title' => 'To',
				),
			);
			$args                                                  = wp_parse_args( $args, $default_args );
			$this->p2p_mapping[ $to_post_type ][ $from_post_type ] = $args;
		}

		/**
		 *  Create a connection between registered post types and Rtbiz_Entity
		 */
		public function create_connection() {
			foreach ( $this->p2p_mapping as $to_post_type => $connected_items ) {
				foreach ( $connected_items as $from_post_type => $args ) {
					$args = array_merge( array(
						'name' => $from_post_type . '_to_' . $to_post_type,
						'from' => $from_post_type,
						'to'   => $to_post_type,
					), $args );
					p2p_register_connection_type( $args );
				}
			}
		}

		public function clear_post_connections_to_entity( $from_post_type, $to_post_type, $id_key, $ids ) {
			p2p_delete_connections( $from_post_type . '_to_' . $to_post_type, array( $id_key => $ids ) );
		}


		public function connect_post_to_entity( $from_post_type, $to_post_type, $from = '', $to = '' ) {
			if ( ! p2p_connection_exists( $from_post_type . '_to_' . $to_post_type, array(
				'from' => $from,
				'to'   => $to
			) )
			) {
				p2p_create_connection( $from_post_type . '_to_' . $to_post_type, array(
					'from' => $from,
					'to'   => $to,
					'meta' => array(
						'date' => current_time( 'mysql' ),
					),
				) );
			}
		}

		public static function connection_to_string( $post_id, $from_post_type, $to_post_type, $term_seperator = ' , ' ) {
			$post     = get_post( $post_id );
			$termsArr = get_posts( array(
				'connected_type'   => $from_post_type . '_to_' . $to_post_type,
				'connected_items'  => $post,
				'nopaging'         => true,
				'suppress_filters' => false,
			) );
			$tmpStr   = '';
			if ( $termsArr ) {
				$sep = '';
				foreach ( $termsArr as $tObj ) {
					$tmpStr .= $sep . $tObj->post_title;
					$sep = $term_seperator;
				}
			}

			return $tmpStr;
		}

		public function get_posts_for_entity( $post_id, $from_post_type, $to_post_type, $fetch_entity = false ) {
			$args = array(
				'post_type'        => $from_post_type,
				'post_status'      => 'any',
				'connected_type'   => $from_post_type . '_to_' . $to_post_type,
				'connected_items'  => $post_id,
				'nopaging'         => true,
				'suppress_filters' => false,
			);

			if ( $fetch_entity ) {
				$args['post_type'] = $to_post_type;
			}

			return get_posts( $args );
		}

		public function clear_post_connection_to_entity( $from_post_type, $to_post_type, $from = '', $to = '' ) {
			return p2p_delete_connections( $from_post_type . '_to_' . $to_post_type, array(
				'from' => $from,
				'to'   => $to
			) );
		}

	}

}
