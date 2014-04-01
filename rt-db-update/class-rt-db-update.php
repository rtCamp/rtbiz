<?php

/**
 * Description of RT_DB_Update
 * Required : rt_plugin_info.php
 *
 * @author Faishal Saiyed<saiyedfaishal@gmail.com>
 *         version 1.1
 */

if ( ! class_exists( 'RT_DB_Update' ) ){
	/**
	 * Class RT_DB_Update
	 */
	class RT_DB_Update
	{
		/**
		 *
		 * @var string String
		 */
		public $db_version;
		/**
		 * @var mixed|void
		 */
		public $install_db_version;
		/**
		 * @var string
		 */
		public $schema_path = '/../schema/';
		/**
		 * @var string
		 */
		public $plugin_path = '/../wp-helpers.php';
		/**
		 * @var string
		 */
		public $db_version_option_name;
		/**
		 * @var RT_Plugin_Info
		 */
		public $rt_plugin_info;
		/**
		 * @var bool
		 */
		public $mu_single_table;

		/**
		 * @var boolean
		 */
		public $debugMode;
		/**
		 * Set db current and installed version and also plugin info .
		 *
		 * @param bool $current_version plugin latest version
		 * @param bool $plugin_path absolute plugin loader file path
		 * @param bool $schema_path absolute schema directory path which contains .schema files
		 * @param bool $mu_single_table true if you want to create single table in multisite
		 *
		 * @internal param string $type $current_version Optional if not defined then will use plugin version
		 */
		public function __construct( $plugin_path = false, $schema_path = false, $mu_single_table = false, $current_version = false )
		{
			$this->debugMode = defined( 'WP_DEBUG' ) && WP_DEBUG;
			if ( $schema_path != false ){
				$this->schema_path = $schema_path;
			} else {
				$this->schema_path = realpath( dirname( __FILE__ ) . $this->schema_path );
			}

			if ( ! file_exists( $this->schema_path ) ) {
				if ( $this->debugMode ){
					$message = sprintf( __( 'The Path %s does not point to a valid schema directory.' ) , $this->schema_path );
					trigger_error( $message, E_USER_WARNING );
				}
			}

			if ( $plugin_path != false ){
				$this->plugin_path = $plugin_path;
			} else {
				$this->plugin_path = realpath( dirname( __FILE__ ) . $this->plugin_path );
			}

			if ( ! file_exists( $this->plugin_path ) ) {
				if ( $this->debugMode ){
					$message = sprintf( __( 'The Path %s does not point to a valid plugin.' ) , $this->plugin_path );
					trigger_error( $message, E_USER_WARNING );
				}
			}

			$this->mu_single_table = $mu_single_table;

			$this->rt_plugin_info = new RT_Plugin_Info( $this->plugin_path );
			if ( $current_version == false ){
				$current_version = $this->rt_plugin_info->version;
			}
			$this->db_version             = $current_version;
			$this->db_version_option_name = $this->get_db_version_option_name();
			$this->install_db_version     = $this->get_install_db_version();
		}

		/**
		 * @param $sql
		 */
		public function create_table( $sql )
		{
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}

		/**
		 * @return string
		 */
		public function get_db_version_option_name()
		{
			return strtoupper( 'RT_' . str_replace( '-', '_', sanitize_title( $this->rt_plugin_info->name ) ) . '_DB_VERSIONS' );
		}

		/**
		 * @return mixed|void
		 */
		public function get_install_db_version()
		{
			return ( $this->mu_single_table ) ? get_site_option( $this->db_version_option_name, '0.0' ) : get_option( $this->db_version_option_name, '0.0' );
		}

		/**
		 * @return mixed
		 */
		public function check_upgrade()
		{
			return version_compare( $this->db_version, $this->install_db_version, '>' );
		}

		/**
		 *
		 */
		public function do_upgrade()
		{
			if ( version_compare( $this->db_version, $this->install_db_version, '>' ) ){
				$path = $this->schema_path;
				if ( $handle = opendir( $path ) ){
					while ( false !== ( $file_name = readdir( $handle ) ) ) {
						if ( $file_name != '.' && $file_name != '..' ){
							if ( strpos( $file_name, '.schema' ) !== false && file_exists( $path . '/' . $file_name ) ){
								do_action( 'rt_db_update_before_create_table', $file_name );
								$this->create_table( $this->genrate_sql( $file_name, file_get_contents( $path . '/' . $file_name ) ) );
								do_action( 'rt_db_update_after_create_table', $file_name );
							}
						}
					}
					closedir( $handle );
				}
				$this->update_version();
				do_action( 'rt_db_update_finished' );
				//Hook for individual plugin
				do_action( 'rt_db_update_finished_' . $this->rt_plugin_info->name );
			}
		}

		function update_version(){
			if ( $this->mu_single_table ){
				update_site_option( $this->db_version_option_name, $this->db_version );
			} else {
				update_option( $this->db_version_option_name, $this->db_version );
			}
		}

		/**
		 * @param $table_name
		 *
		 * @internal param $table
		 *
		 * @return bool
		 */
		static function table_exists( $table_name )
		{
			global $wpdb;

			if ( $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', '%' . $table_name . '%' ) ) == 1 ){
				return true;
			}

			return false;
		}

		/**
		 * @param $file_name
		 * @param $file_content
		 *
		 * @return string
		 */
		public function genrate_sql( $file_name, $file_content )
		{
			return sprintf( $file_content, $this->genrate_table_name( $file_name ) );
		}

		/**
		 * @param $file_name
		 *
		 * @return string
		 */
		public function genrate_table_name( $file_name )
		{
			global $wpdb;

			return ( ( $this->mu_single_table ) ? $wpdb->base_prefix : $wpdb->prefix ) . 'rt_' . str_replace( '.schema', '', strtolower( $file_name ) );
		}

	}

}