<?php

/**
 * Description of plugininfo
 *
 * @author Faishal Saiyed<saiyedfaishal@gmail.com>
 */

if ( ! class_exists( 'RT_Plugin_Info' ) ){
	/**
	 * Class RT_Plugin_Info
	 */
	class RT_Plugin_Info
	{

		/**
		 * @var string plugin loader file path
		 */
		public $plugin_path;
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
		public $desctipriton;
		/**
		 * @var string The author's name
		 */
		public $authro;
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
		public $pluginuri;
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
		public $plugin_data;

		/**
		 * @param null $path
		 */
		public function __construct( $path = null )
		{
			$this->set_current_plugin_path( $path );
			$this->set_plugin_data();
		}

		/**
		 * @return array plugin info in array
		 */
		function get_plugin_data()
		{
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			return get_plugin_data( $this->plugin_path );
		}

		/**
		 * Set plugin data
		 * @return null
		 */
		function set_plugin_data()
		{
			$this->plugin_data  = $this->get_plugin_data();
			$this->name         = $this->plugin_data[ 'Name' ];
			$this->title        = $this->plugin_data[ 'Title' ];
			$this->desctipriton = $this->plugin_data[ 'Description' ];
			$this->author       = $this->plugin_data[ 'Author' ];
			$this->authoruri    = $this->plugin_data[ 'AuthorURI' ];
			$this->version      = $this->plugin_data[ 'Version' ];
			$this->pluginuri    = $this->plugin_data[ 'PluginURI' ];
			$this->textdomain   = $this->plugin_data[ 'TextDomain' ];
			$this->domain_path  = $this->plugin_data[ 'DomainPath' ];
			$this->network      = $this->plugin_data[ 'Network' ];
		}

		/**
		 * Set current plugin loader path
		 * @param $path string plugin loader file path
		 */
		function set_current_plugin_path( $path )
		{
			if ( $path != null ){
				$this->plugin_path = $path;
			} else {
				$this->plugin_path = realpath( plugin_dir_path( __FILE__ ) . '../../index.php' );
			}
		}

	}
}

