<?php
/**
 * Bootstrap the plugin unit testing environment.
 *
 * Edit 'active_plugins' setting below to point to your main plugin file.
 *
 * @package wordpress-plugin-tests
 */

// Activates this plugin in WordPress so it can be tested.
$GLOBALS[ 'wp_tests_options' ] = array( 'active_plugins' => array( 'wp-helpers/wp-helpers.php' ), );
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir || ! file_exists( $_tests_dir ) ) {
	$_tests_dir = '/Users/faishal/work/wordpress-develop/tests/phpunit';
}

require_once $_tests_dir . '/includes/functions.php';


tests_add_filter(
	'muplugins_loaded', function () {
		require dirname( dirname( __FILE__ ) ) . '/wp-helpers.php';
	}
);

require $_tests_dir . '/includes/bootstrap.php';
require_once 'rt-wp-test-case.php';