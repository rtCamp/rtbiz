<?php

/**
 * Description of RT_DB_Update
 * Required : rt_plugin_info.php
 *
 * @author Dipesh KAkadiya<dipesh.kakadiya@rtcamp.com>
 *         version 1.1
 */

if ( ! class_exists( 'RT_Guide_Tour' ) ) {
	/**
	 * Class RT_Guide_Tour
	 */
	class RT_Guide_Tour {

		/**
		 * $tour_list
		 */
		var $tour_list = array();


		/**
		 * construct
		 */
		public function __construct() {
			$this->hook();
		}

		/**
		 * Hook
		 */
		public function hook() {
			add_action( 'admin_enqueue_scripts', array( $this, 'rt_guide_tour_load' ) );
		}

		/**
		 * @return array
		 */
		function get_guide_tour_list() {
			$pointers = array();
			$pointers = apply_filters( 'rt_guide_tour_list', $pointers );

			return $pointers;
		}

		/**
		 *
		 */
		function parse_guide_tour_list() {
			$pointers = $this->get_guide_tour_list();
			if ( empty( $pointers ) ) {
				return;
			}
			foreach ( $pointers as $i => $pointer ) {
				$pointer['id']                     = "{$pointer['prefix']}{$pointer['version']}_{$i}";
				$this->tour_list[ $pointer['id'] ] = (object) $pointer;
			}
		}

		/**
		 * @param $page
		 *
		 * @return array
		 */
		function filter( $page ) {
			if ( empty( $this->tour_list ) ) {
				return array();
			};

			$host = 'http://' . $_SERVER['HTTP_HOST'];
			$Path = $_SERVER['REQUEST_URI'];

			$uid           = get_current_user_id();
			$dismissed     = explode( ',', (string) get_user_meta( $uid, 'dismissed_wp_pointers', true ) );
			$active_ids    = array_diff( array_keys( $this->tour_list ), $dismissed );
			$new_tour_list = array();
			foreach ( $this->tour_list as $i => $pointer ) {
				if ( in_array( $i, $active_ids, true ) // is active
				     && isset( $pointer->where ) // has where
				) {
					$new_tour_list[] = $pointer;
				}
			}
			$count = count( $new_tour_list );
			if ( 0 == $count ) {
				return array();
			}

			foreach ( array_values( $new_tour_list ) as $i => $pointer ) {
				$new_tour_list[ $i ]->prev = ( $i - 1 ) > - 1 ? $new_tour_list[ $i - 1 ]->id : '';
				$new_tour_list[ $i ]->next = ( $i + 1 ) < $count ? $new_tour_list[ $i + 1 ]->id : '';
				if ( ( $i - 1 ) > - 1 && ! empty( $new_tour_list[ $i - 1 ]->where ) && $Path != $new_tour_list[ $i - 1 ]->where ) {
					$new_tour_list[ $i ]->prevurl = $host . $new_tour_list[ $i - 1 ]->where;
				}
				if ( ( $i + 1 ) < $count && ! empty( $new_tour_list[ $i + 1 ]->where ) && $Path != $new_tour_list[ $i + 1 ]->where ) {
					$new_tour_list[ $i ]->nexturl = $host . $new_tour_list[ $i + 1 ]->where;
				}
				if ( $Path == $new_tour_list[ $i ]->where ) {
					unset( $new_tour_list[ $i ]->where );
				}
			}

			return $new_tour_list;
		}

		/**
		 * @return bool
		 */
		function rt_guide_tour_check() {
			$admin_pointers = $this->get_guide_tour_list();
			if ( ! empty( $admin_pointers ) ) {
				return true;
			}
		}

		/**
		 *
		 */
		public function rt_guide_tour_load( $page ) {
			if ( $this->rt_guide_tour_check() ) {

				$this->get_guide_tour_list();
				$this->parse_guide_tour_list();
				$pointers = $this->filter( $page );

				if ( empty( $pointers ) ) {
					return;
				}

				wp_enqueue_style( 'wp-pointer' );
				wp_enqueue_script( 'rt_quide_tour', plugin_dir_url( __FILE__ ) . '/assets/rt_quide_tour.js', array( 'wp-pointer' ), null, true );

				$rt_guide_tour = array(
					'next_label'  => __( 'Next' ),
					'prev_label'  => __( 'Prev' ),
					'close_label' => __( 'Dismiss' ),
					'pointers'    => $pointers,
				);
				wp_localize_script( 'rt_quide_tour', 'RtGuideTourList', $rt_guide_tour );

			}
		}
	}
}
