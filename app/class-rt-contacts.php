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

		public function __construct() {
			$this->check_p2p_dependency();
			$this->init_modules();
			$this->register_organization_person_connection();

			$this->hooks();
		}

		function hooks() {
			if ( is_admin() ) {
				add_action( 'admin_menu', array( $this, 'register_menu' ), 1 );
			}
		}

		function register_menu() {
			add_menu_page( __( 'Contacts' ), __( 'Contacts' ), 'manage_options', 'rt-contacts', array( $this, 'contacts_ui' ), RT_CONTACTS_URL . 'assets/img/contacts-16X16.png', '90.399' );
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
	}

}