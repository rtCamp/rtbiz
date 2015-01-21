<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RT_BIZ_Configuration' ) ) {

	class RT_BIZ_Configuration {

		/**
		 * @var $page_name Page name Which is appear in menubar
		 */
		static $page_name = 'Configuration';

		/**
		 * @var $page_slug - Page slug for gravity importer Page
		 */
		static $page_slug = 'rtbiz-configuration';

		/**
		 * @var $parent_page_slug - Page slug under which the attributes page is to be shown. If null / empty then an individual Menu Page will be added
		 */
		var $parent_page_slug;

		/**
		 * @var $page_cap - Capability for Attributes Admin Page; if not passed, default cap will be 'manage_options'
		 */
		var $page_cap;

		/**
		 * @param $args
		 */
		public function __construct( $args ) {
			$this->parent_page_slug      = $args['parent_slug'];
			$this->page_cap              = $args['page_capability'];

			$this->hook();

		}

		public  function hook(){
			add_action( 'admin_menu', array( $this, 'register_configuration_menu' ) );
			//add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * Rgister the
		 */
		public function register_configuration_menu(  ) {
			if ( ! empty( $this->parent_page_slug ) ) {
				add_submenu_page( $this->parent_page_slug, __( ucfirst( self::$page_name ) ), __( ucfirst( self::$page_name ) ), $this->page_cap, self::$page_slug, array( $this, 'ui' ) );
			} else {
				add_menu_page( __( ucfirst( self::$page_name ) ), __( ucfirst( self::$page_name ) ), $this->page_cap, self::$page_slug, array( $this, 'ui' ) );
			}
		}

		public function get_current_tab(){
			return isset( $_REQUEST['page'] ) ? ( isset( $_REQUEST['subpage'] )? $_REQUEST['page'] .'&subpage='.$_REQUEST['subpage']: self::$page_slug  ) : self::$page_slug ;
		}

		public function importer_tab(){
			// Declare local variables
			$tabs_html    = '';
			$idle_class   = 'nav-tab';
			$active_class = 'nav-tab nav-tab-active';

			// Setup core admin tabs
			$tabs = array(
				array(
					'href' => get_admin_url( null, add_query_arg( array( 'page' => self::$page_slug . '&subpage=' .  Rt_Mailbox::$page_slug ), 'admin.php' ) ),
					'name' => __( ucfirst( Rt_Mailbox::$page_name ) ),
					'slug' => self::$page_slug  . '&subpage=' .  Rt_Mailbox::$page_slug,
				),
				array(
					'href' => get_admin_url( null, add_query_arg( array( 'page' => self::$page_slug . '&subpage=' .  Rt_Importer::$page_slug ), 'admin.php' ) ),
					'name' => __( ucfirst( Rt_Importer::$page_name ) ),
					'slug' => self::$page_slug  . '&subpage=' .  Rt_Importer::$page_slug,
				),
				array (
					'href' => get_admin_url( null, add_query_arg( array( 'page' => self::$page_slug . '&subpage=' .  Rt_Importer_Mapper::$page_slug ), 'admin.php' ) ),
					'name' => __( ucfirst( Rt_Importer_Mapper::$page_name ) ),
					'slug' => self::$page_slug  . '&subpage=' .  Rt_Importer_Mapper::$page_slug,
				),
				array (
					'href' => get_admin_url( null, add_query_arg( array( 'page' => self::$page_slug . '&subpage=' .  Rt_Biz_Attributes::$attributes_page_slug ), 'admin.php' ) ),
					'name' => __( 'Attributes' ),
					'slug' => self::$page_slug  . '&subpage=' .  Rt_Biz_Attributes::$attributes_page_slug,
				),
			);
			$filterd_tab = apply_filters( 'rt_configuration_add_tab', $tabs );
			if ( ! empty( $filterd_tab ) ){
				$tabs_html .= '<div class="nav-tab-wrapper" >';
				// Loop through tabs and build navigation
				foreach ( array_values( $filterd_tab ) as $tab_data ) {
					$is_current = (bool) ( $tab_data['slug'] == $this->get_current_tab() );
					$tab_class  = $is_current ? $active_class : $idle_class;

					if ( isset( $tab_data['class'] ) && is_array( $tab_data['class'] ) ){
						$tab_class .= ' ' . implode( ' ', $tab_data['class'] );
					}

					$tabs_html .= '<a href="' . $tab_data['href'] . '" class="' . $tab_class . '">' . $tab_data['name'] . '</a>';
				}
				$tabs_html .= '</div>';
			}

			// Output the tabs

			echo $tabs_html;
		}


		public function ui(){
			global $rt_importer, $rtlib_importer_mapper, $rt_MailBox, $rt_biz_rt_attributes;
			?>
			<div class="wrap">
				<h2>Configuration</h2>
				<?php $this->importer_tab(); ?>
			</div>
			<?php
			if ( self::$page_slug  . '&subpage=' .  Rt_Importer::$page_slug == $this->get_current_tab() ) {
				$rt_importer->ui();
			} elseif ( self::$page_slug  . '&subpage=' .  Rt_Importer_Mapper::$page_slug == $this->get_current_tab() ) {
				$rtlib_importer_mapper->ui();
			} elseif ( self::$page_slug  . '&subpage=' .  Rt_Mailbox::$page_slug == $this->get_current_tab() ) {
				$rt_MailBox->render_mailbox_setting_page();
			} elseif ( self::$page_slug  . '&subpage=' .  Rt_Biz_Attributes::$attributes_page_slug == $this->get_current_tab() ) {
				$rt_biz_rt_attributes->render_attributes_page();
			}

			do_action( 'rt_configuration_tab_ui', $this->get_current_tab() );
		}

		public function enqueue_scripts(){

		}

	}
}