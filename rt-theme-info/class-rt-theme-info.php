<?php

/**
 * Description of plugininfo
 *
 * @author Faishal Saiyed<saiyedfaishal@gmail.com>
 */

if ( ! class_exists( 'RT_Theme_Info' ) ) {
	/**
	 * Class RT_Plugin_Info
	 */
	class RT_Theme_Info {

		/**
		 * @var string plugin loader file path
		 */
		public $theme_path;
		/**
		 * @var string Name of the plugin, must be unique.
		 */
		public $name;
		/**
		 * @var string Title of the plugin and the link to the plugin's web site.
		 */
		public $title;
		/**
		 * @var string Description of what the plugin does and/or notes from the author.
		 */
		public $description;
		/**
		 * @var string The author's name
		 */
		public $author;
		/**
		 * @var string The authors web site address.
		 */
		public $authoruri;
		/**
		 * @var string|float The plugin version number.
		 */
		public $version;
		/**
		 * @var string Plugin web site address.
		 */
		public $themeuri;
		/**
		 * @var string  Plugin's text domain for localization.
		 */
		public $textdomain;
		/**
		 * @var string Plugin's relative directory path to .mo files.
		 */
		public $domain_path;
		/**
		 * @var boolean Whether the plugin can only be activated network wide.
		 */
		public $network;
		/**
		 * @var array plugin info metadata in raw format
		 */
		public $theme_data;

		/**
		 * @param null $path
		 */
		public function __construct( $path = null ) {
			$this->set_current_theme_path( $path );
			$this->set_theme_data();
		}

		/**
		 * @return array plugin info in array
		 */
		function get_theme_data() {
			require_once( ABSPATH . 'wp-admin/includes/theme.php' );

			return wp_get_theme( basename( $this->theme_path ) );
		}

		/**
		 * Set plugin data
		 * @return null
		 */
		function set_theme_data() {
			$this->theme_data  = $this->get_theme_data();
			$this->name        = $this->theme_data->get( 'Name' );
			$this->title       = $this->theme_data->get( 'Name' );
			$this->description = $this->theme_data->get( 'Description' );
			$this->author      = $this->theme_data->get( 'Author' );
			$this->authoruri   = $this->theme_data->get( 'AuthorURI' );
			$this->version     = $this->theme_data->get( 'Version' );
			$this->themeuri    = $this->theme_data->get( 'ThemeURI' );
			$this->textdomain  = $this->theme_data->get( 'TextDomain' );
			$this->domain_path = $this->theme_data->get( 'DomainPath' );
			$this->network     = $this->theme_data->get( 'Status' );
		}

		/**
		 * Set current plugin loader path
		 *
		 * @param $path string plugin loader file path
		 */
		function set_current_theme_path( $path ) {
			if ( null != $path ) {
				$this->theme_path = $path;
			} else {
				$this->theme_path = realpath( plugin_dir_path( __FILE__ ) . '../../index.php' );
			}
		}

	}
}

