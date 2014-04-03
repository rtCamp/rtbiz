<?php

/*
  Plugin Name: rtBiz
  Plugin URI: http://rtcamp.com/
  Description: WordPress for Business
  Version: 0.0.1
  Author: rtCamp
  Author URI: http://rtcamp.com
  License: GPL
  Text Domain: rt-biz
 */

if ( ! defined( 'RT_BIZ_VERSION' ) ) {
	define( 'RT_BIZ_VERSION', '0.0.1' );
}
if ( ! defined( 'RT_BIZ_PATH' ) ) {
	define( 'RT_BIZ_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'RT_BIZ_URL' ) ) {
	define( 'RT_BIZ_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'RT_BIZ_PATH_TEMPLATES' ) ) {
	define( 'RT_BIZ_PATH_TEMPLATES', plugin_dir_path( __FILE__ ) . 'templates/' );
}
if ( !defined( 'RT_BIZ_TEXT_DOMAIN' ) ) {
	define( 'RT_BIZ_TEXT_DOMAIN', 'rt-biz' );
}

function rt_biz_include() {
	include_once RT_BIZ_PATH . 'app/lib/wp-helpers.php';

	include_once RT_BIZ_PATH . 'app/helper/rt-biz-functions.php';

	global $rtb_app_autoload, $rtb_abstract_autoload, $rtb_attributes_autoload, $rtb_modules_autoload;
	$rtb_app_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/' );
	$rtb_abstract_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/abstract/' );
	$rtb_attributes_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/attributes/' );
	$rtb_modules_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/modules/' );
}

function rt_biz_init() {

	rt_biz_include();

	global $rt_biz;
	$rt_biz = new Rt_Biz();
}

add_action( 'init', 'rt_biz_init', 1 );
