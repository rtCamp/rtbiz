<?php
/**
 * Bootstrap the plugin unit testing environment.
 *
 * Edit 'active_plugins' setting below to point to your main plugin file.
 *
 * @package wordpress-plugin-tests
 */
echo "Welcome to the rtLib Test Suite" . PHP_EOL;
echo "Version: 1.0" . PHP_EOL . PHP_EOL;

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = realpath( dirname( __FILE__ ) . '/../tmp-wp-dev/wordpress-tests' ) . '/';
}

echo 'Tests folder: ' . $_tests_dir . PHP_EOL . PHP_EOL;

// load some helpful functions
require_once $_tests_dir . 'functions.php';

function _rt_lib_manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/wp-helpers.php';
}

tests_add_filter(
	'muplugins_loaded', '_rt_lib_manually_load_plugin'
);


// Activates this plugin in WordPress so it can be tested.
$GLOBALS[ 'wp_tests_options' ] = array( 'active_plugins' => array( 'wp-helpers/wp-helpers.php' ), );

require_once $_tests_dir . 'bootstrap.php';
require_once 'rt-wp-test-case.php';
