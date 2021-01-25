<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 * @author     Your Name <email@example.com>
 */
if ( ! class_exists( 'Rtbiz_Admin' ) ) {
	class Rtbiz_Admin {

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {

		}

		public function init_admin() {
			global $rtbiz_acl_model,
			       $rtbiz_attributes, $rtbiz_access_control, $rtbiz_p2p,
			       $rtbiz_contact, $rtbiz_company, $rtbiz_team,
			       $rtbiz_settings, $rtbiz_dashboard, $rt_migration,
			       $rtbiz_reports, $rtbiz_help;

			$rtbiz_acl_model = new Rtbiz_ACL_Model();

			$rtbiz_attributes = new Rtbiz_Attributes();

			$rtbiz_access_control = new Rtbiz_Access_Control();

			$rtbiz_p2p = new Rtbiz_P2p();

			$rtbiz_contact = new Rtbiz_Contact();
			$rtbiz_company = new Rtbiz_Company();

			$rtbiz_team = new Rtbiz_Teams();

			$rtbiz_settings = new Rtbiz_Setting();

			$rtbiz_dashboard = new Rtbiz_Dashboard();

			$rt_migration = new Rtbiz_Migration();

			$rtbiz_reports = new Rt_Reports( array( Rtbiz_Dashboard::$page_slug ) );

			global $taxonomy_metadata;
			if ( ! class_exists( 'Rt_Lib_Taxonomy_Metadata\Taxonomy_Metadata' ) ) {
				include_once RTBIZ_PATH . 'lib/rt-products/taxonomy-metadata.php';
			}

			if ( ! is_object( $taxonomy_metadata ) ) {
				$taxonomy_metadata = new Rt_Lib_Taxonomy_Metadata\Taxonomy_Metadata();
				$taxonomy_metadata->activate();
			}

			$rtbiz_help = new Rtbiz_Help();

		}

		public function register_menu() {

			global $rtbiz_dashboard, $rtbiz_access_control;

			$logo_url      = RTBIZ_URL . 'admin/img/biz-16X16.png';
			$menu_label    = __( 'rtBiz' );
			$menu_position = 30;

			$dashboard_screen = add_menu_page( $menu_label, $menu_label, rtbiz_get_access_role_cap( RTBIZ_TEXT_DOMAIN, 'author' ), Rtbiz_Dashboard::$page_slug, array(
				$rtbiz_dashboard,
				'dashboard_ui',
			), $logo_url, $menu_position );

			$rtbiz_dashboard->add_screen_id( $dashboard_screen );
			$rtbiz_dashboard->setup_dashboard();

			add_submenu_page( Rtbiz_Dashboard::$page_slug, __( 'Products' ), __( 'Products' ), rtbiz_get_access_role_cap( RTBIZ_TEXT_DOMAIN, 'editor' ), 'edit-tags.php?taxonomy=' . Rt_Products::$product_slug . '&post_type=' . rtbiz_get_contact_post_type() );

			add_submenu_page( Rtbiz_Dashboard::$page_slug, __( 'Access Control' ), __( 'Access Control' ), rtbiz_get_access_role_cap( RTBIZ_TEXT_DOMAIN, 'admin' ), Rtbiz_Access_Control::$page_slug, array(
				$rtbiz_access_control,
				'acl_settings_ui',
			) );

			add_submenu_page( Rtbiz_Dashboard::$page_slug, __( 'Teams' ), __( '--- Teams' ), rtbiz_get_access_role_cap( RTBIZ_TEXT_DOMAIN, 'editor' ), 'edit-tags.php?taxonomy=' . Rtbiz_Teams::$slug . '&post_type=' . rtbiz_get_contact_post_type() );

			/*$contact_groups_label = apply_filters( 'rtbiz_contact_groups_menu_item_label', __( 'Contact Groups' ) );
			add_submenu_page( Rtbiz_Dashboard::$page_slug, $contact_groups_label, '--- ' . $contact_groups_label, rtbiz_get_access_role_cap( RTBIZ_TEXT_DOMAIN, 'editor' ), 'edit-tags.php?taxonomy=' . Rtbiz_Contact::$user_category_taxonomy . '&post_type=' . rtbiz_get_contact_post_type() );*/
		}

		public function custom_pages_order( $menu_order ) {
			global $submenu, $menu;

			$rtbizMenuOrder = $this->get_custom_menu_order();

			if ( isset( $submenu[ Rtbiz_Dashboard::$page_slug ] ) && ! empty( $submenu[ Rtbiz_Dashboard::$page_slug ] ) ) {
				$module_menu = $submenu[ Rtbiz_Dashboard::$page_slug ];
				unset( $submenu[ Rtbiz_Dashboard::$page_slug ] );

				$new_index = 5;
				foreach ( $rtbizMenuOrder as $item ) {
					foreach ( $module_menu as $p_key => $menu_item ) {
						$out = array_filter( $menu_item, function ( $in ) {
							return true !== $in;
						} );
						if ( in_array( $item, $out ) ) {
							$submenu[ Rtbiz_Dashboard::$page_slug ][ $new_index ] = $menu_item;

							if ( Rtbiz_Dashboard::$page_slug == $item ) {
								$submenu[ Rtbiz_Dashboard::$page_slug ][ $new_index ][0] = __( 'Dashboard', 'rtbiz' );
							}

							unset( $module_menu[ $p_key ] );
							$new_index += 5;
							break;
						}
					}
				}
				foreach ( $module_menu as $p_key => $menu_item ) {
					$submenu[ Rtbiz_Dashboard::$page_slug ][ $new_index ] = $menu_item;
					unset( $module_menu[ $p_key ] );
					$new_index += 5;
				}
			}

			return $menu_order;
		}

		public function get_custom_menu_order() {
			// Set menu order
			$rtbizMenuOrder = array(
				Rtbiz_Dashboard::$page_slug,
				'edit.php?post_type=' . rtbiz_get_contact_post_type(),
				/*'edit-tags.php?taxonomy='.Rtbiz_Contact::$user_category_taxonomy . '&post_type=' . rtbiz_get_contact_post_type(),*/
			);

			$rtbizMenuOrder[] = 'edit.php?post_type=' . rtbiz_get_company_post_type();

			if ( class_exists( 'Rt_Products' ) ) {
				$rtbizMenuOrder[] = 'edit-tags.php?taxonomy=' . Rt_Products::$product_slug . '&post_type=' . rtbiz_get_contact_post_type();
			}

			if ( ! empty( Rtbiz_Access_Control::$page_slug ) ) {
				$rtbizMenuOrder[] = Rtbiz_Access_Control::$page_slug;
			}

			if ( class_exists( 'Rtbiz_Teams' ) ) {
				$rtbizMenuOrder[] = 'edit-tags.php?taxonomy=' . Rtbiz_Teams::$slug . '&post_type=' . rtbiz_get_contact_post_type();
			}

			if ( class_exists( 'Rtbiz_Attributes' ) ) {
				$rtbizMenuOrder[] = Rtbiz_Attributes::$page_slug;
			}

			if ( class_exists( 'Rt_Importer' ) ) {
				$rtbizMenuOrder[] = Rt_Importer::$page_slug;
			}

			if ( class_exists( 'Rt_Importer_Mapper' ) ) {
				$rtbizMenuOrder[] = Rt_Importer_Mapper::$page_slug;
			}

			if ( ! empty( self::$settings_slug ) ) {
				$rtbizMenuOrder[] = self::$settings_slug;
			}

			return $rtbizMenuOrder;
		}

		public function plugin_action_links( $links ) {
			$links['get-started'] = '<a href="' . admin_url( 'admin.php?page=' . Rtbiz_Dashboard::$page_slug ) . '">' . __( 'Get Started', 'rtbiz' ) . '</a>';
			$links['settings']    = '<a href="' . admin_url( 'admin.php?page=' . Rtbiz_Dashboard::$page_slug ) . '">' . __( 'Settings', 'rtbiz' ) . '</a>';

			return $links;
		}

		public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
			if ( RTBIZ_BASE_NAME == $plugin_file ) {
				$plugin_meta[] = '<a href="' . 'http://docs.rtcamp.com/rtbiz/' . '">' . __( 'Documentation', 'rtbiz' ) . '</a>';
				//$plugin_meta[] = '<a href="' . 'https://rtcamp.com/rtbiz/faq' . '">' . __( 'FAQ', 'rtbiz' ) . '</a>';
				$plugin_meta[] = '<a href="' . 'https://rtcamp.com/premium-support/' . '">' . __( 'Support', 'rtbiz' ) . '</a>';
			}

			return $plugin_meta;
		}

		public function database_update() {
			$updateDB = new RT_DB_Update( trailingslashit( RTBIZ_PATH ) . 'rtbiz.php', trailingslashit( RTBIZ_PATH . 'admin/schema/' ) );
			$updateDB->do_upgrade();
		}

		public function rtbiz_welcome() {
			// fail if no activation redirect
			if ( ! get_option( 'rtbiz_activation_redirect', false ) ) {
				return;
			}

			// fail if activating from network, or bulk
			if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
				return;
			}

			wp_safe_redirect( admin_url( 'admin.php?page=' . Rtbiz_Dashboard::$page_slug ) );
			delete_option( 'rtbiz_activation_redirect' );
			exit;
		}

		public function module_register( $modules ) {
			global $rtbiz_contact, $rtbiz_company;
			$menu_label = 'rtBiz';

			$modules[ rtbiz_sanitize_module_key( RTBIZ_TEXT_DOMAIN ) ] = array(
				'label'               => $menu_label,
				'post_types'          => array( $rtbiz_contact->post_type, $rtbiz_company->post_type ),
				'team_support'        => array( $rtbiz_contact->post_type ),
				'product_support'     => array( $rtbiz_contact->post_type, $rtbiz_company->post_type ),
				'setting_option_name' => Rtbiz_Setting::$biz_opt,
				// Use For ACL
				'setting_page_url'    => admin_url( 'admin.php?page=' . Rtbiz_Setting::$page_slug ),
				// for setting page
			);

			return $modules;
		}

		public function init_rtlib() {
			global $rtbiz_mailBox, $rtbiz_importer;

			$this->init_product();

			$rtbiz_mailBox  = new Rt_Mailbox( trailingslashit( RTBIZ_PATH ) . 'rtbiz.php' );
			$rtbiz_importer = new Rt_Importer( null, null, false );

			//

		}

		public function init_product() {
			global $rtbiz_products;
			$editor_cap = rtbiz_get_access_role_cap( RTBIZ_TEXT_DOMAIN, 'editor' );
			$terms_caps = array(
				'manage_terms' => $editor_cap,
				'edit_terms'   => $editor_cap,
				'delete_terms' => $editor_cap,
				'assign_terms' => $editor_cap,
			);

			$settings             = rtbiz_get_product_selection_setting();
			$product_plugin       = ! empty( $settings ) ? $settings : array();
			$to_register_posttype = array();
			foreach ( Rtbiz_Access_Control::$modules as $key => $value ) {
				if ( ! empty( $value['product_support'] ) ) {
					$to_register_posttype = array_merge( $to_register_posttype, $value['product_support'] );
				}
			}

			$rtbiz_products = new Rt_Products( $product_plugin, $terms_caps, $to_register_posttype );
		}

		/**
		 * Register the stylesheets for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles() {

			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Plugin_Name_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Plugin_Name_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */

			wp_enqueue_style( RTBIZ_TEXT_DOMAIN . 'admin-css', RTBIZ_URL . 'admin/css/biz-admin.min.css', array(), RTBIZ_VERSION, 'all' );

		}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts() {

			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Plugin_Name_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Plugin_Name_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */

			global $post;

			// Taxonomy menu hack for rtBiz
			if ( isset( $_REQUEST['taxonomy'] ) && isset( $_REQUEST['post_type'] ) && in_array( $_REQUEST['post_type'], array(
					rtbiz_get_contact_post_type(),
					rtbiz_get_company_post_type()
				) )
			) {
				wp_localize_script( RTBIZ_TEXT_DOMAIN . 'admin-js', 'rtbiz_dashboard_screen', Rtbiz_Dashboard::$page_slug );
				wp_localize_script( RTBIZ_TEXT_DOMAIN . 'admin-js', 'rtbiz_menu_url', admin_url( 'edit-tags.php?taxonomy=' . $_REQUEST['taxonomy'] . '&post_type=' . $_REQUEST['post_type'] ) );
			}

			wp_enqueue_script( 'jquery-ui-autocomplete', '', array(
				'jquery-ui-widget',
				'jquery-ui-position'
			), '1.9.2', true );
			wp_enqueue_script( RTBIZ_TEXT_DOMAIN . 'admin-js', RTBIZ_URL . 'admin/js/admin.js', array( 'jquery' ), RTBIZ_VERSION, true );
			wp_localize_script( RTBIZ_TEXT_DOMAIN . 'admin-js', 'rtbiz_ajax_url_admin', admin_url( 'admin-ajax.php' ) );

		}

	}
}
