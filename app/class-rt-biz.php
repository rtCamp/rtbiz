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

	class Rt_Biz {

		public static $dashboard_slug = 'rt-biz-dashboard';
		public $dashboard_screen;
		public static $access_control_slug = 'rt-biz-access-control';
		public static $menu_position = 3.500;
		public static $settings_slug = 'rt-biz-settings';
		public $templateURL;
		public $menu_order = array();

		public function __construct() {
			$this->check_p2p_dependency();

			add_action( 'init', array( $this, 'hooks' ), 11 );

			$this->init_access_control();
			$this->init_settings();
			$this->init_biz_acl();
			$this->init_modules();
			$this->init_roles();
//			$this->init_menu_order();

			$this->register_organization_person_connection();

			$this->templateURL = apply_filters( 'rt_biz_template_url', 'rt_biz/' );
			do_action( 'rt_biz_init' );
		}

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

		function hooks() {
			if ( is_admin() ) {
				add_action( 'admin_menu', array( $this, 'register_menu' ), 1 );
//				add_filter( 'custom_menu_order', array( $this, 'biz_pages_order' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'load_styles_scripts' ) );
			}
		}

		function load_styles_scripts() {
			global $rt_person;
			wp_enqueue_script( 'rt-biz-admin', RT_BIZ_URL . 'app/assets/javascripts/admin.js', array( 'jquery' ), RT_BIZ_VERSION, true );
			if ( isset( $_REQUEST['rt-biz-my-team'] ) ) {
				wp_localize_script( 'rt-biz-admin', 'rt_biz_dashboard_screen', $this->dashboard_screen );
				wp_localize_script( 'rt-biz-admin', 'rt_biz_my_team_url', admin_url( 'edit.php?post_type='.$rt_person->post_type.'&rt-biz-my-team=true' ) );
			}

			if ( isset( $_REQUEST['taxonomy'] ) && $_REQUEST['taxonomy'] == 'user-group' ) {
				wp_localize_script( 'rt-biz-admin', 'rt_biz_dashboard_screen', $this->dashboard_screen );
				wp_localize_script( 'rt-biz-admin', 'rt_biz_department_url', admin_url( 'edit-tags.php?taxonomy=user-group' ) );
			}
		}

		function register_menu() {
			global $rt_person, $rt_organization, $rt_access_control;
			$logo_url = Rt_Biz_Settings::$settings['logo_url'];
			$this->dashboard_screen = add_menu_page( __( 'rtBiz' ), __( 'rtBiz' ), rt_biz_get_minimum_access_role( RT_BIZ_TEXT_DOMAIN, 'author' ), self::$dashboard_slug, array( $this, 'dashboard_ui' ), $logo_url, self::$menu_position );
			add_submenu_page( self::$dashboard_slug, __( 'Our Team' ), __( 'Our Team' ), rt_biz_get_minimum_access_role( RT_BIZ_TEXT_DOMAIN, 'author' ), 'edit.php?post_type='.$rt_person->post_type.'&rt-biz-my-team=true' );
			add_submenu_page( self::$dashboard_slug, __( 'Employees' ), __( '--- Employees' ), rt_biz_get_minimum_access_role( RT_BIZ_TEXT_DOMAIN, 'author' ), 'edit.php?post_type='.$rt_person->post_type.'&rt-biz-my-team=true' );
			add_submenu_page( self::$dashboard_slug, __( 'Departments' ), __( '--- Departments' ), rt_biz_get_minimum_access_role( RT_BIZ_TEXT_DOMAIN, 'author' ), 'edit-tags.php?taxonomy=user-group' );
			add_submenu_page( self::$dashboard_slug, __( 'Access Control' ), __( '--- Access Control' ), rt_biz_get_minimum_access_role( RT_BIZ_TEXT_DOMAIN, 'admin' ), self::$access_control_slug, array( $rt_access_control, 'acl_settings_ui' ) );
			add_submenu_page( self::$dashboard_slug, __( 'Client' ), __( 'Client' ), rt_biz_get_minimum_access_role( RT_BIZ_TEXT_DOMAIN, 'author' ), 'edit.php?post_type='.$rt_person->post_type );
			add_submenu_page( self::$dashboard_slug, __( '--- Contacts' ), __( '--- Contacts' ), rt_biz_get_minimum_access_role( RT_BIZ_TEXT_DOMAIN, 'author' ), 'edit.php?post_type='.$rt_person->post_type );
			add_submenu_page( self::$dashboard_slug, __( '--- Companies' ), __( '--- Companies' ), rt_biz_get_minimum_access_role( RT_BIZ_TEXT_DOMAIN, 'author' ), 'edit.php?post_type='.$rt_organization->post_type );
		}

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

		function dashboard_ui() {
			rt_biz_get_template( 'dashboard.php' );
		}

		function check_p2p_dependency() {
			if ( ! class_exists( 'P2P_Box_Factory' ) ) {
				add_action( 'admin_notices', array( $this, 'p2p_admin_notice' ) );
			}
		}

		function p2p_admin_notice() {
			?>
			<div class="updated">
				<p><?php _e( sprintf( 'rtBiz : It seems that Posts 2 Posts plugin is not installed or activated. Please %s / %s it.', '<a href="' . admin_url( 'plugin-install.php?tab=search&s=posts-2-posts' ) . '">' . __( 'install' ) . '</a>', '<a href="' . admin_url( 'plugins.php' ) . '">' . __( 'activate' ) . '</a>' ) ); ?></p>
			</div>
		<?php
		}

		function init_modules() {
			global $rt_person, $rt_organization;
			$rt_person = new Rt_Person();
			$rt_organization = new Rt_Organization();
		}

		function init_roles() {
			global $rt_biz_roles;
			$rt_biz_roles = new Rt_Biz_Roles();
		}

		function init_access_control() {
			global $rt_access_control;
			$rt_access_control = new Rt_Access_Control();
		}

		function init_settings() {
			global $rt_biz_setttings;
			$rt_biz_setttings = new Rt_Biz_Settings();
		}

		function init_biz_acl() {
			global $rt_biz_acl;
			$rt_biz_acl = new Rt_Biz_ACL();
		}

		function register_organization_person_connection() {
			add_action( 'p2p_init', array( $this, 'organization_person_connection' ) );
		}

		function organization_person_connection() {
			global $rt_organization, $rt_person;
			if ( function_exists( 'p2p_register_connection_type' ) ) {
				p2p_register_connection_type( array(
					'name' => $rt_organization->post_type . '_to_' . $rt_person->post_type,
					'from' => $rt_organization->post_type,
					'to' => $rt_person->post_type,
				) );
			}
		}

		function connect_organization_to_person( $from = '', $to = '' ) {
			global $rt_organization, $rt_person;
			if ( function_exists( 'p2p_create_connection' ) && function_exists( 'p2p_connection_exists' ) ) {
				if ( ! p2p_connection_exists( $rt_organization->post_type . '_to_' . $rt_person->post_type, array( 'from' => $from, 'to' => $to ) ) ) {
					p2p_create_connection( rtcrm_post_type_name( 'account' ) . '_to_' . rtcrm_post_type_name( 'contact' ), array( 'from' => $from, 'to' => $to ) );
				}
			}
		}

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
