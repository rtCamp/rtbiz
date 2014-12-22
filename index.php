<?php

/*
  Plugin Name: rtBiz
  Plugin URI: http://rtcamp.com/rtbiz
  Description: WordPress for Business
  Version: 0.5.6
  Author: rtCamp
  Author URI: http://rtcamp.com
  License: GPL
  Text Domain: rt_biz
 */

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'RT_BIZ_VERSION' ) ) {
	define( 'RT_BIZ_VERSION', '0.5.6' );
}
if ( ! defined( 'RT_BIZ_PATH' ) ) {
	define( 'RT_BIZ_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'RT_BIZ_URL' ) ) {
	define( 'RT_BIZ_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'RT_BIZ_BASE_NAME' ) ){
	define( 'RT_BIZ_BASE_NAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'RT_BIZ_PATH_TEMPLATES' ) ) {
	define( 'RT_BIZ_PATH_TEMPLATES', plugin_dir_path( __FILE__ ) . 'templates/' );
}
if ( ! defined( 'RT_BIZ_TEXT_DOMAIN' ) ) {
	define( 'RT_BIZ_TEXT_DOMAIN', 'rt_biz' );
}

include_once RT_BIZ_PATH . 'app/lib/rt-lib.php';
include_once RT_BIZ_PATH . 'app/helper/rt-biz-functions.php';
include_once RT_BIZ_PATH . 'app/vendor/taxonomy-metadata.php';

/**
 * Description of class-rt-biz
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Biz' ) ) {

	/**
	 * Class Rt_Biz
	 */
	class Rt_Biz {

		/** Singleton *************************************************************/

		/**
		 * @var WP_Time_Is The one true Rt_Biz
		 * @since 0.1
		 */
		private static $instance;

		/**
		 * @var string - rtBiz Dashboard Page Slug
		 */
		public static $dashboard_slug = 'rt-biz-dashboard';

		/**
		 * @var string - rtBiz Dashboard Screen ID
		 */
		public $dashboard_screen;

		/**
		 * @var string - rtBiz Access Control Page Slug
		 */
		public static $access_control_slug = 'rt-biz-access-control';

		/**
		 * @var float - rtBiz Menu Slug
		 */
		public static $menu_position = 30;

		/**
		 * @var string - rtBiz Settings Page Slug
		 */
		public static $settings_slug = 'rt-biz-settings';

		/**
		 * @var mixed|void - rtBiz Templates Path / URL
		 */
		public $templateURL;

		/**
		 * @var array - rtBiz Internal Menu Display Order
		 */
		public $menu_order = array();

		/**
		 *  Class Contstructor - This will initialize rtBiz alltogether
		 *  Provides useful actions/filters for other rtBiz addons to hook.
		 */

		public static $plugins_dependency = array();

		/**
		 * Main Rt_Biz Instance
		 *
		 * Insures that only one instance of Rt_Biz exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 0.1
		 * @static
		 * @static var array $instance
		 * @return The one true Rt_Biz
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Rt_Biz ) ) {

				self::$instance = new Rt_Biz;

				add_action( 'plugins_loaded', array( self::$instance, 'init' ) );

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

		function init() {

			self::$plugins_dependency = array(
				'posts-to-posts' => array(
					'project_type' => 'all',
					'name' => esc_html__( 'Create many-to-many relationships between all types of posts.', RT_BIZ_TEXT_DOMAIN ),
					'active' => class_exists( 'P2P_Autoload' ),
					'filename' => 'posts-to-posts.php',
				),
			);

			if ( ! self::$instance->check_p2p_dependency() ) {
				return false;
			}

			self::$instance->includes();

			self::$instance->load_textdomain();

			add_action( 'init', array( self::$instance, 'hooks' ), 11 );
			add_filter( 'rt_biz_modules', array( self::$instance, 'register_rt_biz_module' ) );

			self::$instance->update_database();

			self::$instance->init_db_models();

			self::$instance->init_attributes();

			self::$instance->init_notification_queue();

			self::$instance->init_access_control();
			self::$instance->init_modules();

			self::$instance->init_department();

			self::$instance->init_settings();

			self::$instance->init_dashboard();

			self::$instance->init_help();

			self::$instance->register_company_contact_connection();

			self::$instance->templateURL = apply_filters( 'rt_biz_template_url', 'rt_biz/' );

			add_action( 'after_setup_theme', array( self::$instance, 'init_wc_product_taxonomy' ),20 );

			//after_setup_theme hook because before that we do not have ACL module registered
			add_action( 'after_setup_theme', array( self::$instance, 'init_rt_mailbox' ),20 );

			do_action( 'rt_biz_init' );
		}

		function includes() {
			require_once plugin_dir_path( __FILE__ ) . 'app/vendor/' . 'redux/ReduxCore/framework.php';
			global $rtb_app_autoload, $rtb_models_autoload, $rtb_abstract_autoload, $rtb_modules_autoload, $rtb_settings_autoload, $rtb_notification_autoload, $rtb_reports_autoload, $rtb_helper_autoload;
			$rtb_app_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/' );
			$rtb_models_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/models/' );
			$rtb_abstract_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/abstract/' );
			$rtb_modules_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/modules/' );
			$rtb_notification_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/notification/' );
			$rtb_settings_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/settings/' );
			$rtb_reports_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/lib/rtreports/' );
			$rtb_helper_autoload = new RT_WP_Autoload( RT_BIZ_PATH . 'app/helper/' );
		}

		/**
		 * Loads the plugin language files
		 *
		 * @access public
		 * @since 0.1
		 * @return void
		 */
		public function load_textdomain() {
			// Set filter for plugin's languages directory
			$lang_dir = dirname( plugin_basename( RT_BIZ_PATH ) ) . '/languages/';
			$lang_dir = apply_filters( 'wp_ti_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale',  get_locale(), RT_BIZ_TEXT_DOMAIN );
			$mofile = sprintf( '%1$s-%2$s.mo', RT_BIZ_TEXT_DOMAIN, $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/' . RT_BIZ_TEXT_DOMAIN . '/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/wp_ti folder
				load_textdomain( RT_BIZ_TEXT_DOMAIN, $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/wp-time-is/languages/ folder
				load_textdomain( RT_BIZ_TEXT_DOMAIN, $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( RT_BIZ_TEXT_DOMAIN, false, $lang_dir );
			}
		}

		function init_rt_mailbox(){
			global $rt_MailBox ;
			$rt_MailBox = new Rt_Mailbox( Rt_Access_Control::$modules, Rt_Biz::$dashboard_slug, trailingslashit( RT_BIZ_PATH ) . 'index.php' );
		}

		function update_database() {
			$updateDB = new RT_DB_Update( trailingslashit( RT_BIZ_PATH ) . 'index.php', trailingslashit( RT_BIZ_PATH . 'app/schema/' ) );
			$updateDB->do_upgrade();
		}

		function init_db_models() {
			global $rt_biz_notification_rules_model, $rt_biz_notification_queue_model;

			$rt_biz_notification_rules_model = new RT_Biz_Notification_Rules_Model();
			$rt_biz_notification_queue_model = new RT_Biz_Notification_Queue_Model();
		}

		function init_attributes() {
			global $rt_biz_attributes;
			$rt_biz_attributes = new Rt_Biz_Attributes();
		}

		function init_notification_queue() {
			global $rt_biz_notification_queue;
			$rt_biz_notification_queue = new RT_Biz_Notification_Queue();
		}

		/**
		 *  Initialize Internal Menu Order.
		 */
		function init_menu_order() {

			global $rtbiz_offerings;

			$this->menu_order = array(
				self::$dashboard_slug,
				'edit.php?post_type=' . rt_biz_get_contact_post_type(),
				'edit-tags.php?taxonomy='.Rt_Contact::$user_category_taxonomy,
			);

			$settings = biz_get_redux_settings();
			if ( isset( $settings['product_plugin'] ) && 'none' != $settings['product_plugin'] ) {
				$this->menu_order[] = 'edit-tags.php?taxonomy=' . Rt_Offerings::$offering_slug . '&post_type=' . rt_biz_get_contact_post_type();
			}

			$this->menu_order = array_merge( $this->menu_order, array(
				'edit.php?post_type=' . rt_biz_get_company_post_type(),
				self::$access_control_slug,
				'edit-tags.php?taxonomy='.RT_Departments::$slug,
				Rt_Biz_Attributes::$attributes_page_slug,
				Rt_Mailbox::$page_name,
				self::$settings_slug,
			) );
		}

		function custom_pages_order( $menu_order ) {
			global $submenu;
			global $menu;
			if ( isset( $submenu[ self::$dashboard_slug ] ) && ! empty( $submenu[ self::$dashboard_slug ] ) ) {
				$module_menu = $submenu[ self::$dashboard_slug ];
				unset( $submenu[ self::$dashboard_slug ] );

				$new_index = 5;
				foreach ( $this->menu_order as $item ) {
					foreach ( $module_menu as $p_key => $menu_item ) {
						$out = array_filter( $menu_item, function( $in ) { return true !== $in; } );
						if ( in_array( $item, $out ) ) {
							$submenu[ self::$dashboard_slug ][ $new_index ] = $menu_item;

							if ( $item === self::$dashboard_slug ) {
								$submenu[ self::$dashboard_slug ][ $new_index ][0] = __( 'Dashboard', RT_BIZ_TEXT_DOMAIN );
							}

							unset( $module_menu[ $p_key ] );
							$new_index += 5;
							break;
						}
					}
				}
				foreach ( $module_menu as $p_key => $menu_item ) {
					$submenu[ self::$dashboard_slug ][ $new_index ] = $menu_item;
					unset( $module_menu[ $p_key ] );
					$new_index += 5;
				}
			}

			return $menu_order;
		}

		function init_dashboard() {
			global $rt_biz_dashboard, $rt_biz_reports;
			$rt_biz_dashboard = new Rt_Biz_Dashboard();
			$page_slugs       = array(
				self::$dashboard_slug,
			);
			$rt_biz_reports   = new Rt_Reports( $page_slugs );
		}

		function init_department() {
			global $rtbiz_department;
			$rtbiz_department = new RT_Departments();

			$taxonomy_metadata = new Rt_Lib_Taxonomy_Metadata\Taxonomy_Metadata();
			$taxonomy_metadata->activate();
		}

		function init_wc_product_taxonomy() {
			global $rtbiz_offerings;
			$editor_cap = rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'editor' );
			$terms_caps = array(
				'manage_terms' => $editor_cap,
				'edit_terms'   => $editor_cap,
				'delete_terms' => $editor_cap,
				'assign_terms' => $editor_cap,
			);

			$settings = biz_get_redux_settings();
			if ( isset( $settings['product_plugin'] ) && 'none' != $settings['product_plugin'] ) {

				$product_plugin   = $settings['product_plugin'];
				$to_register_posttype = array();
				foreach ( Rt_Access_Control::$modules as $key => $value ){

					if ( ! empty( $value['require_product_sync'] ) ) {
						if ( isset( $value['post_types'] ) ) {
							foreach ( $value['post_types'] as $posttype ) {
								array_push( $to_register_posttype, $posttype );
							}
						}
					}
				}

				$rtbiz_offerings = new Rt_Offerings( $product_plugin, $terms_caps, $to_register_posttype );
			}
		}

		function init_help() {
			global $rt_biz_help;
			$rt_biz_help = new Rt_Biz_Help();
		}

		/**
		 *  Actions/Filters used by rtBiz
		 */
		function hooks() {
			if ( is_admin() ) {
				$this->init_menu_order();
				add_filter( 'custom_menu_order', array( $this, 'custom_pages_order' ) );
				add_action( 'admin_menu', array( $this, 'register_menu' ), 1 );
				add_action( 'admin_enqueue_scripts', array( $this, 'load_styles_scripts' ) );

				add_filter( 'plugin_action_links_' . RT_BIZ_BASE_NAME, array( $this, 'plugin_action_links' ) );
				//add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );
			}
		}

		function plugin_action_links( $links ) {
			$links['get-started'] = '<a href="' . admin_url( 'admin.php?page=' . Rt_Biz::$dashboard_slug ) . '">' . __( 'Get Started', RT_BIZ_TEXT_DOMAIN ) . '</a>';
			$links['settings'] = '<a href="' . admin_url( 'admin.php?page=' . Rt_Biz::$settings_slug ) . '">' . __( 'Settings', RT_BIZ_TEXT_DOMAIN ) . '</a>';
			return $links;
		}

		function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
			if ( $plugin_file == RT_BIZ_BASE_NAME ) {
				$plugin_meta[] = '<a href="' . 'https://rtcamp.com/rtbiz/docs' . '">' . __( 'Documentation', RT_BIZ_TEXT_DOMAIN ) . '</a>';
				$plugin_meta[] = '<a href="' . 'https://rtcamp.com/rtbiz/faq' . '">' . __( 'FAQ', RT_BIZ_TEXT_DOMAIN ) . '</a>';
				$plugin_meta[] = '<a href="' . 'https://rtcamp.com/rtbiz/support' . '">' . __( 'Support', RT_BIZ_TEXT_DOMAIN ) . '</a>';
			}
			return $plugin_meta;
		}

		/**
		 *  Enqueue Scripts / Styles
		 *  Admin side as of now. Slipt up in case of front end.
		 */
		function load_styles_scripts() {
			global $rt_contact, $rt_company;
			wp_enqueue_script( 'rt-biz-admin', RT_BIZ_URL . 'app/assets/javascripts/admin.js', array( 'jquery' ), RT_BIZ_VERSION, true );

			if ( isset( $_REQUEST['post'] ) && isset( $_REQUEST['action'] ) && 'edit' == $_REQUEST['action'] ) {

				$post_type = get_post_type( $_REQUEST['post'] );
				if ( in_array( $post_type, array( $rt_contact->post_type, $rt_company->post_type ) ) ) {
					if ( ! wp_style_is( 'rt-jquery-ui-css' ) ) {
						wp_enqueue_style( 'rt-jquery-ui-css', RT_BIZ_URL . 'app/assets/css/jquery-ui-1.9.2.custom.css', false, RT_BIZ_VERSION, 'all' );
					}
					if ( ! wp_script_is( 'jquery-ui-datepicker' ) ) {
						wp_enqueue_script( 'jquery-ui-datepicker' );
					}
				}
			}

			if ( $_SERVER['SCRIPT_NAME'] == '/wp-admin/post-new.php' && isset( $_REQUEST['post_type'] ) && in_array( $_REQUEST['post_type'], array( $rt_contact->post_type, $rt_company->post_type ) ) ) {
				if ( ! wp_style_is( 'rt-jquery-ui-css' ) ) {
					wp_enqueue_style( 'rt-jquery-ui-css', RT_BIZ_URL . 'app/assets/css/jquery-ui-1.9.2.custom.css', false, RT_BIZ_VERSION, 'all' );
				}
				if ( ! wp_script_is( 'jquery-ui-datepicker' ) ) {
					wp_enqueue_script( 'jquery-ui-datepicker' );
				}
			}

			if ( isset( $_REQUEST['taxonomy'] ) && in_array( $_REQUEST['taxonomy'], array( RT_Departments::$slug, Rt_Contact::$user_category_taxonomy ) ) ) {
				wp_localize_script( 'rt-biz-admin', 'rt_biz_dashboard_screen', $this->dashboard_screen );
				wp_localize_script( 'rt-biz-admin', 'rt_biz_department_url', admin_url( 'edit-tags.php?taxonomy=' . $_REQUEST['taxonomy'] ) );
			}

			$settings = biz_get_redux_settings();
			if ( isset( $settings['product_plugin'] ) && 'none' != $settings['product_plugin'] ) {
				if ( isset( $_REQUEST['taxonomy'] ) && $_REQUEST['taxonomy'] == Rt_Offerings::$offering_slug ) {
					wp_localize_script( 'rt-biz-admin', 'rt_biz_dashboard_screen', $this->dashboard_screen );
					wp_localize_script( 'rt-biz-admin', 'rt_biz_offering_url', admin_url( 'edit-tags.php?taxonomy=' . Rt_Offerings::$offering_slug . '&post_type=' . $rt_contact->post_type ) );
				}
			}
		}

		/**
		 *  Registers all the menus/submenus for rtBiz
		 */
		function register_menu() {
			global $rt_contact, $rt_company, $rt_access_control, $rt_biz_dashboard, $rtbiz_offerings;
			$settings  = biz_get_redux_settings();
			$logo_url               = ! empty( $settings['logo_url']['url'] ) ? $settings['logo_url']['url'] : RT_BIZ_URL . 'app/assets/img/biz-16X16.png' ;
			$menu_label             = ! empty( $settings['menu_label'] ) ? $settings['menu_label'] : __( 'rtBiz' );
			$this->dashboard_screen = add_menu_page( $menu_label, $menu_label, rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'author' ), self::$dashboard_slug, array( $this, 'dashboard_ui' ), $logo_url, self::$menu_position );

			$rt_biz_dashboard->add_screen_id( $this->dashboard_screen );
			$rt_biz_dashboard->setup_dashboard();
			$settings = biz_get_redux_settings();
			if ( isset( $settings['product_plugin'] ) && 'none' != $settings['product_plugin'] ) {
				add_submenu_page( self::$dashboard_slug, __( 'Offerings' ), __( '--- Offerings' ), rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'editor' ), 'edit-tags.php?taxonomy=' . Rt_Offerings::$offering_slug . '&post_type=' . $rt_contact->post_type );
			}
			add_submenu_page( self::$dashboard_slug, __( 'Access Control' ), __( 'Access Control' ), rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'admin' ), self::$access_control_slug, array( $rt_access_control, 'acl_settings_ui' ) );
			add_submenu_page( self::$dashboard_slug, __( 'Departments' ), __( '--- Departments' ), rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'editor' ), 'edit-tags.php?taxonomy=' . RT_Departments::$slug );
			add_submenu_page( self::$dashboard_slug, __( 'User Groups' ), __( '--- Contact Groups' ), rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'editor' ), 'edit-tags.php?taxonomy=' . Rt_Contact::$user_category_taxonomy );
		}

		/**
		 *  Dashboard Page UI Template
		 */
		function dashboard_ui() {
			rt_biz_get_template( 'dashboard.php' );
		}

		/**
		 *  Checks for Posts 2 Posts Plugin. It is required for rtBiz to work.
		 *  If not found; rtBiz will throw admin notice to either install / activate it.
		 */
		function check_p2p_dependency() {
			$flag = true;

			if ( ! class_exists( 'P2P_Autoload' ) ) {
				$flag = false;
			}

			if ( ! $flag ) {

				add_action( 'admin_enqueue_scripts', array( $this, 'plugins_dependency_enque_js' ) );
				add_action( 'wp_ajax_rtbiz_install_plugin', array( $this, 'rtbiz_install_plugin_ajax' ), 10 );
				add_action( 'wp_ajax_rtbiz_activate_plugin', array( $this, 'rtbiz_activate_plugin_ajax' ), 10 );

				add_action( 'admin_notices', array( $this, 'admin_notice_rtbiz_plugin_not_installed' ) );
			}

			return $flag;
		}

		function plugins_dependency_enque_js() {
			wp_enqueue_script( 'rtbiz-plugins-dependency', RT_BIZ_URL . 'app/assets/javascripts/rtbiz_plugin_check.js', '', false, true );
			wp_localize_script( 'rtbiz-plugins-dependency', 'rtbiz_ajax_url', admin_url( 'admin-ajax.php' ) );
		}

		/**
		 * hook for admin notices that checks if post to post is installed if not it let it download and install
		 */
		function admin_notice_rtbiz_plugin_not_installed() {
			?>
			<div class="error rtbiz-plugin-not-installed-error">
				<?php
				if ( ! $this->is_rtbiz_plugin_installed( 'posts-to-posts' ) ) {
					$nonce = wp_create_nonce( 'rtbiz_install_plugin_posts-to-posts' );
					?>
					<p><b><?php _e( 'rtBiz:' ) ?></b> <?php _e( 'Click' ) ?> <a href="#"
					                                                            onclick="install_rtbiz_plugin('posts-to-posts','rtbiz_install_plugin','<?php echo $nonce ?>')">here</a> <?php _e( 'to install posts-to-posts.', 'posts-to-posts' ) ?>
					</p>
				<?php
				} else {
					if ( $this->is_rtbiz_plugin_installed( 'posts-to-posts' ) && ! $this->is_rtbiz_plugin_active( 'posts-to-posts' ) ) {
						$path  = $this->get_path_for_rtbiz_plugin( 'posts-to-posts' );
						$nonce = wp_create_nonce( 'rtbiz_activate_plugin_' . $path );
						?>
						<p><b><?php _e( 'rtBiz:' ) ?></b> <?php _e( 'Click' ) ?> <a href="#"
						                                                            onclick="activate_rtbiz_plugin('<?php echo $path ?>','rtbiz_activate_plugin','<?php echo $nonce; ?>')">here</a> <?php _e( 'to activate posts-to-posts.', 'posts-to-posts' ) ?>
						</p>
					<?php
					}
				}
				?>
			</div>
		<?php
		}

		/**
		 * @param $slug
		 * get file path for post to post plugin
		 *
		 * @return string
		 */
		function get_path_for_rtbiz_plugin( $slug ) {

			$filename = ( ! empty( self::$plugins_dependency[ $slug ]['filename'] ) ) ? self::$plugins_dependency[ $slug ]['filename'] : $slug . '.php';

			return $slug . '/' . $filename;
		}

		/**
		 * @param $slug
		 * check if post to post plugin is active or not
		 *
		 * @return bool
		 */
		function is_rtbiz_plugin_active( $slug ) {

			if ( empty( self::$plugins_dependency[ $slug ] ) ) {
				return false;
			}

			return self::$plugins_dependency[ $slug ]['active'];
		}


		/**
		 * @param $slug
		 * check if is post to post plugin in installed
		 *
		 * @return bool
		 */
		function is_rtbiz_plugin_installed( $slug ) {

			if ( empty( self::$plugins_dependency[ $slug ] ) ) {
				return false;
			}

			if ( $this->is_rtbiz_plugin_active( $slug ) || file_exists( WP_PLUGIN_DIR . '/' . $this->get_path_for_rtbiz_plugin( $slug ) ) ) {
				return true;
			}

			return false;
		}

		/**
		 * ajax call for installing plugin
		 */
		function rtbiz_install_plugin_ajax() {

			if ( empty( $_POST['plugin_slug'] ) ) {
				die( __( 'ERROR: No slug was passed to the AJAX callback.', RT_BIZ_TEXT_DOMAIN ) );
			}
			check_ajax_referer( 'rtbiz_install_plugin_' . $_POST['plugin_slug'] );

			if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
				die( __( 'ERROR: You lack permissions to install and/or activate plugins.', RT_BIZ_TEXT_DOMAIN ) );
			}
			$this->rtbiz_install_plugin( $_POST['plugin_slug'] );

			echo 'true';
			die();
		}

		/**
		 * @param $plugin_slug
		 * ajax call calls uses this function to install plugin
		 */
		function rtbiz_install_plugin( $plugin_slug ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

			$api = plugins_api( 'plugin_information', array( 'slug' => $plugin_slug, 'fields' => array( 'sections' => false ) ) );

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

			$activate_result = activate_plugin( $this->get_path_for_rtbiz_plugin( $plugin_slug ) );
			if ( is_wp_error( $activate_result ) ) {
				die( sprintf( __( 'ERROR: Failed to activate plugin: %s', RT_BIZ_TEXT_DOMAIN ), $activate_result->get_error_message() ) );
			}
		}

		/**
		 * ajax call for active plugin
		 */
		function rtbiz_activate_plugin_ajax() {
			if ( empty( $_POST['path'] ) ) {
				die( __( 'ERROR: No slug was passed to the AJAX callback.', RT_BIZ_TEXT_DOMAIN ) );
			}
			check_ajax_referer( 'rtbiz_activate_plugin_' . $_POST['path'] );

			if ( ! current_user_can( 'activate_plugins' ) ) {
				die( __( 'ERROR: You lack permissions to activate plugins.', RT_BIZ_TEXT_DOMAIN ) );
			}

			$this->rtbiz_activate_plugin( $_POST['path'] );

			echo 'true';
			die();
		}

		/**
		 * @param $plugin_path
		 * ajax call for active plugin calls this function to active plugin
		 */
		function rtbiz_activate_plugin( $plugin_path ) {

			$activate_result = activate_plugin( $plugin_path );
			if ( is_wp_error( $activate_result ) ) {
				die( sprintf( __( 'ERROR: Failed to activate plugin: %s', RT_BIZ_TEXT_DOMAIN ), $activate_result->get_error_message() ) );
			}
		}

		/**
		 *  Initialize Rt_Contact & Rt_Company
		 */
		function init_modules() {
			global $rt_contact, $rt_company;
			$rt_contact = new Rt_Contact();
			$rt_company = new Rt_Company();
		}

		/**
		 *  Initialize Access Control Module which is responsible for all the Uses Access & User Control for rtBiz
		 *  as well other addon plugins.
		 */
		function init_access_control() {
			global $rt_access_control;
			$rt_access_control = new Rt_Access_Control();
		}

		/**
		 *  Initialize Settings Object
		 */
		function init_settings() {
			global $rt_biz_setttings;
			$rt_biz_setttings = new Rt_Biz_Setting();
		}

		/**
		 *  Initialize rtBiz ACL. It will register the rtBiz module it self to Rt_Access_Control.
		 *  Accordingly Rt_Access_Control will provide user permissions to the Department
		 */
		function register_rt_biz_module( $modules ) {
			global $rt_contact, $rt_company;
			$settings  = biz_get_redux_settings();
			$menu_label = isset( $settings['menu_label'] ) ? $settings['menu_label'] : 'rtBiz';
			$modules[ rt_biz_sanitize_module_key( RT_BIZ_TEXT_DOMAIN ) ] = array(
				'label'      => $menu_label,
				'post_types' => array( $rt_contact->post_type, $rt_company->post_type ),
				'require_department' => true,
			    'require_product_sync' => true,
			);

			return $modules;
		}

		/**
		 *  Registers Posts 2 Posts relation for Organization - Person
		 */
		function register_company_contact_connection() {
			add_action( 'p2p_init', array( $this, 'company_contact_connection' ) );
		}

		/**
		 *  Organization - Person Connection for Posts 2 Posts
		 */
		function company_contact_connection() {
			global $rt_company, $rt_contact;
			p2p_register_connection_type( array(
				                              'name' => $rt_company->post_type . '_to_' . $rt_contact->post_type,
				                              'from' => $rt_company->post_type,
				                              'to'   => $rt_contact->post_type,
				                              'cardinality' => 'one-to-many',
				                              'admin_column' => 'any',
				                              'from_labels' => array(
					                              'column_title' => $rt_contact->labels['name'],
				                              ),
				                              'to_labels' => array(
					                              'column_title' => $rt_company->labels['singular_name'],
				                              ),
			                              ) );
		}

		/**
		 *  This establishes a connection between any entiy ( either company - from / contact - to )
		 *  acording to the parameters passed.
		 *
		 * @param string $from - Organization
		 * @param string $to   - Person
		 */
		function connect_company_to_contact( $from = '', $to = '' ) {
			global $rt_company, $rt_contact;
			if ( ! p2p_connection_exists( $rt_company->post_type . '_to_' . $rt_contact->post_type, array( 'from' => $from, 'to' => $to ) ) ) {
				p2p_create_connection( $rt_company->post_type . '_to_' . $rt_contact->post_type, array( 'from' => $from, 'to' => $to ) );
			}
		}

		/**
		 *  Returns all the connected posts to the passed parameter entity object.
		 *  It can be either an company object or a contact object.
		 *
		 *  It will return the other half objects of the connection.
		 *
		 * @param $connected_items - Organization / Person Object
		 *
		 * @return array
		 */
		function get_company_to_contact_connection( $connected_items ) {
			global $rt_company, $rt_contact;

			return get_posts(
				array(
					'connected_type'   => $rt_company->post_type . '_to_' . $rt_contact->post_type,
					'connected_items'  => $connected_items,
					'nopaging'         => true,
					'suppress_filters' => false,
				)
			);
		}

	}

}

/**
 * The main function responsible for returning the one true Rt_Biz
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $rtbiz = rtbiz(); ?>
 *
 * @return object The one true Rt_Biz Instance
 */
function rtbiz() {
	return Rt_Biz::instance();
}

// Get rtBiz Running
rtbiz();
