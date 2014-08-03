<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

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
		public function __construct() {

			if ( ! $this->check_p2p_dependency() ) {
				return false;
			}

			add_action( 'init', array( $this, 'hooks' ), 11 );
			add_filter( 'rt_biz_modules', array( $this, 'register_rt_biz_module' ) );

			$this->update_database();

			$this->init_db_models();

			$this->init_attributes();

			$this->init_notification_queue();

			$this->init_access_control();
			$this->init_modules();
			$this->init_settings();
//			$this->init_menu_order();

			$this->init_dashboard();

			$this->register_organization_person_connection();

			$this->templateURL = apply_filters( 'rt_biz_template_url', 'rt_biz/' );
			do_action( 'rt_biz_init' );
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
		 *  TODO - Not used as of now. Later on might be used if we have other sub-menus in rtBiz Menu.
		 */
		function init_menu_order() {
			$this->menu_order[ self::$dashboard_slug ] = 5;

			$this->menu_order[ self::$my_team_slug ] = 6;

			global $rt_person, $rt_organization, $rt_biz_attributes;

			$this->menu_order[ 'post-new.php?post_type=' . $rt_person->post_type ] = 10;
			$this->menu_order[ 'edit.php?post_type=' . $rt_person->post_type ] = 15;
			$this->menu_order[ 'post-new.php?post_type=' . $rt_organization->post_type ] = 50;
			$this->menu_order[ 'edit.php?post_type=' . $rt_organization->post_type ] = 55;

			$this->menu_order[ $rt_biz_attributes->attributes_page_slug ] = 90;

			$this->menu_order[ self::$settings_slug ] = 100;
		}

		function init_dashboard() {
			global $rt_biz_dashboard, $rt_biz_reports;
			$rt_biz_dashboard = new Rt_Biz_Dashboard();
			$page_slugs = array(
				self::$dashboard_slug,
			);
			$rt_biz_reports = new Rt_Reports( $page_slugs );
		}

		/**
		 *  Actions/Filters used by rtBiz
		 */
		function hooks() {
			if ( is_admin() ) {
				add_action( 'admin_menu', array( $this, 'register_menu' ), 1 );
//				add_filter( 'custom_menu_order', array( $this, 'biz_pages_order' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'load_styles_scripts' ) );
			}
		}

		/**
		 *  Enqueue Scripts / Styles
		 *  Admin side as of now. Slipt up in case of front end.
		 */
		function load_styles_scripts() {
			global $rt_person, $rt_organization;
			wp_enqueue_script( 'rt-biz-admin', RT_BIZ_URL . 'app/assets/javascripts/admin.js', array( 'jquery' ), RT_BIZ_VERSION, true );
			if ( isset( $_REQUEST[ 'rt-biz-my-team' ] ) ) {
				wp_localize_script( 'rt-biz-admin', 'rt_biz_dashboard_screen', $this->dashboard_screen );
				wp_localize_script( 'rt-biz-admin', 'rt_biz_my_team_url', admin_url( 'edit.php?post_type=' . $rt_person->post_type . '&rt-biz-my-team=true' ) );
			}

			if ( isset( $_REQUEST[ 'post' ] ) && isset( $_REQUEST[ 'action' ] ) && $_REQUEST[ 'action' ] == 'edit' ) {
				$is_our_team_mate = get_post_meta( $_REQUEST[ 'post' ], Rt_Person::$meta_key_prefix . Rt_Person::$our_team_mate_key, true );
				if ( $is_our_team_mate ) {
					wp_localize_script( 'rt-biz-admin', 'rt_biz_dashboard_screen', $this->dashboard_screen );
					wp_localize_script( 'rt-biz-admin', 'rt_biz_my_team_url', admin_url( 'edit.php?post_type=' . $rt_person->post_type . '&rt-biz-my-team=true' ) );
				}

				$post_type = get_post_type( $_REQUEST[ 'post' ] );
				if ( in_array( $post_type, array( $rt_person->post_type, $rt_organization->post_type ) ) ) {
					if ( ! wp_style_is( 'rt-jquery-ui-css' ) ) {
						wp_enqueue_style( 'rt-jquery-ui-css', RT_BIZ_URL . 'app/assets/css/jquery-ui-1.9.2.custom.css', false, RT_BIZ_VERSION, 'all' );
					}
					if ( ! wp_script_is( 'jquery-ui-datepicker' ) ) {
						wp_enqueue_script( 'jquery-ui-datepicker' );
					}
					wp_localize_script( 'rt-biz-admin', 'rt_biz_module_page_active', '1' );
					wp_localize_script( 'rt-biz-admin', 'rt_biz_dashboard_screen', $this->dashboard_screen );
				}
			}

			if ( $_SERVER[ 'SCRIPT_NAME' ] == '/wp-admin/post-new.php' && isset( $_REQUEST[ 'post_type' ] ) && in_array( $_REQUEST[ 'post_type' ], array( $rt_person->post_type, $rt_organization->post_type ) ) ) {
				if ( ! wp_style_is( 'rt-jquery-ui-css' ) ) {
					wp_enqueue_style( 'rt-jquery-ui-css', RT_BIZ_URL . 'app/assets/css/jquery-ui-1.9.2.custom.css', false, RT_BIZ_VERSION, 'all' );
				}
				if ( ! wp_script_is( 'jquery-ui-datepicker' ) ) {
					wp_enqueue_script( 'jquery-ui-datepicker' );
				}
				wp_localize_script( 'rt-biz-admin', 'rt_biz_module_new_page', admin_url( 'edit.php?post_type=' . $rt_person->post_type ) );
				wp_localize_script( 'rt-biz-admin', 'rt_biz_dashboard_screen', $this->dashboard_screen );
			}

			if ( isset( $_REQUEST[ 'taxonomy' ] ) && $_REQUEST[ 'taxonomy' ] == 'user-group' ) {
				wp_localize_script( 'rt-biz-admin', 'rt_biz_dashboard_screen', $this->dashboard_screen );
				wp_localize_script( 'rt-biz-admin', 'rt_biz_department_url', admin_url( 'edit-tags.php?taxonomy=user-group' ) );
			}
		}

		/**
		 *  Registers all the menus/submenus for rtBiz
		 */
		function register_menu() {
			global $rt_person, $rt_organization, $rt_access_control, $rt_biz_dashboard;
			$logo_url = Rt_Biz_Settings::$settings[ 'logo_url' ];
			$menu_label = Rt_Biz_Settings::$settings[ 'menu_label' ];
			$this->dashboard_screen = add_menu_page( $menu_label, $menu_label, rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'author' ), self::$dashboard_slug, array( $this, 'dashboard_ui' ), $logo_url, self::$menu_position );

			$rt_biz_dashboard->add_screen_id( $this->dashboard_screen );
			$rt_biz_dashboard->setup_dashboard();

			add_submenu_page( self::$dashboard_slug, __( 'Our Team' ), __( 'Our Team' ), rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'author' ), 'edit.php?post_type=' . $rt_person->post_type . '&rt-biz-my-team=true' );
			add_submenu_page( self::$dashboard_slug, __( 'Employees' ), __( '--- Employees' ), rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'author' ), 'edit.php?post_type=' . $rt_person->post_type . '&rt-biz-my-team=true' );
			add_submenu_page( self::$dashboard_slug, __( 'Departments' ), __( '--- Departments' ), rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'editor' ), 'edit-tags.php?taxonomy=user-group' );
			add_submenu_page( self::$dashboard_slug, __( 'Access Control' ), __( '--- Access Control' ), rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'admin' ), self::$access_control_slug, array( $rt_access_control, 'acl_settings_ui' ) );
			add_submenu_page( self::$dashboard_slug, __( 'Client' ), __( 'Client' ), rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'author' ), 'edit.php?post_type=' . $rt_person->post_type );
			add_submenu_page( self::$dashboard_slug, __( '--- Contacts' ), __( '--- Contacts' ), rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'author' ), 'edit.php?post_type=' . $rt_person->post_type );
			add_submenu_page( self::$dashboard_slug, __( '--- Companies' ), __( '--- Companies' ), rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'author' ), 'edit.php?post_type=' . $rt_organization->post_type );
		}

		/**
		 *  Tempers with the global $submenu array.
		 *  It arranges all the menus according to $menu_order that is defined in init_menu_order()
		 *
		 * @param $menu_order
		 * @return mixed
		 */
		function biz_pages_order( $menu_order ) {
			global $submenu;

			if ( isset( $submenu[ self::$dashboard_slug ] ) && ! empty( $submenu[ self::$dashboard_slug ] ) ) {
				$menu = $submenu[ self::$dashboard_slug ];
				$new_menu = array();

				foreach ( $menu as $p_key => $item ) {
					foreach ( $this->menu_order as $slug => $order ) {
						if ( false !== array_search( $slug, $item ) ) {
							$new_menu[ $order ] = $item;
						}
					}
				}
				ksort( $new_menu );
				$submenu[ self::$dashboard_slug ] = $new_menu;
			}

			return $menu_order;
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
			$used_function = array(
				'p2p_register_connection_type',
				'p2p_create_connection',
				'p2p_connection_exists',
				'p2p_delete_connections',
				'p2p_register_connection_type',
			);

			foreach ( $used_function as $fn ) {
				if ( ! function_exists( $fn ) ) {
					$flag = false;
				}
			}

			if ( ! $flag ) {
				add_action( 'admin_notices', array( $this, 'p2p_admin_notice' ) );
			}

			return $flag;
		}

		/**
		 *  Posts 2 Posts Plugin Admin Notice
		 */
		function p2p_admin_notice() {
			?>
			<div class="updated">
				<p><?php _e( sprintf( 'rtBiz : It seems that Posts 2 Posts plugin is not installed or activated. Please %s / %s it.', '<a href="' . admin_url( 'plugin-install.php?tab=search&s=posts-2-posts' ) . '">' . __( 'install' ) . '</a>', '<a href="' . admin_url( 'plugins.php' ) . '">' . __( 'activate' ) . '</a>' ) ); ?></p>
			</div>
			<?php
		}

		/**
		 *  Initialize Rt_Person & Rt_Organization
		 */
		function init_modules() {
			global $rt_person, $rt_organization;
			$rt_person = new Rt_Person();
			$rt_organization = new Rt_Organization();
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
			$rt_biz_setttings = new Rt_Biz_Settings();
		}

		/**
		 *  Initialize rtBiz ACL. It will register the rtBiz module it self to Rt_Access_Control.
		 *  Accordingly Rt_Access_Control will provide user permissions to the groups
		 */
		function register_rt_biz_module( $modules ) {
			global $rt_person, $rt_organization;
			$rt_biz_options = maybe_unserialize( get_option( RT_BIZ_TEXT_DOMAIN . '_options' ) );
			$menu_label = $rt_biz_options[ 'menu_label' ];
			$modules[ rt_biz_sanitize_module_key( RT_BIZ_TEXT_DOMAIN ) ] = array(
				'label' => $menu_label,
				'post_types' => array( $rt_person->post_type, $rt_organization->post_type ),
			);
			return $modules;
		}

		/**
		 *  Registers Posts 2 Posts relation for Organization - Person
		 */
		function register_organization_person_connection() {
			add_action( 'p2p_init', array( $this, 'organization_person_connection' ) );
		}

		/**
		 *  Organization - Person Connection for Posts 2 Posts
		 */
		function organization_person_connection() {
			global $rt_organization, $rt_person;
			p2p_register_connection_type( array(
				'name' => $rt_organization->post_type . '_to_' . $rt_person->post_type,
				'from' => $rt_organization->post_type,
				'to' => $rt_person->post_type,
			) );
		}

		/**
		 *  This establishes a connection between any entiy ( either organization - from / person - to )
		 *  acording to the parameters passed.
		 *
		 * @param string $from  - Organization
		 * @param string $to    - Person
		 */
		function connect_organization_to_person( $from = '', $to = '' ) {
			global $rt_organization, $rt_person;
			if ( ! p2p_connection_exists( $rt_organization->post_type . '_to_' . $rt_person->post_type, array( 'from' => $from, 'to' => $to ) ) ) {
				p2p_create_connection( $rt_organization->post_type . '_to_' . $rt_person->post_type, array( 'from' => $from, 'to' => $to ) );
			}
		}

		/**
		 *  Returns all the connected posts to the passed parameter entity object.
		 *  It can be either an organization object or a person object.
		 *
		 *  It will return the other half objects of the connection.
		 *
		 * @param $connected_items  - Organization / Person Object
		 * @return array
		 */
		function get_organization_to_person_connection( $connected_items ) {
			global $rt_organization, $rt_person;
			return get_posts(
					array(
						'connected_type' => $rt_organization->post_type . '_to_' . $rt_person->post_type,
						'connected_items' => $connected_items,
						'nopaging' => true,
						'suppress_filters' => false,
					)
			);
		}

	}

}
