<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rt_Biz_Metabox' ) ) {

	abstract class Rt_Biz_Metabox {

		public static function ui( $post ) {

		}

		public static function save( $post_id, $post ) {

		}

	}

}
