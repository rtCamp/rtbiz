<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'RT_Departments' ) ) {

	/**
	 * Class RT_Departments
	 */
	class RT_Departments {
		/**
		 * @var $slug - department slug
		 */
		static $slug = 'rt-department';

		/**
		 * @var $labels - Lable array
		 */
		var $labels = array();

		/**
		 * Constructor
		 */
		public function __construct(  ) {
			$this->get_lables();

			add_action( 'init', array( $this, 'register_taxonomy_department' ), 20 );

			add_action( 'admin_print_scripts', array( $this, 'js_includes' ) );
			add_action( 'admin_print_styles', array( $this, 'css_includes' ) );
			add_action( 'admin_head', array( $this, 'colorpicker' ) );
			add_action( 'admin_head', array( $this, 'hide_slug' ) );

			add_action( 'admin_init', array( $this, 'add_remove_department_field' ), 1000 );
			add_action( 'create_term', array( $this, 'save_department' ), 10, 2 );
			add_action( 'edit_term', array( $this, 'save_department' ), 10, 2 );

			add_filter( self::$slug . '_row_actions', array( $this, 'row_actions' ), 1, 2 );
			add_action( 'manage_' . self::$slug . '_custom_column', array( $this, 'manage_department_column_body' ), 10, 3 );
			add_filter( 'manage_edit-' . self::$slug . '_columns', array( $this, 'manage_department_column_header' ) );
			add_filter( 'admin_notices', array( $this, 'add_manage_acl_button' ) );

		}

		function get_lables(){
			$this->labels = array(
				'name'                       => __( 'Departments' ),
				'singular_name'              => __( 'Department' ),
				'menu_name'                  => __( 'Departments' ),
				'search_items'               => __( 'Search Departments' ),
				'popular_items'              => __( 'Popular Departments' ),
				'all_items'                  => __( 'All User Departments' ),
				'edit_item'                  => __( 'Edit Department' ),
				'update_item'                => __( 'Update Department' ),
				'add_new_item'               => __( 'Add New Department' ),
				'new_item_name'              => __( 'New Department Name' ),
				'separate_items_with_commas' => __( 'Separate departments with commas' ),
				'add_or_remove_items'        => __( 'Add or remove departments' ),
				'choose_from_most_used'      => __( 'Choose from the most popular departments' ),
			);
			return $this->labels;
		}

		/**
		 * Register Department
		 */
		function register_taxonomy_department() {

			$editor_cap = rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'editor' );
			$caps = array(
				'manage_terms' => $editor_cap,
				'edit_terms'   => $editor_cap,
				'delete_terms' => $editor_cap,
				'assign_terms' => $editor_cap,
			);

			$arg = array( 'public' => true, 'show_ui' => false, 'labels' => $this->labels, 'rewrite' => false, 'capabilities' => $caps, 'hierarchical' => true, 'show_admin_column' => true );
			$supported_posttypes = array();
			$supported_posttypes = apply_filters( 'rtbiz_department_support', $supported_posttypes );
			$supported_posttypes = array_unique( $supported_posttypes );

			register_taxonomy( self::$slug, $supported_posttypes, $arg );
		}

		/**
		 * Save department taxonomy
		 *
		 * @param type $term_id
		 */
		function save_department( $term_id ) {
			if ( isset( $_POST[ self::$slug ] ) ) {
				$prev_value = Rt_Lib_Taxonomy_Metadata\get_term_meta( $term_id, self::$slug . '-meta', true );
				$meta_value = (array) $_POST[ self::$slug ];
				Rt_Lib_Taxonomy_Metadata\update_term_meta( $term_id, self::$slug . '-meta', $meta_value, $prev_value );
				if ( isset( $_POST['_wp_original_http_referer'] ) ) {
					wp_safe_redirect( $_POST['_wp_original_http_referer'] );
					exit();
				}
			}
		}

		/**
		 * add view action for Department
		 */
		function row_actions( $actions, $term ) {
			$actions['view'] = sprintf( __( '%sView%s', RT_BIZ_TEXT_DOMAIN ), '<a href="' . esc_url( add_query_arg( array( self::$slug => $term->slug ) ), admin_url( 'users.php' ) ) . '">', '</a>' );

			return $actions;
		}

		/**
		 * add Department List View columns
		 *
		 * @param type $columns
		 *
		 * @return type
		 */
		function manage_department_column_header( $columns ) {

			unset( $columns['posts'], $columns['slug'] );

			$columns['contacts']         = __( 'Contacts', RT_BIZ_TEXT_DOMAIN );
			$columns['color']         = __( 'Color', RT_BIZ_TEXT_DOMAIN );
			$columns['email_address'] = __( 'Email Address', RT_BIZ_TEXT_DOMAIN );

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
		function manage_department_column_body( $display, $column, $term_id ) {
			switch ( $column ) {
				case 'contacts':
					$term = get_term( $term_id, self::$slug );
					$contacts_count = count( rt_biz_get_department_contacts( $term_id ) );
					echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=rt_contact&' . self::$slug . '=' . $term->slug ) ) . '">' . $contacts_count . '</a>';
					break;
				case 'color':
					$color = $this->get_department_meta( 'group-color', $term_id );
					if ( ! empty( $color ) ) {
						echo '<div style="width:3.18em; height:3em; background-color:' . esc_attr( $color ) . ';"></div>';
					}
					break;
				case 'email_address';
					$email_address = $this->get_department_meta( 'email_address', $term_id );
					if ( isset( $email_address ) && ! empty( $email_address ) ) {
						echo esc_html( $email_address );
					}
					break;
			}
			return;
		}

		/**
		 * is_edit_user_group
		 *
		 * @param bool $page
		 *
		 * @return bool
		 */
		function is_edit_department( $page = false ) {
			global $pagenow;

			if ( ( ! $page || 'edit' === $page ) && 'edit-tags.php' === $pagenow && isset( $_GET['action'] ) && 'edit' === $_GET['action'] && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === self::$slug ) {
				return true;
			}

			if ( ( ! $page || 'all' === $page ) && 'edit-tags.php' === $pagenow && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === self::$slug && ( ! isset( $_GET['action'] ) || 'edit' !== $_GET['action'] ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Add jQuery for User group
		 * @return type
		 */
		function js_includes() {
			if ( ! $this->is_edit_department() ) {
				return;
			}
			wp_enqueue_script( 'farbtastic', array( 'jquery' ) );
		}

		/**
		 * Add CSS for User group
		 * @return type
		 */
		function css_includes() {
			if ( ! $this->is_edit_department() ) {
				return;
			}
			wp_enqueue_style( 'farbtastic', array( 'jquery' ) );
		}

		/**
		 * Configure Colorpicker
		 * @return type
		 */
		function colorpicker() {

			if ( ! $this->is_edit_department() ) {
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
			if ( $this->is_edit_department( 'all' ) ) {
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
			} elseif ( $this->is_edit_department( 'edit' ) ) {
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
		function add_remove_department_field( $group ) {
			//remove_all_actions('after-user-group-table');
			//remove_all_actions('user-group_edit_form');
			//remove_all_actions('user-group_add_form_fields');

			// If you use Rich Text tags, go ahead!
			if ( function_exists( 'kws_rich_text_tags' ) ) {
				add_action( self::$slug . '_edit_form_fields', 'kws_add_form' );
				add_action( self::$slug . '_add_form_fields', 'kws_add_form' );
			}

			add_action( self::$slug . '_add_form_fields', array( $this, 'department_add_custom_field' ), 10, 2 );
			add_action( self::$slug . '_edit_form', array( $this, 'department_add_custom_field' ), 10, 2 );
		}

		/**
		 * get meta for department
		 *
		 * @param string $key
		 * @param int    $term_id
		 *
		 * @return bool
		 */
		function get_department_meta( $key = '', $term_id = 0 ) {

			if ( isset( $_GET['tag_ID'] ) ) {
				$term_id = $_GET['tag_ID'];
			}
			if ( empty( $term_id ) ) {
				return false;
			}

			$term_meta = Rt_Lib_Taxonomy_Metadata\get_term_meta( $term_id, self::$slug . '-meta', true );
			if ( ! empty( $term_meta ) ) {
				if ( ! empty( $key ) ) {
					return isset( $term_meta[ $key ] ) ? $term_meta[ $key ] : false;
				} else {
					return $term_meta;
				}
			}
			return false;
		}

		/**
		 * Add Color picker Field
		 *
		 * @param        $tag
		 * @param string $group
		 */
		function department_add_custom_field( $tag, $group = '' ) {

			$tax = get_taxonomy( $group );
			$this->get_department_meta( 'email_address' );

			if ( $this->is_edit_department( 'edit' ) ) {
				?>

				<h3><?php _e( 'User Group Settings', 'rtlib' ); ?></h3>

				<table class="form-table">
					<tbody>
						<tr class="form-field">
							<th scope="row" valign="top"><label
									for="term_meta[email_address]"><?php _e( 'Email Address', RT_BIZ_TEXT_DOMAIN ); ?></label></th>
							<td>
								<input type="text" name="<?php echo esc_attr( self::$slug ); ?>[email_address]"
								       id="<?php echo esc_attr( self::$slug ); ?>[email_address]"
								       value="<?php echo esc_html( $this->get_department_meta( 'email_address' ) ); ?>"/>

								<p class="description"><?php _e( 'Enter a email address for Department', 'rtcamp' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top">
								<label><?php _e( 'Color for the User Group', 'genesis' ); ?></label>
							</th>
							<td id="group-color-row">
								<p>
									<input type="text" name="<?php echo esc_attr( self::$slug ); ?>[group-color]"
									       id="group-color"
									       value="<?php echo esc_html( $this->get_department_meta( 'group-color' ) ); ?>"/>
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
						<label for="term_meta[email_address]"><?php _e( 'Email Address', 'rtcamp' ); ?></label>
						<input type="text" name="<?php echo esc_attr( self::$slug ); ?>[email_address]"
					       id="<?php echo esc_attr( self::$slug ); ?>[email_address]" value="">
					</p>
					<p class="description"><?php _e( 'Enter a email address for Department', 'rtcamp' ); ?></p>
				</div>
				<div class="form-field">
					<p>
						<input type="text" style="width:40%" name="<?php echo esc_attr( self::$slug ); ?>[group-color]"
						       id="group-color"
						       value=""/>
						<input type="button" style="margin-left:.5em;width:auto!important;" class="button hide-if-no-js"
						       value="Select a Color" id="pickcolor"/>
					</p>
				</div>
				<div id="color-picker"
				     style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
			<?php }
		}

		function add_manage_acl_button( $taxonomy ){
			global $pagenow;
			if ( 'edit-tags.php' == $pagenow && ! empty( $_REQUEST['taxonomy'] ) && $_REQUEST['taxonomy'] == self::$slug ){
				$acl_url = admin_url( 'admin.php?page=' . Rt_Biz::$access_control_slug );
				echo '<div class="updated" style="padding: 10px 10px 10px;">You can manage ACL for these departments from <a href="' . esc_url( $acl_url ) . '">Access Control</a></div>';
			}
		}
	}
}
