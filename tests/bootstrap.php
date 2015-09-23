<?php

// Activates this plugin in WordPress so it can be tested.
$GLOBALS['wp_tests_options'] = array(
	'active_plugins' => array( 'posts-to-posts/posts-to-posts.php', 'rtbiz/index.php' ),
);

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib/';
}

require_once $_tests_dir . 'includes/functions.php';

function _manually_load_plugin() {
	define( 'WP_ADMIN', true );
	$_SERVER['REMOTE_ADDR'] = 'localhost';
	$_SERVER['PHP_SELF']    = 'http://localhost/wp-admin/';
	define( 'AUTH_KEY', '1234567890' );
	define( 'SECURE_AUTH_KEY', '1234567890' );
	require_once dirname( __FILE__ ) . '/../../posts-to-posts/posts-to-posts.php';
	require_once dirname( __FILE__ ) . '/../rtbiz.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

function _manually_init() {
	// This is for p2p tables
	wp_set_current_user( 1 );
	do_action( 'admin_notices' );
}

tests_add_filter( 'plugins_loaded', '_manually_init', 15 );

require $_tests_dir . '/includes/bootstrap.php';
require_once 'rt-wp-test-case.php';
