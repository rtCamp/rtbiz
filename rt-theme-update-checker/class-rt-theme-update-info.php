<?php
/**
 * Created by PhpStorm.
 * User: faishal
 * Date: 20/02/14
 * Time: 7:20 PM
 */

if ( ! class_exists( 'RT_Theme_Update_Info' ) ):

	/**
	 * A simple container class for holding information about an available update.
	 */
	class RT_Theme_Update_Info {
		public $version; //Version number.
		public $details_url; //The URL where the user can learn more about this version.
		public $download_url; //The download URL for this version of the theme. Optional.

		/**
		 * Create a new instance of ThemeUpdate from its JSON-encoded representation.
		 *
		 * @param string $json Valid JSON string representing a theme information object.
		 *
		 * @return RT_Theme_Update_Info New instance of ThemeUpdate, or NULL on error.
		 */
		public static function from_json( $json ) {
			$apiResponse = json_decode( $json );
			if ( empty( $apiResponse ) || ! is_object( $apiResponse ) ) {
				return null;
			}

			//Very, very basic validation.
			$valid = isset( $apiResponse->version ) && ! empty( $apiResponse->version ) && isset( $apiResponse->download_url ) && ! empty( $apiResponse->download_url );
			if ( ! $valid ) {
				return null;
			}

			$update = new self();
			foreach ( get_object_vars( $apiResponse ) as $key => $value ) {
				$update->$key = $value;
			}

			return $update;
		}

		/**
		 * Transform the update into the format expected by the WordPress core.
		 *
		 * @return array
		 */
		public function to_wp_format() {
			$update = array( 'new_version' => $this->version, 'url' => $this->details_url, );

			if ( ! empty( $this->download_url ) ) {
				$update[ 'package' ] = $this->download_url;
			}

			return $update;
		}
	}

endif;