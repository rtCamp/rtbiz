<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Description of class-rt-biz-roles
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Biz_Roles' ) ) {
	class Rt_Biz_Roles {

		public static $global_caps = array(
			'manage_rt_biz' => 'manage_rt_biz',
		);

		public function __construct() {
			$this->register_roles();

			add_action( 'edit_user_profile', array( $this, 'add_access_profile_fields' ), 1 );
			add_action( 'show_user_profile', array( $this, 'add_access_profile_fields' ), 1 );
			add_action( 'profile_update', array( $this, 'update_access_profile_fields' ), 10, 2 );
			add_filter( 'editable_roles', array( $this, 'remove_rt_biz_roles' ) );
		}

		function remove_rt_biz_roles( $roles ) {
			unset( $roles['rt_biz_manager'] );
			return $roles;
		}

		function register_roles() {

			if ( isset( $_REQUEST['rt_biz_reset_roles'] ) && ! empty( $_REQUEST['rt_biz_reset_roles'] ) ) {
				remove_role( 'rt_biz_manager' );
			}

			$role = get_role( 'rt_biz_manager' );
			if( empty( $role ) ) {

				global $rt_person, $rt_organization;
				$caps = array( self::$global_caps['manage_rt_biz'] => true );
				$caps = array_merge( $caps, $rt_person->get_post_type_capabilities());
				$caps = array_merge( $caps, $rt_organization->get_post_type_capabilities());

				add_role( 'rt_biz_manager', __( 'rtBiz Manager' ), $caps );
			}
		}

		function add_access_profile_fields( $user ) {
			$current_user = new WP_User( get_current_user_id() );
			if ( $current_user->has_cap( 'create_users' ) ) {
				if ( in_array( 'rt_biz_manager', $user->roles ) ) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				} ?>
				<h3><?php _e( 'rtBiz' ); ?></h3>
				<a href="<?php echo add_query_arg( 'rt_biz_reset_roles', true, $_SERVER['REQUEST_URI'] ); ?>"><?php _e('Reset Roles'); ?></a>
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="rt_biz_role"><?php _e('rtBiz Role'); ?></label></th>
							<td>
								<select id="rt_biz_role" name="rt_biz_role">
									<option value="no_role"><?php _e( 'No Role' ); ?></option>
									<option value="rt_biz_manager" <?php echo $selected; ?>><?php _e( 'rtBiz Manager' ); ?></option>
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
				if ( isset( $_REQUEST['rt_biz_role'] ) ) {
					switch( $_REQUEST['rt_biz_role'] ) {
						case 'rt_biz_manager':
							if ( ! in_array( 'rt_biz_manager', $user->roles ) ) {
								$user->add_role( 'rt_biz_manager' );
							}
							break;
						default:
							if ( in_array( 'rt_biz_manager', $user->roles ) ) {
								$user->remove_role( 'rt_biz_manager' );
							}
							break;
					}
				}
			}
		}
	}
}
