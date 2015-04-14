<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-rt-access-control
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Access_Control' ) ) {

	/**
	 * Class Rt_Access_Control
	 *
	 * This class works as the base for all the permissions, user access for rtBiz family plugins.
	 * All the plugins including rtBiz will register with this class for the Access Control that it requires
	 * & from there Rt_Access_Controll will take it over.
	 *
	 * It will map a matrix for permissions for Plugin Modules => User Groups (Departments) and from there user access
	 * can be set as per requirement.
	 */
	class Rt_Access_Control {

		/**
		 * @var - These are more of the permission roles for the access control.
		 */
		public static $permissions;

		/**
		 * @var - Registered Plugin Modules for Rt_Access_Control
		 */
		public static $modules = array();

		/**
		 *
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'init_acl' ), 15 );
			add_filter( 'rtbiz_department_support', array( $this, 'add_department_support' ) );

			add_filter( 'user_has_cap', array( $this, 'filter_caps' ), 900, 4 );

		}

		function filter_caps( $all_caps, $required_caps, $args, $user ) {
			global $rt_biz_acl_model;

			$rt_biz_caps = array();
			// $m - module
			// $mkey - module_key
			if ( ! empty( self::$modules ) ) {
				foreach ( self::$modules as $mkey => $m ) {
					// $p - permission
					// $pkey - permission_key
					foreach ( self::$permissions as $pkey => $p ) {
						$rt_biz_caps[] = $mkey . '_' . $pkey;
					}
					$post_types = ( isset( $m['post_types'] ) && is_array( $m['post_types'] ) ) ? $m['post_types'] : array();
					// $pt - post_type
					foreach ( $post_types as $pt ) {
						$post_caps   = self::get_admin_post_caps( $pt );
						$rt_biz_caps = array_merge( $rt_biz_caps, array_keys( $post_caps ) );
					}
				}
			}

			if ( isset( $all_caps['administrator'] ) && $all_caps['administrator'] ) {
				foreach ( $required_caps as $cap ) {
					if ( ! in_array( $cap, $rt_biz_caps ) ) {
						continue;
					}
					$all_caps[ $cap ] = true;
				}
				return $all_caps;
			}

			foreach ( $required_caps as $cap ) {
				if ( ! in_array( $cap, $rt_biz_caps ) ) {
					continue;
				}

				$module_permissions = array();
				$sql = "select module, max( permission ) as permission from $rt_biz_acl_model->table_name where userid = $user->ID group by module";
				$permissions = $rt_biz_acl_model->get_result_by_query( $sql );
				foreach ( $permissions as $permission ) {
					$module_permissions[ $permission->module ] = $permission->permission;
				}

				$valid_caps = array();
				foreach ( $module_permissions as $mkey => $valid_role_value ){
					$valid_role_key = self::get_role_key( $valid_role_value );
					// rtbiz role capability
					foreach ( self::$permissions as $ap ) {
						if ( $ap['value'] > $valid_role_value ) {
							continue;
						}
						$role_cap = self::get_capability_from_access_role( $mkey, self::get_role_key( $ap['value'] ) );
						if ( empty( $role_cap ) ) {
							continue;
						}
						$valid_caps[ $role_cap ] = true;
					}

					// rtbiz post type capability
					$post_types = ( isset( self::$modules[ $mkey ]['post_types'] ) && is_array( self::$modules[ $mkey ]['post_types'] ) ) ? self::$modules[ $mkey ]['post_types'] : array();
					// $pt - post_type
					foreach ( $post_types as $pt ) {
						$post_caps = call_user_func( array( 'Rt_Access_Control', 'get_'.$valid_role_key.'_post_caps' ), $pt );
						if ( ! empty( $post_caps ) && is_array( $post_caps ) ) {
							$valid_caps = array_merge( $valid_caps, $post_caps );
						}
					}
				}
				$all_caps = array_merge( $all_caps, $valid_caps );
			}
			return $all_caps;
		}

		/**
		 *  Initilize ACL on plugins_loaded with 15 priority. So that before this gets executed;
		 *  other addon plugins get chance to hook into it and register themselved
		 */
		function init_acl() {
			/**
			 *
			 *  Filter for other addons to register.
			 *  Array Structure is array( 'module_slug' => array() )
			 *
			 *  $biz_module = array(
			 *		RT_BIZ_TEXT_DOMAIN => array(
			 *			'label' => __( 'rtBiz' ),                                                                   // module label
			 *			'post_types' => array( 'post', 'page', 'rt_contact', 'rt_lead', ),                          // array of post type for given module
			 *          'department_support' => array( 'rt_contact', 'rt_ticket' ),                                 // array of post types for which department taxonomy is to be registered
			 *          'offering_support' => array( 'rt_contact', 'rt_ticket' ),                                   // array of post types for which offerings taxonomy is to be registered
			 *          'setting_option_name' => 'redux_biz_settings',                                              // redux opions Name, if used
			 *          'setting_page_url' => 'http://rtbiz.rtcamp.net/wp-admin/admin.php?page=rt-biz-settings',    // Settings Page URL, if Mailbox module is used.
			 *		),
			 *  );
			 */
			self::$modules = apply_filters( 'rt_biz_modules', array() );

			self::$permissions = apply_filters( 'rt_biz_permissions', array(
				'no_access' => array(
					'value' => 0,
					'name' => __( 'No Role' ),
					'tooltip' => __( 'No Access' ),
				),
				'author' => array(
					'value' => 10,
					'name' => __( 'Author' ),
					'tooltip' => __( 'Read/Write (Self)' ),
				),
				'editor' => array(
					'value' => 20,
					'name' => __( 'Editor' ),
					'tooltip' => __( 'Read/Write (Everything)' ),
				),
				'admin' => array(
					'value' => 30,
					'name' => __( 'Admin' ),
					'tooltip' => __( 'Read/Write (Everything) + Settings' ),
				),
			) );
		}

		/**
		 *
		 * This method return the capability name text if you pass the module_slug & role
		 * It will return proper cap if passed module_key is registered with Rt_Access_Control
		 * & passed role is valid.
		 *
		 * Other wise it will return empty string.
		 *
		 * Example : $module_key = 'rt_biz', $role = 'author'
		 *          Return String - rt_biz_author
		 *
		 * @param $module_key
		 * @param string $role
		 * @return string
		 */
		static function get_capability_from_access_role( $module_key, $role = 'no_access' ) {

			if ( isset( self::$modules[ $module_key ] ) && isset( self::$permissions[ $role ] ) ) {
				$module_key = rt_biz_sanitize_module_key( $module_key );
				return $module_key . '_' . $role;
			}
			return '';
		}

		static function get_role_key( $role_value ) {
			foreach ( self::$permissions as $pkey => $p ) {
				if ( $p['value'] == $role_value ) {
					return $pkey;
				}
			}
			return '';
		}

		static function get_no_access_post_caps( $post_type ) {
			return array(
				"edit_{$post_type}" => false,
				"read_{$post_type}" => false,
				"delete_{$post_type}" => false,
				"edit_{$post_type}s" => false,
				"edit_others_{$post_type}s" => false,
				"publish_{$post_type}s" => false,
				"read_private_{$post_type}s" => false,
				"delete_{$post_type}s" => false,
				"delete_private_{$post_type}s" => false,
				"delete_published_{$post_type}s" => false,
				"delete_others_{$post_type}s" => false,
				"edit_private_{$post_type}s" => false,
				"edit_published_{$post_type}s" => false,
				'manage_terms' => false,
				'edit_terms' => false,
				'delete_terms' => false,
				'assign_terms' => false,
			);
		}

		static function get_author_post_caps( $post_type ) {
			return array(
				"edit_{$post_type}" => true,
				"read_{$post_type}" => true,
				"delete_{$post_type}" => true,
				"edit_{$post_type}s" => true,
				"edit_others_{$post_type}s" => false,
				"publish_{$post_type}s" => true,
				"read_private_{$post_type}s" => false,
				"delete_{$post_type}s" => true,
				"delete_private_{$post_type}s" => false,
				"delete_published_{$post_type}s" => true,
				"delete_others_{$post_type}s" => false,
				"edit_private_{$post_type}s" => false,
				"edit_published_{$post_type}s" => true,
				'manage_terms' => false,
				'edit_terms' => false,
				'delete_terms' => false,
				'assign_terms' => false,
			);
		}

		static function get_editor_post_caps( $post_type ) {
			return array(
				"edit_{$post_type}" => true,
				"read_{$post_type}" => true,
				"delete_{$post_type}" => true,
				"edit_{$post_type}s" => true,
				"edit_others_{$post_type}s" => true,
				"publish_{$post_type}s" => true,
				"read_private_{$post_type}s" => true,
				"delete_{$post_type}s" => true,
				"delete_private_{$post_type}s" => true,
				"delete_published_{$post_type}s" => true,
				"delete_others_{$post_type}s" => true,
				"edit_private_{$post_type}s" => true,
				"edit_published_{$post_type}s" => true,
				'manage_terms' => true,
				'edit_terms' => true,
				'delete_terms' => true,
				'assign_terms' => true,
			);
		}

		static function get_admin_post_caps( $post_type ) {
			$admin_cap = array(
				"edit_{$post_type}"              => true,
				"read_{$post_type}"              => true,
				"delete_{$post_type}"            => true,
				"edit_{$post_type}s"             => true,
				"edit_others_{$post_type}s"      => true,
				"publish_{$post_type}s"          => true,
				"read_private_{$post_type}s"     => true,
				"delete_{$post_type}s"           => true,
				"delete_private_{$post_type}s"   => true,
				"delete_published_{$post_type}s" => true,
				"delete_others_{$post_type}s"    => true,
				"edit_private_{$post_type}s"     => true,
				"edit_published_{$post_type}s"   => true,
				'manage_terms'                   => true,
				'edit_terms'                     => true,
				'delete_terms'                   => true,
				'assign_terms'                   => true,
			);

			// get all module setting option names
			$setting_options = array();
			foreach ( self::$modules as $module ) {

				if ( ! empty( $module['setting_option_name'] ) ) {

					$setting_options[] = $module['setting_option_name'] . '_group';

				}
			}
			if ( isset( $_POST['option_page'] ) && in_array( $_POST['option_page'], $setting_options, true )  ) {
				$admin_cap = array_merge( $admin_cap, array( 'manage_options' => true ) );
			}
			return $admin_cap;
		}

		function get_module_users( $module_key, $category_slug = '' ) {

			global $wpdb;
			$module_key_length = strlen( $module_key );

			/**
			 *	Include All the admins
			 */
			$users = get_users( array( 'fields' => 'ID', 'role' => 'administrator' ) );
			$user_obj = array();
			foreach ( array_unique( $users ) as $id ) {
				$user_obj[] = new WP_User( $id );
			}

			/**
			 *	Include All Profile Access Level Users
			 */
			$contacts = array();
			$contact_meta = $wpdb->get_results( "SELECT * from {$wpdb->postmeta} WHERE meta_key = 'rt_biz_profile_permissions' and meta_value REGEXP 's:{$module_key_length}:\"{$module_key}\";s:[0-9]*:\"[0-9]*\"'" );
			// $cm - user_meta single
			foreach ( $contact_meta as $cm ) {

				$pp = get_post_meta( $cm->post_id, 'rt_biz_profile_permissions', true );
				if ( isset( $pp[ $module_key ] ) && 0 == intval( $pp[ $module_key ] )  ) {
					continue;
				}
				if ( $category_slug == '' || has_term( $category_slug, Rt_Contact::$user_category_taxonomy, $cm->post_id ) ){
					$contacts[] = $cm->post_id;
				}
			}
			if ( ! empty( $contacts ) ){
				$user_obj = array_merge( $user_obj, rt_biz_get_wp_user_for_contact( $contacts ) );
			}

			/**
			 *	Include All Group Access Level Users
			 */
			$department = rt_biz_get_department();
			$module_permissions = get_site_option( 'rt_biz_module_permissions' );
			// $ug - user_group single
			if ( ! $department instanceof WP_Error ) {
				foreach ( $department as $ug ) {
					if ( isset( $module_permissions[ $module_key ][ $ug->term_id ] ) && 0 != intval( $module_permissions[ $module_key ][ $ug->term_id ] ) ) {
						$user_obj = array_merge( $user_obj, rt_biz_get_module_department_users( $ug->term_id, $category_slug, $module_key ) );
					}
				}
			}

			$user_obj = array_unique( $user_obj, SORT_REGULAR );

			return $user_obj;
		}

		/**
		 *  Saves the ACL Permission Matrix to the Database
		 */
		function save_acl_settings() {
			if ( ! isset( $_POST['rt_biz_acl_permissions'] ) ) {
				return;
			}
			if ( ! isset( $_POST['rt_biz_module_permissions'] ) || ! is_array( $_POST['rt_biz_module_permissions'] ) ) {
				return;
			}

			//update acl custom table
			global $rt_biz_acl_model;

			$old_module_permissions = get_site_option( 'rt_biz_module_permissions' );
			$module_permissions = $_POST['rt_biz_module_permissions'];

			// New Module added
			$Module_added = array_diff_key( $module_permissions, $old_module_permissions );
			if ( !empty( $Module_added ) ){
				foreach ( $Module_added as $module_Key=>$dept_permission ){
					foreach( $dept_permission as $groupid=>$permissoin ){
						$where = array(
							'groupid'    => $groupid,
						);
						$users = $rt_biz_acl_model->get_acl( $where );
						if ( !empty( $users ) ){
							$users = array_unique( wp_list_pluck( $users, 'userid' ) );
							foreach( $users as $user ){
								$data = array(
									'userid'     => $user,
									'module'     => $module_Key,
									'groupid'    => $groupid,
									'permission' => $permissoin,
								);
								$rt_biz_acl_model->add_acl( $data );
							}
						}

					}
				}
			}

			// existing module removed
			$Module_removed = array_diff_key( $old_module_permissions, $module_permissions );

			// existing module permission updated
			foreach ( $module_permissions as $module_Key=>$dept_permission ){

				// new group permission added
				$dept_added = array_diff_key( $dept_permission, $old_module_permissions[ $module_Key ] );
				// existing group removed
				$dept_removed = array_diff_key( $old_module_permissions[ $module_Key ], $dept_permission );

				// existing group permission updated
				$permission_diff = array_diff_assoc( $dept_permission, $old_module_permissions[ $module_Key ] );
				foreach( $permission_diff as $groupid=>$permissoin ){
					$data = array(
						'permission' => $permissoin,
					);
					$where = array(
						'module'     => $module_Key,
						'groupid'    => $groupid,
					);
					$rt_biz_acl_model->update_acl( $data, $where );
				}
			}
			update_site_option( 'rt_biz_module_permissions', $module_permissions );
		}

		/**
		 *  Take Action according to permission saved from the form & then display the ACL Settings UI
		 */
		function acl_settings_ui() {

			$this->save_acl_settings();

			rt_biz_get_template( 'acl-settings.php' );
		}

		function profile_level_permission( $post ) {
			global $rt_contact;
			if ( current_user_can( 'create_users' ) && p2p_connection_exists( $rt_contact->post_type . '_to_user', array( 'from' => $post->ID ) ) ) {
				$modules     = rt_biz_get_modules();
				$permissions = rt_biz_get_acl_permissions();
				$user_permissions = get_post_meta( $post->ID, 'rt_biz_profile_permissions', true );
				$settings  = rt_biz_get_redux_settings();
				$menu_label = $settings['menu_label'];
				?>
				<h3><?php echo $menu_label . __( ' Profile Access' ); ?></h3>
				<table class="form-table">
					<tbody>
						<?php foreach ( $modules as $mkey => $m ) { ?>
						<tr>
							<th><?php echo $m['label']; ?></th>
							<td>
								<select name="rt_biz_profile_permissions[<?php echo $mkey ?>]">
									<option title="<?php _e( 'No Profile Access Override' ); ?>" value=""><?php _e( 'Use Group Access' ); ?></option>
									<?php foreach ( $permissions as $pkey => $p ) { ?>
									<option title="<?php echo $p['tooltip']; ?>" value="<?php echo $p['value']; ?>" <?php echo ( isset( $user_permissions[ $mkey ] ) && intval( $user_permissions[ $mkey ] ) == $p['value'] && 0 != strlen( $user_permissions[ $mkey ] ) ) ? 'selected="selected"' : ''; ?>><?php echo $p['name']; ?></option>
									<?php } ?>
								</select>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<?php
			} else {
				?><div><?php printf( '%s <strong>%s</strong> %s', __( 'In order to assign profile level access, connect user with contact from' ), __( 'Connected Users' ), __( 'metabox.' ) ); ?> </div><?php
			}
		}

		function save_profile_level_permission( $contact_id ) {

			if ( current_user_can( 'create_users' ) ) {
				if ( isset( $_REQUEST['rt_biz_profile_permissions'] ) && is_array( $_REQUEST['rt_biz_profile_permissions'] ) ) {

					//update acl custom table
					global $rt_biz_acl_model;

					// if user is not connected with contact then acl not stored
					$user = rt_biz_get_wp_user_for_contact( $contact_id );
					if ( empty( $user ) ){
						return;
					}

					$departments = wp_get_post_terms( $contact_id, RT_Departments::$slug );
					$module_permissions = get_site_option( 'rt_biz_module_permissions' );

					$profile_permissions = $_REQUEST['rt_biz_profile_permissions'];
					$old_profile_permissions = get_post_meta( $contact_id, 'rt_biz_profile_permissions', true );

					foreach ( $profile_permissions as $module_Key => $module_permission  ){
						$old_permission_len = strlen( $old_profile_permissions[ $module_Key ] );
						$isOldPermission = isset( $old_profile_permissions[ $module_Key ] );

						switch( $module_permission  ){
							case 0:
								if ( 0 == strlen( $module_permission ) ){
									// Group Level permission
									$module_permission = ( ! empty( $module_permissions ) ) ? $module_permissions[ $module_Key ] : array();
									//check if old permission is group level
									if ( $isOldPermission && 0 == $old_permission_len ){

										//get old group and its old permission from custom table
										$where = array(
											'userid'     => $user[0]->ID,
											'module'     => $module_Key,
										);
										$old_group_permission = $rt_biz_acl_model->get_acl( $where );

										if ( ! empty( $old_group_permission ) ){
											$old_group = array_unique( wp_list_pluck( $old_group_permission, 'groupid' ) );
											$old_group_permission = array_unique( wp_list_pluck( $old_group_permission, 'permission' ) );
										}else{
											$old_group = array();
											$old_group_permission = array();
										}

										foreach ( $departments as $department ) {
											// find index is current group exist in old group list
											$position = array_search( $department->term_id, $old_group );
											//check if group permission is already exist or not
											if ( strlen( $position ) > 0 ){
												// check id group permission update or not
												if ( $module_permission[ $department->term_id ] != $old_group_permission[ $position ] ){
													// update group level permission
													$data = array(
														'permission' => $module_permission[ $department->term_id ],
													);
													$where = array(
														'userid'     => $user[0]->ID,
														'module'     => $module_Key,
														'groupid'    => $department->term_id,
													);
													$rt_biz_acl_model->update_acl( $data, $where );
												}
											}else{
												// add new group level permission
												$data = array(
													'userid'     => $user[0]->ID,
													'module'     => $module_Key,
													'groupid'    => $department->term_id,
													'permission' => $module_permission[ $department->term_id ],
												);
												$rt_biz_acl_model->add_acl( $data );
											}
										}

										//if group remove remove its acl
										if ( ! empty( $departments ) ){
											$new_group = array_unique( wp_list_pluck( $departments, 'term_id' ) );
										} else {
											$new_group = array();
										}
										$group_removed = array_diff( $old_group, $new_group );
										//any group remove from customer profile remove access
										if ( ! empty( $group_removed ) ){
											// remove group level acl
											foreach( $group_removed as $group ){
												$where = array(
													'userid'     => $user[0]->ID,
													'groupid'    => $group,
													'module'     => $module_Key,
												);
												$rt_biz_acl_model->remove_acl( $where );
											}
										}
									}else {
										//check if old permission is profile level permission
										if ( $isOldPermission && 0 != $old_permission_len ){
											// remove old profile level permission
											$where = array(
												'userid'     => $user[0]->ID,
												'module'     => $module_Key,
											);
											$rt_biz_acl_model->remove_acl( $where );
										}
										// Add new group level permission
										foreach ( $departments as $department ) {
											$data = array(
												'userid'     => $user[0]->ID,
												'module'     => $module_Key,
												'groupid'    => $department->term_id,
												'permission' => $module_permission[ $department->term_id ],
											);
											$rt_biz_acl_model->add_acl( $data );
										}
									}
								} else {
									// No Role

									//remove all permission
									if ( $isOldPermission ){
										$where = array(
											'userid'     => $user[0]->ID,
											'module'     => $module_Key,
										);
										$rt_biz_acl_model->remove_acl( $where );
									}
								}
								break;
							case 10:
							case 20:
							case 30:
								//check if old permission is profile level permission || 0 != $old_permission_len means profile level permission already set
								if ( $isOldPermission && 0 != $old_permission_len && 0 != $old_profile_permissions[ $module_Key ] ) {
									//check if profile level permission changed
									if( $module_permission != $old_profile_permissions[ $module_Key ] ){
										$data = array(
											'permission' => $module_permission ,
										);
										$where = array(
											'userid'     => $user[0]->ID,
											'module'     => $module_Key,
											'groupid'    => 0,
										);
										$rt_biz_acl_model->update_acl( $data, $where );
									}
								}else{
									// remove old group level permission if already set
									if ( $isOldPermission && 0 == $old_permission_len ){
										$where = array(
											'userid'     => $user[0]->ID,
											'module'     => $module_Key,
										);
										$rt_biz_acl_model->remove_acl( $where );
									}
									// Add new profile level permission
									$data = array(
										'userid'     => $user[0]->ID,
										'module'     => $module_Key,
										'groupid'    => 0,
										'permission' => $module_permission ,
									);
									$rt_biz_acl_model->add_acl( $data );
								}
								break;
						}
					}
					update_post_meta( $contact_id, 'rt_biz_profile_permissions', $_REQUEST['rt_biz_profile_permissions'] );
				}
			}
		}

		function add_department_support( $supports ){

	        foreach ( self::$modules as $key => $value ){
		        if ( ! empty( $value['department_support'] ) ) {
			        $supports = array_merge( $supports, $value['department_support'] );
		        }
	        }
			return $supports;
		}
	}
}
