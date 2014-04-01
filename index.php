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
	define( 'RT_CONTACTS_PATH_TEMPLATES', plugin_dir_path( __FILE__ ) . 'templates/' );
}

function rt_contacts_include() {
	include_once RT_CONTACTS_PATH . 'app/lib/wp-helpers.php';

	include_once RT_CONTACTS_PATH . 'app/helper/rt-contacts-functions.php';

	global $rt_contacts_app_autoload, $rt_contacts_abstract_autoload, $rt_contacts_attributes_autoload, $rt_contacts_modules_autoload;
	$rt_contacts_app_autoload = new RT_WP_Autoload( RT_CONTACTS_PATH . 'app/' );
	$rt_contacts_abstract_autoload = new RT_WP_Autoload( RT_CONTACTS_PATH . 'app/abstract/' );
	$rt_contacts_attributes_autoload = new RT_WP_Autoload( RT_CONTACTS_PATH . 'app/attributes/' );
	$rt_contacts_modules_autoload = new RT_WP_Autoload( RT_CONTACTS_PATH . 'app/modules/' );
}

function rt_contacts_init() {

	rt_contacts_include();

	global $rt_contacts;
	$rt_contacts = new Rt_Contacts();
}

add_action( 'init', 'rt_contacts_init', 1 );
