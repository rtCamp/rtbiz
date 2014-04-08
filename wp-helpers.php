<?php
/*
  Plugin Name: wp-helpers
  Plugin URI: http://rtcamp.com/
  Description: This plugin contains common libs that are used in many rtcamp plugins
  Version: 0.1
  Author: rtcamp
  Author URI: http://rtcamp.com/
 */
$rt_helper_version = '0.1';
$rt_wp_helper_path = __FILE__;

#. '/rt-wp-autoloader/class-rt-wp-autoload.php';

global $rt_wp_helper;
if ( ! isset ( $rt_wp_helper ) ) {
	$rt_wp_helper = array(
		'version' => $rt_helper_version, 'wp_helper_path' => $rt_wp_helper_path,
	);
	function rt_wp_helper_loader() {
		global $rt_wp_helper;

		$rt_autoloader_file = realpath( dirname( $rt_wp_helper[ 'wp_helper_path' ] ) ) . '/rt-wp-autoloader/class-rt-wp-autoload.php';
		if ( file_exists( $rt_autoloader_file ) ) {
			include_once $rt_autoloader_file;
		}
		define( 'WP_HELPER_FILE', $rt_wp_helper[ 'wp_helper_path' ] );
		/**
		 * Initiate Loader class
		 */
		if ( class_exists( 'RT_WP_Autoload' ) ) {
			$GLOBALS[ 'wp_helper_loader' ] = new RT_WP_Autoload();
		}

	}
	add_action( 'plugins_loaded', 'rt_wp_helper_loader', 1 );
} else {
	if ( version_compare( $rt_helper_version, $rt_wp_helper[ 'version' ], '>' ) ) {
		$rt_wp_helper[ 'version' ]        = $rt_helper_version;
		$rt_wp_helper[ 'wp_helper_path' ] = $rt_wp_helper_path;
	}
}

