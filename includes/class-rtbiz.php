<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    rtBiz
 * @subpackage rtBiz/includes
 */

/**
 * The core plugin singleton class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    rtBiz
 * @subpackage rtBiz/includes
 * @author     Dipesh <dipesh.kakadiya@rtcamp.com>
 */
if ( ! class_exists( 'Rtbiz' ) ) {
	class Rtbiz {

		/**
		 * The class that's responsible for Singleton object of Rtbiz
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      Rtbiz $instance Singleton object of Rtbiz
		 */
		private static $instance;

		public static $plugins_dependency = array();

		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      Rtbiz_Loader $loader Maintains and registers all hooks for the plugin.
		 */
		public static $loader;

		public static $templateURL;

		/**
		 * Create singleton object of Rtbiz
		 *
		 * @since     1.0.0
		 * @access    private
		 * @return    string    The name of the plugin.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Rtbiz ) ) {
				self::_p2p_load();
				self::$instance = new Rtbiz();
				add_action( 'plugins_loaded', array( self::$instance, 'init_plugin' ) );
			}

			return self::$instance;
		}

		private static function _p2p_load() {
			require_once RTBIZ_PATH . 'includes/class-rtbiz-posts-to-posts.php';
			global $rtbiz_posts_to_posts;
			// Load p2p library and init
			$rtbiz_posts_to_posts = new Rtbiz_Post_To_Post();
		}

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since 0.1
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'rtbiz' ), '1.6' );
		}

		/**
		 * Disable unserializing of the class
		 *
		 * @since 0.1
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'rtbiz' ), '1.6' );
		}


		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {

		}

		public function init_plugin() {
			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();

			$this->run();
			do_action( 'rtbiz_init' );
		}

		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
		 * - Plugin_Name_i18n. Defines internationalization functionality.
		 * - Plugin_Name_Admin. Defines all hooks for the admin area.
		 * - Plugin_Name_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since     1.0.0
		 * @access    private
		 */
		private function load_dependencies() {

			/**
			 * The class responsible for orchestrating the helping function
			 * core plugin.
			 */
			require_once RTBIZ_PATH . 'admin/helper/rtbiz-functions.php';
			require_once RTBIZ_PATH . 'vendor/redux/ReduxCore/framework.php';

			new RT_WP_Autoload( RTBIZ_PATH . 'includes/' );
			new RT_WP_Autoload( RTBIZ_PATH . 'admin/' );
			new RT_WP_Autoload( RTBIZ_PATH . 'admin/classes/' );
			new RT_WP_Autoload( RTBIZ_PATH . 'admin/classes/models' );
			new RT_WP_Autoload( RTBIZ_PATH . 'admin/classes/metabox' );
			new RT_WP_Autoload( RTBIZ_PATH . 'admin/helper/' );
			new RT_WP_Autoload( RTBIZ_PATH . 'includes/migration/' );

			self::$loader = new Rtbiz_Loader();
		}

		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function set_locale() {
			$plugin_i18n = new Rtbiz_i18n();
			$plugin_i18n->set_domain( RTBIZ_TEXT_DOMAIN );

			// called on plugins_loaded hook
			$plugin_i18n->load_plugin_textdomain();
		}

		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_admin_hooks() {

			self::$templateURL = apply_filters( 'rtbiz_template_url', 'rtbiz/' );

			$plugin_admin = new Rtbiz_Admin();

			if ( is_admin() ) {
				// update menu order of rtbiz menu
				Rtbiz::$loader->add_action( 'admin_menu', $plugin_admin, 'register_menu', 1 );
				Rtbiz::$loader->add_action( 'custom_menu_order', $plugin_admin, 'custom_pages_order' );

				Rtbiz::$loader->add_filter( 'plugin_action_links_' . RTBIZ_BASE_NAME, $plugin_admin, 'plugin_action_links' );
				Rtbiz::$loader->add_filter( 'plugin_row_meta', $plugin_admin, 'plugin_row_meta', 10, 4 );
			}

			self::$loader->add_action( 'admin_init', $plugin_admin, 'database_update' );
			self::$loader->add_action( 'admin_init', $plugin_admin, 'rtbiz_welcome' );
			self::$loader->add_filter( 'rtbiz_modules', $plugin_admin, 'module_register' );

			$plugin_admin->init_admin();

			self::$loader->add_action( 'after_setup_theme', $plugin_admin, 'init_rtlib', 20 );

			self::$loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			self::$loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_public_hooks() {

			//$plugin_public = new Plugin_Name_Public( $this->get_plugin_name(), $this->get_version() );

			//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
			//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		}

		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			self::$loader->run();
		}

		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @since     1.0.0
		 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
		 */
		public function get_loader() {
			return $this->loader;
		}

	}
}
