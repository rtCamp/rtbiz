<?php
/**
 * Fired during plugin activation
 *
 * @link       https://rtcamp.com/
 * @since      1.2.19
 *
 * @package    rtbiz
 * @subpackage rtbiz/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.2.19
 * @package    rtbiz
 * @subpackage rtbiz/includes
 * @author     Dipesh <dipesh.kakadiya@rtcamp.com>
 */
if ( ! class_exists( 'Rtbiz_Activator' ) ) {
	class Rtbiz_Activator {

		/**
		 * Short Description. (use period)
		 *
		 * Long Description.
		 *
		 * @since    1.0.0
		 */
		public static function activate() {
			// Add the transient to redirect
			update_option( 'rtbiz_activation_redirect', true, false );
			do_action( 'rtbiz_activation_redirect' );
		}

	}
}
