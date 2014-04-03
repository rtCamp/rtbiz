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

		public static $menu_page_slug = 'rt-biz';
		public static $menu_position = 3.500;
		public static $settings_page_slug = 'rt-biz-settings';
		public $templateURL;
		public $menu_order = array();

		public function __construct() {
			$this->check_p2p_dependency();

			$this->hooks();

			$this->init_settings();
			$this->init_modules();
			$this->init_roles();
			$this->init_attributes();
			$this->init_menu_order();

			$this->register_organization_person_connection();

			$this->templateURL = apply_filters( 'rt_biz_template_url', 'rt_biz/' );
		}

		function init_menu_order() {
			$this->menu_order[ self::$menu_page_slug ] = 5;

			global $rt_person, $rt_organization, $rt_biz_attributes;

			$this->menu_order[ 'post-new.php?post_type=' . $rt_person->post_type ] = 10;
			$this->menu_order[ 'edit.php?post_type=' . $rt_person->post_type ] = 15;
			$this->menu_order[ 'post-new.php?post_type=' . $rt_organization->post_type ] = 50;
			$this->menu_order[ 'edit.php?post_type=' . $rt_organization->post_type ] = 55;

			$this->menu_order[ $rt_biz_attributes->attributes_page_slug ] = 90;

			$this->menu_order[ self::$settings_page_slug ] = 100;
		}

		function hooks() {
			if ( is_admin() ) {
				add_action( 'admin_menu', array( $this, 'register_menu' ), 1 );
				add_filter( 'custom_menu_order', array( $this, 'biz_pages_order' ) );
			}
		}

		function register_menu() {
			$logo_url = '';
			add_menu_page( __( 'rtBiz' ), __( 'rtBiz' ), Rt_Biz_Roles::$global_caps[ 'manage_rt_biz' ], self::$menu_page_slug, array( $this, 'biz_ui' ), $logo_url, self::$menu_position );
		}

		function biz_pages_order( $menu_order ) {
			global $submenu;

//			echo '<pre>';
//			var_dump($submenu);
//			die();

			if ( isset( $submenu[ self::$menu_page_slug ] ) && ! empty( $submenu[ self::$menu_page_slug ] ) ) {
				$menu = $submenu[ self::$menu_page_slug ];
				$new_menu = array();

				foreach ( $menu as $p_key => $item ) {
					foreach ( $this->menu_order as $slug => $order ) {
						if ( false !== array_search( $slug, $item ) ) {
							$new_menu[ $order ] = $item;
						}
					}
				}
				ksort( $new_menu );
				$submenu[ self::$menu_page_slug ] = $new_menu;
			}

			return $menu_order;
		}

		function biz_ui() {
			echo 'rtBiz Dashboard';
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

		function init_attributes() {
			global $rt_biz_attributes;
			$rt_biz_attributes = new Rt_Biz_Attributes();
		}

		function init_settings() {
			global $rt_biz_setttings;
			$rt_biz_setttings = new Rt_Biz_Settings();
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
