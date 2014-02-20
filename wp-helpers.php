<?php
/*
  Plugin Name: wp-helpers
  Plugin URI: http://rtcamp.com/
  Description: This plugin contains common libs that are used in many rtcamp plugins
  Version: 0.1
  Author: rtcamp
  Author URI: http://rtcamp.com/
 */
$rt_autoloader_file = realpath( dirname( __FILE__ ) ) . '/rt-wp-autoloader/class-rt-wp-autoload.php';
if ( file_exists( $rt_autoloader_file ) ){
	include_once $rt_autoloader_file ;
}
/**
 * Initiate Loader class
 */
if ( class_exists( 'RT_WP_Autoload' ) ){
	$GLOBALS[ 'wp_helper_loader' ] = new RT_WP_Autoload();
}
