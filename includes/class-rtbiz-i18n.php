<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */
if ( ! class_exists( 'Rtbiz_i18n' ) ) {
	class Rtbiz_i18n {

		/**
		 * The domain specified for this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $domain The domain identifier for this plugin.
		 */
		private $domain;

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_textdomain() {

			// Set filter for plugin's languages directory
			$lang_dir = dirname( plugin_basename( RTBIZ_PATH ) ) . 'languages/';
			$lang_dir = apply_filters( 'rtbiz_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), RTBIZ_TEXT_DOMAIN );
			$mofile = sprintf( '%1$s-%2$s.mo', RTBIZ_TEXT_DOMAIN, $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/' . RTBIZ_TEXT_DOMAIN . '/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/rtbiz folder
				load_textdomain( RTBIZ_TEXT_DOMAIN, $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/rtbiz/languages/ folder
				load_textdomain( RTBIZ_TEXT_DOMAIN, $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain(
					$this->domain,
					false,
					$lang_dir
				);
			}
		}

		/**
		 * Set the domain equal to that of the specified domain.
		 *
		 * @since    1.0.0
		 *
		 * @param    string $domain The domain that represents the locale of this plugin.
		 */
		public function set_domain( $domain ) {
			$this->domain = $domain;
		}

	}
}
