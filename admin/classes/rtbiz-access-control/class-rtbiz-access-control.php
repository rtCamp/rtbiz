<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
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
if ( ! class_exists( 'Rtbiz_Access_Control' ) ) {

	/**
	 * Class Rtbiz_Access_Control
	 *
	 * This class works as the base for all the permissions, user access for rtBiz family plugins.
	 * All the plugins including rtBiz will register with this class for the Access Control that it requires
	 * & from there Rt_Access_Controll will take it over.
	 *
	 * It will map a matrix for permissions for Plugin Modules => User Groups (Teams) and from there user access
	 * can be set as per requirement.
	 */
	class Rtbiz_Access_Control {

		public static $page_slug = 'rtbiz-access-control';

		/**
		 * @var - These are more of the permission roles for the access control.
		 */
		public static $permissions;

		/**
		 * @var - Registered Plugin Modules for Rtbiz_Access_Control
		 */
		public static $modules = array();

		/**
		 *
		 */
		public function __construct() {
			Rtbiz::$loader->add_action( 'plugins_loaded', $this, 'init_acl', 15 );
			Rtbiz::$loader->add_filter( 'user_has_cap', $this, 'filter_caps', 900, 4 );
		}

		/**
		 *  Initilize ACL on plugins_loaded with 15 priority. So that before this gets executed;
		 *  other addon plugins get chance to hook into it and register themselved
		 */
		public function init_acl() {
			/**
			 *
			 *  Filter for other addons to register.
			 *  Array Structure is array( 'module_slug' => array() )
			 *
			 *  $biz_module = array(
			 *        RTBIZ_TEXT_DOMAIN => array(
			 *            'label' => __( 'rtBiz' ),                                                                   // module label
			 *            'post_types' => array( 'post', 'page', 'rtbiz_contact', 'rt_lead', ),                          // array of post type for given module
			 *          'team_support' => array( 'rtbiz_contact', 'rt_ticket' ),                                 // array of post types for which team taxonomy is to be registered
			 *          'product_support' => array( 'rtbiz_contact', 'rt_ticket' ),                                   // array of post types for which products taxonomy is to be registered
			 *          'setting_option_name' => 'redux_biz_settings',                                              // redux opions Name, if used
			 *          'setting_page_url' => 'http://rtbiz.rtcamp.net/wp-admin/admin.php?page=rt-biz-settings',    // Settings Page URL, if Mailbox module is used.
			 *        ),
			 *  );
			 */

			self::$modules = apply_filters( 'rtbiz_modules', array() );

			self::$permissions = apply_filters( 'rtbiz_permissions', array(
				'no_access' => array(
					'value'   => 0,
					'name'    => __( 'No Role' ),
					'tooltip' => __( 'No Access' ),
				),
				'author'    => array(
					'value'   => 10,
					'name'    => __( 'Author' ),
					'tooltip' => __( 'Read/Write (Self)' ),
				),
				'editor'    => array(
					'value'   => 20,
					'name'    => __( 'Editor' ),
					'tooltip' => __( 'Read/Write (Everything)' ),
				),
				'admin'     => array(
					'value'   => 30,
					'name'    => __( 'Admin' ),
					'tooltip' => __( 'Read/Write (Everything) + Settings' ),
				),
			) );
		}

		public function filter_caps( $all_caps, $required_caps, $args, $user ) {
			global $rtbiz_acl_model;

			$rtbiz_caps = array();
			// $m - module
			// $mkey - module_key
			if ( ! empty( self::$modules ) ) {
				foreach ( self::$modules as $mkey => $m ) {
					// $p - permission
					// $pkey - permission_key
					foreach ( self::$permissions as $pkey => $p ) {
						$rtbiz_caps[] = $mkey . '_' . $pkey;
					}
					$post_types = ( isset( $m['post_types'] ) && is_array( $m['post_types'] ) ) ? $m['post_types'] : array();
					// $pt - post_type
					foreach ( $post_types as $pt ) {
						$post_caps  = self::get_admin_post_caps( $pt );
						$rtbiz_caps = array_merge( $rtbiz_caps, array_keys( $post_caps ) );
					}
				}
			}

			if ( isset( $all_caps['administrator'] ) && $all_caps['administrator'] ) {
				foreach ( $required_caps as $cap ) {
					if ( ! in_array( $cap, $rtbiz_caps ) ) {
						continue;
					}
					$all_caps[ $cap ] = true;
				}

				return $all_caps;
			}

			foreach ( $required_caps as $cap ) {
				if ( ! in_array( $cap, $rtbiz_caps ) ) {
					continue;
				}

				$module_permissions = array();
				$sql                = "select module, max( permission ) as permission from $rtbiz_acl_model->table_name where userid = $user->ID group by module";
				$permissions        = $rtbiz_acl_model->get_result_by_query( $sql );
				foreach ( $permissions as $permission ) {
					$module_permissions[ $permission->module ] = $permission->permission;
				}

				$valid_caps = array();
				foreach ( $module_permissions as $mkey => $valid_role_value ) {
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
						$post_caps = call_user_func( array(
							'Rtbiz_Access_Control',
							'get_' . $valid_role_key . '_post_caps'
						), $pt );
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
		 *
		 * This method return the capability name text if you pass the module_slug & role
		 * It will return proper cap if passed module_key is registered with Rtbiz_Access_Control
		 * & passed role is valid.
		 *
		 * Other wise it will return empty string.
		 *
		 * Example : $module_key = 'rtbiz', $role = 'author'
		 *          Return String - rtbiz_author
		 *
		 * @param $module_key
		 * @param string $role
		 *
		 * @return string
		 */
		public static function get_capability_from_access_role( $module_key, $role = 'no_access' ) {
			if ( isset( self::$modules[ $module_key ] ) && isset( self::$permissions[ $role ] ) ) {
				$module_key = rtbiz_sanitize_module_key( $module_key );

				return $module_key . '_' . $role;
			}

			return '';
		}

		public static function get_role_key( $role_value ) {
			foreach ( self::$permissions as $pkey => $p ) {
				if ( $p['value'] == $role_value ) {
					return $pkey;
				}
			}

			return '';
		}

		public static function get_no_access_post_caps( $post_type ) {
			return array(
				"edit_{$post_type}"              => false,
				"read_{$post_type}"              => false,
				"delete_{$post_type}"            => false,
				"edit_{$post_type}s"             => false,
				"edit_others_{$post_type}s"      => false,
				"publish_{$post_type}s"          => false,
				"read_private_{$post_type}s"     => false,
				"delete_{$post_type}s"           => false,
				"delete_private_{$post_type}s"   => false,
				"delete_published_{$post_type}s" => false,
				"delete_others_{$post_type}s"    => false,
				"edit_private_{$post_type}s"     => false,
				"edit_published_{$post_type}s"   => false,
				'manage_terms'                   => false,
				'edit_terms'                     => false,
				'delete_terms'                   => false,
				'assign_terms'                   => false,
			);
		}

		public static function get_author_post_caps( $post_type ) {
			return array(
				"edit_{$post_type}"              => true,
				"read_{$post_type}"              => true,
				"delete_{$post_type}"            => true,
				"edit_{$post_type}s"             => true,
				"edit_others_{$post_type}s"      => false,
				"publish_{$post_type}s"          => true,
				"read_private_{$post_type}s"     => false,
				"delete_{$post_type}s"           => true,
				"delete_private_{$post_type}s"   => false,
				"delete_published_{$post_type}s" => true,
				"delete_others_{$post_type}s"    => false,
				"edit_private_{$post_type}s"     => false,
				"edit_published_{$post_type}s"   => true,
				'manage_terms'                   => false,
				'edit_terms'                     => false,
				'delete_terms'                   => false,
				'assign_terms'                   => false,
			);
		}

		public static function get_editor_post_caps( $post_type ) {
			return array(
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
		}

		public static function get_admin_post_caps( $post_type ) {
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
			if ( isset( $_POST['option_page'] ) && in_array( $_POST['option_page'], $setting_options, true ) ) {
				$admin_cap = array_merge( $admin_cap, array( 'manage_options' => true ) );
			}

			return $admin_cap;
		}

		public function get_module_users( $module_key ) {

			global $rtbiz_acl_model;

			// Include All the admins
			$module_user = get_users( array( 'fields' => 'ID', 'role' => 'administrator' ) );
			global $wpdb;
			// include other module user
			$sql      = 'SELECT DISTINCT(acl.userid) FROM ' . $rtbiz_acl_model->table_name . ' as acl INNER JOIN ' . $wpdb->prefix . 'p2p as p2p on ( acl.userid = p2p.p2p_to ) INNER JOIN ' . $wpdb->posts . " as posts on (p2p.p2p_from = posts.ID )  where acl.module =  '" . $module_key . "' and acl.permission > 0 and p2p.p2p_type = '" . rtbiz_get_contact_post_type() . "_to_user' and posts.post_status= 'publish' and posts.post_type= '" . rtbiz_get_contact_post_type() . "' ";
			$user_ids = $wpdb->get_col( $sql );

			if ( ! empty( $user_ids ) ) {
				$module_user = array_merge( $module_user, $user_ids );
			}
			$module_user = array_unique( $module_user );

			// get user object from user ids
			$user_obj = array();
			if ( ! empty( $module_user ) ) {
				$user_obj = get_users( array(
					'include' => $module_user,
					'orderby' => 'display_name',
					'order'   => 'ASC'
				) );
			}

			return $user_obj;
		}

		/**
		 *  Saves the ACL Permission Matrix to the Database
		 */
		public function save_acl_settings() {
			if ( ! isset( $_POST['rtbiz_acl_permissions'] ) ) {
				return;
			}
			if ( ! isset( $_POST['rtbiz_acl_module_permissions'] ) || ! is_array( $_POST['rtbiz_acl_module_permissions'] ) ) {
				return;
			}

			//update acl custom table
			global $rtbiz_acl_model;

			$old_module_permissions = get_site_option( 'rtbiz_acl_module_permissions' );
			if ( empty( $old_module_permissions ) || ! is_array( $old_module_permissions ) ) {
				$old_module_permissions = array();
			}
			$module_permissions = $_POST['rtbiz_acl_module_permissions'];

			// New Module added
			$Module_added = array_diff_key( $module_permissions, $old_module_permissions );
			if ( ! empty( $Module_added ) ) {
				foreach ( $Module_added as $module_Key => $dept_permission ) {
					foreach ( $dept_permission as $groupid => $permissoin ) {
						$where = array(
							'groupid' => $groupid,
						);
						$users = $rtbiz_acl_model->get_acl( $where );
						if ( ! empty( $users ) ) {
							$users = array_unique( wp_list_pluck( $users, 'userid' ) );
							foreach ( $users as $user ) {
								$data = array(
									'userid'     => $user,
									'module'     => $module_Key,
									'groupid'    => $groupid,
									'permission' => $permissoin,
								);
								$rtbiz_acl_model->add_acl( $data );
							}
						}
					}
				}
			}

			// existing module removed
			$Module_removed = array_diff_key( $old_module_permissions, $module_permissions );

			// existing module permission updated
			foreach ( $module_permissions as $module_Key => $dept_permission ) {
				if ( empty( $old_module_permissions[ $module_Key ] ) || ! is_array( $old_module_permissions[ $module_Key ] ) ) {
					$old_module_permissions[ $module_Key ] = array();
				}
				// new group permission added
				//$dept_added = array_diff_key( $dept_permission, $old_module_permissions[ $module_Key ] );
				// existing group removed
				//$dept_removed = array_diff_key( $old_module_permissions[ $module_Key ], $dept_permission );

				// existing group permission updated
				$permission_diff = array_diff_assoc( $dept_permission, $old_module_permissions[ $module_Key ] );
				foreach ( $permission_diff as $groupid => $permissoin ) {
					$data  = array(
						'permission' => $permissoin,
					);
					$where = array(
						'module'  => $module_Key,
						'groupid' => $groupid,
					);
					$rtbiz_acl_model->update_acl( $data, $where );
				}
			}
			update_site_option( 'rtbiz_acl_module_permissions', $module_permissions );
		}

		/**
		 *  Take Action according to permission saved from the form & then display the ACL Settings UI
		 */
		public function acl_settings_ui() {
			$this->save_acl_settings();
			rtbiz_get_template( 'acl-settings.php', array(), '', RTBIZ_PATH . 'admin/page/' );
		}


	}
}
