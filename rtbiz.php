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
 * @since             1.3
 * @package           rtbiz
 *
 * @wordpress-plugin
 * Plugin Name:       rtBiz
 * Plugin URI:        https://rtcamp.com/
 * Description:       WordPress for Business
 * Version:           1.4.4
 * Author:            rtCamp
 * Author URI:        https://rtcamp.com/
 * License:           GPL-2.0+
 * License URI:       https://rtcamp.com/
 * Text Domain:       rtbiz
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! defined( 'RTBIZ_VERSION' ) ) {
	define( 'RTBIZ_VERSION', '1.4.4' );
}

if ( ! defined( 'RTBIZ_TEXT_DOMAIN' ) ) {
	define( 'RTBIZ_TEXT_DOMAIN', 'rtbiz' );
}

if ( ! defined( 'RTBIZ_PLUGIN_FILE' ) ) {
	define( 'RTBIZ_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'RTBIZ_PATH' ) ) {
	define( 'RTBIZ_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'RTBIZ_URL' ) ) {
	define( 'RTBIZ_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'RTBIZ_BASE_NAME' ) ) {
	define( 'RTBIZ_BASE_NAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'RTBIZ_PATH_TEMPLATES' ) ) {
	define( 'RTBIZ_PATH_TEMPLATES', plugin_dir_path( __FILE__ ) . 'public/templates/' );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_rtbiz() {
	require_once RTBIZ_PATH . 'includes/class-rtbiz-activator.php';
	Rtbiz_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_rtbiz() {
	require_once RTBIZ_PATH . 'includes/class-rtbiz-deactivator.php';
	Rtbiz_Deactivator::deactivate();
}

register_activation_hook( RTBIZ_PLUGIN_FILE, 'activate_rtbiz' );
register_deactivation_hook( RTBIZ_PLUGIN_FILE, 'deactivate_rtbiz' );

/**
 * The class responsible for orchestrating the lib class
 * core plugin.
 */
require_once RTBIZ_PATH . 'lib/rt-lib.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once RTBIZ_PATH . 'includes/class-rtbiz.php';

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
	$plugin = Rtbiz::instance();
}

if ( _rtbiz_php_version_check() ) {
	run_rtbiz();
}

function _rtbiz_php_version_check(){
	$php_version = phpversion();
	if ( version_compare( $php_version ,'5.3', '<' ) ) {
		// running older version do not load our plugins.
		add_action( 'admin_notices','_rtbiz_running_older_php_version' );
		add_action( 'admin_init','_rtbiz_deactive_self' );
		return false;
	}
	return true;
}

function _rtbiz_running_older_php_version(){ ?>
	<div class="error rtbiz-php-older-version">
		<p><?php _e( 'You are running an older PHP version. Please upgrade to PHP <strong>5.3 or above</strong> to run rtBiz plugin.', 'rtbiz' ) ?></p>
	</div> <?php
}

function _rtbiz_deactive_self(){
	deactivate_plugins(plugin_basename(__FILE__));
}
