<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Description of class-rt-user-groups
 * To create User group
 *
 * @author dipesh
 * Version 0.0.1
 */

if ( ! class_exists( 'RT_User_Groups' ) ) {

	class RT_User_Groups {

		/**
		 * @var $user_group_slug - User group slug
		 */
		static $user_group_slug = '';

		/**
		 * @var $labels - Lable array
		 */
		var $labels = array();

		/**
		 * @var $caps - Capability for taxonomy
		 */
		var $caps = array();

		/**
		 * @var $multiple - allows to select multiple group or single
		 */
		var $multiple = true;

		var $to_register_posttype = array();

		/**
		 * Constructor
		 *
		 * @param       $slug
		 * @param       $label
		 * @param       $caps
		 * @param array $post_types add post_types on which you want support for user group
		 * @param bool $allow_multiple_select
		 */
		public function __construct( $slug, $label, $caps, $post_types = array(), $allow_multiple_select = true ) {

			$this->to_register_posttype = $post_types;
			self::$user_group_slug      = $slug;
			$this->labels               = $label;
			$this->caps                 = $caps;
			$this->multiple             = $allow_multiple_select;

			self::auto_loader();

			$this->init();

			$this->db_upgrade();

			add_action( 'plugins_loaded', array( $this, 'register_user_group' ), 21 );
			add_action( 'create_term', array( $this, 'save_user_group' ), 10, 2 );
			add_action( 'edit_term', array( $this, 'save_user_group' ), 10, 2 );

			add_filter( self::$user_group_slug . '_row_actions', array( $this, 'row_actions' ), 1, 2 );
			add_action( 'manage_' . self::$user_group_slug . '_custom_column', array(
				$this,
				'manage_user_group_user_column_ui'
			), 10, 3 );
			add_filter( 'manage_edit-' . self::$user_group_slug . '_columns', array(
				$this,
				'manage_user_group_user_column'
			) );

			add_action( 'admin_print_scripts', array( $this, 'js_includes' ) );
			add_action( 'admin_print_styles', array( $this, 'css_includes' ) );
			add_action( 'admin_head', array( $this, 'colorpicker' ) );
			add_action( 'admin_head', array( $this, 'hide_slug' ) );

			add_action( 'admin_init', array( $this, 'add_remove_user_group_field' ), 1000 );

			//User.php
			add_filter( 'manage_users_columns', array( $this, 'add_group_columns_user' ), 15, 1 );
			add_action( 'manage_users_custom_column', array( $this, 'add_group_columns_user_ui' ), 15, 3 );

			/* Bulk edit */
			add_action( 'admin_init', array( $this, 'bulk_edit_action' ) );
			add_filter( 'restrict_manage_users', array( $this, 'bulk_edit' ) );

			/* Add section to the edit user page in the admin to select profession. */
			add_action( 'show_user_profile', array( $this, 'edit_user_user_group_section' ), 99999 );
			add_action( 'edit_user_profile', array( $this, 'edit_user_user_group_section' ), 99999 );

			/* Update the user group when the edit user page is updated. */
			add_action( 'personal_options_update', array( $this, 'save_user_user_group' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_user_user_group' ) );

			add_action( 'pre_user_query', array( $this, 'user_query' ) );

			/* Achieve filtering by User group. on User page. */
			add_filter( 'views_users', array( $this, 'user_view_filters' ) );

			add_action( 'delete_user', array( $this, 'delete_term_relationships' ) );
			add_filter( 'sanitize_user', array( $this, 'disable_username' ) );
			add_action( 'wp_ajax_remove_group', array( $this, 'remove_group' ) );

		}

		public function remove_group() {
			$flag = false;
			if ( isset( $_POST['user'] ) && ! empty( $_POST['user'] ) && isset( $_POST['groupslug'] ) && ! empty( $_POST['groupslug'] ) ) {
				$this->remove_user_group( $_POST['user'], $_POST['groupslug'] );
				$flag = true;
			}
			echo json_encode( array( 'status' => $flag ) );
			die();
		}

		/**
		 * Auto loader for model classes
		 */
		static function auto_loader() {
			$auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'model/' );
		}

		/**
		 * Init helper objects
		 * @global RT_User_Groups_Relationships_Model $rtlib_user_group_model
		 */
		function init() {
			//			$rtlib_user_group_model = new RT_User_Groups_Relationships_Model();
			//			$rtlib_user_group_model = new RT_User_Groups_Relationships_Model();
		}

		/**
		 * Update database for user group
		 */
		function db_upgrade() {
			$updateDB = new  RT_DB_Update( RT_LIB_FILE, trailingslashit( dirname( __FILE__ ) ) . 'schema/' );
			$updateDB->db_version_option_name .= '_USER_GROUPS';
			$updateDB->install_db_version = $updateDB->get_install_db_version();
			$updateDB->do_upgrade();
		}

		/**
		 * Register User group
		 */
		function register_user_group() {

			$arg          = array(
				'public'       => true,
				'labels'       => $this->labels,
				'rewrite'      => false,
				'capabilities' => $this->caps,
				'hierarchical' => true,
			);
			$tmp_posttype = array( 'user' );
			if ( ! empty( $this->to_register_posttype ) ) {
				$tmp_posttype = $this->to_register_posttype;
			}
			$supports = apply_filters( 'rtlib_user_group_support', $tmp_posttype );
			register_taxonomy( self::$user_group_slug, $supports, $arg );
		}

		/**
		 * Save User group
		 *
		 * @param type $term_id
		 */
		function save_user_group( $term_id ) {
			if ( isset( $_POST[ self::$user_group_slug ] ) ) {
				$term_meta             = (array) get_option( self::$user_group_slug . '-meta' );
				$term_meta[ $term_id ] = (array) $_POST[ self::$user_group_slug ];
				update_option( self::$user_group_slug . '-meta', $term_meta );
				if ( isset( $_POST['_wp_original_http_referer'] ) ) {
					wp_safe_redirect( $_POST['_wp_original_http_referer'] );
					exit();
				}
			}
		}

		/**
		 * add view action for group
		 */
		function row_actions( $actions, $term ) {
			$actions['view'] = sprintf( __( '%sView%s', 'rtlib' ), '<a href="' . esc_url( add_query_arg( array( self::$user_group_slug => $term->slug ) ), admin_url( 'users.php' ) ) . '">', '</a>' );

			return $actions;
		}

		/**
		 * add group List View columns
		 *
		 * @param type $columns
		 *
		 * @return type
		 */
		function manage_user_group_user_column( $columns ) {

			unset( $columns['posts'], $columns['slug'] );

			$columns['users']         = __( 'Users', 'rtlib' );
			$columns['color']         = __( 'Color', 'rtlib' );
			$columns['email_address'] = __( 'Email Address', 'rtlib' );

			return $columns;
		}

		/**
		 * UI for group List View custom Columns
		 *
		 * @param type $display
		 * @param type $column
		 * @param type $term_id
		 *
		 * @return type
		 */
		function manage_user_group_user_column_ui( $display, $column, $term_id ) {
			switch ( $column ) {
				case 'users':
					$term  = get_term( $term_id, self::$user_group_slug );
					$users = self::get_user_by_group_slug( $term->slug );
					echo '<a href="' . esc_url( admin_url( 'users.php?' . self::$user_group_slug . '=' . $term->slug ) ) . '">' . sanitize_title( sprintf( _n( '%s Users', '%s Users', count( $users ), 'rtlib' ), count( $users ) ) );
					break;
				case 'color':
					$color = $this->get_group_meta( 'group-color', $term_id );
					if ( ! empty( $color ) ) {
						echo '<div style="width:3.18em; height:3em; background-color:' . esc_attr( $this->get_group_meta( 'group-color', $term_id ) ) . ';"></div>';
					}
					break;
				case 'email_address';
					$email_address = $this->get_group_meta( 'email_address', $term_id );
					if ( isset( $email_address ) && ! empty( $email_address ) ) {
						echo esc_html( $email_address );
					}
					break;
			}

			return;
		}

		/**
		 * Add jQuery for User group
		 * @return type
		 */
		function js_includes() {
			if ( ! $this->is_edit_user_group() ) {
				return;
			}
			wp_enqueue_script( 'farbtastic', array( 'jquery' ) );
		}

		/**
		 * Add CSS for User group
		 * @return type
		 */
		function css_includes() {
			if ( ! $this->is_edit_user_group() ) {
				return;
			}
			wp_enqueue_style( 'farbtastic', array( 'jquery' ) );
		}

		/**
		 * Configure Colorpicker
		 * @return type
		 */
		function colorpicker() {

			if ( ! $this->is_edit_user_group() ) {
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
			</script><?php
		}

		/**
		 * hide_slug
		 */
		function hide_slug() {
			if ( $this->is_edit_user_group( 'all' ) ) {
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
				</script><?php
			} elseif ( $this->is_edit_user_group( 'edit' ) ) {
				?>
				<style type="text/css">
					.form-table .form-field td span.description, .form-table .form-field {
						display: none;
					}
				</style>
				<script type="text/javascript">
					jQuery(document).ready(function ($) {
						//$('#menu-posts').removeClass('wp-menu-open wp-has-current-submenu').addClass('wp-not-current-submenu');
						//$('#menu-users').addClass('wp-has-current-submenu wp-menu-open menu-top menu-top-first').removeClass('wp-not-current-submenu');
						//$('#menu-users a.wp-has-submenu').addClass('wp-has-current-submenu wp-menu-open menu-top');
						//$('#menu-posts a.wp-has-submenu').removeClass('wp-has-current-submenu wp-menu-open menu-top');
						$('#edittag #slug').parents('tr.form-field').addClass('hide-if-js');
						$('.form-table .form-field').not('.hide-if-js').css('display', 'table-row');
					});
				</script> <?php
			}
		}

		/**
		 * Add custom fields for user group
		 *
		 * @param type $group
		 */
		function add_remove_user_group_field( $group ) {
			//remove_all_actions('after-user-group-table');
			//remove_all_actions('user-group_edit_form');
			//remove_all_actions('user-group_add_form_fields');
			// If you use Rich Text tags, go ahead!
			if ( function_exists( 'kws_rich_text_tags' ) ) {
				add_action( self::$user_group_slug . '_edit_form_fields', 'kws_add_form' );
				add_action( self::$user_group_slug . '_add_form_fields', 'kws_add_form' );
			}
			add_action( self::$user_group_slug . '_add_form_fields', array(
				$this,
				'user_group_group_add_new_meta_field'
			), 10, 2 );
			add_action( self::$user_group_slug . '_add_form_fields', array( $this, 'add_form_color_field' ), 11, 2 );
			add_action( self::$user_group_slug . '_edit_form_fields', array(
				$this,
				'user_group_group_edit_meta_field'
			), 10, 2 );
			add_action( self::$user_group_slug . '_edit_form', array( $this, 'add_form_color_field' ), 11, 2 );
		}

		/**
		 * Add User taxomony column on user list view
		 *
		 * @param array $defaults
		 *
		 * @return type
		 */
		function add_group_columns_user( $defaults ) {
			$defaults[ self::$user_group_slug ] = $this->labels['name'];

			return $defaults;
		}

		/**
		 * Add UI for User taxomony column on user list view
		 *
		 * @param type $value
		 * @param type $column_name
		 * @param type $user_id
		 *
		 * @return boolean
		 */
		function add_group_columns_user_ui( $value, $column_name, $user_id ) {
			switch ( $column_name ) {
				case self::$user_group_slug:
					//return self::get_user_user_group_tags( $user_id );
					$terms = self::get_user_groups( $user_id );
					if ( empty( $terms ) ) {
						return false;
					}
					$in = array();
					foreach ( $terms as $term ) {
						$href  = empty( $page ) ? add_query_arg( array( self::$user_group_slug => $term->slug ), admin_url( 'users.php' ) ) : add_query_arg( array( self::$user_group_slug => $term->slug ), $page );
						$color = $this->get_group_meta( 'group-color', $term->term_id );
						if ( ! isset( $color ) || empty( $color ) ) {
							$color = '#FFF';
						}
						$in[] = sprintf( '%s%s%s', '<input type="hidden" class="rthdgroupslug" value=' . $term->slug . '><a style="text-decoration:none; cursor: pointer; border:0; padding:2px 3px; margin:0 .3em .2em 0; border-radius:3px; background-color:' . $color . '; color:' . self::get_text_color( $color ) . ';float:left;" href="' . esc_url( $href ) . '" title="' . esc_attr( $term->description ) . '">', $term->name, '</a><a class="removeUserGroup" style="margin-left: -15px;margin-top: -10px;position: relative;display: block; float: left; text-align: center; color: ' . self::get_text_color( $color ) . '; background-color: ' . $color . '; border-radius: 100%; padding: 0 5px;box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.2); text-decoration: none;">x</a>' );
					}

					return implode( '', $in );
					break;
			}

			return $value;
		}

		/**
		 * Perform bulk action for User group on User.php
		 * @return type
		 */
		function bulk_edit_action() {
			$usergroupkey = self::$user_group_slug . '-select';
			if ( ! isset( $_REQUEST[ 'bulk_edit_' . self::$user_group_slug . '_submit' ] ) || empty( $_REQUEST[ $usergroupkey ] ) ) {
				return;
			}
			//			check_admin_referer( 'bulk-edit-' . self::$user_group_slug );

			// Get an array of users from the string
			parse_str( urldecode( $_REQUEST[ 'users_' . self::$user_group_slug ] ), $users );

			if ( empty( $users ) ) {
				return;
			}
			$action = $_REQUEST[ 'groupaction_' . self::$user_group_slug ];

			foreach ( $users['users'] as $user ) {
				$update_groups = array();
				$groups        = self::get_user_groups( $user );

				foreach ( $groups as $group ) {
					$update_groups[ $group->slug ] = $group->slug;
				}

				if ( 'add' === $action ) {
					if ( ! in_array( $_REQUEST[ $usergroupkey ], $update_groups ) ) {
						$this->set_user_group( $user, $_REQUEST[ $usergroupkey ] );
					}
				} elseif ( 'remove' === $action ) {
					if ( in_array( $_REQUEST[ $usergroupkey ], $update_groups ) ) {
						$this->remove_user_group( $user, $_REQUEST[ $usergroupkey ] );
					}
				}
			}
		}

		/**
		 * UI for bulk action on user.php
		 */
		function bulk_edit( $views ) {
			if ( ! current_user_can( 'assign_terms' ) ) {
				return $views;
			}
			$terms = get_terms( self::$user_group_slug, array( 'hide_empty' => false ) );
			?>
			<label for="groupactionadd_<?php echo esc_attr( self::$user_group_slug ); ?>"
			       style="margin-right:5px; display: none;">
				<input name="groupaction_<?php echo esc_attr( self::$user_group_slug ); ?>" value="add" type="radio"
				       id="$groupactionadd_<?php esc_attr( self::$user_group_slug ); ?>"
				       checked="checked"/> <?php _e( 'Add users to', 'rtlib' ); ?>
			</label>
			<input name="users_<?php echo esc_attr( self::$user_group_slug ); ?>" value="" type="hidden"
			       id="bulk_edit_<?php echo esc_attr( self::$user_group_slug ); ?>_users"/>

			<label for="<?php echo esc_attr( self::$user_group_slug ); ?>-select"
			       class="screen-reader-text"><?php echo sanitize_title( $this->labels['name'] ); ?></label>
			<?php submit_button( __( 'Update' ), 'medium', 'bulk_edit_' . esc_attr( self::$user_group_slug ) . '_submit', false, array( 'style' => 'float:right;' ) ); ?>

			<select name="<?php echo esc_attr( self::$user_group_slug ); ?>-select"
			        id="<?php echo esc_attr( self::$user_group_slug ); ?>-select"
			        style="max-width: 300px; float: right">
				<option
					value=""><?php _e( 'Add to ' . sanitize_title( $this->labels['name'] ) . '&hellip;', 'rtlib' ); ?></option>
				<?php foreach ( $terms as $term ) { ?>
					<option
						value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option>
				<?php } ?>
			</select>
			<!--						--><?php //wp_nonce_field( 'bulk-edit-' . self::$user_group_slug ) ?>
			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					$('#bulk_edit_<?php echo esc_attr( self::$user_group_slug ) ?>_submit').click(function () {
						$('#bulk_edit_<?php echo esc_attr( self::$user_group_slug ); ?>_form').remove().insertAfter('ul.subsubsub');
						var users = $('.wp-list-table.users .check-column input:checked').serialize();
						$('#bulk_edit_<?php echo esc_attr( self::$user_group_slug ); ?>_users').val(users);
						console.log(users);
					});
					$('.removeUserGroup').click(function () {
						var that = this;
						var requestArray = new Object();
						requestArray['user'] = $(this).parent().parent().find('input[name="users[]"]').val();
						requestArray['groupslug'] = $(this).siblings('.rthdgroupslug').val();
						requestArray['action'] = 'remove_group';

						$.ajax({
							url: ajaxurl,
							dataType: "json",
							data: requestArray,
							type: 'post',
							success: function (data) {
								if (data.status) {
									$(that).parent().remove();
								}
								else {
									console.log('Something went wrong');
								}
							}
						});
					});
				});
			</script>
			<?php
			return $views;
		}

		/**
		 * UI of User group
		 *
		 * @param type $user
		 *
		 * @return type
		 */
		function edit_user_user_group_section( $user ) {
			$tax = get_taxonomy( self::$user_group_slug );

			/* Make sure the user can assign terms of the profession group before proceeding. */
			if ( ! current_user_can( $tax->cap->assign_terms ) || ! current_user_can( 'assign_terms' ) ) {
				return;
			}

			/* Get the terms of the 'profession' group. */
			$terms = get_terms( self::$user_group_slug, array( 'hide_empty' => false ) );
			?>

			<h3 id="<?php echo esc_attr( self::$user_group_slug ); ?>"><?php printf( $this->labels['name'] ); ?></h3>
			<table class="form-table">
				<tr>
					<th>
						<label for="<?php echo esc_attr( self::$user_group_slug ); ?>"
						       style="font-weight:bold; display:block;"><?php printf( __( 'Add a %s', 'rtlib' ), $this->labels['singular_name'] ); ?></label>
						<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=' . self::$user_group_slug ) ); ?>"><?php printf( __( 'Add a %s', 'rtlib' ), $this->labels['singular_name'] ); ?></a>
					</th>

					<td>
						<?php
						/* If there are any terms availabel, loop through them and display radioboxes/checkboxes. */
						if ( ! empty( $terms ) ) {
							echo '<ul>';
							$ele_type = $this->multiple ? 'checkbox' : 'radio';
							foreach ( $terms as $term ) {
								$color = $this->get_group_meta( 'group-color', $term->term_id );
								if ( ! empty( $color ) ) {
									$color = ' style="padding:2px .5em; border-radius:3px; background-color:' . $color . '; color:' . self::get_text_color( $color ) . '"';
								}
								?>
								<li><input type="<?php echo esc_attr( $ele_type ); ?>"
								           name="<?php echo esc_attr( self::$user_group_slug ); ?>[]"
								           id="<?php echo esc_attr( self::$user_group_slug ); ?>-<?php echo esc_attr( $term->slug ); ?>"
								           value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( true, $this->is_user_has_group( $user->ID, $term->term_taxonomy_id ) ); ?> />
									<label
										for="<?php echo esc_attr( self::$user_group_slug ); ?>-<?php echo esc_attr( $term->slug ); ?>"<?php printf( $color ); ?>><?php echo esc_html( $term->name ); ?></label>
								</li> <?php }
							echo '</ul>';
							/* If there are no user group terms, display a message. */
						} else {
							_e( '<a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=' . self::$user_group_slug ) ) . '">' . ( __( 'Add', 'rtlib' ) ) . '</a>' );
						} ?>
					</td>
				</tr>
			</table> <?php
		}

		/**
		 * Save User group
		 *
		 * @param       $user_id
		 * @param array $user_groups
		 * @param bool $bulk
		 *
		 * @return bool
		 */
		function save_user_user_group( $user_id, $user_groups = array(), $bulk = false ) {

			$tax = get_taxonomy( self::$user_group_slug );
			/* Make sure the current user can edit the user and assign terms before proceeding. */
			if ( ! current_user_can( 'assign_terms', $user_id ) && current_user_can( $tax->cap->assign_terms ) ) {
				return false;
			}

			$user_groups = ( isset( $_POST[ self::$user_group_slug ] ) ) ? $_POST[ self::$user_group_slug ] : '';

			$old_terms = self::get_user_groups( $user_id );

			$old_groups = array();

			foreach ( $old_terms as $old_term ) {
				$old_groups[] = $old_term->slug;
			}

			if ( ! isset( $user_groups ) || empty( $user_groups ) ) {
				$user_groups = array();
			}

			$diff = array_diff( $old_groups, $user_groups );

			$this->remove_user_groups( $user_id, $diff );

			$this->set_user_groups( $user_id, $user_groups );
		}

		/**
		 * Update user query filter User list accoding to its group
		 *
		 * @param string $Query
		 */
		function user_query( $Query = '' ) {
			global $pagenow, $wpdb;

			if ( 'users.php' !== $pagenow ) {
				return;
			}

			if ( ! empty( $_GET[ self::$user_group_slug ] ) ) {

				$groups = explode( ',', $_GET[ self::$user_group_slug ] );
				$ids    = array();
				foreach ( $groups as $group ) {
					$user_ids = self::get_user_by_group_slug( $group );
					if ( isset( $user_ids ) && ! empty( $user_ids ) ) {
						$ids = array_merge( $user_ids, $ids );
					}
				}
				$ids = implode( ',', wp_parse_id_list( $user_ids ) );

				if ( ! empty( $ids ) ) {
					$Query->query_where .= " AND $wpdb->users.ID IN ( $ids )";
				} else {
					$Query->query_where .= ' AND 1=2';
				}
			}
		}

		/**
		 * UI for filter User list accoding to its group
		 * @global type $wp_roles
		 *
		 * @param array $views
		 *
		 * @return type
		 */
		function user_view_filters( $views ) {
			global $wp_roles;

			$terms            = get_terms( self::$user_group_slug, array( 'hide_empty' => false ) );
			$current          = false;
			$select           = '';
			$sum              = 0;
			$allDepartmentCSS = '';
			if ( isset( $_REQUEST['user-group'] ) && ! empty( $_REQUEST['user-group'] ) ) {
				$activeSlug = $_REQUEST['user-group'];
			} else {
				$allDepartmentCSS = 'color:#000;font-weight:bold;';
			}
			foreach ( $terms as $term ) {
				$users = self::get_user_by_group_slug( $term->slug );
				$CSS   = '';
				if ( isset( $activeSlug ) && $term->slug == $activeSlug ) {
					$CSS = 'color:#000;font-weight:bold;';
				}
				$select .= '| <a href="?' . self::$user_group_slug . '=' . $term->slug . '" style = "' . $CSS . '">' . esc_html( $term->name ) . '</a> (' . count( $users ) . ')  ';
				$sum += count( $users );
				if ( isset( $_GET[ self::$user_group_slug ] ) && $_GET[ self::$user_group_slug ] === $term->slug ) {
					$current = $term;
				}
			}
			if ( $current ) {
				$bgcolor   = $this->get_group_meta( 'group-color', $current->term_id );
				$color     = self::get_text_color( $bgcolor );
				$roleli    = '';
				$role      = false;
				$role_name = __( 'users', 'rtlib' );
				if ( isset( $_GET['role'] ) ) {
					$role  = $_GET['role'];
					$roles = $wp_roles->get_names();
					if ( array_key_exists( $role, $roles ) ) {
						$role_name = $roles["{$role}"];
						if ( 's' !== substr( $role_name, - 1, 1 ) ) {
							$role_name .= 's';
						}
					}
					$roleli = sprintf( '%sRemove Role Filter%s', '<li><a href="' . remove_query_arg( 'role' ) . '">', '</li>' );
				}

				$colorblock = empty( $bgcolor ) ? '' : '<span style="width:1.18em; height:1.18em; float:left; margin-right:.25em; background-color:' . $this->get_group_meta( 'group-color', $current->term_id ) . ';"></span>';
				?>
				<div id="<?php echo esc_attr( self::$user_group_slug ); ?>-header">
					<h2><?php echo balanceTags( $colorblock );
						echo esc_html( sprintf( __( 'User Group: %s', 'rtlib' ), $current->name ) );
						?> <a
							href="<?php echo esc_url( admin_url( 'edit-tags.php?action=edit&taxonomy=' . esc_attr( self::$user_group_slug ) . '&tag_ID=' . esc_attr( $current->term_id ) . '&post_type=post' ) ); ?>"
							class="add-new-h2"
							style="background:#fefefe;"><?php printf( __( 'Edit %s', 'rtlib' ), $this->labels['name'] ); ?></a>
					</h2>
					<?php echo esc_html( wpautop( $current->description ) ); ?>
				</div>
				<p class="howto" style="font-style:normal;">
                    <span><?php echo esc_html( sprintf( __( 'Showing %s in %s', 'rtlib' ), $role_name, '&ldquo;' . sanitize_title( $current->name ) . '&rdquo;' ) ); ?>
	                    .</span>

					<a href="<?php echo esc_html( remove_query_arg( self::$user_group_slug ) ); ?>"
					   class="<?php echo esc_attr( self::$user_group_slug ); ?>-<?php echo esc_attr( self::$user_group_slug ); ?>-filter"><span></span> <?php echo esc_html( sprintf( __( 'Show all %s', 'rtlib' ), $role_name ) ); ?>
					</a>

					<?php if ( ! empty( $role ) ) { ?>
						<a href="<?php echo esc_html( remove_query_arg( 'role' ) ); ?>"
						   class="<?php echo esc_attr( self::$user_group_slug ); ?>-<?php echo esc_attr( self::$user_group_slug ); ?>-filter"><span></span> <?php echo esc_html( sprintf( __( 'Show all users in "%s"', 'rtlib' ), $current->name ) ); ?>
						</a>
					<?php } ?>
				</p>
				<div class="clear"></div> <?php
			}

			ob_start();

			$args = array();
			if ( isset( $_GET['s'] ) ) {
				$args['s'] = $_GET['s'];
			}
			if ( isset( $_GET['role'] ) ) {
				$args['role'] = $_GET['role'];
			}
			?>
			<label for="<?php echo esc_attr( self::$user_group_slug ); ?>-select"
			       style="vertical-align:baseline;<?php echo $allDepartmentCSS ?>;"><?php echo 'All ' . ucwords( $this->labels['name'] ); ?></label> <?php echo ' (' . $sum . ')' ?>
			<?php echo balanceTags( $select ); ?>
			<div class="clear"></div>
			<style type="text/css">
				.subsubsub li. <?php echo esc_attr( self::$user_group_slug ); ?> {
					display: inline-block !important;
				}
			</style>
			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					<?php if ( isset( $_GET[ self::$user_group_slug ] ) ) { ?>
					$('ul.subsubsub li a').each(function () {
						var $that = $(this);
						$(this).attr('href', function () {
							var sep = $that.attr('href').match(/\?/i) ? '&' : '?';
							return $(this).attr('href') + sep + '<?php echo esc_attr( self::$user_group_slug ); ?>=<?php echo esc_attr( $_GET[ self::$user_group_slug ] ); ?>';
						});
					});
					<?php }
				?>
				});
			</script> <?php
			$form = ob_get_clean();
			echo $form;

			return $views;
		}

		/**
		 * delete group while user deleted
		 * @global RT_User_Groups_Relationships_Model $rtlib_user_group_model
		 *
		 * @param type $user_id
		 */
		function delete_term_relationships( $user_id ) {
			//			$rtlib_user_group_model = new RT_User_Groups_Relationships_Model();

			wp_delete_object_term_relationships( $user_id, self::$user_group_slug );
			$this->remove_all_user_groups( $user_id );
		}

		/**
		 * Exclude group name form user name
		 *
		 * @param string $username
		 *
		 * @return string
		 */
		function disable_username( $username ) {
			if ( self::$user_group_slug === $username ) {
				$username = '';
			}

			return $username;
		}

		/**
		 * get_text_color
		 * Code from http://serennu.com/colour/rgbtohsl.php
		 *
		 * @param string $hexcode
		 *
		 * @return string
		 */
		static function get_text_color( $hexcode = '' ) {
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

			if ( 0 === $del_max ) {
				$h = 0;
				$s = 0;
			} else {
				if ( $l < 0.5 ) {
					$s = $del_max / ( $var_max + $var_min );
				} else {
					$s = $del_max / ( 2 - $var_max - $var_min );
				};

				$del_r = ( ( ( $var_max - $var_r ) / 6 ) + ( $del_max / 2 ) ) / $del_max;
				$del_g = ( ( ( $var_max - $var_g ) / 6 ) + ( $del_max / 2 ) ) / $del_max;
				$del_b = ( ( ( $var_max - $var_b ) / 6 ) + ( $del_max / 2 ) ) / $del_max;

				if ( $var_r == $var_max ) {
					$h = $del_b - $del_g;
				} elseif ( $var_g == $var_max ) {
					$h = ( 1 / 3 ) + $del_r - $del_b;
				} elseif ( $var_b == $var_max ) {
					$h = ( 2 / 3 ) + $del_g - $del_r;
				};

				if ( $h < 0 ) {
					$h += 1;
				};

				if ( $h > 1 ) {
					$h -= 1;
				};
			};

			if ( ( $l * 100 ) < 50 ) {
				return 'white';
			} else {
				return 'black';
			}
		}

		/**
		 * Add Color picker Field
		 */
		function add_colorpicker_field() {
			?>
			<tr>
				<th scope="row" valign="top"><label><?php _e( 'Color for the User Group', 'genesis' ); ?></label></th>
				<td id="group-color-row">
					<p>
						<input type="text" name="<?php echo esc_attr( self::$user_group_slug ); ?>[group-color]"
						       id="group-color"
						       value="<?php echo esc_html( $this->get_group_meta( 'group-color' ) ); ?>"/>
                        <span
	                        class="description hide-if-js">If you want to hide header text, add <strong>#blank</strong> as text color.</span>
						<input type="button" class="button hide-if-no-js" value="Select a Color" id="pickcolor"/>
					</p>

					<div id="color-picker"
					     style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
				</td>
			</tr> <?php
		}

		/**
		 * add mail field
		 */
		function user_group_group_add_new_meta_field() {
			?>
			<div class="form-field">
				<label for="term_meta[email_address]"><?php _e( 'Email Address', 'rtcamp' ); ?></label>
				<input type="text" name="<?php echo esc_attr( self::$user_group_slug ); ?>[email_address]"
				       id="<?php echo esc_attr( self::$user_group_slug ); ?>[email_address]" value="">

				<p class="description"><?php _e( 'Enter a Email address for this field', 'rtcamp' ); ?></p>
			</div> <?php
		}

		/**
		 * add mail field
		 *
		 * @param type $term
		 */
		function user_group_group_edit_meta_field( $term ) {
			$t_id      = $term->term_id;
			$term_meta = '';
			if ( is_multisite() ) {
				$term_meta = get_site_option( self::$user_group_slug . '-meta' );
			} else {
				$term_meta = get_option( self::$user_group_slug . '-meta' );
			}
			?>
			<tr class="form-field">
				<th scope="row" valign="top"><label
						for="term_meta[email_address]"><?php _e( 'Email Address', 'rtCamp' ); ?></label></th>
				<td>
					<input type="text" name="<?php echo esc_attr( self::$user_group_slug ); ?>[email_address]"
					       id="<?php echo esc_attr( self::$user_group_slug ); ?>[email_address]"
					       value="<?php echo esc_attr( $term_meta[ $t_id ]['email_address'] ) ? esc_attr( $term_meta[ $t_id ]['email_address'] ) : ''; ?>"/>

					<p class="description"><?php _e( 'Enter a email address for this field', 'rtcamp' ); ?></p>
				</td>
			</tr> <?php
		}

		/**
		 * Add Color picker Field
		 *
		 * @param        $tag
		 * @param string $group
		 */
		function add_form_color_field( $tag, $group = '' ) {

			$tax = get_taxonomy( $group );

			if ( $this->is_edit_user_group( 'edit' ) ) {
				?>

				<h3><?php _e( 'User Group Settings', 'rtlib' ); ?></h3>

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row" valign="top">
							<label><?php _e( 'Color for the User Group', 'genesis' ); ?></label>
						</th>
						<td id="group-color-row">
							<p>
								<input type="text" name="<?php echo esc_attr( self::$user_group_slug ); ?>[group-color]"
								       id="group-color"
								       value="<?php echo esc_html( $this->get_group_meta( 'group-color' ) ); ?>"/>
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
						<input type="text" style="width:40%"
						       name="<?php echo esc_attr( self::$user_group_slug ); ?>[group-color]"
						       id="group-color"
						       value="<?php echo esc_html( $this->get_group_meta( 'group-color' ) ); ?>"/>
						<input type="button" style="margin-left:.5em;width:auto!important;" class="button hide-if-no-js"
						       value="Select a Color" id="pickcolor"/>
					</p>
				</div>
				<div id="color-picker"
				     style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
			<?php }
		}

		/**
		 * get meta for user group
		 *
		 * @param string $key
		 * @param int $term_id
		 *
		 * @return bool
		 */
		function get_group_meta( $key = '', $term_id = 0 ) {

			if ( isset( $_GET['tag_ID'] ) ) {
				$term_id = $_GET['tag_ID'];
			}
			if ( empty( $term_id ) ) {
				return false;
			}

			$term_meta = (array) get_option( self::$user_group_slug . '-meta' );

			if ( ! isset( $term_meta[ $term_id ] ) ) {
				return false;
			}

			if ( ! empty( $key ) ) {
				return isset( $term_meta[ $term_id ][ $key ] ) ? $term_meta[ $term_id ][ $key ] : false;
			} else {
				return $term_meta[ $term_id ];
			}
		}

		/**
		 * is_edit_user_group
		 *
		 * @param bool $page
		 *
		 * @return bool
		 */
		function is_edit_user_group( $page = false ) {
			global $pagenow;

			if ( ( ! $page || 'edit' === $page ) && 'edit-tags.php' === $pagenow && isset( $_GET['action'] ) && 'edit' === $_GET['action'] && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === self::$user_group_slug ) {
				return true;
			}

			if ( ( ! $page || 'all' === $page ) && 'edit-tags.php' === $pagenow && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === self::$user_group_slug && ( ! isset( $_GET['action'] ) || 'edit' !== $_GET['action'] ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Assign groups for given User
		 *
		 * @param type $user_id
		 * @param type $groups
		 */
		function set_user_groups( $user_id, $groups ) {
			foreach ( $groups as $group_name ) {
				$this->set_user_group( $user_id, $group_name );
			}
		}

		/**
		 * Assign given group for given User
		 * @global RT_User_Groups_Relationships_Model $rtlib_user_group_model
		 *
		 * @param type $user_id
		 * @param type $group_name
		 */
		function set_user_group( $user_id, $group_name ) {

			$rtlib_user_group_model = new RT_User_Groups_Relationships_Model();

			$term = get_term_by( 'slug', $group_name, self::$user_group_slug );

			$data = array( 'term_taxonomy_id' => $term->term_taxonomy_id, 'term_order' => 0, );

			if ( ( $this->multiple && ! $this->is_user_has_group( $user_id, $term->term_taxonomy_id ) ) || ( ! $this->multiple && ! $rtlib_user_group_model->is_user_has_group( $user_id ) ) ) {
				$data = array_merge( $data, array( 'user_id' => $user_id, ) );
				$rtlib_user_group_model->insert( $data );
			} else {
				$where = array( 'user_id' => $user_id, );
				$rtlib_user_group_model->update( $data, $where );
			}
		}

		/**
		 * remove groups for given User
		 *
		 * @param type $user_id
		 * @param type $groups
		 */
		function remove_user_groups( $user_id, $groups ) {
			foreach ( $groups as $group_name ) {
				$this->remove_user_group( $user_id, $group_name );
			}
		}

		/**
		 * remove given group for given User
		 * @global RT_User_Groups_Relationships_Model $rtlib_user_group_model
		 *
		 * @param type $user_id
		 * @param type $group_name
		 */
		function remove_user_group( $user_id, $group_name ) {
			$rtlib_user_group_model = new RT_User_Groups_Relationships_Model();
			$term                   = get_term_by( 'slug', esc_attr( $group_name ), self::$user_group_slug );

			$where = array( 'user_id' => $user_id, 'term_taxonomy_id' => $term->term_taxonomy_id, );

			$rtlib_user_group_model->delete( $where );
		}

		/**
		 * remove all group for given User
		 * @global RT_User_Groups_Relationships_Model $rtlib_user_group_model
		 *
		 * @param type $user_id
		 */
		function remove_all_user_groups( $user_id ) {
			$rtlib_user_group_model = new RT_User_Groups_Relationships_Model();

			$where = array( 'user_id' => $user_id, );

			$rtlib_user_group_model->delete( $where );
		}

		/**
		 * For checking given user has given group
		 * @global RT_User_Groups_Relationships_Model $rtlib_user_group_model
		 *
		 * @param type $user_id
		 * @param type $term_taxonomy_id
		 *
		 * @return boolean
		 */
		function is_user_has_group( $user_id, $term_taxonomy_id ) {
			$rtlib_user_group_model = new RT_User_Groups_Relationships_Model();

			$columns         = array( 'user_id' => $user_id, 'term_taxonomy_id' => $term_taxonomy_id, );
			$all_users_group = $rtlib_user_group_model->get( $columns );

			if ( count( $all_users_group ) > 0 ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Get list of group for given user
		 * @global RT_User_Groups_Relationships_Model $rtlib_user_group_model
		 *
		 * @param type $user_id
		 *
		 * @return type
		 */
		static function get_user_groups( $user_id ) {
			self::auto_loader();
			$rtlib_user_group_model = new RT_User_Groups_Relationships_Model();

			$columns = array( 'user_id' => $user_id, );

			$groups = $rtlib_user_group_model->get( $columns );
			$terms  = array();
			if ( isset( $groups ) && ! empty( $groups ) ) {
				foreach ( $groups as $group ) {
					$temp_term = get_term_by( 'term_taxonomy_id', esc_attr( $group->term_taxonomy_id ), self::$user_group_slug );
					if ( isset( $temp_term ) && ! empty( $temp_term ) ) {
						$terms[] = $temp_term;
					}
				}
			}

			return $terms;
		}

		/**
		 * Get list of user for given group
		 * @global RT_User_Groups_Relationships_Model $rtlib_user_group_model
		 *
		 * @param type $slug
		 *
		 * @return type
		 */
		static function get_user_by_group_slug( $slug ) {
			self::auto_loader();
			$rtlib_user_group_model = new RT_User_Groups_Relationships_Model();
			$user_ids               = array();

			$term = get_term_by( 'slug', esc_attr( $slug ), self::$user_group_slug );
			if ( isset( $term ) && ! empty( $term ) ) {
				$columns = array( 'term_taxonomy_id' => $term->term_taxonomy_id, );
				$users   = $rtlib_user_group_model->get( $columns );
				foreach ( $users as $user ) {
					$user_ids[] = $user->user_id;
				}
			}

			return $user_ids;
		}

		/**
		 * Get list of user for given group id
		 *
		 * @param $group_id
		 *
		 * @return array
		 */
		static function get_user_by_group_id( $group_id ) {
			self::auto_loader();
			$rtlib_user_group_model = new RT_User_Groups_Relationships_Model();
			$user_ids               = array();

			$term = get_term_by( 'id', $group_id, self::$user_group_slug );
			if ( isset( $term ) && ! empty( $term ) ) {
				$columns = array( 'term_taxonomy_id' => $term->term_taxonomy_id, );
				$users   = $rtlib_user_group_model->get( $columns );
				foreach ( $users as $user ) {
					$user_ids[] = $user->user_id;
				}
			}

			return $user_ids;
		}
	}

}
