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
		 * @var $pageflag - flag for page :  true for page | false for subpage
		 */
		var $pageflag;

		/**
		 * @var $parent_page_slug - Page slug under which the attributes page is to be shown. If null / empty then an individual Menu Page will be added
		 */
		var $parent_page_slug;

		/**
		 * Calling to Set Hooks
		 *
		 * @since
		 */
		public function __construct( $parent_page_slug = false, $page_cap = false, $pageflag = true ) {
			$this->pageflag         = $pageflag;
			$this->parent_page_slug = $parent_page_slug;
			if ( $this->pageflag ) {
				$this->page_cap = $page_cap;
			}
			$this->hooks();
		}


		/**
		 * Hook function
		 *
		 * @since
		 */
		function hooks() {
			if ( $this->pageflag ) {
				add_action( 'admin_menu', array( $this, 'register_attribute_menu' ) );
			}
			add_action( 'wp_ajax_rtlib_delete_mapping', array( $this, 'delete_mapping_ajax' ) );
			add_action( 'wp_ajax_rtlib_enable_mapping', array( $this, 'enable_mapping_ajax' ) );

		}

		function register_attribute_menu() {
			if ( ! empty( $this->parent_page_slug ) ) {
				add_submenu_page( $this->parent_page_slug, __( ucfirst( self::$page_name ) ), '--- ' . __( ucfirst( self::$page_name ) ), $this->page_cap, self::$page_slug, array(
					$this,
					'ui'
				) );
			} else {
				add_menu_page( __( ucfirst( self::$page_name ) ), '--- ' . __( ucfirst( self::$page_name ) ), $this->page_cap, self::$page_slug, array(
					$this,
					'ui'
				) );
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
			$data               = array( 'enable' => isset( $_POST['mapping_enable'] ) ? 'true' == $_POST['mapping_enable'] ? 'yes' : 'no' : 'no' );
			$where              = array( 'id' => $_POST['mapping_id'] );
			$response['status'] = $rtlib_gravity_fields_mapping_model->update_mapping( $data, $where );
			echo json_encode( $response );
			die( 0 );
		}

		public function get_current_tab() {
			return isset( $_REQUEST['page'] ) ? ( isset( $_REQUEST['type'] ) ? $this->base_url . '&type=' . $_REQUEST['type'] : $this->base_url . '&type=gravity' ) : $this->base_url . '&type=gravity';
		}

		public function importer_tab() {
			// Declare local variables
			$tabs_html = '';

			// Setup core admin tabs
			$tabs        = array(
				/*array(
					'href' => $this->base_url . '&type=CSV',
					'name' => __( 'CSV' ),
					'slug' => self::$page_slug . '&type=CSV' ,
				),*/
				array(
					'href' => $this->base_url . '&type=gravity',
					'name' => __( 'Gravity' ),
					'slug' => $this->base_url . '&type=gravity',
				),
			);
			$filterd_tab = apply_filters( 'rt_importer_add_tab', $tabs );

			if ( ! empty( $filterd_tab ) ) {
				if ( $this->pageflag ) {

					$idle_class   = 'nav-tab';
					$active_class = 'nav-tab nav-tab-active';

					$tabs_html .= '<div class="nav-tab-wrapper" >';
					// Loop through tabs and build navigation
					foreach ( array_values( $filterd_tab ) as $tab_data ) {
						$is_current = (bool) ( $tab_data['slug'] == $this->get_current_tab() );
						$tab_class  = $is_current ? $active_class : $idle_class;

						if ( isset( $tab_data['class'] ) && is_array( $tab_data['class'] ) ) {
							$tab_class .= ' ' . implode( ' ', $tab_data['class'] );
						}

						$tabs_html .= '<a href="' . $tab_data['href'] . '" class="' . $tab_class . '">' . $tab_data['name'] . '</a>';
					}
					$tabs_html .= '</div>';
				} else {
					$idle_class   = '';
					$active_class = 'current';
					$tabs_html .= '<div class="sub-nav-tab-wrapper"><ul class="subsubsub">';
					foreach ( array_values( $filterd_tab ) as $i => $tab_data ) {
						$is_current = (bool) ( $tab_data['slug'] == $this->get_current_tab() );
						$tab_class  = $is_current ? $active_class : $idle_class;

						if ( isset( $tab_data['class'] ) && is_array( $tab_data['class'] ) ) {
							$tab_class .= ' ' . implode( ' ', $tab_data['class'] );
						}
						$separator = ( count( $filterd_tab ) - 1 ) != $i ? ' | ' : '';
						$tabs_html .= '<li class="' . $tab_data['name'] . '"><a href="' . $tab_data['href'] . '" class="' . $tab_class . '">' . $tab_data['name'] . '</a>' . $separator . '</li>';
					}
					$tabs_html .= '</ul></div>';
				}
			}

			// Output the tabs
			echo $tabs_html;
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

			$title_ele = $this->pageflag ? 'h2' : 'h3'; ?>
			<div class="wrap">
			<?php if ( $this->pageflag ) {
				echo '<h2>' . __( 'Importer Mapping List' ) . '</h2>';
			}

			//$this->importer_tab();

			include dirname( __FILE__ ) . '/template/list-importer-mapper.php';

			?></div><?php
		}


	}

}

