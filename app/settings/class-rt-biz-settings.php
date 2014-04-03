<?php

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

	class Rt_Biz_Settings {

		public static $titan_obj;
		public static $settings;

		public function __construct() {
			$this->embedd_titan_framework();

			self::$titan_obj = $this->get_settings_instance();

			add_action( 'after_setup_theme', array( $this, 'init_settings' ) );
			add_action( 'after_setup_theme', array( $this, 'load_settings' ) );
		}

		function load_settings() {
			self::$titan_obj = $this->get_settings_instance();
			self::$settings['logo_url'] = self::$titan_obj->getOption( 'logo_url' );
		}

		function init_settings() {
			$settings_page = self::$titan_obj->createAdminPanel( array(
				'name' => __( 'Settings' ), // Name of the menu item
				'title' => __( 'Settings' ), // Title displayed on the top of the admin panel
				'parent' => Rt_Biz::$menu_page_slug, // id of parent, if blank, then this is a top level menu
				'id' => Rt_Biz::$settings_page_slug, // Unique ID of the menu item
				'capability' => Rt_Biz_Roles::$global_caps[ 'manage_rt_biz' ], // User role
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
				'name' => __( 'Icon (Logo) URL' ), // Name of the option
				'desc' => 'This logo will be used for all the Menu, Submenu, Post Types Menu Icons in rtBiz', // Description of the option
				'id' => 'logo_url', // Unique ID of the option
				'type' => 'text', //
				'default' => RT_BIZ_URL . 'app/assets/img/rt-biz-16X16.png', // Menu icon for top level menus only
				'example' => 'http://google.com/icon.png', // An example value for this field, will be displayed in a <code>
				'livepreview' => '', // jQuery script to update something in the site. For theme customizer only
			) );
			$general_tab->createOption( array(
				'type' => 'save'
			) );
		}

		function get_settings_instance() {
			return TitanFramework::getInstance( RT_BIZ_TEXT_DOMAIN );
		}

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

		function is_titan_activated() {
			// Check if the framework plugin is activated
			$useEmbeddedFramework = true;
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

		function embedd_titan_framework() {
			/*
			 * When using the embedded framework, use it only if the framework
			 * plugin isn't activated.
			 */

			if ( $this->is_plugin_activation_action() ) {
				return;
			}

			if ( $this->is_titan_activated() ) {
				return;
			}

			// Use the embedded Titan Framework
			if ( ! class_exists( 'TitanFramework' ) ) {
				require_once( RT_BIZ_PATH . 'app/vendor/titan-framework/titan-framework.php' );
			}
		}

	}

}
