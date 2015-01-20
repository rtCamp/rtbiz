<?php
/**
 * Don't load this file directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rt_Importer_Mapper' ) ) {

	/**
	 * Class
	 *
	 * @author dipesh
	 * @since
	 */
	class Rt_Importer_Mapper {

		/**
		 * @var $page_name Page name Which is appear in menubar
		 */
		static $page_name = 'Mapper';

		/**
		 * @var $page_slug - Page slug for gravity importer Page
		 */
		static $page_slug = 'rtbiz-importer-mapper';

		/**
		 * @var $page_cap - Capability for Attributes Admin Page; if not passed, default cap will be 'manage_options'
		 */
		var $page_cap;

		/**
		 * @var $parent_page_slug - Page slug under which the attributes page is to be shown. If null / empty then an individual Menu Page will be added
		 */
		var $parent_page_slug;

		/**
		 * Calling to Set Hooks
		 *
		 * @since
		 */
		public function __construct( $parent_page_slug = false, $page_cap = false ) {

			$this->page_cap = $page_cap;
			$this->parent_page_slug = $parent_page_slug;

			$this->hooks();
		}


		/**
		 * Hook function
		 *
		 * @since
		 */
		function hooks() {
			$pageflag = isset( $this->page_cap )  && $this->page_cap !== false && isset( $this->parent_page_slug )  && $this->parent_page_slug !== false ? true : false;
			if (  $pageflag ) {
				add_action( 'admin_menu', array( $this, 'register_attribute_menu' ) );
			} else {
				add_action( 'rt_configuration_add_tab', array( $this, 'register_tab' ) );
				add_action( 'rt_configuration_tab_ui', array( $this, 'register_tab_ui' ) );
			}
			add_action( 'wp_ajax_rtlib_delete_mapping', array( $this, 'delete_mapping_ajax' ) );
			add_action( 'wp_ajax_rtlib_enable_mapping', array( $this, 'enable_mapping_ajax' ) );

		}

		public function register_tab( $tabs ){
			$tabs[] = array (
				'href' => get_admin_url( null, add_query_arg( array( 'page' => RT_BIZ_Configuration::$page_slug . '&subpage=' .  self::$page_slug ), 'admin.php' ) ),
				'name' => __( ucfirst( self::$page_name ) ),
				'slug' => RT_BIZ_Configuration::$page_slug  . '&subpage=' .  self::$page_slug,
			);
			return $tabs;
		}

		public function register_tab_ui( $current_tab ){
			if( RT_BIZ_Configuration::$page_slug  . '&subpage=' .  self::$page_slug == $current_tab ){
				$this->ui();
			}
		}

		function register_attribute_menu(){
			if ( ! empty( $this->parent_page_slug ) ) {
				add_submenu_page( $this->parent_page_slug, __( ucfirst( self::$page_name ) ), '--- ' . __( ucfirst( self::$page_name ) ), $this->page_cap, self::$page_slug, array( $this, 'ui' ) );
			} else {
				add_menu_page( __( ucfirst( self::$page_name ) ), '--- ' . __( ucfirst( self::$page_name ) ), $this->page_cap, self::$page_slug, array( $this, 'ui' ) );
			}
		}


		/**
		 * delete mapping for gravity form using AJAX
		 *
		 * @since
		 */
		function delete_mapping_ajax() {
			global $rtlib_gravity_fields_mapping_model;
			$response = array();
			if ( ! isset( $_POST['action'] ) || 'rtlib_delete_mapping' != $_POST['action'] || ! isset( $_POST['mapping_id'] ) ) {
				die( 0 );
			}
			$response['status'] = $rtlib_gravity_fields_mapping_model->delete_mapping( array( 'id' => $_POST['mapping_id'] ) );
			echo json_encode( $response );
			die( 0 );
		}

		/**
		 * enable gravity form mapping via ajax call
		 *
		 * @since
		 */
		function enable_mapping_ajax() {
			global $rtlib_gravity_fields_mapping_model;
			$response = array();
			if ( ! isset( $_POST['action'] ) || 'rtlib_enable_mapping' != $_POST['action'] || ! isset( $_POST['mapping_id'] ) ) {
				die( 0 );
			}
			$data               = array( 'enable' => isset( $_POST['mapping_enable'] ) ? $_POST['mapping_enable'] == 'true' ? 'yes' : 'no' : 'no' );
			$where              = array( 'id' => $_POST['mapping_id'] );
			$response['status'] = $rtlib_gravity_fields_mapping_model->update_mapping( $data, $where );
			echo json_encode( $response );
			die( 0 );
		}

		/**
		 * Setting up UI
		 *
		 * @since
		 */
		public function ui() {
			global $rtlib_gravity_fields_mapping_model;
			$args           = array();
			$gravity_fields = $rtlib_gravity_fields_mapping_model->get_all_mappings();
			foreach ( $gravity_fields as $key => $gravity_field ) {
				if ( class_exists( 'RGFormsModel' ) ) {
					$forms = RGFormsModel::get_forms();
				}
				if ( isset( $forms ) && ! empty( $forms ) ) {
					foreach ( $forms as $form ) {
						if ( $form->id == $gravity_field->form_id ) {
							$gravity_fields[ $key ]->form_name = $form->title;
							break;
						}
					}
				}
			}
			$args['gravity_fields'] = $gravity_fields;
			include dirname( __FILE__ ) . '/template/list-importer-mapper.php';
		}


	}

}

