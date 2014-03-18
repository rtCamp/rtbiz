<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Description of class-rt-contacts-roles
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Contacts_Roles' ) ) {
	class Rt_Contacts_Roles {

		public $global_caps = array(
			'manage_contacts' => 'manage_contacts',
		);

		public function __construct() {
			$this->register_roles();

			add_action( 'edit_user_profile', array( $this, 'add_access_profile_fields' ), 1 );
			add_action( 'show_user_profile', array( $this, 'add_access_profile_fields' ), 1 );
			add_action( 'profile_update', array( $this, 'update_access_profile_fields' ), 10, 2 );
			add_filter( 'editable_roles', array( $this, 'remove_wp_contacts_roles' ) );
		}

		function remove_wp_contacts_roles( $roles ) {
			unset( $roles['rt_wp_contacts_manager'] );
			return $roles;
		}

		function register_roles() {

			if ( isset( $_REQUEST['rt_wp_contacts_reset_roles'] ) && ! empty( $_REQUEST['rt_wp_contacts_reset_roles'] ) ) {
				remove_role( 'rt_wp_contacts_manager' );
			}

			$role = get_role( 'rt_wp_contacts_manager' );
			if( empty( $role ) ) {

				global $rt_person, $rt_organization;
				$caps = array( $this->global_caps['manage_contacts'] => true );
				$caps = array_merge( $caps, $rt_person->get_post_type_capabilities());
				$caps = array_merge( $caps, $rt_organization->get_post_type_capabilities());

				add_role( 'rt_wp_contacts_manager', __( 'WordPress Contacts Manager' ), $caps );
			}
		}

		function add_access_profile_fields( $user ) {
			$current_user = new WP_User( get_current_user_id() );
			if ( $current_user->has_cap( 'create_users' ) ) {
				if ( in_array( 'rt_wp_contacts_manager', $user->roles ) ) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				} ?>
				<h3><?php _e( 'WordPress Contacts' ); ?></h3>
				<a href="?rt_wp_contacts_reset_roles=true"><?php _e('Reset Roles'); ?></a>
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="rt_wp_contacts_role"><?php _e('WordPress Contacts Role'); ?></label></th>
							<td>
								<select id="rt_wp_contacts_role" name="rt_wp_contacts_role">
									<option value="no_role"><?php _e( 'No Role' ); ?></option>
									<option value="rt_wp_contacts_manager" <?php echo $selected; ?>><?php _e( 'WordPress Contacts Manager' ); ?></option>
								</select>
							</td>
						</tr>
					</tbody>
				</table>
			<?php }
		}

		function update_access_profile_fields( $user_id, $old_data ) {
			if ( current_user_can( 'create_users' ) ) {
				$user = new WP_User( $user_id );
				if ( isset( $_REQUEST['rt_wp_contacts_role'] ) ) {
					switch( $_REQUEST['rt_wp_contacts_role'] ) {
						case 'rt_wp_contacts_manager':
							if ( ! in_array( 'rt_wp_contacts_manager', $user->roles ) ) {
								$user->add_role( 'rt_wp_contacts_manager' );
							}
							break;
						default:
							if ( in_array( 'rt_wp_contacts_manager', $user->roles ) ) {
								$user->remove_role( 'rt_wp_contacts_manager' );
							}
							break;
					}
				}
			}
		}
	}
}