<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

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
if( ! class_exists('Rt_Access_Control') ) {

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
		public static $modules;

		/**
		 *
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'init_acl' ), 15 );
			add_filter( 'user_has_cap', array( $this, 'filter_caps' ), 999, 4 );

			add_action( 'edit_user_profile', array( $this, 'profile_level_permission' ), 1 );
			add_action( 'show_user_profile', array( $this, 'profile_level_permission' ), 1 );
			add_action( 'profile_update', array( $this, 'save_profile_level_permission' ), 10, 2 );
		}

		function filter_caps( $all_caps, $required_caps, $args, $user ) {

			$rt_biz_caps = array();
			foreach ( self::$modules as $mkey => $m ) {
				foreach ( self::$permissions as $pkey => $p ) {
					$rt_biz_caps[] = $mkey . '_' . $pkey;
				}
			}

			if ( isset( $all_caps['administrator'] ) && $all_caps['administrator'] ) {
				foreach ( $rt_biz_caps as $cap ) {
					$all_caps[$cap] = true;
				}
				return $all_caps;
			}

			foreach ( $required_caps as $cap ) {
				if ( ! in_array( $cap, $rt_biz_caps ) ) {
					continue;
				}
				echo '<pre>';
//				var_dump($all_caps);
				var_dump($required_caps);
				var_dump($args);
//				var_dump($user);
				echo '</pre>';
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
			 *  Array Structure is array( 'module_slug' => 'label' )
			 *
			 *  $biz_module = array(
			 *		RT_BIZ_TEXT_DOMAIN => array(
			 *			'label' => __( 'rtBiz' ),
			 *			'post_types' => array( 'post', 'page', 'rt_contact', 'rt_lead', ),
			 *		),
			 *  );
			 */
			self::$modules = apply_filters( 'rt_biz_modules', array() );

			self::$permissions = array(
				'no_access' => array(
					'value' => 0,
					'name' => __( 'No Access' ),
				),
				'author' => array(
					'value' => 10,
					'name' => __( 'Author - Read/Write (Self)' ),
				),
				'editor' => array(
					'value' => 20,
					'name' => __( 'Editor - Read/Write (Everything)' ),
				),
				'admin' => array(
					'value' => 30,
					'name' => __( 'Admin - Read/Write (Everything) + Settings' ),
				),
			);
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
		public static function get_capability_from_access_role( $module_key, $role = 'no_access' ) {

			if ( isset( self::$modules[ $module_key ] ) && isset( self::$permissions[ $role ] ) ) {
				$module_key = rt_biz_sanitize_module_key( $module_key );
				return $module_key . '_' . $role;
			}
			return '';
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
			$module_permissions = $_POST['rt_biz_module_permissions'];
			update_site_option( 'rt_biz_module_permissions', $module_permissions );
		}

		/**
		 *  Take Action according to permission saved from the form & then display the ACL Settings UI
		 */
		function acl_settings_ui() {

			$this->save_acl_settings();

			rt_biz_get_template( 'acl-settings.php' );
		}

		function profile_level_permission( $user ) {
			$current_user = new WP_User( get_current_user_id() );
			if ( $current_user->has_cap( 'create_users' ) ) {
				$modules     = rt_biz_get_modules();
				$permissions = rt_biz_get_acl_permissions();
				$user_permissions = get_user_meta( $user->ID, 'rt_biz_profile_permissions', true );
				?>
				<h3><?php _e( 'rtBiz Profile Access' ); ?></h3>
				<table class="form-table">
					<tbody>
						<?php foreach ( $modules as $mkey => $m ) { ?>
						<tr>
							<th><?php echo $m['label']; ?></th>
							<td>
								<select name="rt_biz_profile_permissions[<?php echo $mkey ?>]">
								<?php foreach ( $permissions as $pkey => $p ) { ?>
								<option value="<?php echo $p['value']; ?>" <?php echo ( isset( $user_permissions[$mkey] ) && $user_permissions[$mkey] == $p['value'] ) ? 'selected="selected"' : ''; ?>><?php echo $p['name']; ?></option>
								<?php } ?>
								</select>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<?php
			}
		}

		function save_profile_level_permission( $user_id, $old_data ) {
			if ( current_user_can( 'create_users' ) ) {
				if ( isset( $_REQUEST['rt_biz_profile_permissions'] ) && is_array( $_REQUEST['rt_biz_profile_permissions'] ) ) {
					update_user_meta( $user_id, 'rt_biz_profile_permissions', $_REQUEST['rt_biz_profile_permissions'] );
				}
			}
		}
	}
}
