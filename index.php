<?php

/*
  Plugin Name: WordPress Contacts
  Plugin URI: http://rtcamp.com/
  Description: Manage Contacts for WordPress
  Version: 0.0.1
  Author: rtCamp
  Author URI: http://rtcamp.com
  License: GPL
  Text Domain: rt-contacts
 */

if ( ! defined( 'RT_CONTACTS_VERSION' ) ) {
	define( 'RT_CONTACTS_VERSION', '0.0.1' );
}
if ( ! defined( 'RT_CONTACTS_PATH' ) ) {
	define( 'RT_CONTACTS_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'RT_CONTACTS_URL' ) ) {
	define( 'RT_CONTACTS_URL', plugin_dir_url( __FILE__ ) );
}
if ( !defined( 'RT_CONTACTS_PATH_TEMPLATES' ) ) {
	define( 'RT_CONTACTS_PATH_TEMPLATES', plugin_dir_path( __FILE__ ) . 'app/view/' );
}

/**
 * Auto Loader Function
 *
 * Autoloads classes on instantiation. Used by spl_autoload_register.
 *
 * @param string $class_name The name of the class to autoload
 */
function rt_contacts_autoloader( $class_name ) {

	$file_name = 'class-' . implode( '-', array_map( 'strtolower', explode( '_', $class_name ) ) );

	$rt_contacts_paths = array(
		'app/' . $file_name . '.php',
		'app/abstract/' . $file_name . '.php',
		'app/modules/organization/' . $file_name . '.php',
		'app/modules/person/' . $file_name . '.php',
	);
	foreach ( $rt_contacts_paths as $path ) {
		$path = RT_CONTACTS_PATH . $path;
		if ( file_exists( $path ) ) {
			include $path;
			break;
		}
	}
}

/**
 * Register the autoloader function into spl_autoload
 */
spl_autoload_register( 'rt_contacts_autoloader' );

function rt_contacts_init() {

	global $rt_contacts;
	$rt_contacts = new Rt_Contacts();

	include_once RT_CONTACTS_PATH . 'app/helper/rt-contacts-functions.php';
}

add_action( 'init', 'rt_contacts_init', 1 );
