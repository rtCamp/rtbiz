<?php
/*
  Plugin Name: rtLib
  Plugin URI: http://rtcamp.com/
  Description: This plugin contains common libs that are used in many rtCamp plugins
  Version: 1.4
  Author: rtCamp
  Author URI: http://rtcamp.com/
 */
$rt_lib_version = '1.4';
$rt_lib_path    = __FILE__;

#. '/rt-wp-autoloader/class-rt-wp-autoload.php';

global $rt_lib;
if ( ! isset( $rt_lib ) ) {
	$rt_lib = array(
		'version'     => $rt_lib_version,
		'rt_lib_path' => $rt_lib_path,
	);
	function rt_lib_loader() {
		global $rt_lib;

		$rt_autoloader_file = realpath( dirname( $rt_lib['rt_lib_path'] ) ) . '/rt-wp-autoloader/class-rt-wp-autoload.php';
		if ( file_exists( $rt_autoloader_file ) ) {
			include_once $rt_autoloader_file;
		}

		if ( ! defined( 'RT_LIB_FILE' ) ) {
			define( 'RT_LIB_FILE', $rt_lib['rt_lib_path'] );
		}

		/**
		 * Initiate Loader class
		 */
		if ( class_exists( 'RT_WP_Autoload' ) ) {
			$GLOBALS['rt_lib_loader'] = new RT_WP_Autoload();
		}

	}

	add_action( 'plugins_loaded', 'rt_lib_loader', 1 );
} else {
	if ( version_compare( $rt_lib_version, $rt_lib['version'], '>' ) ) {
		$rt_lib['version']     = $rt_lib_version;
		$rt_lib['rt_lib_path'] = $rt_lib_path;
	}
}
