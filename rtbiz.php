<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://rtcamp.com/
 * @since             1.2.20
 * @package           rtBiz
 *
 * @wordpress-plugin
 * Plugin Name:       rtBiz
 * Plugin URI:        https://rtcamp.com/
 * Description:       WordPress for Business
 * Version:           1.2.20
 * Author:            rtCamp
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       https://rtcamp.com/
 * Text Domain:       rt_biz
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! defined( 'RT_BIZ_VERSION' ) ) {
	define( 'RT_BIZ_VERSION', '1.2.20' );
}

if ( ! defined( 'RT_BIZ_PLUGIN_FILE' ) ) {
	define( 'RT_BIZ_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'RT_BIZ_PATH' ) ) {
	define( 'RT_BIZ_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'RT_BIZ_URL' ) ) {
	define( 'RT_BIZ_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'RT_BIZ_BASE_NAME' ) ) {
	define( 'RT_BIZ_BASE_NAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'RT_BIZ_PATH_TEMPLATES' ) ) {
	define( 'RT_BIZ_PATH_TEMPLATES', plugin_dir_path( __FILE__ ) . 'public/templates/' );
}

if ( ! defined( 'RT_BIZ_TEXT_DOMAIN' ) ) {
	define( 'RT_BIZ_TEXT_DOMAIN', 'rt_biz' );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_rtbiz() {
	require_once RT_BIZ_PATH . 'includes/class-rt-biz-activator.php';
	Rt_Biz_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_rtbiz() {
	require_once RT_BIZ_PATH . 'includes/class-rt-biz-deactivator.php';
	Rt_Biz_Deactivator::deactivate();
}

register_activation_hook( RT_BIZ_PLUGIN_FILE, 'activate_rtbiz' );
register_deactivation_hook( RT_BIZ_PLUGIN_FILE, 'deactivate_rtbiz' );

/**
 * The class responsible for orchestrating the lib class
 * core plugin.
 */
require_once RT_BIZ_PATH . 'lib/rt-lib.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once RT_BIZ_PATH . 'includes/class-rt-biz.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_rtbiz() {
	$plugin = Rt_Biz::instance();
}
run_rtbiz();
