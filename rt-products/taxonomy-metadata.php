<?php

namespace Rt_Lib_Taxonomy_Metadata;

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Name: Taxonomy Metadata
 * Description: Infrastructure plugin which implements metadata functionality for taxonomy terms, including for tags
 * and categories. Version: 0.4 Author: mitcho (Michael Yoshitaka Erlewine), sirzooro Author URI: http://mitcho.com/
 */
class Taxonomy_Metadata {
	function __construct() {
		add_action( 'init', array( $this, 'wpdbfix' ) );
		add_action( 'switch_blog', array( $this, 'wpdbfix' ) );
		add_action( 'wpmu_new_blog', array( $this, 'new_blog' ), 10, 6 );
		add_action( 'admin_init', array( $this, 'migration' ) );
	}

	public function migration() {
		global $wpdb;
		if ( ! empty( $wpdb->termmeta ) ) {
			$is_migration_done = get_option( 'rtbiz_taxonomy_metadata_migration_complete' );
			if ( empty( $is_migration_done ) ) {
				// because we don't want to check table exist or not every time.
				$tables = $wpdb->get_results( "show tables like '{$wpdb->prefix}taxonomymeta'" );
				if ( count( $tables ) ) {
					// migration
					$wpdb->query( "INSERT INTO {$wpdb->termmeta} (term_id, meta_key, meta_value ) SELECT taxonomy_id,meta_key,meta_value FROM {$wpdb->prefix}taxonomymeta" );
					//one less table to worry about
					$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}taxonomymeta" );
				}
				// update even if table does not exist because maybe we never created table in first place in case of wordpress 4.4 or above fresh installation.
				update_option( 'rtbiz_taxonomy_metadata_migration_complete', 1 );
			}
		}
	}

	/*
	 * Quick touchup to wpdb
	 */
	function wpdbfix() {
		global $wpdb;
		$wpdb->taxonomymeta = "{$wpdb->prefix}taxonomymeta";
	}

	/*
	 * TABLE MANAGEMENT
	 */

	function activate( $network_wide = false ) {
		global $wpdb;

		// if activated on a particular blog, just set it up there.
		if ( ! $network_wide ) {
			$this->setup_blog();

			return;
		}

		$blogs = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}'" );
		foreach ( $blogs as $blog_id ) {
			$this->setup_blog( $blog_id );
		}
		// I feel dirty... this line smells like perl.
		do {
		} while ( restore_current_blog() );
	}

	function setup_blog( $id = false ) {
		global $wpdb;

		if ( ! empty( $wpdb->termmeta ) ) {
			// wordpress 4.4+ no need to create meta table
			return;
		}

		if ( false != $id ) {
			switch_to_blog( $id );
		}

		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}

		$tables = $wpdb->get_results( "show tables like '{$wpdb->prefix}taxonomymeta'" );
		if ( ! count( $tables ) ) {
			$wpdb->query( "CREATE TABLE {$wpdb->prefix}taxonomymeta (
			meta_id bigint(20) unsigned NOT NULL auto_increment,
			taxonomy_id bigint(20) unsigned NOT NULL default '0',
			meta_key varchar(255) default NULL,
			meta_value longtext,
			PRIMARY KEY	(meta_id),
			KEY taxonomy_id (taxonomy_id),
			KEY meta_key (meta_key)
		) $charset_collate;" );
		}
	}

	function new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
			$this->setup_blog( $blog_id );
		}
	}
}

//$taxonomy_metadata = new Taxonomy_Metadata();
//register_activation_hook( __FILE__, array( $taxonomy_metadata, 'activate' ) );

// THE REST OF THIS CODE IS FROM http://core.trac.wordpress.org/ticket/10142
// BY sirzooro

//
// Taxonomy meta functions
//

/**
 * Add meta data field to a term.
 *
 * @param int $term_id Post ID.
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Metadata value.
 * @param bool $unique Optional, default is false. Whether the same key should not be added.
 *
 * @return bool False for failure. True for success.
 */

function add_term_meta( $term_id, $meta_key, $meta_value, $unique = false ) {
	global $wpdb;
	if ( function_exists( 'add_term_meta' ) && ! empty( $wpdb->termmeta ) ) {
		// call global name space function add_term_meta
		return \add_term_meta( $term_id, $meta_key, $meta_value, $unique );
	} else {
		return add_metadata( 'taxonomy', $term_id, $meta_key, $meta_value, $unique );
	}
}

/**
 * Remove metadata matching criteria from a term.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @param int $term_id term ID
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Optional. Metadata value.
 *
 * @return bool False for failure. True for success.
 */
function delete_term_meta( $term_id, $meta_key, $meta_value = '' ) {
	global $wpdb;
	if ( function_exists( 'delete_term_meta' ) && ! empty( $wpdb->termmeta ) ) {
		return \delete_term_meta( $term_id, $meta_value, $meta_value );
	} else {
		return delete_metadata( 'taxonomy', $term_id, $meta_key, $meta_value );
	}
}

/**
 * Retrieve term meta field for a term.
 *
 * @param int $term_id Term ID.
 * @param string $key The meta key to retrieve.
 * @param bool $single Whether to return a single value.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single
 *  is true.
 */
function get_term_meta( $term_id, $key, $single = false ) {
	global $wpdb;
	if ( function_exists( 'get_term_meta' ) && ! empty( $wpdb->termmeta ) ) {
		return \get_term_meta( $term_id, $key, $single );
	} else {
		return get_metadata( 'taxonomy', $term_id, $key, $single );
	}
}

/**
 * Update term meta field based on term ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and term ID.
 *
 * If the meta field for the term does not exist, it will be added.
 *
 * @param int $term_id Term ID.
 * @param string $meta_key Metadata key.
 * @param mixed $meta_value Metadata value.
 * @param mixed $prev_value Optional. Previous value to check before removing.
 *
 * @return bool False on failure, true if success.
 */
function update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
	global $wpdb;
	if ( function_exists( 'update_term_meta' ) && ! empty( $wpdb->termmeta ) ) {
		return \update_term_meta( $term_id, $meta_key, $meta_value, $prev_value );
	} else {
		return update_metadata( 'taxonomy', $term_id, $meta_key, $meta_value, $prev_value );
	}
}
