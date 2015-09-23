<?php
/**
 * Created by PhpStorm.
 * User: spock
 * Date: 3/6/15
 * Time: 4:37 PM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'P2P_TEXTDOMAIN' ) ) {
	define( 'P2P_TEXTDOMAIN', RTBIZ_TEXT_DOMAIN );
}

if ( ! class_exists( 'Rtbiz_Post_To_Post' ) ) {

	class Rtbiz_Post_To_Post {


		function __construct() {

			$this->_p2p_load_framework();
			add_action( 'wp_loaded', array( $this, '_p2p_init' ) );
			add_action( 'admin_notices', array( $this, 'maybe_install' ) );
		}

		function maybe_install() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$current_ver = get_option( 'p2p_storage' );

			if ( $current_ver == P2P_Storage::$version ) {
				return;
			}

			P2P_Storage::install();

			update_option( 'p2p_storage', P2P_Storage::$version );
		}

		function _p2p_load() {

			if ( ! function_exists( 'p2p_register_connection_type' ) ) {
				require_once RTBIZ_PATH . 'vendor/scribu/lib-posts-to-posts/autoload.php';
			}

			P2P_Storage::init();

			P2P_Query_Post::init();
			P2P_Query_User::init();

			P2P_URL_Query::init();

			P2P_Widget::init();
			P2P_Shortcodes::init();

			if ( is_admin() ) {
				$this->_load_admin();
			}

		}

		function _p2p_init() {
			// Safe hook for calling p2p_register_connection_type()
			do_action( 'p2p_init' );
		}

		private function _p2p_load_framework() {
			if ( is_dir( RTBIZ_PATH . 'vendor' ) ) {
				require_once RTBIZ_PATH . 'vendor/scribu/scb-framework/load.php';
			}
			scb_init( array( $this, '_p2p_load' ) );
		}

		private function _load_admin() {
			//			P2P_Autoload::register( dirname('P2P_'), RTBIZ_PATH. 'vendor/scribu/p2p_required_stuff' );
			if ( ! class_exists( 'P2P_Factory' ) && ! class_exists( 'P2P_Column_Factory' ) && ! class_exists( 'P2P_Column_User' ) && ! class_exists( 'P2P_Column' ) && ! class_exists( 'P2P_Column_Post' ) ) {
				require_once RTBIZ_PATH . 'vendor/scribu/p2p_required_stuff/column.php';
				require_once RTBIZ_PATH . 'vendor/scribu/p2p_required_stuff/column-post.php';
				require_once RTBIZ_PATH . 'vendor/scribu/p2p_required_stuff/column-user.php';
				require_once RTBIZ_PATH . 'vendor/scribu/p2p_required_stuff/factory.php';
				require_once RTBIZ_PATH . 'vendor/scribu/p2p_required_stuff/column-factory.php';
			}

			new P2P_Column_Factory;
		}

	}

}
