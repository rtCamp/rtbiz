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
if ( ! class_exists( 'Rt_Biz_Admin' ) ) {
	class Rt_Biz_Admin {

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $plugin_name The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $version The current version of this plugin.
		 */
		private $version;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 *
		 * @param      string $plugin_name The name of this plugin.
		 * @param      string $version The version of this plugin.
		 */
		public function __construct( $plugin_name, $version ) {

			$this->plugin_name = $plugin_name;
			$this->version     = $version;

		}

		public function rt_biz_init_admin() {
			global  $rt_biz_acl_model,
					$rt_biz_attributes, $rt_biz_access_control, $rt_biz_p2p,
			        $rt_biz_contact, $rt_biz_company, $rt_biz_team,
			        $rt_biz_setttings, $rt_biz_dashboard, $rt_migration,
			        $rt_biz_reports;

			$rt_biz_acl_model = new RT_Biz_ACL_Model();

			$rt_biz_attributes = new Rt_Biz_Attributes();

			$rt_biz_access_control = new Rt_Biz_Access_Control();

			$rt_biz_p2p = new Rt_Biz_P2p();

			$rt_biz_contact = new Rt_Biz_Contact();
			$rt_biz_company = new Rt_Biz_Company();

			$rt_biz_team = new RT_Biz_Teams();

			$rt_biz_setttings = new Rt_Biz_Setting();

			$rt_biz_dashboard = new Rt_Biz_Dashboard();

			$rt_migration = new Rt_Biz_Migration();

			$rt_biz_reports = new Rt_Reports( array( Rt_Biz_Dashboard::$page_slug ) );

			global $taxonomy_metadata;
			if ( ! class_exists( 'Rt_Lib_Taxonomy_Metadata\Taxonomy_Metadata' ) ) {
				include_once RT_BIZ_PATH . 'lib/rt-offerings/taxonomy-metadata.php';
			}

			if ( ! is_object( $taxonomy_metadata ) ) {
				$taxonomy_metadata = new Rt_Lib_Taxonomy_Metadata\Taxonomy_Metadata();
				$taxonomy_metadata->activate();
			}

		}

		public function rt_biz_register_menu() {

			global $rt_biz_dashboard, $rt_biz_access_control;

			$logo_url      = RT_BIZ_URL . 'admin/img/biz-16X16.png';
			$menu_label    = __( 'rtBiz' );
			$menu_position = 30;

			$dashboard_screen = add_menu_page( $menu_label, $menu_label, rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'author' ), Rt_Biz_Dashboard::$page_slug, array(
				$rt_biz_dashboard,
				'rt_biz_dashboard_ui',
			), $logo_url, $menu_position );

			$rt_biz_dashboard->rt_biz_add_screen_id( $dashboard_screen );
			$rt_biz_dashboard->rt_biz_setup_dashboard();

			add_submenu_page( Rt_Biz_Dashboard::$page_slug, __( 'Offerings' ), __( 'Offerings' ), rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'editor' ), 'edit-tags.php?taxonomy=' . Rt_Offerings::$offering_slug . '&post_type=' . rt_biz_get_contact_post_type() );

			add_submenu_page( Rt_Biz_Dashboard::$page_slug, __( 'Access Control' ), __( 'Access Control' ), rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'admin' ), Rt_Biz_Access_Control::$page_slug, array(
				$rt_biz_access_control,
				'rt_biz_acl_settings_ui',
			) );

			add_submenu_page( Rt_Biz_Dashboard::$page_slug, __( 'Teams' ), __( '--- Teams' ), rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'editor' ), 'edit-tags.php?taxonomy=' . RT_Biz_Teams::$slug . '&post_type=' . rt_biz_get_contact_post_type() );

			/*$contact_groups_label = apply_filters( 'rtbiz_contact_groups_menu_item_label', __( 'Contact Groups' ) );
			add_submenu_page( Rt_Biz_Dashboard::$page_slug, $contact_groups_label, '--- ' . $contact_groups_label, rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'editor' ), 'edit-tags.php?taxonomy=' . Rt_Biz_Contact::$user_category_taxonomy . '&post_type=' . rt_biz_get_contact_post_type() );*/
		}

		public function rt_biz_custom_pages_order( $menu_order ) {
			global $submenu, $menu;

			$rtbizMenuOrder = $this->rt_biz_get_custom_menu_order();

			if ( isset( $submenu[ Rt_Biz_Dashboard::$page_slug ] ) && ! empty( $submenu[ Rt_Biz_Dashboard::$page_slug ] ) ) {
				$module_menu = $submenu[ Rt_Biz_Dashboard::$page_slug ];
				unset( $submenu[ Rt_Biz_Dashboard::$page_slug ] );

				$new_index = 5;
				foreach ( $rtbizMenuOrder as $item ) {
					foreach ( $module_menu as $p_key => $menu_item ) {
						$out = array_filter( $menu_item, function ( $in ) {
							return true !== $in;
						} );
						if ( in_array( $item, $out ) ) {
							$submenu[ Rt_Biz_Dashboard::$page_slug ][ $new_index ] = $menu_item;

							if ( Rt_Biz_Dashboard::$page_slug == $item ) {
								$submenu[ Rt_Biz_Dashboard::$page_slug ][ $new_index ][0] = __( 'Dashboard', RT_BIZ_TEXT_DOMAIN );
							}

							unset( $module_menu[ $p_key ] );
							$new_index += 5;
							break;
						}
					}
				}
				foreach ( $module_menu as $p_key => $menu_item ) {
					$submenu[ Rt_Biz_Dashboard::$page_slug ][ $new_index ] = $menu_item;
					unset( $module_menu[ $p_key ] );
					$new_index += 5;
				}
			}

			return $menu_order;
		}

		public function rt_biz_get_custom_menu_order() {
			// Set menu order
			$rtbizMenuOrder = array(
				Rt_Biz_Dashboard::$page_slug,
				'edit.php?post_type=' . rt_biz_get_contact_post_type(),
				/*'edit-tags.php?taxonomy='.Rt_Biz_Contact::$user_category_taxonomy . '&post_type=' . rt_biz_get_contact_post_type(),*/
			);

			$rtbizMenuOrder[] = 'edit.php?post_type=' . rt_biz_get_company_post_type();

			if ( class_exists( 'Rt_Offerings' ) ) {
				$rtbizMenuOrder[] = 'edit-tags.php?taxonomy=' . Rt_Offerings::$offering_slug . '&post_type=' . rt_biz_get_contact_post_type();
			}

			if ( ! empty( Rt_Biz_Access_Control::$page_slug ) ) {
				$rtbizMenuOrder[] = Rt_Biz_Access_Control::$page_slug;
			}

			if ( class_exists( 'RT_Biz_Teams' ) ) {
				$rtbizMenuOrder[] = 'edit-tags.php?taxonomy=' . RT_Biz_Teams::$slug . '&post_type=' . rt_biz_get_contact_post_type();
			}

			if ( class_exists( 'Rt_Biz_Attributes' ) ) {
				$rtbizMenuOrder[] = Rt_Biz_Attributes::$page_slug;
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

		public function rt_biz_plugin_action_links( $links ) {
			$links['get-started'] = '<a href="' . admin_url( 'admin.php?page=' . Rt_Biz_Dashboard::$page_slug ) . '">' . __( 'Get Started', RT_BIZ_TEXT_DOMAIN ) . '</a>';
			$links['settings'] = '<a href="' . admin_url( 'admin.php?page=' . Rt_Biz_Dashboard::$page_slug ) . '">' . __( 'Settings', RT_BIZ_TEXT_DOMAIN ) . '</a>';
			return $links;
		}

		public function rt_biz_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
			if ( RT_BIZ_BASE_NAME == $plugin_file ) {
				$plugin_meta[] = '<a href="' . 'http://docs.rtcamp.com/rtbiz/' . '">' . __( 'Documentation', RT_BIZ_TEXT_DOMAIN ) . '</a>';
				//$plugin_meta[] = '<a href="' . 'https://rtcamp.com/rtbiz/faq' . '">' . __( 'FAQ', RT_BIZ_TEXT_DOMAIN ) . '</a>';
				$plugin_meta[] = '<a href="' . 'https://rtcamp.com/premium-support/' . '">' . __( 'Support', RT_BIZ_TEXT_DOMAIN ) . '</a>';
			}
			return $plugin_meta;
		}

		public function rt_biz_database_update() {
			$updateDB = new RT_DB_Update( trailingslashit( RT_BIZ_PATH ) . 'rtbiz.php', trailingslashit( RT_BIZ_PATH . 'admin/schema/' ) );
			$updateDB->do_upgrade();
		}

		public function rt_biz_welcome() {
			// fail if no activation redirect
			if ( ! get_option( '_rt_biz_activation_redirect', false ) ) {
				return;
			}

			// fail if activating from network, or bulk
			if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
				return;
			}

			wp_safe_redirect( admin_url( 'admin.php?page=' . Rt_Biz_Dashboard::$page_slug ) );
			delete_option( '_rt_biz_activation_redirect' );
			exit;
		}

		public function rt_biz_module_register( $modules ) {
			global $rt_biz_contact, $rt_biz_company;
			$menu_label = 'rtBiz';

			$modules[ rt_biz_sanitize_module_key( RT_BIZ_TEXT_DOMAIN ) ] = array(
				'label'               => $menu_label,
				'post_types'          => array( $rt_biz_contact->post_type, $rt_biz_company->post_type ),
				'team_support'  => array( $rt_biz_contact->post_type ),
				'offering_support'    => array( $rt_biz_contact->post_type, $rt_biz_company->post_type ),
				'setting_option_name' => Rt_Biz_Setting::$biz_opt,
				// Use For ACL
				'setting_page_url'    => admin_url( 'admin.php?page=' . Rt_Biz_Setting::$page_slug ),
				// for setting page
			);

			return $modules;
		}

		public function rt_biz_init_rtlib() {
			global $rt_MailBox, $rt_importer;

			$this->rt_biz_init_offering();

			$rt_MailBox  = new Rt_Mailbox( trailingslashit( RT_BIZ_PATH ) . 'rtbiz.php' );
			$rt_importer = new Rt_Importer( null, null, false );

			//

		}

		public function rt_biz_init_offering() {
			global $rtbiz_offerings;
			$editor_cap = rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'editor' );
			$terms_caps = array(
				'manage_terms' => $editor_cap,
				'edit_terms'   => $editor_cap,
				'delete_terms' => $editor_cap,
				'assign_terms' => $editor_cap,
			);

			$settings             = rt_biz_get_offering_selection_setting();
			$offering_plugin      = ! empty( $settings ) ? $settings : array();
			$to_register_posttype = array();
			foreach ( Rt_Biz_Access_Control::$modules as $key => $value ) {
				if ( ! empty( $value['offering_support'] ) ) {
					$to_register_posttype = array_merge( $to_register_posttype, $value['offering_support'] );
				}
			}

			$rtbiz_offerings = new Rt_Offerings( $offering_plugin, $terms_caps, $to_register_posttype );
		}

		/**
		 * Register the stylesheets for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function rt_biz_enqueue_styles() {

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

			wp_enqueue_style( $this->plugin_name . 'admin-css', RT_BIZ_URL . 'admin/css/biz-admin.min.css', array(), $this->version, 'all' );

		}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function rt_biz_enqueue_scripts() {

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
			if ( isset( $_REQUEST['taxonomy'] ) && isset( $_REQUEST['post_type'] ) && in_array( $_REQUEST['post_type'], array( rt_biz_get_contact_post_type(), rt_biz_get_company_post_type() ) ) ) {
				wp_localize_script( $this->plugin_name . 'admin-js', 'rt_biz_dashboard_screen', Rt_Biz_Dashboard::$page_slug );
				wp_localize_script( $this->plugin_name . 'admin-js', 'rt_biz_menu_url', admin_url( 'edit-tags.php?taxonomy=' . $_REQUEST['taxonomy'] . '&post_type=' . $_REQUEST['post_type'] ) );
			}

			wp_enqueue_script( 'jquery-ui-autocomplete', '', array( 'jquery-ui-widget', 'jquery-ui-position' ), '1.9.2', true );
			wp_enqueue_script( $this->plugin_name . 'admin-js', RT_BIZ_URL . 'admin/js/admin.js', array( 'jquery' ), $this->version, true );
			wp_localize_script( $this->plugin_name . 'admin-js', 'rtbiz_ajax_url_admin', admin_url( 'admin-ajax.php' ) );

		}

	}
}
