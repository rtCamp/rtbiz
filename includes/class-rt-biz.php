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
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string $plugin_name The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;

		/**
		 * The current version of the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string $version The current version of the plugin.
		 */
		protected $version;

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
				add_action( 'plugins_loaded', array( self::$instance, 'init_plugin' ) );
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
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', RT_BIZ_TEXT_DOMAIN ), '1.6' );
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
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', RT_BIZ_TEXT_DOMAIN ), '1.6' );
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
			$this->plugin_name = RT_BIZ_TEXT_DOMAIN;
			$this->version     = RT_BIZ_VERSION;
		}

		public function init_plugin() {

			if ( ! self::$instance->check_plugin_dependency() ) {
				return false;
			}
			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();

			$this->run();

		}

		private function check_plugin_dependency() {
			$flag = true;

			self::$plugins_dependency = array(
				'posts-to-posts' => array(
					'project_type' => 'all',
					'name'         => esc_html__( 'Posts 2 Posts', RT_BIZ_TEXT_DOMAIN ),
					'desc'         => esc_html__( 'Create many-to-many relationships between all types of posts.', RT_BIZ_TEXT_DOMAIN ),
					'active'       => class_exists( 'P2P_Autoload' ),
					'filename'     => 'posts-to-posts.php',
				),
			);

			foreach ( self::$plugins_dependency as $plugin ) {
				if ( ! $plugin['active'] ) {
					add_action( 'admin_enqueue_scripts', array(
						self::$instance,
						'rt_biz_plugins_dependency_enqueue_js',
					) );
					add_action( 'wp_ajax_rtbiz_install_plugin', array( self::$instance, 'rt_biz_install_plugin_ajax' ) );
					add_action( 'wp_ajax_rtbiz_activate_plugin', array(
						self::$instance,
						'rt_biz_activate_plugin_ajax',
					) );
					add_action( 'admin_notices', array( self::$instance, 'rt_biz_plugin_not_installed_admin_notice' ) );
					$flag = false;
				}
			}

			return $flag;
		}

		public function rt_biz_plugins_dependency_enqueue_js() {
			wp_enqueue_script( $this->plugin_name . '-plugins-dependency', RT_BIZ_URL . 'admin/js/rtbiz-plugin-check.js', '', $this->version, true );
			wp_localize_script( $this->plugin_name . '-plugins-dependency', 'rtbiz_ajax_url', admin_url( 'admin-ajax.php' ) );
		}

		public function rt_biz_install_plugin_ajax() {

			if ( empty( $_POST['plugin_slug'] ) ) {
				die( __( 'ERROR: No slug was passed to the AJAX callback.', RT_BIZ_TEXT_DOMAIN ) );
			}
			check_ajax_referer( 'rtbiz_install_plugin_' . $_POST['plugin_slug'] );

			if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
				die( __( 'ERROR: You lack permissions to install and/or activate plugins.', RT_BIZ_TEXT_DOMAIN ) );
			}
			$this->rt_biz_install_plugin( $_POST['plugin_slug'] );

			echo 'true';
			die();
		}

		public function rt_biz_install_plugin( $plugin_slug ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

			$api = plugins_api( 'plugin_information', array(
				'slug'   => $plugin_slug,
				'fields' => array( 'sections' => false )
			) );

			if ( is_wp_error( $api ) ) {
				die( sprintf( __( 'ERROR: Error fetching plugin information: %s', RT_BIZ_TEXT_DOMAIN ), $api->get_error_message() ) );
			}

			if ( ! class_exists( 'Plugin_Upgrader' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
			}

			if ( ! class_exists( 'Rt_Biz_Plugin_Upgrader_Skin' ) ) {
				require_once( RT_BIZ_PATH . '/app/abstract/class-rt-biz-plugin-upgrader-skin.php' );
			}

			$upgrader = new Plugin_Upgrader( new Rt_Biz_Plugin_Upgrader_Skin( array(
				'nonce'  => 'install-plugin_' . $plugin_slug,
				'plugin' => $plugin_slug,
				'api'    => $api,
			) ) );

			$install_result = $upgrader->install( $api->download_link );

			if ( ! $install_result || is_wp_error( $install_result ) ) {
				// $install_result can be false if the file system isn't writeable.
				$error_message = __( 'Please ensure the file system is writeable', RT_BIZ_TEXT_DOMAIN );

				if ( is_wp_error( $install_result ) ) {
					$error_message = $install_result->get_error_message();
				}

				die( sprintf( __( 'ERROR: Failed to install plugin: %s', RT_BIZ_TEXT_DOMAIN ), $error_message ) );
			}

			$activate_result = activate_plugin( $this->rt_biz_get_path_for_plugin( $plugin_slug ) );
			if ( is_wp_error( $activate_result ) ) {
				die( sprintf( __( 'ERROR: Failed to activate plugin: %s', RT_BIZ_TEXT_DOMAIN ), $activate_result->get_error_message() ) );
			}
		}

		public function rt_biz_get_path_for_plugin( $slug ) {

			$filename = ( ! empty( self::$plugins_dependency[ $slug ]['filename'] ) ) ? self::$plugins_dependency[ $slug ]['filename'] : $slug . '.php';

			return $slug . '/' . $filename;
		}

		function rt_biz_activate_plugin_ajax() {
			if ( empty( $_POST['path'] ) ) {
				die( __( 'ERROR: No slug was passed to the AJAX callback.', RT_BIZ_TEXT_DOMAIN ) );
			}
			check_ajax_referer( 'rtbiz_activate_plugin_' . $_POST['path'] );

			if ( ! current_user_can( 'activate_plugins' ) ) {
				die( __( 'ERROR: You lack permissions to activate plugins.', RT_BIZ_TEXT_DOMAIN ) );
			}

			$this->rt_biz_activate_plugin( $_POST['path'] );

			echo 'true';
			die();
		}

		function rt_biz_activate_plugin( $plugin_path ) {

			$activate_result = activate_plugin( $plugin_path );
			if ( is_wp_error( $activate_result ) ) {
				die( sprintf( __( 'ERROR: Failed to activate plugin: %s', RT_BIZ_TEXT_DOMAIN ), $activate_result->get_error_message() ) );
			}
		}

		public function rt_biz_plugin_not_installed_admin_notice() { ?>
			<div class="error rtbiz-plugin-not-installed-error"><?php
			foreach ( self::$plugins_dependency as $plugin_slug => $plugin ) {
				if ( ! $this->rt_biz_is_plugin_installed( $plugin_slug ) ) {
					$nonce = wp_create_nonce( 'rtbiz_install_plugin_' . $plugin_slug ); ?>
					<p>
						<b><?php _e( 'rtBiz:' ); ?></b><?php _e( 'Click' ) ?>
						<a href="#"
					        onclick="install_rtbiz_plugin( $plugin_slug, 'rtbiz_install_plugin', '<?php echo $nonce ?>' )">
							here</a><?php
							_e( 'to install ' . $plugin['name'] . '.', $plugin_slug ) ?>
					</p><?php
				} elseif ( $this->rt_biz_is_plugin_installed( $plugin_slug ) && ! $this->rt_biz_is_plugin_active( $plugin_slug ) ) {
					$path  = $this->rt_biz_get_path_for_plugin( $plugin_slug );
					$nonce = wp_create_nonce( 'rtbiz_activate_plugin_' . $path ); ?>
					<p>
						<b><?php _e( 'rtBiz:' ); ?></b><?php _e( 'Click' ) ?>
						<a href="#"
						   onclick="activate_rtbiz_plugin( '<?php echo $path ?>', 'rtbiz_activate_plugin', '<?php echo $nonce; ?>' )">
							here</a> <?php
							_e( 'to activate ' . $plugin['name'] . '.', $plugin_slug ) ?>
					</p><?php
				}
			} ?>
			</div> <?php
		}

		public function rt_biz_is_plugin_installed( $slug ) {

			if ( empty( self::$plugins_dependency[ $slug ] ) ) {
				return false;
			}

			if ( $this->rt_biz_is_plugin_active( $slug ) || file_exists( WP_PLUGIN_DIR . '/' . $this->rt_biz_get_path_for_plugin( $slug ) ) ) {
				return true;
			}

			return false;
		}

		public function rt_biz_is_plugin_active( $slug ) {

			if ( empty( self::$plugins_dependency[ $slug ] ) ) {
				return false;
			}

			return self::$plugins_dependency[ $slug ]['active'];
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
			require_once RT_BIZ_PATH . 'admin/helper/rt-biz-functions.php';
			require_once RT_BIZ_PATH . 'vendor/redux/ReduxCore/framework.php';

			$rtb_includes_autoload  = new RT_WP_Autoload( RT_BIZ_PATH . 'includes/' );
			$rtb_admin_autoload     = new RT_WP_Autoload( RT_BIZ_PATH . 'admin/' );
			$rtb_admin_autoload     = new RT_WP_Autoload( RT_BIZ_PATH . 'admin/rt-biz-contact/metabox' );
			$rtb_models_autoload    = new RT_WP_Autoload( RT_BIZ_PATH . 'admin/models/' );
			$rtb_abstract_autoload  = new RT_WP_Autoload( RT_BIZ_PATH . 'admin/abstract/' );
			$rtb_abstract_autoload  = new RT_WP_Autoload( RT_BIZ_PATH . 'admin/abstract/metabox' );
			$rtb_settings_autoload  = new RT_WP_Autoload( RT_BIZ_PATH . 'admin/settings/' );
			$rtb_helper_autoload    = new RT_WP_Autoload( RT_BIZ_PATH . 'admin/helper/' );
			$rtb_migration_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'includes/migration/' );

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
			$plugin_i18n->set_domain( $this->get_plugin_name() );

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

			$plugin_admin = new Rt_Biz_Admin( $this->get_plugin_name(), $this->get_version() );

			if ( is_admin() ) {
				// update menu order of rtbiz menu
				Rt_Biz::$loader->add_action( 'admin_menu', $plugin_admin, 'rt_biz_register_menu', 1 );
				Rt_Biz::$loader->add_action( 'custom_menu_order', $plugin_admin, 'rt_biz_custom_pages_order' );

				Rt_Biz::$loader->add_filter( 'plugin_action_links_' . RT_BIZ_BASE_NAME, $plugin_admin, 'rt_biz_plugin_action_links' );
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
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     1.0.0
		 * @return    string    The name of the plugin.
		 */
		public function get_plugin_name() {
			return $this->plugin_name;
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

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     1.0.0
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
		}

	}
}
