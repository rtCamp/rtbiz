<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rtbiz_Metabox' ) ) {

	abstract class Rtbiz_Metabox {

		public static function ui( $post ) {

		}

		public static function save( $post_id, $post ) {

		}

	}

}
