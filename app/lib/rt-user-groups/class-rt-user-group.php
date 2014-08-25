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
 * Description of class-rt-user-groups
 *
 * @author udit
 */
add_action( 'plugins_loaded', array( 'RT_User_Group', 'load' ), 1 );
include_once   realpath(__DIR__.'/../').'/rt-db-model/class-rt-db-model.php';
include_once   'model/class-rt-user-group-relationships-model.php';

if ( ! class_exists( 'RT_User_Group' ) ) {

	class RT_User_Group {
            
		static function load() {
			global $RT_User_Group;
			$RT_User_Group = new RT_User_Group();
		}

		public function __construct() {
                    global $user_group_model;
                      $user_group_model = new RT_User_Group_Relationships_Model();
                    
                    add_filter( 'manage_users_columns', array( &$this, 'add_manage_users_columns' ), 15, 1 );
                    add_action( 'manage_users_custom_column', array( &$this, 'user_column_data' ), 15, 3 );

                    add_action( 'admin_print_scripts', array( &$this, 'js_includes' ) );
                    add_action( 'admin_print_styles', array( &$this, 'css_includes' ) );
                    add_action( 'admin_head', array( &$this, 'colorpicker' ) );
                    add_action( 'admin_head', array( &$this, 'hide_slug' ) );

                    /* Achieve filtering by User Group. A hack that may need refining. */
                    add_action( 'pre_user_query', array( &$this, 'user_query' ) );

                    add_filter( 'views_users', array( &$this, 'views' ) );

                    /* Bulk edit */
                    add_action( 'admin_init', array( &$this, 'bulk_edit_action' ) );
                    add_filter( 'views_users', array( &$this, 'bulk_edit' ) );

                    add_action( 'admin_init', array( &$this, 'remove_add_form_actions' ), 1000 );

                    add_action( 'init', array( &$this, 'register_user_taxonomy' ) );
                    add_action( 'create_term', array( &$this, 'save_department' ), 10, 2 );
                    add_action( 'edit_term', array( &$this, 'save_department' ), 10, 2 );
                    
                    add_filter( 'user-group_row_actions', array( &$this, 'row_actions' ), 1, 2 );
                    add_action( 'manage_user-group_custom_column', array( &$this, 'manage_user_group_column' ), 10, 3 );
                    add_filter( 'manage_edit-user-group_columns', array( &$this, 'manage_user_group_user_column' ) );

                    /* Update the user groups when the edit user page is updated. */
                    add_action( 'personal_options_update', array( &$this, 'save_user_user_groups' ) );
                    add_action( 'edit_user_profile_update', array( &$this, 'save_user_user_groups' ) );

                    /* Add section to the edit user page in the admin to select profession. */
                    add_action( 'show_user_profile', array( &$this, 'edit_user_user_group_section' ), 99999 );
                    add_action( 'edit_user_profile', array( &$this, 'edit_user_user_group_section' ), 99999 );

                    /* Cleanup stuff */
                    add_action( 'delete_user', array( &$this, 'delete_term_relationships' ) );
                    add_filter( 'sanitize_user', array( &$this, 'disable_username' ) );
                    
                   // $this->update_database();
                    
		}
                
                function update_database() {
			$updateDB = new RT_DB_Update( trailingslashit( RT_BIZ_PATH ) . 'index.php', trailingslashit(  RT_BIZ_PATH .'app/lib/rt-user-groups/schema' ) );
			$updateDB->install_db_version = 0;
                        $updateDB->do_upgrade();
		}
                
                function register_user_taxonomy()
		{
			$lable = array(
				'name' => __( 'Departments' ),
				'singular_name' => __( 'Departmet' ),
				'menu_name' => __( 'Departments' ),
				'search_items' => __( 'Search Departments' ),
				'popular_items' => __( 'Popular Departments' ),
				'all_items' => __( 'All User Departments' ),
				'edit_item' => __( 'Edit Department' ),
				'update_item' => __( 'Update Department' ),
				'add_new_item' => __( 'Add New Department' ),
				'new_item_name' => __( 'New Department Name' ),
				'separate_items_with_commas' => __( 'Separate departments with commas' ),
				'add_or_remove_items' => __( 'Add or remove departments' ),
				'choose_from_most_used' => __( 'Choose from the most popular departments' ), );

			$arg = array(
				'public' => TRUE,
				'labels' => $lable,
				'rewrite' => false,
				'capabilities' => array( 'manage_terms' => 'edit_users', ) );

                        $supports = apply_filters('rtbiz_department_support', array( 'user' ) );

                        register_taxonomy( 'user-group', $supports, $arg );
		}
                
                function save_department( $term_id )
		{
			if ( isset( $_POST[ 'user-group' ] ) ){
				$term_meta = (array)get_option( 'user-group-meta' );
				$term_meta[ $term_id ] = (array)$_POST[ 'user-group' ];
				update_option( 'user-group-meta', $term_meta );
				if ( isset( $_POST[ '_wp_original_http_referer' ] ) ){
					wp_safe_redirect( $_POST[ '_wp_original_http_referer' ] );
					exit();
				}
			}
		}
                
                function row_actions( $actions, $term )
		{
			$actions[ 'view' ] = sprintf( __( '%sView%s', 'user-groups' ), '<a href="' . add_query_arg( array( 'user-group' => $term->slug ), admin_url( 'users.php' ) ) . '">', '</a>' );

			return $actions;
		}

		function update_user_group_count( $terms, $taxonomy )
		{
			global $wpdb;

			foreach ( (array)$terms as $term ) {

				$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term ) );

				do_action( 'edit_term_taxonomy', $term, $taxonomy );
				$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
				do_action( 'edited_term_taxonomy', $term, $taxonomy );
			}
		}

		function add_user_group_admin_page()
		{

			$tax = get_taxonomy( 'user-group' );
		}

		function manage_user_group_user_column( $columns )
		{

			unset( $columns[ 'posts' ], $columns[ 'slug' ] );


			$columns[ 'users' ]         = __( 'Users', 'user-group' );
			$columns[ 'color' ]         = __( 'Color', 'user-group' );
			$columns[ 'email_address' ] = __( 'Email Address', 'user-group' );

			return $columns;
		}

		function manage_user_group_column( $display, $column, $term_id )
		{

			switch ( $column ) {
				case 'users':
					$term = get_term( $term_id, 'user-group' );
					echo '<a href="' . admin_url( 'users.php?user-group=' . $term->slug ) . '">' . sprintf( _n( __( '%s User' ), __( '%s Users' ), $term->count ), $term->count ) . '</a>';
					break;
				case 'color':
					$color = self::get_meta( 'group-color', $term_id );
					if ( ! empty( $color ) ){
						echo '<div style="width:3.18em; height:3em; background-color:' . self::get_meta( 'group-color', $term_id ) . ';"></div>';
					}
					break;
				case 'email_address';
					$term_meta     = get_option( 'user-group-meta' );
					$email_address = $term_meta[ $term_id ][ 'email_address' ];
					echo esc_html( $email_address );
					break;
			}
			return;
		}


		function edit_user_user_group_section( $user )
		{

			$tax = get_taxonomy( 'user-group' );

			/* Make sure the user can assign terms of the profession taxonomy before proceeding. */
			if ( ! current_user_can( $tax->cap->assign_terms ) || ! current_user_can( 'edit_users' ) ) return;

			/* Get the terms of the 'profession' taxonomy. */
			$terms = get_terms( 'user-group', array( 'hide_empty' => false ) ); ?>

			<h3 id="user-groups">User Groups</h3>
			<table class="form-table">
				<tr>
					<th>
						<label for="user-group"
							   style="font-weight:bold; display:block;"><?php _e( sprintf( _n( __( 'Add to Group', 'user-groups' ), __( 'Add to Groups', 'user-groups' ), sizeof( $terms ) ) ) ); ?></label>
						<a href="<?php echo admin_url( 'edit-tags.php?taxonomy=user-group' ); ?>"><?php _e( 'Add a User Group', 'user-groups' ); ?></a>
					</th>

					<td><?php

						/* If there are any terms available, loop through them and display checkboxes. */
			if ( ! empty( $terms ) ){
				echo '<ul>';
				foreach ( $terms as $term ) {

					$color = self::get_meta( 'group-color', $term->term_id );
					if ( ! empty( $color ) ){
						$color = ' style="padding:2px .5em; border-radius:3px; background-color:' . $color . '; color:' . self::get_text_color( $color ) . '"';
					}
						?>
								<li><input type="checkbox" name="user-group[]"
										   id="user-group-<?php echo esc_attr( $term->slug ); ?>"
                                                                                   value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( true, self::is_user_in_group( $user->ID, $term->term_id ) ); ?> />
									<label
										for="user-group-<?php echo esc_attr( $term->slug ); ?>"<?php echo $color; ?>><?php echo esc_html( $term->name ); ?></label>
								</li>
			<?php
				}
				echo '</ul>';
			} /* If there are no user-group terms, display a message. */ else {
				_e( 'There are no user groups defined. <a href="' . admin_url( 'edit-tags.php?taxonomy=user-group' ) . '">' . __( 'Add a User Group', 'user-groups' ) . '</a>' );
			}
				?>
					</td>
				</tr>
			</table>
		<?php
		}

		// Code from http://serennu.com/colour/rgbtohsl.php
		static function get_text_color( $hexcode = '' )
		{
			$hexcode = str_replace( '#', '', $hexcode );

			$redhex   = substr( $hexcode, 0, 2 );
			$greenhex = substr( $hexcode, 2, 2 );
			$bluehex  = substr( $hexcode, 4, 2 );

			// $var_r, $var_g and $var_b are the three decimal fractions to be input to our RGB-to-HSL conversion routine
			$var_r = ( hexdec( $redhex ) ) / 255;
			$var_g = ( hexdec( $greenhex ) ) / 255;
			$var_b = ( hexdec( $bluehex ) ) / 255;

			$var_min = min( $var_r, $var_g, $var_b );
			$var_max = max( $var_r, $var_g, $var_b );
			$del_max = $var_max - $var_min;

			$l = ( $var_max + $var_min ) / 2;

			if ( $del_max == 0 ){
				$h = 0;
				$s = 0;
			} else {
				if ( $l < 0.5 ){
					$s = $del_max / ( $var_max + $var_min );
				} else {
					$s = $del_max / ( 2 - $var_max - $var_min );
				};

				$del_r = ( ( ( $var_max - $var_r ) / 6 ) + ( $del_max / 2 ) ) / $del_max;
				$del_g = ( ( ( $var_max - $var_g ) / 6 ) + ( $del_max / 2 ) ) / $del_max;
				$del_b = ( ( ( $var_max - $var_b ) / 6 ) + ( $del_max / 2 ) ) / $del_max;

				if ( $var_r == $var_max ){
					$h = $del_b - $del_g;
				} elseif ( $var_g == $var_max ) {
					$h = ( 1 / 3 ) + $del_r - $del_b;
				} elseif ( $var_b == $var_max ) {
					$h = ( 2 / 3 ) + $del_g - $del_r;
				};

				if ( $h < 0 ){
					$h += 1;
				};

				if ( $h > 1 ){
					$h -= 1;
				};
			};

			if ( ( $l * 100 ) < 50 ){
				return 'white';
			} else {
				return 'black';
			}
		}

		static function save_user_user_groups( $user_id, $user_groups = array(), $bulk = false )
		{
                        global $user_group_model, $pagenow;
                        
			$tax = get_taxonomy( 'user-group' );

			/* Make sure the current user can edit the user and assign terms before proceeding. */
			if ( ! current_user_can( 'edit_user', $user_id ) && current_user_can( $tax->cap->assign_terms ) ){
				return false;
			}
                       	
                        
                        $user_groups = @$_POST[ 'user-group' ];
      
                       self::add_user_in_groups( $user_id, $user_groups );
                        
	
		}

		function disable_username( $username )
		{
			if ( 'user-group' === $username ) $username = '';

			return $username;
		}

		function delete_term_relationships( $user_id )
		{
                        global $user_group_model;
			
                       wp_delete_object_term_relationships( $user_id, 'user-group' );
		}

                function add_colorpicker_field()
		{
			?>
			<tr>
				<th scope="row" valign="top"><label><?php _e( 'Color for the User Group', 'genesis' ); ?></label></th>
				<td id="group-color-row">
					<p>
						<input type="text" name="user-group[group-color]" id="group-color"
							   value="<?php echo esc_html( self::get_meta( 'group-color' ) ); ?>"/>
						<span
							class="description hide-if-js">If you want to hide header text, add <strong>#blank</strong> as text color.</span>
						<input type="button" class="button hide-if-no-js" value="Select a Color" id="pickcolor"/>
					</p>

					<div id="color-picker"
						 style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
				</td>
			</tr>
		<?php
		}

		function hide_slug()
		{
			if ( self::is_edit_user_group( 'all' ) ){
				?>
				<style type="text/css">
					.form-wrap form span.description {
						display: none !important;
					}
				</style>

				<script type="text/javascript">
					jQuery(document).ready(function ($) {
//						$('#menu-posts').removeClass('wp-menu-open wp-has-current-submenu').addClass('wp-not-current-submenu');
//						$('#menu-users').addClass('wp-has-current-submenu wp-menu-open menu-top menu-top-first').removeClass('wp-not-current-submenu');
//						$('#menu-users a.wp-has-submenu').addClass('wp-has-current-submenu wp-menu-open menu-top');
//						$('#menu-posts a.wp-has-submenu').removeClass('wp-has-current-submenu wp-menu-open menu-top');
						$('#tag-slug').parent('div.form-field').hide();
						$('.inline-edit-col input[name=slug]').parents('label').hide();
					});
				</script>
			<?php
			} elseif ( self::is_edit_user_group( 'edit' ) ) {
				?>
				<style type="text/css">
					.form-table .form-field td span.description, .form-table .form-field {
						display: none;
					}
				</style>
				<script type="text/javascript">
					jQuery(document).ready(function ($) {
//						$('#menu-posts').removeClass('wp-menu-open wp-has-current-submenu').addClass('wp-not-current-submenu');
//						$('#menu-users').addClass('wp-has-current-submenu wp-menu-open menu-top menu-top-first').removeClass('wp-not-current-submenu');
//						$('#menu-users a.wp-has-submenu').addClass('wp-has-current-submenu wp-menu-open menu-top');
//						$('#menu-posts a.wp-has-submenu').removeClass('wp-has-current-submenu wp-menu-open menu-top');
						$('#edittag #slug').parents('tr.form-field').addClass('hide-if-js');
						$('.form-table .form-field').not('.hide-if-js').css('display', 'table-row');
					});
				</script>
			<?php
			}
		}

		// Get rid of theme, plugin crap for other taxonomies.
		function remove_add_form_actions( $taxonomy )
		{
			//remove_all_actions('after-user-group-table');
			//remove_all_actions('user-group_edit_form');
			//remove_all_actions('user-group_add_form_fields');

			// If you use Rich Text tags, go ahead!
			if ( function_exists( 'kws_rich_text_tags' ) ){
				add_action( 'user-group_edit_form_fields', 'kws_add_form' );
				add_action( 'user-group_add_form_fields', 'kws_add_form' );
			}

			add_action( 'user-group_add_form_fields', array( &$this, 'user_group_taxonomy_add_new_meta_field' ), 10, 2 );
			add_action( 'user-group_add_form_fields', array( &$this, 'add_form_color_field' ), 11, 2 );
			add_action( 'user-group_edit_form_fields', array( &$this, 'user_group_taxonomy_edit_meta_field' ), 10, 2 );
			add_action( 'user-group_edit_form', array( &$this, 'add_form_color_field' ), 11, 2 );
		}

		/**
		 * Adds Email Address field in User Group Taxonomy
		 */
		function user_group_taxonomy_add_new_meta_field()
		{
			?>
			<div class="form-field">
				<label for="term_meta[email_address]"><?php _e( 'Email Address', 'rtcamp' ); ?></label>
				<input type="text" name="user-group[email_address]" id="user-group[email_address]" value="">

				<p class="description"><?php _e( 'Enter a Email address for this field', 'rtcamp' ); ?></p>
			</div>
		<?php
		}

		/**
		 * Edit User-Group
		 *
		 * @param type $term
		 */
		function user_group_taxonomy_edit_meta_field( $term )
		{
			$t_id      = $term->term_id;
			$term_meta = '';
			if ( is_multisite() ) $term_meta = get_site_option( 'user-group-meta' ); else
				$term_meta = get_option( 'user-group-meta' );
			?>
			<tr class="form-field">
				<th scope="row" valign="top"><label
						for="term_meta[email_address]"><?php _e( 'Email Address', 'rtCamp' ); ?></label></th>
				<td>
					<input type="text" name="user-group[email_address]" id="user-group[email_address]"
						   value="<?php echo esc_attr( $term_meta[ $t_id ][ 'email_address' ] ) ? esc_attr( $term_meta[ $t_id ][ 'email_address' ] ) : ''; ?>"/>

					<p class="description"><?php _e( 'Enter a email address for this field', 'rtcamp' ); ?></p>
				</td>
			</tr>
		<?php
		}



		function add_form_color_field( $tag, $taxonomy = '' )
		{

			$tax = get_taxonomy( $taxonomy );

			if ( self::is_edit_user_group( 'edit' ) ){
				?>

				<h3><?php _e( 'User Group Settings', 'user-group' ); ?></h3>

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row" valign="top">
							<label><?php _e( 'Color for the User Group', 'genesis' ); ?></label></th>
						<td id="group-color-row">
							<p>
								<input type="text" name="user-group[group-color]" id="group-color"
									   value="<?php echo esc_html( self::get_meta( 'group-color' ) ); ?>"/>
								<input type="button" class="button hide-if-no-js" value="Select a Color"
									   id="pickcolor"/>
							</p>

							<div id="color-picker"
								 style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
							<div class="clear"></div>
						</td>
					</tr>
					</tbody>
				</table>
			<?php } else { ?>
				<div class="form-field">
					<p>
						<input type="text" style="width:40%" name="user-group[group-color]" id="group-color"
							   value="<?php echo esc_html( self::get_meta( 'group-color' ) ); ?>"/>
						<input type="button" style="margin-left:.5em;width:auto!important;" class="button hide-if-no-js"
							   value="Select a Color" id="pickcolor"/>
					</p>
				</div>
				<div id="color-picker"
					 style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
			<?php
			}
		}

		function bulk_edit_action()
		{
			if ( ! isset( $_REQUEST[ 'bulkeditusergroupsubmit' ] ) || empty( $_POST[ 'user-group' ] ) ){
				return;
			}

			check_admin_referer( 'bulk-edit-user-group' );

			// Get an array of users from the string
			parse_str( urldecode( $_POST[ 'users' ] ), $users );

			if ( empty( $users ) ){
				return;
			}

			$action = $_POST[ 'groupaction' ];

			foreach ( $users[ 'users' ] as $user ) {
				$update_groups = array();
				$groups        = self::get_users_groups( $user );
                                
				foreach ( $groups as $group ) {
					$update_groups[ $group->slug ] = $group->slug;
				}

				if ( $action === 'add' ){
					if ( ! in_array( $_POST[ 'user-group' ], $update_groups ) ){
						self::add_user_in_group($user, $_POST[ 'user-group' ] );
                                               
					}
				} elseif ( $action === 'remove' ) {
					self::remove_user_group($user, $_POST[ 'user-group' ] );
				}

			}
		}

		function bulk_edit( $views )
		{
			if ( ! current_user_can( 'edit_users' ) ){
				return $views;
			}
			$terms = get_terms( 'user-group', array( 'hide_empty' => false ) );
			?>
			<form method="post" id="bulkeditusergroupform" class="alignright" style="clear:right; margin:0 10px;">
				<fieldset>
					<legend class="screen-reader-text"><?php _e( 'Update User Groups', 'user-group' ); ?></legend>
					<div>
						<label for="groupactionadd" style="margin-right:5px;"><input name="groupaction" value="add"
																					 type="radio" id="groupactionadd"
																					 checked="checked"/> <?php _e( 'Add users to', 'user-group' ); ?>
						</label>
						<label for="groupactionremove"><input name="groupaction" value="remove" type="radio"
															  id="groupactionremove"/> <?php _e( 'Remove users from', 'user-group' ); ?>
						</label>
					</div>
					<div>
						<input name="users" value="" type="hidden" id="bulkeditusergroupusers"/>

						<label for="user-groups-select"
							   class="screen-reader-text"><?php _e( 'User Groups', 'rtCamp' ); ?></label>
						<select name="user-group" id="user-groups-select" style="max-width: 300px;">
			<?php
			$select = '<option value="">' . __( 'Select User Group&hellip;', 'user-group' ) . '</option>';
			foreach ( $terms as $term ) {
				$select .= '<option value="' . $term->slug . '">' . $term->name . '</option>' . "\n";
			}
			echo $select;
				?>
						</select>
						<?php wp_nonce_field( 'bulk-edit-user-group' ) ?>
					</div>
					<div class="clear" style="margin-top:.5em;">
						<?php submit_button( __( 'Update' ), 'small', 'bulkeditusergroupsubmit', false ); ?>
					</div>
				</fieldset>
			</form>
			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					$('#bulkeditusergroupform').remove().insertAfter('ul.subsubsub');
					$('#bulkeditusergroupform').live('submit', function () {
						var users = $('.wp-list-table.users .check-column input:checked').serialize();
						$('#bulkeditusergroupusers').val(users);
					});
				});
			</script>
			<?php
			return $views;
		}

		function views( $views )
		{
			global $wp_roles;
			$terms = get_terms( 'user-group', array( 'hide_empty' => true ) );

			$select  = '<select name="user-group" id="user-groups-select">
			<option value="0">All Users</option>' . "\n";
			$current = false;
			foreach ( $terms as $term ) {
				$user_ids = get_objects_in_term( $term->term_id, 'user-group' );
				if ( isset( $_GET[ 'user-group' ] ) && $_GET[ 'user-group' ] === $term->slug ){
					$current = $term;
				}
				$select .= '<option value="' . $term->slug . '"' . selected( true, isset( $_GET[ 'user-group' ] ) && $_GET[ 'user-group' ] === $term->slug, false ) . '>' . $term->name . '</option>' . "\n";
			}

			$select .= '</select>';

			if ( $current ){
				$bgcolor   = self::get_meta( 'group-color', $current->term_id );
				$color     = self::get_text_color( $bgcolor );
				$roleli    = '';
				$role      = false;
				$role_name = __( 'users', 'user-group' );
				if ( isset( $_GET[ 'role' ] ) ){
					$role  = $_GET[ 'role' ];
					$roles = $wp_roles->get_names();
					if ( array_key_exists( $role, $roles ) ){
						$role_name = $roles[ "{$role}" ];
						if ( substr( $role_name, - 1, 1 ) !== 's' ){
							$role_name .= 's';
						}
					}
					$roleli = sprintf( '%sRemove Role Filter%s', '<li><a href="' . remove_query_arg( 'role' ) . '">', '</li>' );
				}

				$colorblock = empty( $bgcolor ) ? '' : '<span style="width:1.18em; height:1.18em; float:left; margin-right:.25em; background-color:' . self::get_meta( 'group-color', $current->term_id ) . ';"></span>';

				?>
				<div id="user-group-header">
					<h2><?php echo $colorblock;
						echo esc_html( sprintf( __( 'User Group: %s', 'user-group' ), $current->name ) ); ?> <a
							href="<?php echo admin_url( 'edit-tags.php?action=edit&taxonomy=user-group&tag_ID=' . $current->term_id . '&post_type=post' ); ?>"
							class="add-new-h2"
							style="background:#fefefe;"><?php _e( 'Edit User Group', 'user-group' ); ?></a></h2>
					<?php echo wpautop( $current->description ) ; ?>
				</div>
				<p class="howto" style="font-style:normal;">
					<span><?php echo esc_html( sprintf( __( 'Showing %s in %s', 'user-group' ), $role_name, '&ldquo;' . $current->name . '&rdquo;' ) ); ?>
						.</span>

					<a href="<?php echo esc_html( remove_query_arg( 'user-group' ) ); ?>"
					   class="user-group-user-group-filter"><span></span> <?php echo esc_html( sprintf( __( 'Show all %s', 'user-group' ), $role_name ) ); ?>
					</a>

					<?php if ( ! empty( $role ) ){ ?>
						<a href="<?php echo esc_html( remove_query_arg( 'role' ) ); ?>"
						   class="user-group-user-group-filter"><span></span> <?php echo esc_html( sprintf( __( 'Show all users in "%s"', 'user-group' ), $current->name ) ); ?>
						</a>
					<?php } ?>
				</p>
				<div class="clear"></div>
			<?php
			}

			ob_start();

			$args = array();
			if ( isset( $_GET[ 's' ] ) ){
				$args[ 's' ] = $_GET[ 's' ];
			}
			if ( isset( $_GET[ 'role' ] ) ){
				$args[ 'role' ] = $_GET[ 'role' ];
			}

			?>
			<label for="user-groups-select"><?php _e( 'User Groups:', 'user-group' ); ?></label>

			<form method="get"
				  action="<?php echo preg_replace( '/(.*?)\/users/ism', 'users', add_query_arg( $args, remove_query_arg( 'user-group' ) ) ); ?>"
				  style="display:inline;">
				<?php echo $select; ?>
			</form>
			<style type="text/css">
				.subsubsub li.user-group {
					display: inline-block !important;
				}
			</style>
			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					<?php if ( isset( $_GET['user-group'] ) ) { ?>
					$('ul.subsubsub li a').each(function () {
						var $that = $(this);
						$(this).attr('href', function () {
							var sep = $that.attr('href').match(/\?/i) ? '&' : '?';
							return $( this ).attr( 'href' ) + sep + 'user-group=<?php echo esc_attr( $_GET['user-group'] ); ?>';
						});
					});
					<?php } ?>
					$("#user-groups-select").change(function () {
						var action = $(this).parents("form").attr('action');
						if (action.match(/\?/i)) {
							action = action + '&user-group=' + $(this).val();
						} else {
							action = action + '?user-group=' + $(this).val();
						}

						window.location = action;
					});
				});
			</script>

			<?php
			$form = ob_get_clean();

			$views[ 'user-group' ] = $form;

			return $views;

		}

		function user_query( $Query = '' )
		{
			global $pagenow, $wpdb;

			if ( $pagenow !== 'users.php' ){
				return;
			}

			if ( ! empty( $_GET[ 'user-group' ] ) ){

				$groups = explode( ',', $_GET[ 'user-group' ] );
				$ids    = array();
				foreach ( $groups as $group ) {
					$term     = get_term_by( 'slug', esc_attr( $group ), 'user-group' );
					//$user_ids = get_objects_in_term( $term->term_id, 'user-group' );
					$user_ids = self::get_groups_users( $term->term_id );
                                     
					$ids      = array_merge( $user_ids, $ids );
				}
				$ids = implode( ',', wp_parse_id_list( $user_ids ) );

				if ( ! empty( $ids ) ) {
					$Query->query_where .= " AND $wpdb->users.ID IN ( $ids )";
				}

			}

		}

		function css_includes()
		{
			if ( ! self::is_edit_user_group() ){
				return;
			}
			wp_enqueue_style( 'farbtastic', array( 'jquery' ) );
		}

		function js_includes()
		{
			if ( ! self::is_edit_user_group() ){
				return;
			}
			wp_enqueue_script( 'farbtastic', array( 'jquery' ) );
		}

		function user_column_data( $value, $column_name, $user_id )
		{
			switch ( $column_name ) {
				case 'user-group':
					return self::get_user_user_group_tags( $user_id );
					break;
			}
			return $value;
		}

		function add_manage_users_columns( $defaults )
		{

			$defaults[ 'user-group' ] = __( 'User Group', 'user-group' );

			return $defaults;
		}

		function colorpicker()
		{

			if ( ! self::is_edit_user_group() ){
				return;
			}

			?>
			<script type="text/javascript">
				/* <![CDATA[ */
				var farbtastic;
				var default_color = '#333';
				var old_color = null;

				function pickColor(color) {
					jQuery('#group-color').val(color).css('background', color);
					farbtastic.setColor(color);
					jQuery('#group-color').processColor((farbtastic.hsl[2] * 100), (farbtastic.hsl[1] * 100));
				}

				jQuery(document).ready(function () {

					jQuery('#pickcolor,#group-color').click(function () {
						jQuery('#color-picker').show();
					});

					jQuery('#defaultcolor').click(function () {
						pickColor(default_color);
						jQuery('#group-color').val(default_color).css('background', default_color)
					});

					jQuery('#group-color').keyup(function () {
						var _hex = jQuery('#group-color').val();
						var hex = _hex;
						if (hex[0] != '#')
							hex = '#' + hex;
						hex = hex.replace(/[^#a-fA-F0-9]+/, '');
						if (hex != _hex)
							jQuery('#group-color').val(hex).css('background', hex);
						if (hex.length == 4 || hex.length == 7)
							pickColor(hex);
					});

					jQuery(document).mousedown(function () {
						jQuery('#color-picker').each(function () {
							var display = jQuery(this).css('display');
							if (display == 'block')
								jQuery(this).fadeOut(2);
						});
					});

					farbtastic = jQuery.farbtastic('#color-picker', function (color) {
						pickColor(color);
					});
					pickColor(jQuery('#group-color').val());
				});

				jQuery.fn.processColor = function (black, sat) {
					if (sat > 40) {
						black = black - 10;
					}

					if (black <= 50) {
						jQuery(this).css('color', '#ffffff');
					} else {
						jQuery(this).css('color', 'black');
					}
				};
				/* ]]> */
			</script>
		<?php
		}
                
                static function get_meta( $key = '', $term_id = 0 )
		{

			if ( isset( $_GET[ 'tag_ID' ] ) ){
				$term_id = $_GET[ 'tag_ID' ];
			}
			if ( empty( $term_id ) ){
				return false;
			}

			$term_meta = (array)get_option( 'user-group-meta' );

			if ( ! isset( $term_meta[ $term_id ] ) ){
				return false;
			}

			if ( ! empty( $key ) ){
				return isset( $term_meta[ $term_id ][ $key ] ) ? $term_meta[ $term_id ][ $key ] : false;
			} else {
				return $term_meta[ $term_id ];
			}

		}

		static function get_user_user_group_tags( $user, $page = null )
		{
			$terms = self::get_users_groups( $user );

			if ( empty( $terms ) ){
				return false;
			}
			$in = array();
			foreach ( $terms as $term ) {
				$href  = empty( $page ) ? add_query_arg( array( 'user-group' => $term->slug ), admin_url( 'users.php' ) ) : add_query_arg( array( 'user-group' => $term->slug ), $page );
				$color = self::get_meta( 'group-color', $term->term_id );
				$in[ ] = sprintf( '%s%s%s', '<a style="text-decoration:none; color:white; cursor: pointer; border:0; padding:2px 3px; margin:0 .3em .2em 0; border-radius:3px; background-color:' . $color . '; color:' . self::get_text_color( $color ) . ';" href="' . $href . '" title="' . esc_attr( $term->description ) . '">', $term->name, '</a>' );
			}
                       
			return implode( '', $in );
                        
                        
		}

                static function is_edit_user_group( $page = false )
		{
			global $pagenow;

			if ( ( ! $page || $page === 'edit' ) && $pagenow === 'edit-tags.php' && isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' && isset( $_GET[ 'taxonomy' ] ) && $_GET[ 'taxonomy' ] === 'user-group'
			){
				return true;
			}

			if ( ( ! $page || $page === 'all' ) && $pagenow === 'edit-tags.php' && isset( $_GET[ 'taxonomy' ] ) && $_GET[ 'taxonomy' ] === 'user-group' && ( ! isset( $_GET[ 'action' ] ) || $_GET[ 'action' ] !== 'edit' )
			){
				return true;
			}

			return false;
		}
                
                static function get_department_support(){
                    
                    $supports = array( 'user' );
                    
                    foreach ( Rt_Access_Control::$modules as $module ) {
                        
                        foreach ( $module['post_types'] as $post_type ) {
                            
                           $supports[] = $post_type;
                        }
                    }
                    
                    return $supports;
                }
                
                /**
                 * Map user with list of given groups name
                 * @global type $user_group_model
                 * @global type $pagenow
                 * @param type $user_id
                 * @param type $groups
                 * @param type $remove_old
                 */
                static function add_user_in_groups( $user_id, $groups ){
     
                    global $user_group_model;
        
                    $old_terms = self::get_users_groups( $user_id );
                    
                    $old_groups = array();
                    
                    foreach ($old_terms as $old_term) {
                        
                        $old_groups[] = $old_term->slug;
                        
                    }
                    
                    $diff = array_diff($old_groups, $groups);
                    
                    
                    
                    self::remove_user_groups($user_id, $diff);
                  
                    
                    foreach ( $groups as $group_name ) {
                        
                         $term = get_term_by('slug' , $group_name, 'user-group');

                        $data = array(
                                    'user_id' => $user_id,
                                    'term_taxonomy_id' => $term->term_id,
                                    'term_order' => 0,

                         );
                        
                            $user_group_model->insert( $data );
                    }
                }
                
                /**
                 * Map user with give group name
                 * @global type $user_group_model
                 * @global type $pagenow
                 * @param type $user_id
                 * @param type $group_name
                 */
                static function add_user_in_group( $user_id, $group_name ){
     
                    global $user_group_model;

                    $term = get_term_by('slug' , $group_name, 'user-group');

                    $data = array(
                            'user_id' => $user_id,
                            'term_taxonomy_id' => $term->term_id,
                            'term_order' => 0,

                    );
                    
                    $user_group_model->insert( $data );
                    
                }
                
                /**
                 * Unmap user from given list of group name
                 * @global type $user_group_model
                 * @param type $user_id
                 */
                static function remove_user_groups( $user_id, $groups ){
                    global $user_group_model;
                     
                    
                    foreach ($groups as $group_name) {
                        
                        $term = get_term_by( 'slug', esc_attr( $group_name ), 'user-group' );
                        
                        $where = array(
                            'user_id' => $user_id,
                            'term_taxonomy_id' => $term->term_id,
                        );

                        $user_group_model->delete( $where );
                    }
                    
                }
                
                /**
                 * Unmap user from given group name
                 * @global type $user_group_model
                 * @param type $user_id
                 * @param type $group_name
                 */
                static function remove_user_group( $user_id, $group_name){
                    global $user_group_model;
                    $term = get_term_by( 'slug', esc_attr( $group_name ), 'user-group' );
                   
                    $where = array(
                        'user_id' => $user_id,
                        'term_taxonomy_id' => $term->term_id,
                    );
                            
                    $user_group_model->delete( $where );

                }

                /**
                 * Check user is belong to specified group or not.
                 * @global type $user_group_model
                 * @param type $user_id
                 * @param type $group_id
                 * @return boolean
                 */
                static function is_user_in_group( $user_id, $group_id ){
                    global $user_group_model;
                    
                    $columns = array(
                        'user_id' => $user_id,
                        'term_taxonomy_id' => $group_id,
                    );
                    
                    $all_users_group = $user_group_model->get( $columns );
                    
                   
                    if ( count($all_users_group) > 0 ) {
                        return true;
                    }else{
                        return false;
                    }
                    
                }
                
                /**
                 * Get all groups details of given user
                 * @global type $user_group_model
                 * @param type $user_id
                 * @return type array of term objects
                 */
                static function get_users_groups( $user_id ){
                     global $user_group_model;
                   
                      $columns = array(
                        'user_id' => $user_id,
                    );
                      
                   $groups = $user_group_model->get( $columns );
                    
                   $terms =  array(
                       
                   );
                   
                   foreach ( $groups as $group ) {
                       
                       $terms[] = get_term_by( 'id', esc_attr( $group->term_taxonomy_id ), 'user-group' );
                   
                   }
                   
                   return $terms;
                }
                
                /**
                 * Get all users of given group
                 * @global type $user_group_model
                 * @param type $group_id
                 * @return type array of user_ids
                 */
                static function get_groups_users( $group_id ){
                     global $user_group_model;
                   
                      $columns = array(
                        'term_taxonomy_id' => $group_id,
                    );
                      
                   $users = $user_group_model->get( $columns );
            
                   $user_ids = array();
                   
                   foreach ( $users as $user ) {
                       
                       $user_ids[] = $user->user_id;
                   
                   }
                   return $user_ids;
                }
                
                
            



	}

}