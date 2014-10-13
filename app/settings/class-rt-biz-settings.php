<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-rt-biz-settings
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Biz_Settings' ) ) {

	/**
	 * Class Rt_Biz_Settings
	 */
	class Rt_Biz_Settings {

		/**
		 * @var TitanFramework
		 */
		public static $titan_obj;

		/**
		 * @var - saved Settings
		 */
		public static $settings;

		public static $options;

		/**
		 *
		 */
		public function __construct() {

			// Proceed only if Titan Framework Found
			if ( ! $this->embedd_titan_framework() ) {
				return;
			}
			self::$options = array( 'none'=> 'None' ,'woocommerce' => 'Woocommerce', 'edd' => 'Easy Digital Download',);

			// Init Titan Instance
			self::$titan_obj = $this->get_settings_instance();

			// Init Titan Settings
			add_action( 'plugins_loaded', array( $this, 'init_settings' ), 20 );
			// Load Saved Settings Values
			add_action( 'after_setup_theme', array( $this, 'load_settings' ),10 );
		}

		/**
		 *  Load Settings
		 */
		function load_settings() {
			self::$settings['logo_url'] = ( isset( self::$titan_obj ) && ! empty( self::$titan_obj ) ) ? self::$titan_obj->getOption( 'logo_url' ) : '';
			self::$settings['menu_label'] = ( isset( self::$titan_obj ) && ! empty( self::$titan_obj ) ) ? self::$titan_obj->getOption( 'menu_label' ) : '';
			self::$settings['product_plugin'] = ( isset( self::$titan_obj ) && ! empty( self::$titan_obj ) ) ? self::$titan_obj->getOption( 'product_plugin' ) : '';
		}

		/**
		 *  Init Settings
		 */
		function init_settings() {
//			$choice = array('woo' => 'Woocommerce', 'edd' => 'EDD',);
			if ( ! isset( self::$titan_obj ) || empty( self::$titan_obj ) ) {
				return;
			}

			$settings_page = self::$titan_obj->createAdminPanel( array(
				'name' => __( 'Settings' ), // Name of the menu item
				'title' => __( 'Settings' ), // Title displayed on the top of the admin panel
				'parent' => Rt_Biz::$dashboard_slug, // id of parent, if blank, then this is a top level menu
				'id' => Rt_Biz::$settings_slug, // Unique ID of the menu item
				'capability' => rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'admin' ), // User role
//				'icon' => $logo_url, // Menu icon for top level menus only http://melchoyce.github.io/dashicons/
				'position' => 10, // Menu position. Can be used for both top and sub level menus
				'use_form' => true, // If false, options will not be wrapped in a form
			) );
			$general_tab = $settings_page->createTab( array(
				'name' => __( 'General' ), // Name of the tab
				'id' => 'general', // Unique ID of the tab
				'title' => __( 'General' ), // Title to display in the admin panel when tab is active
			) );
			$general_tab->createOption( array(
				'name' => __( 'Menu Label' ), // Name of the option
				'desc' => 'This label will be used for the Menu Item label for rtBiz', // Description of the option
				'id' => 'menu_label', // Unique ID of the option
				'type' => 'text', //
				'default' => __( 'rtBiz' ), // Menu icon for top level menus only
				'example' => '', // An example value for this field, will be displayed in a <code>
				'livepreview' => '', // jQuery script to update something in the site. For theme customizer only
			) );
			$general_tab->createOption( array(
				'name' => __( 'Icon (Logo) URL' ), // Name of the option
				'desc' => 'This logo will be used for all the Menu, Submenu, Post Types Menu Icons in rtBiz', // Description of the option
				'id' => 'logo_url', // Unique ID of the option
				'type' => 'text', //
				'default' => RT_BIZ_URL . 'app/assets/img/biz-16X16.png', // Menu icon for top level menus only
				'example' => 'http://google.com/icon.png', // An example value for this field, will be displayed in a <code>
				'livepreview' => '', // jQuery script to update something in the site. For theme customizer only
			) );
			$general_tab->createOption( array(
				                            'name' => __( 'Product Sync Option' ), // Name of the option
				                            'desc' => 'Select the plugin you want to use for product sync', // Description of the option
				                            'id' => 'product_plugin', // Unique ID of the option
				                            'type' => 'radio',
				                            'options' => self::$options,
				                            'default' => 'none',
			                            ) );
			$general_tab->createOption( array(
				'type' => 'save'
			) );
		}

		/**
		 * @return TitanFramework
		 */
		function get_settings_instance() {
			return TitanFramework::getInstance( RT_BIZ_TEXT_DOMAIN );
		}

		/**
		 * @return bool
		 */
		function is_plugin_activation_action() {
			// Don't do anything when we're activating a plugin to prevent errors
			// on redeclaring Titan classes
			if ( ! empty( $_GET[ 'action' ] ) && ! empty( $_GET[ 'plugin' ] ) ) {
				if ( $_GET[ 'action' ] == 'activate' ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * @return bool
		 */
		function is_titan_activated() {
			// Check if the framework plugin is activated
			$activePlugins = get_option( 'active_plugins' );
			if ( is_array( $activePlugins ) ) {
				foreach ( $activePlugins as $plugin ) {
					if ( is_string( $plugin ) ) {
						if ( stripos( $plugin, '/titan-framework.php' ) !== false ) {
							return true;
						}
					}
				}
			}
			return false;
		}

		/**
		 * @return bool
		 */
		function embedd_titan_framework() {
			/*
			 * When using the embedded framework, use it only if the framework
			 * plugin isn't activated.
			 */

			if ( $this->is_plugin_activation_action() ) {
				return false;
			}

			// Titan Already available as Plugin
			if ( $this->is_titan_activated() ) {
				return true;
			}

			// Use the embedded Titan Framework
			if ( ! class_exists( 'TitanFramework' ) ) {
				require_once( RT_BIZ_PATH . 'app/vendor/titan-framework/titan-framework.php' );
			}
			return true;
		}
	}
}
