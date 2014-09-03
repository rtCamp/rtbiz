<?php

/*
  Plugin Name: rtBiz
  Plugin URI: http://rtcamp.com/
  Description: WordPress for Business
  Version: 0.0.5
  Author: rtCamp
  Author URI: http://rtcamp.com
  License: GPL
  Text Domain: rt_biz
 */

if ( ! defined( 'RT_BIZ_VERSION' ) ) {
	define( 'RT_BIZ_VERSION', '0.0.5' );
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
if ( ! defined( 'RT_BIZ_TEXT_DOMAIN' ) ) {
	define( 'RT_BIZ_TEXT_DOMAIN', 'rt_biz' );
}

include_once RT_BIZ_PATH . 'app/lib/rt-lib.php';

function rt_biz_include() {

	include_once RT_BIZ_PATH . 'app/helper/rt-biz-functions.php';

	global $rtb_app_autoload, $rtb_models_autoload, $rtb_abstract_autoload, $rtb_modules_autoload, $rtb_settings_autoload, $rtb_notification_autoload, $rtb_reports_autoload;
	$rtb_app_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/' );
	$rtb_models_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/models/' );
	$rtb_abstract_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/abstract/' );
	$rtb_modules_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/modules/' );
	$rtb_notification_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/notification/' );
	$rtb_settings_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/settings/' );
	$rtb_reports_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/lib/rtreports/' );
}

function rt_biz_init() {

	rt_biz_include();

	global $rt_biz;
	$rt_biz = new Rt_Biz();
}

add_action( 'plugins_loaded', 'rt_biz_init' );

function rt_biz_deactivate() {
	wp_clear_scheduled_hook( 'rtpm_notification_queue_cron_hook' );
}

register_deactivation_hook( __FILE__, 'rt_biz_deactivate' );

function rt_biz_notification_init_hook() {
	// Notification Queue Execute Function
	global $rt_biz_notification_queue;
	add_action( 'rtpm_notification_queue_cron_hook', array( $rt_biz_notification_queue, 'execute_notification_queue_cron' ) );
}

add_action( 'init', 'rt_biz_notification_init_hook' );
