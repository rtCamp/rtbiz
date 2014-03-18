<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Description of class-rt-contacts
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Contacts' ) ) {

	class Rt_Contacts {

		public $menu_page_slug = 'rt-contacts';

		public function __construct() {
			$this->check_p2p_dependency();
			$this->init_modules();
			$this->register_organization_person_connection();
			$this->init_roles();
			$this->hooks();
		}

		function hooks() {
			if ( is_admin() ) {
				add_action( 'admin_menu', array( $this, 'register_menu' ), 1 );
			}
		}

		function register_menu() {
			global $rt_contacts_roles;
			add_menu_page( __( 'Contacts' ), __( 'Contacts' ), $rt_contacts_roles->global_caps['manage_contacts'], $this->menu_page_slug, array( $this, 'contacts_ui' ), RT_CONTACTS_URL . 'assets/img/contacts-16X16.png', '90.399' );
		}

		function contacts_ui() {
			echo 'Contacts Dashboard';
		}

		function check_p2p_dependency() {
			if ( ! class_exists( 'P2P_Box_Factory' ) ) {
				add_action( 'admin_notices', array( $this, 'p2p_admin_notice' ) );
			}
		}

		function p2p_admin_notice() { ?>
			<div class="updated">
				<p><?php _e( sprintf( 'WordPress Contacts : It seems that Posts 2 Posts plugin is not installed or activated. Please %s / %s it.', '<a href="'.admin_url( 'plugin-install.php?tab=search&s=posts-2-posts' ).'">'.__( 'install' ).'</a>', '<a href="'.admin_url( 'plugins.php' ).'">'.__( 'activate' ).'</a>' ) ); ?></p>
			</div>
		<?php }

		function init_modules() {
			global $rt_person, $rt_organization;
			$rt_person = new Rt_Person();
			$rt_organization = new Rt_Organization();
		}

		function init_roles() {
			global $rt_contacts_roles;
			$rt_contacts_roles = new Rt_Contacts_Roles();
		}

		function register_organization_person_connection() {
			add_action( 'p2p_init', array( $this, 'organization_person_connection' ) );
		}

		function organization_person_connection() {
			global $rt_organization, $rt_person;
			if ( function_exists( 'p2p_register_connection_type' ) ) {
				p2p_register_connection_type( array(
					'name' => $rt_organization->post_type.'_to_'.$rt_person->post_type,
					'from' => $rt_organization->post_type,
					'to' => $rt_person->post_type,
				) );
			}
		}

		function connect_organization_to_person( $from = '', $to = '' ) {
			global $rt_organization, $rt_person;
			if ( function_exists( 'p2p_create_connection' ) && function_exists( 'p2p_connection_exists' ) ) {
				if ( ! p2p_connection_exists( $rt_organization->post_type.'_to_'.$rt_person->post_type, array( 'from' => $from, 'to' => $to ) ) ) {
					p2p_create_connection( rtcrm_post_type_name( 'account' ).'_to_'.rtcrm_post_type_name( 'contact' ), array( 'from' => $from, 'to' => $to ) );
				}
			}
		}

		function get_organization_to_person_connection( $connected_items ) {
			global $rt_organization, $rt_person;
			return get_posts(
				array(
					'connected_type' => $rt_organization->post_type.'_to_'.$rt_person->post_type,
					'connected_items' => $connected_items,
					'nopaging' => true,
					'suppress_filters' => false,
				)
			);
		}
	}
}