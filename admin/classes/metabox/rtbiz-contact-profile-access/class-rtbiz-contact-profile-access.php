<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rtbiz_Contact_Profile_Access' ) ) {

	class Rtbiz_Contact_Profile_Access extends Rtbiz_Metabox {

		public static function ui( $post ) {
			global $rtbiz_contact;
			$modules          = rtbiz_get_modules();
			$permissions      = rtbiz_get_acl_permissions();
			$user_permissions = get_post_meta( $post->ID, 'rtbiz_profile_permissions', true );
			$is_staff_member  = get_post_meta( $post->ID, 'rtbiz_is_staff_member', true );
			if ( empty( $is_staff_member ) && isset( $_REQUEST['rt_contact_group'] ) && 'staff' == $_REQUEST['rt_contact_group'] ) {
				$is_staff_member = 'yes';
			}
			$user = rtbiz_get_wp_user_for_contact( $post->ID );
			if ( empty( $user ) ) {
				_e( 'You can not change Helpdesk role until User is not connected with this customer/staff.', 'rtbiz' );
				return;
			}
			if ( isset( $user[0]->roles ) && in_array( 'administrator', $user[0]->roles ) ) {
				_e( "Admin have full access for all plugins. You can't change it", 'rtbiz' );

				return;
			} ?>
			<div>
				<?php $selected = ( ( isset( $is_staff_member ) && 'yes' == $is_staff_member ) || ( ! empty( $_GET['contact_group'] ) && 'staff' == $_GET['contact_group'] ) ) ? 'Checked="Checked"' : ''; ?>
				<label><input type="checkbox" id="rtbiz_is_staff_member" <?php echo $selected; ?>
				              name="rtbiz_is_staff_member" value="yes"><span
						class="checkbox-title"><?php _e( 'Staff Member ', 'rtbiz' ) ?></span></label>
			</div>
			<?php $class = ( 'Checked="Checked"' == $selected ) ? '' : 'rtbiz-hide'; ?>
			<div id="rtbiz-permission-container" class="<?php echo $class; ?>">
				<table class="form-table">
					<tbody>
					<?php foreach ( $modules as $mkey => $m ) {
						if ( RTBIZ_TEXT_DOMAIN == $mkey && is_plugin_active( 'rtbiz-helpdesk/rtbiz-helpdesk.php' ) ) {
							continue;
						} ?>
						<tr>
							<th><?php echo $m['label']; ?></th>
							<td>
								<select name="rtbiz_profile_permissions[<?php echo $mkey ?>]"><?php
									if ( ! is_plugin_active( 'rtbiz-helpdesk/rtbiz-helpdesk.php' ) ) { ?>
										<option title="<?php _e( 'No Profile Access Override' ); ?>"
										        value=""><?php _e( 'Use Group Access' ); ?></option><?php
									}
									foreach ( $permissions as $pkey => $p ) {
										$selected = ( isset( $user_permissions[ $mkey ] ) && intval( $user_permissions[ $mkey ] ) == $p['value'] && 0 != strlen( $user_permissions[ $mkey ] ) ) ? 'selected="selected"' : ''; ?>
										<option title="<?php echo $p['tooltip']; ?>"
										        value="<?php echo $p['value']; ?>" <?php echo $selected; ?>><?php echo $p['name']; ?></option><?php
									} ?>
								</select>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div> <?php
		}

		public static function save( $contact_id, $post ) {

			global $rtbiz_acl_model;
			$user = rtbiz_get_wp_user_for_contact( $contact_id );
			if ( empty( $user ) ) {
				return;
			}
			$profile_permissions = array();
			if ( ! empty( $_REQUEST['rtbiz_is_staff_member'] ) && 'yes' == $_REQUEST['rtbiz_is_staff_member'] ) {
				if ( isset( $_REQUEST['rtbiz_profile_permissions'] ) && is_array( $_REQUEST['rtbiz_profile_permissions'] ) ) {
					$teams              = wp_get_post_terms( $contact_id, Rtbiz_Teams::$slug );
					$module_permissions = get_site_option( 'rtbiz_acl_module_permissions' );

					$profile_permissions     = $_REQUEST['rtbiz_profile_permissions'];
					$old_profile_permissions = get_post_meta( $contact_id, 'rtbiz_profile_permissions', true );

					//if helpdesk exist rtbiz & helpdesk permission are same and rtbiz acl is hidden
					if ( is_plugin_active( 'rtbiz-helpdesk/rtbiz-helpdesk.php' ) ) {
						$profile_permissions[ RTBIZ_TEXT_DOMAIN ]                   = $profile_permissions[ RTBIZ_HD_TEXT_DOMAIN ];
						$_REQUEST['rtbiz_profile_permissions'][ RTBIZ_TEXT_DOMAIN ] = $profile_permissions[ RTBIZ_TEXT_DOMAIN ];
					}

					foreach ( $profile_permissions as $module_Key => $module_permission ) {

						if ( empty( $old_profile_permissions[ $module_Key ] ) ) {
							$old_profile_permissions[ $module_Key ] = '';
						}
						$old_permission_len = strlen( $old_profile_permissions[ $module_Key ] );
						$isOldPermission    = isset( $old_profile_permissions[ $module_Key ] );
						switch ( $module_permission ) {
							case 0:
								if ( 0 == strlen( $module_permission ) ) {
									// Group Level permission
									$module_permission = ( ! empty( $module_permissions ) ) ? $module_permissions[ $module_Key ] : array();
									//check if old permission is group level
									if ( $isOldPermission && 0 == $old_permission_len ) {

										//get old group and its old permission from custom table
										$where                = array(
											'userid' => $user[0]->ID,
											'module' => $module_Key,
										);
										$old_group_permission = $rtbiz_acl_model->get_acl( $where );

										if ( ! empty( $old_group_permission ) ) {
											$old_group            = array_unique( wp_list_pluck( $old_group_permission, 'groupid' ) );
											$old_group_permission = array_unique( wp_list_pluck( $old_group_permission, 'permission' ) );
										} else {
											$old_group            = array();
											$old_group_permission = array();
										}

										foreach ( $teams as $team ) {
											// find index is current group exist in old group list
											$position = array_search( $team->term_id, $old_group );
											//check if group permission is already exist or not
											if ( strlen( $position ) > 0 ) {
												// check id group permission update or not
												if ( $module_permission[ $team->term_id ] != $old_group_permission[ $position ] ) {
													// update group level permission
													$data  = array(
														'permission' => $module_permission[ $team->term_id ],
													);
													$where = array(
														'userid'  => $user[0]->ID,
														'module'  => $module_Key,
														'groupid' => $team->term_id,
													);
													$rtbiz_acl_model->update_acl( $data, $where );
												}
											} else {
												// add new group level permission
												$data = array(
													'userid'     => $user[0]->ID,
													'module'     => $module_Key,
													'groupid'    => $team->term_id,
													'permission' => $module_permission[ $team->term_id ],
												);
												$rtbiz_acl_model->add_acl( $data );
											}
										}

										//if group remove from customer profile remove access
										if ( ! empty( $teams ) ) {
											$new_group = array_unique( wp_list_pluck( $teams, 'term_id' ) );
										} else {
											$new_group = array();
										}
										$group_removed = array_diff( $old_group, $new_group );
										if ( ! empty( $group_removed ) ) {
											// remove group level acl
											foreach ( $group_removed as $group ) {
												$where = array(
													'userid'  => $user[0]->ID,
													'groupid' => $group,
													'module'  => $module_Key,
												);
												$rtbiz_acl_model->remove_acl( $where );
											}
										}
									} else {
										//check if old permission is profile level permission
										if ( $isOldPermission && 0 != $old_permission_len ) {
											// remove old profile level permission
											$where = array(
												'userid' => $user[0]->ID,
												'module' => $module_Key,
											);
											$rtbiz_acl_model->remove_acl( $where );
										}
										// Add new group level permission
										foreach ( $teams as $team ) {
											$data = array(
												'userid'     => $user[0]->ID,
												'module'     => $module_Key,
												'groupid'    => $team->term_id,
												'permission' => $module_permission[ $team->term_id ],
											);
											$rtbiz_acl_model->add_acl( $data );
										}
									}
								} else {
									// No Role

									//remove all permission
									if ( $isOldPermission ) {
										$where = array(
											'userid' => $user[0]->ID,
											'module' => $module_Key,
										);
										$rtbiz_acl_model->remove_acl( $where );
									}
								}
								break;
							case 10:
							case 20:
							case 30:
								//check if old permission is profile level permission || 0 != $old_permission_len means profile level permission already set
								if ( $isOldPermission && 0 != $old_permission_len && 0 != $old_profile_permissions[ $module_Key ] ) {
									//check if profile level permission changed
									if ( $module_permission != $old_profile_permissions[ $module_Key ] ) {
										$data  = array(
											'permission' => $module_permission,
										);
										$where = array(
											'userid'  => $user[0]->ID,
											'module'  => $module_Key,
											'groupid' => 0,
										);
										$rtbiz_acl_model->update_acl( $data, $where );
									}
								} else {
									// remove old group level permission if already set
									if ( $isOldPermission && 0 == $old_permission_len ) {
										$where = array(
											'userid' => $user[0]->ID,
											'module' => $module_Key,
										);
										$rtbiz_acl_model->remove_acl( $where );
									}
									// Add new profile level permission
									$data = array(
										'userid'     => $user[0]->ID,
										'module'     => $module_Key,
										'groupid'    => 0,
										'permission' => $module_permission,
									);
									$rtbiz_acl_model->add_acl( $data );
								}
								break;
						}
					}
				}
			} else {
				$where = array(
					'userid' => $user[0]->ID,
				);
				$rtbiz_acl_model->remove_acl( $where );
			}
			update_post_meta( $contact_id, 'rtbiz_profile_permissions', $profile_permissions );
			update_post_meta( $contact_id, 'rtbiz_is_staff_member', $_REQUEST['rtbiz_is_staff_member'] );
		}

	}

}
