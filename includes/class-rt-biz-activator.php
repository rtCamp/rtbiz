<?php
/**
 * Fired during plugin activation
 *
 * @link       https://rtcamp.com/
 * @since      1.2.19
 *
 * @package    rt_biz
 * @subpackage rt_biz/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.2.19
 * @package    rt_biz
 * @subpackage rt_biz/includes
 * @author     Dipesh <dipesh.kakadiya@rtcamp.com>
 */
if ( ! class_exists( 'Rt_Biz_Activator' ) ) {
	class Rt_Biz_Activator {

		/**
		 * Short Description. (use period)
		 *
		 * Long Description.
		 *
		 * @since    1.0.0
		 */
		public static function activate() {
			// Add the transient to redirect
			update_option( '_rt_biz_activation_redirect', true, false );
			do_action( 'rt_biz_activation_redirect' );
		}

	}
}
