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

	}

}