<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Description of class-rt-entity
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Entity' ) ) {

	/**
	 * Class Rt_Entity
	 *
	 * An abstract class for Rt_Person & Rt_Organization - Core Modules of rtBiz.
	 * This will handle most of the functionalities of these two entities.
	 *
	 * If at all any individual entity wants to change the behavior for itself
	 * then it will override that particular method in the its child class
	 */
	abstract class Rt_Entity {

		/**
		 * This array will hold all the post types that are meant to be connected with ORganization / Person
		 * Other plugin addons will register their useful post type here in the array and accordingly will be connected
		 * with person / organization via Posts 2 Posts
		 *
		 * @var array
		 */
		public $enabled_post_types = array();

		/**
		 * @var - Entity Core Post Type (Organization / Person)
		 */
		public $post_type;

		/**
		 * @var - Post Type Labels (Organization / Person)
		 */
		public $labels;

		/**
		 * @var array - Meta Fields Keys for Entity (Organzation / Person)
		 */
		public $meta_fields = array();

		/**
		 * @var string - Meta Key Prefix
		 */
		public static $meta_key_prefix = 'rt_biz_';

		/**
		 * @param $post_type
		 */
		public function __construct( $post_type ) {
			$this->post_type = $post_type;
			$this->hooks();
		}

		/**
		 *  Register Rt_Entity Core Post Type
		 */
		function init_entity() {
			$this->register_post_type( $this->post_type, $this->labels );
		}

		/**
		 *  Actions/Filtes used by Rt_Entity
		 */
		function hooks() {

			if ( is_admin() ) {
				add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'post_table_columns' ), 10, 1 );
				add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'manage_post_table_columns' ), 10, 2 );

				add_action( 'add_meta_boxes', array( $this, 'entity_meta_boxes' ) );
				add_action( 'admin_init', array( $this, 'entity_meta_boxes' ) );
				add_action( 'save_post', array( $this, 'save_entity_details' ) );

				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );

				add_filter( 'gettext', array( $this, 'change_publish_button' ), 10, 2 );
			}

			add_action( 'wp_ajax_seach_user_from_name', array( $this, 'get_user_from_name' ) );

			do_action( 'rt_biz_entity_hooks', $this );
		}

		/**
		 * @param $translation
		 * @param $text
		 * @return string
		 */
		function change_publish_button( $translation, $text ) {
			if ( $this->post_type == get_post_type() && $text == 'Publish' )
				return 'Add';

			return $translation;
		}

		/**
		 *
		 */
		function enqueue_scripts_styles() {
			global $post;
			if ( isset( $post->post_type ) && $post->post_type == $this->post_type && ! wp_script_is( 'jquery-ui-autocomplete' ) ) {
				wp_enqueue_script( 'jquery-ui-autocomplete', '', array( 'jquery-ui-widget', 'jquery-ui-position' ), '1.9.2', true );
			}
		}

		/**
		 * Registers Meta Box for Rt_Entity Meta Fields - Additional Information for Rt_Entity
		 */
		function entity_meta_boxes() {
			add_meta_box( 'rt-biz-entity-details', __( 'Additional Details' ), array( $this, 'render_additional_details_meta_box' ), $this->post_type );
			do_action( 'rt_biz_entity_meta_boxes', $this->post_type );
                        add_meta_box( 'rt-biz-contacts-details', __( 'Contacts' ), array( $this, 'render_contact_details_meta_box' ), $this->post_type, 'side', 'low' );
			
		}

		/**
		 *
		 * Render Additional Info MetaBox
		 *
		 * @param $post
		 */
		function render_additional_details_meta_box( $post ) {
			do_action( 'rt_biz_before_render_meta_fields', $post, $this );
			foreach ( $this->meta_fields as $field ) {
				$field = apply_filters( 'rt_entity_fields_loop_single_field', $field );
				$is_our_team_mate = get_post_meta( $post->ID, Rt_Person::$meta_key_prefix.Rt_Person::$our_team_mate_key, true );
				if( empty( $is_our_team_mate ) && isset( $field['hide_for_client'] ) && $field['hide_for_client'] ) {
					continue;
				}
				if ( isset( $field[ 'is_autocomplete' ] ) && isset( $field[ 'data_source' ] ) && $field[ 'is_autocomplete' ] && $field[ 'data_source' ] == 'WP_User' ) {
					$user_id = self::get_meta( $post->ID, $field[ 'key' ], true );
					?>
					<div class="form-field-2">
						<?php if ( isset( $field[ 'label' ] ) ) { ?><label><?php echo $field[ 'label' ]; ?></label><?php } ?>
						<input type="text" class="<?php echo $field[ 'autocomplete_class' ]; ?>" />
						<div id="<?php echo $field[ 'selected_div_id' ]; ?>">
							<?php if ( $user_id ) { ?>
								<div id='<?php echo $field[ 'selected_item_id_prefix' ] . $user_id; ?>'>
									<?php
									$acuser = new WP_User( intval( $user_id ) );
									echo get_avatar( $acuser->user_email, 32 );
									echo $acuser->display_name;
									?>
									&nbsp;<a href='<?php echo $field[ 'remove_item_href' ] ?>'>X</a>
								</div>
							<?php } ?>
						</div>
						<input type='hidden' <?php echo ( isset( $field[ 'name' ] ) ) ? 'name="' . $field[ 'name' ] . '"' : ''; ?> <?php echo ( isset( $field[ 'id' ] ) ) ? 'id="' . $field[ 'id' ] . '"' : ''; ?> value='<?php echo ( isset( $user_id ) ) ? $user_id : ''; ?>' <?php echo ( isset( $field[ 'class' ] ) ) ? 'class="' . $field[ 'class' ] . '"' : ''; ?> />
						<?php echo ( isset( $field[ 'description' ] ) ) ? '<p class="description">' . $field[ 'description' ] . '</p>' : ''; ?>
					</div>
					<?php
				} else if ( isset( $field[ 'is_datepicker' ] ) && $field[ 'is_datepicker' ] ) {
					$values = self::get_meta( $post->ID, $field[ 'key' ], true );
					?>
					<script>
						jQuery( document ).ready( function( $ ) {
							$( document ).on( 'focus', ".datepicker", function() {
								$( this ).datepicker( {
									'dateFormat': 'dd/mm/yy'
								} );
							} );
						} );
					</script>
					<div class="form-field">
						<?php if ( isset( $field[ 'label' ] ) ) { ?><label><?php echo $field[ 'label' ]; ?></label><?php } ?>
						<input type="text" <?php echo ( isset( $field[ 'name' ] ) ) ? 'name="' . $field[ 'name' ] . '"' : ''; ?> <?php echo ( isset( $field[ 'id' ] ) ) ? 'id="' . $field[ 'id' ] . '"' : ''; ?> value='<?php echo $values; ?>' <?php echo ( isset( $field[ 'class' ] ) ) ? 'class="datepicker ' . $field[ 'class' ] . '"' : 'class="datepicker"'; ?>>
						<?php echo ( isset( $field[ 'description' ] ) ) ? '<p class="description">' . $field[ 'description' ] . '</p>' : ''; ?>
					</div>
					<?php
				} else if ( isset( $field[ 'is_multiple' ] ) && $field[ 'is_multiple' ] ) {
					$values = self::get_meta( $post->ID, $field[ 'key' ] );
					?>
					<div class="form-field">
						<?php if ( isset( $field[ 'label' ] ) ) { ?><label><?php echo $field[ 'label' ]; ?></label><?php } ?>
						<input <?php echo ( isset( $field[ 'type' ] ) ) ? 'type="' . $field[ 'type' ] . '"' : ''; ?> <?php echo ( isset( $field[ 'name' ] ) ) ? 'name="' . $field[ 'name' ] . '"' : ''; ?> <?php echo ( isset( $field[ 'class' ] ) ) ? 'class="' . $field[ 'class' ] . '"' : ''; ?>><button data-type='<?php echo ( stristr( $field[ 'key' ], 'email' ) != false ) ? 'email' : ''; ?>' type='button' class='button button-primary add-multiple'>+</button>
						<?php foreach ( $values as $value ) { ?>
							<input <?php echo ( isset( $field[ 'type' ] ) ) ? 'type="' . $field[ 'type' ] . '"' : ''; ?> <?php echo ( isset( $field[ 'name' ] ) ) ? 'name="' . $field[ 'name' ] . '"' : ''; ?> value = '<?php echo $value; ?>' <?php echo ( isset( $field[ 'class' ] ) ) ? 'class="' . $field[ 'class' ] . '"' : ''; ?>>
							<button type='button' class='button delete-multiple'> - </button>
						<?php } ?>
						<?php echo ( isset( $field[ 'description' ] ) ) ? '<p class="description">' . $field[ 'description' ] . '</p>' : ''; ?>
					</div>
					<?php
				} else if ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'textarea' ) {
					$values = self::get_meta( $post->ID, $field[ 'key' ], true );
					?>
					<div class="form-field">
						<?php if ( isset( $field[ 'label' ] ) ) { ?><label><?php echo $field[ 'label' ]; ?></label><?php } ?>
						<textarea <?php echo ( isset( $field[ 'name' ] ) ) ? 'name="' . $field[ 'name' ] . '"' : ''; ?> <?php echo ( isset( $field[ 'id' ] ) ) ? 'id="' . $field[ 'id' ] . '"' : ''; ?> <?php echo ( isset( $field[ 'class' ] ) ) ? 'class="' . $field[ 'class' ] . '"' : ''; ?>><?php echo $values; ?></textarea>
						<?php echo ( isset( $field[ 'description' ] ) ) ? '<p class="description">' . $field[ 'description' ] . '</p>' : ''; ?>
					</div>
					<?php
				} else if ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'user_group' ) {
					$user_id = self::get_meta( $post->ID, $field[ 'key' ], true );
					if ( empty( $user_id ) ) {
						continue;
					}
					?>
					<div class="">
						<?php call_user_func( $field[ 'data_source' ], new WP_User( $user_id ) ); ?>
						<?php echo ( isset( $field[ 'description' ] ) ) ? '<p class="description">' . $field[ 'description' ] . '</p>' : ''; ?>
					</div>
					<?php
				} else {
					$values = self::get_meta( $post->ID, $field[ 'key' ], true );
					?>
					<div class="form-field">
						<?php if ( isset( $field[ 'label' ] ) ) { ?><label><?php echo $field[ 'label' ]; ?></label><?php } ?>
						<input <?php echo ( isset( $field[ 'type' ] ) ) ? 'type="' . $field[ 'type' ] . '"' : ''; ?> <?php echo ( isset( $field[ 'name' ] ) ) ? 'name="' . $field[ 'name' ] . '"' : ''; ?> <?php echo ( isset( $field[ 'id' ] ) ) ? 'id="' . $field[ 'id' ] . '"' : ''; ?> value='<?php echo $values; ?>' <?php echo ( isset( $field[ 'class' ] ) ) ? 'class="' . $field[ 'class' ] . '"' : ''; ?>>
						<?php echo ( isset( $field[ 'description' ] ) ) ? '<p class="description">' . $field[ 'description' ] . '</p>' : ''; ?>
					</div>
					<?php
				}
			}
			do_action( 'rt_biz_after_render_meta_fields', $post, $this );
			wp_nonce_field( 'rt_biz_additional_details_metabox', 'rt_biz_additional_details_metabox_nonce' );
			$this->print_metabox_js();
			do_action( 'rt_biz_print_metabox_js', $post, $this );
		}
                
		function render_contact_details_meta_box( $post ) {
                    global $current_user;
                    if ($current_user->roles[0] == 'administrator') {
                            $user_id = self::get_meta( $post->ID, 'contact_user_id', true );
					?>
					<div class="form-field-2">
						<label><?php_e( 'Select user for contact' ) ?></label>
						<input type="text" class="user-autocomplete" />
						<div id="selected-user-contact">
							<?php if ( $user_id ) { ?>
								<div id='<?php echo 'subscribe-auth-' . $user_id; ?>'>
									<?php
									$acuser = new WP_User( intval( $user_id ) );
									echo get_avatar( $acuser->user_email, 32 );
									echo $acuser->display_name;
									?>
									&nbsp;<a href='#deleteContactUser'>X</a>
								</div>
							<?php } ?>
						</div>
                                                <input type='hidden' name="contact_meta[contact_user_id]" id="contact_meta_userid"  value='<?php echo ( isset( $user_id ) ) ? $user_id : ''; ?>' class="" />
						<?php echo '<p class="description">' . __( 'User to which this contact belongs.' ) . '</p>'  ?>
					</div>
					<?php
                    }
            }
		/**
		 *  MetaBox JS - Overridden in Child Classes - Rt_Organization & Rt_Person
		 */
		function print_metabox_js() {

		}

		/**
		 *
		 * Saves Additional Info from MetaBox
		 *
		 * @param $post_id
		 */
		function save_entity_details( $post_id ) {
			/*
			 * We need to verify this came from the our screen and with proper authorization,
			 * because save_post can be triggered at other times.
			 */

			// Check if our nonce is set.
			if ( ! isset( $_POST[ 'rt_biz_additional_details_metabox_nonce' ] ) ) {
				return;
			}

			$nonce = $_POST[ 'rt_biz_additional_details_metabox_nonce' ];

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $nonce, 'rt_biz_additional_details_metabox' ) ) {
				return;
			}

			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			/* OK, its safe for us to save the data now. */

			$this->save_meta_values( $post_id );
		}

		/**
		 *
		 * Overridden in Child Classes
		 *
		 * @param $post_id
		 */
		function save_meta_values( $post_id ) {
			do_action( 'rt_biz_save_entity_meta', $post_id, $this );
		}

		/**
		 *
		 * Overridden in Child Classes
		 *
		 * @param $columns
		 * @return mixed|void
		 */
		function post_table_columns( $columns ) {
			return apply_filters( 'rt_entity_columns', $columns, $this );
		}

		/**
		 *
		 * Overridden in Child Classes
		 *
		 * @param $column
		 * @param $post_id
		 */
		function manage_post_table_columns( $column, $post_id ) {
			do_action( 'rt_entity_manage_columns', $column, $post_id, $this );
		}

		/**
		 *
		 * Registers post type for Connection with Rt_Entity (Organization / Person)
		 *
		 * @param $post_type
		 * @param $label
		 */
		function init_connection( $post_type, $label ) {
			add_action( 'p2p_init', array( $this, 'create_connection' ) );
			$this->enabled_post_types[ $post_type ] = $label;
		}

		/**
		 *  Create a connection between registered post types and Rt_Entity
		 */
		function create_connection() {
			foreach ( $this->enabled_post_types as $post_type => $label ) {
				p2p_register_connection_type( array(
					'name' => $post_type . '_to_' . $this->post_type,
					'from' => $post_type,
					'to' => $this->post_type,
				) );
			}
		}

		function clear_post_connections_to_entity( $post_type, $from ) {
			p2p_delete_connections( $post_type . '_to_' . $this->post_type, array( 'from' => $from ) );
		}

		/**
		 *
		 *
		 *
		 * @param $post_type
		 * @param string $from
		 * @param string $to
		 */
		function connect_post_to_entity( $post_type, $from = '', $to = '' ) {
			if ( ! p2p_connection_exists( $post_type . '_to_' . $this->post_type, array( 'from' => $from, 'to' => $to ) ) ) {
				p2p_create_connection( $post_type . '_to_' . $this->post_type, array( 'from' => $from, 'to' => $to ) );
			}
		}

		/**
		 *
		 * Converts Connections into String form. Kind of toString method.
		 *
		 * @param $post_id
		 * @param $connection
		 * @param string $term_seperator
		 * @return string
		 */
		static function connection_to_string( $post_id, $connection, $term_seperator = ' , ' ) {
			$post = get_post( $post_id );
			$termsArr = get_posts( array(
				'connected_type' => $post->post_type . '_to_' . $connection,
				'connected_items' => $post,
				'nopaging' => true,
				'suppress_filters' => false,
					) );
			$tmpStr = '';
			if ( $termsArr ) {
				$sep = '';
				foreach ( $termsArr as $tObj ) {
					$tmpStr .= $sep . $tObj->post_title;
					$sep = $term_seperator;
				}
			}
			return $tmpStr;
		}

		/**
		 * @param $name
		 * @param array $labels
		 */
		function register_post_type( $name, $labels = array() ) {
			$args = array(
				'labels' => $labels,
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true, // Show the UI in admin panel
				'show_in_nav_menus' => false,
				'show_in_menu' => false,
				'show_in_admin_bar' => false,
//				'menu_icon' => $menu_icon,
				'supports' => array( 'title', 'editor', 'author', 'comments', 'thumbnail' ),
				'capability_type' => $name,
			);
			register_post_type( $name, $args );
		}

		/**
		 * @param $post_id
		 * @param $post_type
		 * @param bool $fetch_entity
		 * @return array
		 */
		function get_posts_for_entity( $post_id, $post_type, $fetch_entity = false ) {
			$args = array(
				'post_type' => $post_type,
				'post_status' => 'any',
				'connected_type' => $post_type . '_to_' . $this->post_type,
				'connected_items' => $post_id,
				'nopaging' => true,
				'suppress_filters' => false,
			);

			if ( $fetch_entity ) {
				$args[ 'post_type' ] = $this->post_type;
			}

			return get_posts( $args );
		}

		/**
		 *
		 * Returns Rt_Entity Caps
		 *
		 * @return array
		 */
		function get_post_type_capabilities() {
			return array(
				"edit_{$this->post_type}" => true,
				"read_{$this->post_type}" => true,
				"delete_{$this->post_type}" => true,
				"edit_{$this->post_type}s" => true,
				"edit_others_{$this->post_type}s" => true,
				"publish_{$this->post_type}s" => true,
				"read_private_{$this->post_type}s" => true,
				"delete_{$this->post_type}s" => true,
				"delete_private_{$this->post_type}s" => true,
				"delete_published_{$this->post_type}s" => true,
				"delete_others_{$this->post_type}s" => true,
				"edit_private_{$this->post_type}s" => true,
				"edit_published_{$this->post_type}s" => true,
			);
		}

		/**
		 * @param $id
		 * @param $key
		 * @param $value
		 * @param bool $unique
		 */
		static function add_meta( $id, $key, $value, $unique = false ) {
			add_post_meta( $id, self::$meta_key_prefix . $key, $value, $unique );
		}

		/**
		 * @param $id
		 * @param $key
		 * @param bool $single
		 * @return mixed
		 */
		static function get_meta( $id, $key, $single = false ) {
			return get_post_meta( $id, self::$meta_key_prefix . $key, $single );
		}

		/**
		 * @param $id
		 * @param $key
		 * @param $value
		 * @param string $prev_value
		 */
		static function update_meta( $id, $key, $value, $prev_value = '' ) {
			update_post_meta( $id, self::$meta_key_prefix . $key, $value, $prev_value );
		}

		/**
		 * @param $id
		 * @param $key
		 * @param string $value
		 */
		static function delete_meta( $id, $key, $value = '' ) {
			delete_post_meta( $id, self::$meta_key_prefix . $key, $value );
		}

		/**
		 *
		 */
		function add() {

		}

		/**
		 *
		 */
		function update() {

		}

		/**
		 *
		 */
		function delete() {

		}

		/**
		 * @param $query
		 * @return array
		 */
		function search( $query, $args = array() ) {
			$query_args = array(
				'post_type' => $this->post_type,
				'post_status' => 'any',
				'posts_per_page' => 10,
				's' => $query,
			);
			$args = array_merge( $query_args, $args );
			$entity = new WP_Query( $args );

			return $entity->posts;
		}

		function get_user_from_name() {
			if ( ! isset( $_POST[ 'query' ] ) ) {
				wp_die( 'Invalid request Data' );
			}
			$query = $_POST[ 'query' ];
			global $wpdb;

			$results = $wpdb->get_results( "select ID,display_name,user_email from $wpdb->users where user_email like '%{$query}%' or display_name like '%{$query}%' or user_nicename like '%{$query}%' ;" );
			$arrReturn = array();
			foreach ( $results as $author ) {
				$arrReturn[] = array( "id" => $author->ID, "label" => $author->display_name, "imghtml" => get_avatar( $author->user_email, 25 ) );
			}
			header( 'Content-Type: application/json' );
			echo json_encode( $arrReturn );
			die( 0 );
		}

	}

}
