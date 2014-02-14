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

		//put your code here
		/**
		 * @var
		 */
		public $plugin_path;
		/**
		 * @var
		 */
		public $name; //'Name' - Name of the plugin, must be unique.
		/**
		 * @var
		 */
		public $title; //'Title' - Title of the plugin and the link to the plugin's web site.
		/**
		 * @var
		 */
		public $desctipriton; //'Description' - Description of what the plugin does and/or notes from the author.
		/**
		 * @var
		 */
		public $authro; //'Author' - The author's name
		/**
		 * @var
		 */
		public $authoruri; //'AuthorURI' - The authors web site address.
		/**
		 * @var
		 */
		public $version; //'Version' - The plugin version number.
		/**
		 * @var
		 */
		public $pluginuri; //'PluginURI' - Plugin web site address.
		/**
		 * @var
		 */
		public $textdomain; //'TextDomain' - Plugin's text domain for localization.
		/**
		 * @var
		 */
		public $domain_path; //'DomainPath' - Plugin's relative directory path to .mo files.
		/**
		 * @var
		 */
		public $network; //'Network' - Boolean. Whether the plugin can only be activated network wide.
		/**
		 * @var
		 */
		public $plugin_data;

		/**
		 * @param null $path
		 */
		public function __construct( $path = NULL )
		{
			$this->set_current_plugin_path( $path );
			$this->set_plugin_data();
		}

		/**
		 * @return array
		 */
		function get_plugin_data()
		{
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			return @get_plugin_data( $this->plugin_path );
		}

		/**
		 *
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
		 * @param $path
		 */
		function set_current_plugin_path( $path )
		{
			if ( $path != NULL ){
				$this->plugin_path = $path;
			} else {
				$this->plugin_path = realpath( plugin_dir_path( __FILE__ ) . '../../index.php' );
			}
		}

	}
}

