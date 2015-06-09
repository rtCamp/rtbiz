<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rtbiz_Text_Diff' ) ) {

	if ( ! class_exists( 'WP_Text_Diff_Renderer_Table' ) ) {
		require( ABSPATH . WPINC . '/wp-diff.php' );
	}

	/**
	 * Class Rtbiz_Text_Diff
	 * Email Diff text generator
	 *
	 * @since  0.1
	 *
	 * @author udit
	 */
	class Rtbiz_Text_Diff extends WP_Text_Diff_Renderer_Table {

		/**
		 * @var int
		 */
		var $_leading_context_lines = 2;
		/**
		 * @var int
		 */
		var $_trailing_context_lines = 2;

		/**
		 * Added Content format for Email Diff Content
		 *
		 * @since  0.1
		 *
		 * @param string $line
		 *
		 * @return string
		 */
		function addedLine( $line ) {
			return "<td style='padding: .5em;border: 0;width:25px;'>+</td><td style='padding: .5em;border: 0;background-color: #dfd; width: auto;'>{$line}</td>";
		}

		/**
		 * Delete Content format for Email Diff Content
		 *
		 * @since  0.1
		 *
		 * @param string $line HTML-escape the value.
		 *
		 * @return string
		 */
		function deletedLine( $line ) {
			return "<td style='padding: .5em;border: 0;width:25px;' >-</td><td style='padding: .5em;border: 0;background-color: #fdd; width: 40%;' >{$line}</td>";
		}

		/**
		 * Content format for Email Diff Content
		 *
		 * @since  0.1
		 *
		 * @param string $line HTML-escape the value.
		 *
		 * @return string
		 */
		function contextLine( $line ) {
			return "<td style='padding: .5em;border: 0;' > </td><td style='padding: .5em;border: 0; width: auto;'>{$line}</td>";

		}
	}
}

