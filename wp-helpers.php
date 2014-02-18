<?php
/*
  Plugin Name: wp-helpers
  Plugin URI: http://rtcamp.com/
  Description: This plugin contains common libs that are used in many rtcamp plugins
  Version: 0.1
  Author: rtcamp
  Author URI: http://rtcamp.com/
 */

if ( file_exists( './rt-wp-autoloader/class-rt-wp-autoload.php' ) ){
	include_once './rt-wp-autoloader/class-rt-wp-autoload.php';
}
/**
 * Initiate Loader class
 */
if ( class_exists( 'RT_WP_Autoload' ) ){
	$GLOBALS[ 'wp_helper_loader' ] = new RT_WP_Autoload();
}