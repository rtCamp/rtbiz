<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Rtbiz_Teams' ) ) {

	/**
	 * Class Rtbiz_Teams
	 */
	class Rtbiz_Teams {
		/**
		 * @var $slug - team slug
		 */
		static $slug = 'rt-team';

		/**
		 * @var $labels - Lable array
		 */
		var $labels = array();

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->get_lables();

			Rtbiz::$loader->add_action( 'init', $this, 'register_taxonomy_team', 20 );
			Rtbiz::$loader->add_action( 'rtbiz_team_support', $this, 'add_team_support' );
			Rtbiz::$loader->add_action( 'admin_head', $this, 'hide_slug' );
			Rtbiz::$loader->add_action( self::$slug . '_add_form_fields', $this, 'team_add_custom_field', 10, 2 );
			Rtbiz::$loader->add_action( self::$slug . '_edit_form', $this, 'team_add_custom_field', 10, 2 );
			Rtbiz::$loader->add_filter( self::$slug . '_row_actions', $this, 'row_actions', 1, 2 );

			Rtbiz::$loader->add_action( 'create_term', $this, 'save_team', 10, 2 );
			Rtbiz::$loader->add_action( 'edit_term', $this, 'save_team', 10, 2 );

			Rtbiz::$loader->add_action( 'manage_' . self::$slug . '_custom_column', $this, 'manage_team_column_body', 10, 3 );
			Rtbiz::$loader->add_filter( 'manage_edit-' . self::$slug . '_columns', $this, 'manage_team_column_header' );
			Rtbiz::$loader->add_filter( 'admin_notices', $this, 'add_manage_acl_button' );

		}


		function get_lables() {
			$this->labels = array(
				'name'                       => __( 'Teams' ),
				'singular_name'              => __( 'Team' ),
				'menu_name'                  => __( 'Teams' ),
				'search_items'               => __( 'Search Teams' ),
				'popular_items'              => null,
				'all_items'                  => __( 'All User Teams' ),
				'edit_item'                  => __( 'Edit Team' ),
				'update_item'                => __( 'Update Team' ),
				'add_new_item'               => __( 'Add New Team' ),
				'new_item_name'              => __( 'New Team Name' ),
				'separate_items_with_commas' => __( 'Separate Teams with commas' ),
				'add_or_remove_items'        => __( 'Add or remove Teams' ),
				'choose_from_most_used'      => __( 'Choose from the most popular Teams' ),
				'not_found'                  => __( 'No team found.' ),
				'no_terms'                   => __( 'No team' ),
			);

			return $this->labels;
		}

		/**
		 * Register Team
		 */
		function register_taxonomy_team() {

			$editor_cap = rtbiz_get_access_role_cap( RTBIZ_TEXT_DOMAIN, 'editor' );
			$caps       = array(
				'manage_terms' => $editor_cap,
				'edit_terms'   => $editor_cap,
				'delete_terms' => $editor_cap,
				'assign_terms' => $editor_cap,
			);

			$arg                 = array(
				'public'            => false,
				'show_ui'           => true,
				'labels'            => $this->labels,
				'rewrite'           => false,
				'capabilities'      => $caps,
				'hierarchical'      => true,
				'show_admin_column' => true
			);
			$supported_posttypes = array();
			$supported_posttypes = apply_filters( 'rtbiz_team_support', $supported_posttypes );
			$supported_posttypes = array_unique( $supported_posttypes );

			register_taxonomy( self::$slug, $supported_posttypes, $arg );
		}

		public function add_team_support( $supports ) {
			$modules = rtbiz_get_modules();
			foreach ( $modules as $key => $value ) {
				if ( ! empty( $value['team_support'] ) ) {
					$supports = array_merge( $supports, $value['team_support'] );
				}
			}

			return $supports;
		}

		/**
		 * hide_slug
		 */
		function hide_slug() {
			if ( $this->is_edit_team( 'all' ) ) {
				?>
				<style type="text/css">
					.form-wrap form span.description {
						display: none !important;
					}
				</style>

				<script type="text/javascript">
					jQuery(document).ready(function ($) {
						$('#tag-slug').parent('div.form-field').hide();
						$('.inline-edit-col input[name=slug]').parents('label').hide();
					});
				</script><?php
			} elseif ( $this->is_edit_team( 'edit' ) ) {
				?>
				<style type="text/css">
					.form-table .form-field td span.description, .form-table .form-field {
						display: none;
					}
				</style>
				<script type="text/javascript">
					jQuery(document).ready(function ($) {
						$('#edittag #slug').parents('tr.form-field').addClass('hide-if-js');
						$('.form-table .form-field').not('.hide-if-js').css('display', 'table-row');
					});
				</script> <?php
			}
		}

		/**
		 * Add Color picker Field
		 *
		 * @param        $tag
		 * @param string $group
		 */
		function team_add_custom_field( $tag, $group = '' ) {

			$tax = get_taxonomy( $group );
			$this->get_team_meta( 'email_address' );

			if ( $this->is_edit_team( 'edit' ) ) {
				?>

				<h3><?php _e( 'User Group Settings', 'rtlib' ); ?></h3>

				<table class="form-table">
					<tbody>
					<tr class="form-field">
						<th scope="row" valign="top"><label
								for="term_meta[email_address]"><?php _e( 'Email Address', 'rtbiz' ); ?></label>
						</th>
						<td>
							<input type="text" name="<?php echo esc_attr( self::$slug ); ?>[email_address]"
							       id="<?php echo esc_attr( self::$slug ); ?>[email_address]"
							       value="<?php echo esc_html( $this->get_team_meta( 'email_address' ) ); ?>"/>

							<p class="description"><?php _e( 'Enter an email address for Team', 'rtcamp' ); ?></p>
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

					<p class="description"><?php _e( 'Enter an email address for Team', 'rtcamp' ); ?></p>
				</div>
			<?php }
		}

		/**
		 * add view action for Team
		 */
		function row_actions( $actions, $term ) {
			$actions['view'] = sprintf( __( '%sView%s', 'rtbiz' ), '<a href="' . esc_url( add_query_arg( array( self::$slug => $term->slug ) ), admin_url( 'users.php' ) ) . '">', '</a>' );

			return $actions;
		}

		/**
		 * Save team taxonomy
		 *
		 * @param type $term_id
		 */
		function save_team( $term_id ) {
			if ( isset( $_POST[ self::$slug ] ) ) {
				$prev_value = Rt_Lib_Taxonomy_Metadata\get_term_meta( $term_id, '_' . self::$slug . '_meta', true );
				$meta_value = (array) $_POST[ self::$slug ];
				Rt_Lib_Taxonomy_Metadata\update_term_meta( $term_id, '_' . self::$slug . '_meta', $meta_value, $prev_value );
				if ( isset( $_POST['_wp_original_http_referer'] ) ) {
					wp_safe_redirect( $_POST['_wp_original_http_referer'] );
					exit();
				}
			}
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
		function manage_team_column_body( $display, $column, $term_id ) {
			switch ( $column ) {
				case 'contacts':
					$term           = get_term( $term_id, self::$slug );
					$contacts_count = count( rtbiz_get_team_contacts( $term_id ) );
					$module         = '';
					$module         = apply_filters( 'rtbiz_current_module_name', $module );
					echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=' . rtbiz_get_contact_post_type() . '&contact_group=staff' . $module . '&' . self::$slug . '=' . $term->slug ) ) . '">' . $contacts_count . '</a>';
					break;
				case 'email_address';
					$email_address = $this->get_team_meta( 'email_address', $term_id );
					if ( isset( $email_address ) && ! empty( $email_address ) ) {
						echo esc_html( $email_address );
					}
					break;
			}

			return;
		}

		/**
		 * add Team List View columns
		 *
		 * @param type $columns
		 *
		 * @return type
		 */
		function manage_team_column_header( $columns ) {

			unset( $columns['posts'], $columns['slug'] );

			$columns['contacts'] = __( 'Staff', 'rtbiz' );
			//			$columns['color']         = __( 'Color', 'rtbiz' );
			$columns['email_address'] = __( 'Email Address', 'rtbiz' );

			return $columns;
		}

		/**
		 * get meta for team
		 *
		 * @param string $key
		 * @param int $term_id
		 *
		 * @return bool
		 */
		function get_team_meta( $key = '', $term_id = 0 ) {

			if ( isset( $_GET['tag_ID'] ) ) {
				$term_id = $_GET['tag_ID'];
			}
			if ( empty( $term_id ) ) {
				return false;
			}

			$term_meta = Rt_Lib_Taxonomy_Metadata\get_term_meta( $term_id, '_' . self::$slug . '_meta', true );
			if ( ! empty( $term_meta ) ) {
				if ( ! empty( $key ) ) {
					return isset( $term_meta[ $key ] ) ? $term_meta[ $key ] : false;
				} else {
					return $term_meta;
				}
			}

			return false;
		}

		function add_manage_acl_button( $taxonomy ) {
			global $pagenow;
			if ( ! is_plugin_active( 'rtbiz-helpdesk/rtbiz-helpdesk.php' ) ) {
				if ( 'edit-tags.php' == $pagenow && ! empty( $_REQUEST['taxonomy'] ) && $_REQUEST['taxonomy'] == self::$slug ) {
					$acl_url = admin_url( 'admin.php?page=' . Rtbiz_Access_Control::$page_slug );
					echo '<div class="updated" style="padding: 10px 10px 10px;">You can manage ACL for these Team from <a href="' . esc_url( $acl_url ) . '">Access Control</a></div>';
				}
			}
		}

		/**
		 * is_edit_user_group
		 *
		 * @param bool $page
		 *
		 * @return bool
		 */
		function is_edit_team( $page = false ) {
			global $pagenow;

			if ( ( ! $page || 'edit' === $page ) && 'edit-tags.php' === $pagenow && isset( $_GET['action'] ) && 'edit' === $_GET['action'] && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === self::$slug ) {
				return true;
			}

			if ( ( ! $page || 'all' === $page ) && 'edit-tags.php' === $pagenow && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === self::$slug && ( ! isset( $_GET['action'] ) || 'edit' !== $_GET['action'] ) ) {
				return true;
			}

			return false;
		}

	}
}
