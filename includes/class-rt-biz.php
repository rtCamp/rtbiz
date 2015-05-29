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
if ( ! class_exists( 'Rt_Biz' ) ) {
	class Rt_Biz {

		/**
		 * The class that's responsible for Singleton object of Rt_Biz
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      Rt_Biz $instance Singleton object of Rt_Biz
		 */
		private static $instance;

		public static $plugins_dependency = array();

		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      Rt_Biz_Loader $loader Maintains and registers all hooks for the plugin.
		 */
		public static $loader;

		public static $templateURL;

		/**
		 * Create singleton object of Rt_Biz
		 *
		 * @since     1.0.0
		 * @access    private
		 * @return    string    The name of the plugin.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Rt_Biz ) ) {
				self::$instance = new Rt_Biz();
				add_action( 'plugins_loaded', array( self::$instance, 'rt_biz_init_plugin' ) );
			}

			return self::$instance;
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
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', RTBIZ_TEXT_DOMAIN ), '1.6' );
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
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', RTBIZ_TEXT_DOMAIN ), '1.6' );
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

		public function rt_biz_init_plugin() {

			require_once RTBIZ_PATH . 'includes/class-rtbiz-plugin-check.php';

			$plugins_dependency = array(
				'posts-to-posts' => array(
					'project_type' => 'all',
					'name'         => esc_html__( 'Posts 2 Posts', RTBIZ_TEXT_DOMAIN ),
					'desc'         => esc_html__( 'Create many-to-many relationships between all types of posts.', RTBIZ_TEXT_DOMAIN ),
					'active'       => class_exists( 'P2P_Autoload' ),
					'filename'     => 'posts-to-posts.php',
				),
			);

			$rtbiz_plugin_check = new RtBiz_Plugin_Check( $plugins_dependency );

			if ( ! $rtbiz_plugin_check->rt_biz_check_plugin_dependency() ) {
				return false;
			}
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

			$rtb_includes_autoload  = new RT_WP_Autoload( RTBIZ_PATH . 'includes/' );
			$rtb_admin_autoload     = new RT_WP_Autoload( RTBIZ_PATH . 'admin/' );
			$rtb_admin_autoload     = new RT_WP_Autoload( RTBIZ_PATH . 'admin/rtbiz-contact/metabox' );
			$rtb_models_autoload    = new RT_WP_Autoload( RTBIZ_PATH . 'admin/models/' );
			$rtb_abstract_autoload  = new RT_WP_Autoload( RTBIZ_PATH . 'admin/abstract/' );
			$rtb_abstract_autoload  = new RT_WP_Autoload( RTBIZ_PATH . 'admin/abstract/metabox' );
			$rtb_settings_autoload  = new RT_WP_Autoload( RTBIZ_PATH . 'admin/settings/' );
			$rtb_helper_autoload    = new RT_WP_Autoload( RTBIZ_PATH . 'admin/helper/' );
			$rtb_migration_autoload = new RT_WP_Autoload( RTBIZ_PATH . 'includes/migration/' );

			self::$loader = new Rt_Biz_Loader();
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
			$plugin_i18n = new Rt_Biz_i18n();
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

			self::$templateURL = apply_filters( 'rtbiz_template_url', 'rt_biz/' );

			$plugin_admin = new Rtbiz_Admin( );

			if ( is_admin() ) {
				// update menu order of rtbiz menu
				Rt_Biz::$loader->add_action( 'admin_menu', $plugin_admin, 'rt_biz_register_menu', 1 );
				Rt_Biz::$loader->add_action( 'custom_menu_order', $plugin_admin, 'rt_biz_custom_pages_order' );

				Rt_Biz::$loader->add_filter( 'plugin_action_links_' . RTBIZ_BASE_NAME, $plugin_admin, 'rt_biz_plugin_action_links' );
				Rt_Biz::$loader->add_filter( 'plugin_row_meta', $plugin_admin, 'rt_biz_plugin_row_meta', 10, 4 );
			}

			self::$loader->add_action( 'admin_init', $plugin_admin, 'rt_biz_database_update' );
			self::$loader->add_action( 'admin_init', $plugin_admin, 'rt_biz_welcome' );
			self::$loader->add_filter( 'rt_biz_modules', $plugin_admin, 'rt_biz_module_register' );

			$plugin_admin->rt_biz_init_admin();

			self::$loader->add_action( 'after_setup_theme', $plugin_admin, 'rt_biz_init_rtlib', 20 );

			self::$loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'rt_biz_enqueue_styles' );
			self::$loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'rt_biz_enqueue_scripts' );
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
