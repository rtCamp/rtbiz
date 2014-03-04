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

if ( ! defined( 'RT_CONTACT_VERSION' ) ) {
	define( 'RT_CONTACT_VERSION', '0.0.1' );
}
if ( ! defined( 'RT_CONTACT_PATH' ) ) {
	define( 'RT_CONTACT_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'RT_CONTACT_URL' ) ) {
	define( 'RT_CONTACT_URL', plugin_dir_url( __FILE__ ) );
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
		$path = RT_CONTACT_PATH . $path;
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

	rt_crm_include();

	global $rt_contacts;
	$rt_contacts = new Rt_Contacts();
}

add_action( 'init', 'rt_contacts_init', 1 );
