<?php

/**
 * A container class for holding and transforming various plugin metadata.
 *
 * @author faishal
 */
if ( ! class_exists( 'RT_Plugin_Update_Info' ) ){
	/**
	 * Class RT_Plugin_Update_Info for parse the metadata file information
	 */
	class RT_Plugin_Update_Info
	{

		/**
		 * @var string plugin name
		 */
		public $name;
		/**
		 * @var string plugin unique identifier
		 */
		public $slug;
		/**
		 * @var string|float latest plugin version available on server
		 */
		public $version;
		/**
		 * @var string plugin home page url
		 */
		public $homepage;
		/**
		 * @var array
		 */
		public $sections;
		/**
		 * @var string latest plugin download url
		 */
		public $download_url;
		/**
		 * @var string plugin author name
		 */
		public $author;
		/**
		 * @var string plugin author homepage url
		 */
		public $author_homepage;
		/**
		 * @var string|float required WordPress version
		 */
		public $requires;
		/**
		 * @var string|float tested WordPress version
		 */
		public $tested;
		/**
		 * @var string Upgrade message to user why they should upgrade
		 */
		public $upgrade_notice;
		/**
		 * @var string plugin rating on server
		 */
		public $rating;
		/**
		 * @var
		 */
		public $num_ratings;
		/**
		 * @var int download count
		 */
		public $downloaded;
		/**
		 * @var string last updated date
		 */
		public $last_updated;
		/**
		 * @var int The native WP.org API returns numeric plugin IDs, but they're not used for anything.
		 */
		public $id = 0;

		/**
		 * Create a new instance of PluginInfo from JSON-encoded plugin info
		 * returned by an external update API.
		 *
		 * @param string $json Valid JSON string representing plugin info.
		 * @param bool   $triggerErrors
		 *
		 * @return RT_Plugin_Update_Info|null New instance of PluginInfo, or NULL on error.
		 */

		public static function from_json( $json, $triggerErrors = false )
		{
			/** @var StdClass $apiResponse */
			$apiResponse = json_decode( $json );
			if ( empty( $apiResponse ) || ! is_object( $apiResponse ) ){
				if ( $triggerErrors ){
					trigger_error( sprintf( __( 'Failed to parse plugin metadata. Try validating your .json file with %s', 'wp-helpers' ), 'http://jsonlint.com/' ), E_USER_NOTICE );
				}

				return null;
			}

			//Very, very basic validation.
			$valid = isset( $apiResponse->name ) && ! empty( $apiResponse->name ) && isset( $apiResponse->version ) && ! empty( $apiResponse->version );
			if ( ! $valid ){
				if ( $triggerErrors ){
					trigger_error( __( "The plugin metadata file does not contain the required 'name' and/or 'version' keys.", 'wp-helpers' ), E_USER_NOTICE );
				}

				return null;
			}

			$info = new self();
			foreach ( get_object_vars( $apiResponse ) as $key => $value ) {
				$info->$key = $value;
			}

			return $info;
		}

		/**
		 * Transform plugin info into the format used by the native WordPress.org API
		 *
		 * @return object
		 */

		/**
		 *
		 * @return \StdClass
		 */
		public function to_wp_format()
		{
			$info = new StdClass;

			//The custom update API is built so that many fields have the same name and format
			//as those returned by the native WordPress.org API. These can be assigned directly.
			$sameFormat = array( 'name', 'slug', 'version', 'requires', 'tested', 'rating', 'upgrade_notice', 'num_ratings', 'downloaded', 'homepage', 'last_updated', );
			foreach ( $sameFormat as $field ) {
				if ( isset( $this->$field ) ){
					$info->$field = $this->$field;
				} else {
					$info->$field = null;
				}
			}

			//Other fields need to be renamed and/or transformed.
			$info->download_link = $this->download_url;

			if ( ! empty( $this->author_homepage ) ){
				$info->author = sprintf( '<a href="%s">%s</a>', $this->author_homepage, $this->author );
			} else {
				$info->author = $this->author;
			}

			if ( is_object( $this->sections ) ){
				$info->sections = get_object_vars( $this->sections );
			} elseif ( is_array( $this->sections ) ) {
				$info->sections = $this->sections;
			} else {
				$info->sections = array( 'description' => '' );
			}

			return $info;
		}

	}
}
