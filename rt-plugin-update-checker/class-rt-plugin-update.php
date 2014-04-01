<?php

/**
 * Description of rtPluginUpdate
 * A simple container class for holding information about an available update.
 *
 * @author faishal
 */
if ( ! class_exists( 'RT_Plugin_Update' ) ){
	/**
	 * Class RT_Plugin_Update
	 */
	class RT_Plugin_Update
	{

		/**
		 * @var int
		 */
		public $id = 0;
		/**
		 * @var
		 */
		public $slug;
		/**
		 * @var
		 */
		public $version;
		/**
		 * @var
		 */
		public $homepage;
		/**
		 * @var
		 */
		public $download_url;
		/**
		 * @var
		 */
		public $upgrade_notice;
		/**
		 * @var array
		 */
		private static $fields = array( 'id', 'slug', 'version', 'homepage', 'download_url', 'upgrade_notice' );

		/**
		 * Create a new instance of PluginUpdate from its JSON-encoded representation.
		 *
		 * @param string $json
		 * @param bool   $triggerErrors
		 *
		 * @return RT_Plugin_Update|null
		 */

		public static function from_json( $json, $triggerErrors = false )
		{
			//Since update-related information is simply a subset of the full plugin info,
			//we can parse the update JSON as if it was a plugin info string, then copy over
			//the parts that we care about.
			$pluginInfo = RT_Plugin_Update_Info::from_json( $json, $triggerErrors );
			if ( $pluginInfo != null ){
				return self::from_plugin_info( $pluginInfo );
			} else {
				return null;
			}
		}

		/**
		 * Create a new instance of PluginUpdate based on an instance of PluginInfo.
		 * Basically, this just copies a subset of fields from one object to another.
		 *
		 * @param RT_Plugin_Update_Info $info
		 *
		 * @return RT_Plugin_Update
		 */

		public static function from_plugin_info( $info )
		{
			return self::from_object( $info );
		}

		/**
		 * Create a new instance of PluginUpdate by copying the necessary fields from
		 * another object.
		 *
		 * @param StdClass|RT_Plugin_Update_Info|RT_Plugin_Update $object The source object.
		 *
		 * @return RT_Plugin_Update The new copy.
		 */

		public static function from_object( $object )
		{
			$update = new self();
			foreach ( self::$fields as $field ) {
				$update->$field = $object->$field;
			}

			return $update;
		}

		/**
		 * Create an instance of StdClass that can later be converted back to
		 * a PluginUpdate. Useful for serialization and caching, as it avoids
		 * the "incomplete object" problem if the cached value is loaded before
		 * this class.
		 *
		 * @return StdClass
		 */

		public function to_std_class()
		{
			$object = new StdClass();
			foreach ( self::$fields as $field ) {
				$object->$field = $this->$field;
			}

			return $object;
		}

		/**
		 * Transform the update into the format used by WordPress native plugin API.
		 *
		 * @return object
		 */

		public function to_wp_format()
		{
			$update = new StdClass;

			$update->id          = $this->id;
			$update->slug        = $this->slug;
			$update->new_version = $this->version;
			$update->url         = $this->homepage;
			$update->package     = $this->download_url;
			if ( ! empty( $this->upgrade_notice ) ){
				$update->upgrade_notice = $this->upgrade_notice;
			}

			return $update;
		}

	}
}