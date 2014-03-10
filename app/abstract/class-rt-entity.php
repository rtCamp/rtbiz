<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Description of rt-contacts-abstrast-entity
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Entity' ) ) {
	abstract class Rt_Entity {

		public $enabled_post_types = array();
		public $post_type;
		public $meta_fields = array();
		public $meta_key_prefix = 'rt_contacts_';

		public function __construct( $post_type ) {
			$this->post_type = $post_type;
			$this->hooks();
		}

		function hooks() {

			if ( is_admin() ) {
				add_filter( 'manage_'.$this->post_type.'_posts_columns', array( $this, 'post_table_columns' ), 10, 1 );
				add_action( 'manage_'.$this->post_type.'_posts_custom_column', array( $this, 'manage_post_table_columns' ), 10, 2 );

				add_action( 'add_meta_boxes', array( $this, 'entity_meta_boxes' ) );
				add_action( 'admin_init', array( $this, 'entity_meta_boxes' ) );
				add_action( 'save_post', array( $this, 'save_entity_details' ) );

				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );

				add_filter( 'gettext', array( $this, 'change_publish_button' ), 10, 2 );
			}

			do_action( 'rt_contacts_entity_hooks', $this );
		}

		function change_publish_button( $translation, $text ) {
			if ( $this->post_type == get_post_type() && $text == 'Publish' )
				return 'Add';

			return $translation;
		}

		function enqueue_scripts_styles() {
			global $post;
			if ( isset( $post->post_type ) && $post->post_type == $this->post_type && !wp_script_is( 'jquery-ui-autocomplete' ) ) {
				wp_enqueue_script('jquery-ui-autocomplete', '', array('jquery-ui-widget', 'jquery-ui-position'), '1.9.2',true);
			}
		}

		function entity_meta_boxes() {
			add_meta_box( 'rt-contacts-entity-details', __( 'Additional Details' ), array( $this, 'render_additional_details_meta_box' ), $this->post_type );
		}

		function render_additional_details_meta_box( $post ) {
			foreach ( $this->meta_fields as $field ) {
				if ( isset( $field['is_autocomplete'] ) && isset( $field['data_source'] ) && $field['is_autocomplete'] && $field['data_source'] == 'WP_User' ) {
					$user_id = get_post_meta( $post->ID, $field['key'], true );
				?>
					<div class="form-field-2">
						<?php if ( isset( $field['label'] ) ) { ?><label><?php echo $field['label']; ?></label><?php } ?>
						<input type="text" class="<?php echo $field['autocomplete_class']; ?>" />
						<div id="<?php echo $field['selected_div_id']; ?>">
							<?php if ( $user_id ) { ?>
								<div id='<?php echo $field['selected_item_id_prefix'].$user_id; ?>'>
									<?php
									$acuser = new WP_User( intval( $user_id ) );
									echo get_avatar( $acuser->user_email, 32 );
									echo $acuser->display_name;
									?>
									&nbsp;<a href='<?php echo $field['remove_item_href'] ?>'>X</a>
								</div>
							<?php } ?>
						</div>
						<input type='hidden' <?php echo ( isset( $field['name'] ) ) ? 'name="'.$field['name'].'"' : ''; ?> <?php echo ( isset( $field['id'] ) ) ? 'id="'.$field['id'].'"' : ''; ?> value='<?php echo ( isset( $user_id ) ) ? $user_id : ''; ?>' <?php echo ( isset( $field['class'] ) ) ? 'class="'.$field['class'].'"' : ''; ?> />
						<?php echo ( isset( $field['description'] ) ) ? '<p class="description">'.$field['description'].'</p>' : ''; ?>
					</div>
				<?php } else if ( isset( $field['is_multiple'] ) && $field['is_multiple'] ) {
					$values = get_post_meta( $post->ID, $field['key'] );
				?>
					<div class="form-field">
						<?php if ( isset( $field['label'] ) ) { ?><label><?php echo $field['label']; ?></label><?php } ?>
						<input <?php echo ( isset( $field['type'] ) ) ? 'type="'.$field['type'].'"' : ''; ?> <?php echo ( isset( $field['name'] ) ) ? 'name="'.$field['name'].'"' : ''; ?> <?php echo ( isset( $field['class'] ) ) ? 'class="'.$field['class'].'"' : ''; ?>><button data-type='<?php echo ( stristr( $field['key'], 'email' ) != false ) ? 'email' : ''; ?>' type='button' class='button button-primary add-multiple'>+</button>
						<?php foreach ( $values as $value ) { ?>
							<input <?php echo ( isset( $field['type'] ) ) ? 'type="'.$field['type'].'"' : ''; ?> <?php echo ( isset( $field['name'] ) ) ? 'name="'.$field['name'].'"' : ''; ?> value = '<?php echo $value; ?>' <?php echo ( isset( $field['class'] ) ) ? 'class="'.$field['class'].'"' : ''; ?>>
							<button type='button' class='button delete-multiple'> - </button>
						<?php } ?>
						<?php echo ( isset( $field['description'] ) ) ? '<p class="description">'.$field['description'].'</p>' : ''; ?>
					</div>
				<?php } else if( isset( $field['type'] ) && $field['type'] == 'textarea' ) {
					$values = get_post_meta( $post->ID, $field['key'] );
				?>
					<div class="form-field">
						<?php if ( isset( $field['label'] ) ) { ?><label><?php echo $field['label']; ?></label><?php } ?>
						<textarea <?php echo ( isset( $field['name'] ) ) ? 'name="'.$field['name'].'"' : ''; ?> <?php echo ( isset( $field['id'] ) ) ? 'id="'.$field['id'].'"' : ''; ?>><?php echo $value; ?></textarea>
						<?php echo ( isset( $field['description'] ) ) ? '<p class="description">'.$field['description'].'</p>' : ''; ?>
					</div>
				<?php } else {
					$values = get_post_meta( $post->ID, $field['key'] );
				?>
					<div class="form-field">
						<?php if ( isset( $field['label'] ) ) { ?><label><?php echo $field['label']; ?></label><?php } ?>
						<input <?php echo ( isset( $field['type'] ) ) ? 'type="'.$field['type'].'"' : ''; ?> <?php echo ( isset( $field['name'] ) ) ? 'name="'.$field['name'].'"' : ''; ?> <?php echo ( isset( $field['id'] ) ) ? 'id="'.$field['id'].'"' : ''; ?> value='<?php echo $value; ?>' >
						<?php echo ( isset( $field['description'] ) ) ? '<p class="description">'.$field['description'].'</p>' : ''; ?>
					</div>
				<?php }
			}
			do_action( 'rt_contacts_render_meta_fields', $post, $this );
			wp_nonce_field( 'rt_contacts_additional_details_metabox', 'rt_contacts_additional_details_metabox_nonce' );
			$this->print_metabox_js();
			do_action( 'rt_contacts_print_metabox_js', $post, $this );
		}

		function print_metabox_js() { }

		function save_entity_details( $post_id ) {
			/*
			 * We need to verify this came from the our screen and with proper authorization,
			 * because save_post can be triggered at other times.
			 */

			// Check if our nonce is set.
			if ( ! isset( $_POST['rt_contacts_additional_details_metabox_nonce'] ) ) {
				return;
			}

			$nonce = $_POST['rt_contacts_additional_details_metabox_nonce'];

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $nonce, 'rt_contacts_additional_details_metabox' ) ) {
				return;
			}

			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			/* OK, its safe for us to save the data now. */

			$this->save_meta_values( $post_id );
		}

		function save_meta_values( $post_id ) {
			do_action( 'rt_contacts_save_entity_meta', $post_id, $this );
		}

		function post_table_columns( $columns ) {
			return apply_filters( 'rt_entity_columns', $columns, $this );
		}

		function manage_post_table_columns( $column, $post_id ) {
			do_action( 'rt_entity_manage_columns', $column, $post_id, $this );
		}

		function init_connection( $post_type, $label ) {
			add_action( 'p2p_init', array( $this, 'create_connection' ) );
			$this->enabled_post_types[$post_type] = $label;
		}

		function create_connection() {
			foreach ( $this->enabled_post_types as $post_type => $label ) {
				if ( function_exists( 'p2p_register_connection_type' ) ) {
					p2p_register_connection_type( array(
						'name' => $post_type.'_to_'.$this->post_type,
						'from' => $post_type,
						'to' => $this->post_type,
					) );
				}
			}
		}

		function connect_post_to_entity( $post_type, $from = '', $to = '', $clear_old = false ) {
			if ( function_exists( 'p2p_create_connection' ) && function_exists( 'p2p_connection_exists' ) && function_exists( 'p2p_delete_connections' ) ) {
				if ( $clear_old ) {
					p2p_delete_connections( $post_type.'_to_'.$this->post_type, array( 'from' => $from ) );
				}
				if ( ! p2p_connection_exists( $post_type.'_to_'.$this->post_type, array( 'from' => $from, 'to' => $to ) ) ) {
					p2p_create_connection( $post_type.'_to_'.$this->post_type, array( 'from' => $from, 'to' => $to ) );
				}
			}
		}

		static function connection_to_string( $post_id, $connection, $term_seperator = ' , ' ) {
			$post = get_post( $post_id );
			$termsArr = get_posts(array(
				'connected_type' => $post->post_type.'_to_'.$connection,
				'connected_items' => $post,
				'nopaging' => true,
				'suppress_filters' => false,
			));
			$tmpStr = '';
			if( $termsArr ) {
				$sep = '';
				foreach ( $termsArr as $tObj ) {
					$tmpStr .= $sep . $tObj->post_title;
					$sep = $term_seperator;
				}
			}
			return $tmpStr;
		}

		function register_post_type( $name, $labels = array(), $menu_icon = '' ) {
			$args = array(
				'labels' => $labels,
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true, // Show the UI in admin panel
				'show_in_nav_menus' => true,
				'show_in_menu' => 'rt-contacts',
				'show_in_admin_bar' => true,
				'menu_icon' => $menu_icon,
				'supports' => array( 'title', 'editor', 'author', 'comments', 'thumbnail' ),
				'capability_type' => $name,
			);
			register_post_type( $name, $args );
		}

		function get_posts_for_entity( $post_id, $post_type, $fetch_entity = false ) {
			$args = array(
				'post_type' => $post_type,
				'post_status' => 'any',
				'connected_type' => $post_type.'_to_'.$this->post_type,
				'connected_items' => $post_id,
				'nopaging' => true,
				'suppress_filters' => false,
			);

			if ( $fetch_entity ) {
				$args['post_type'] = $this->post_type;
			}

			return get_posts( $args );
		}

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

		static function add_meta( $id, $key, $value, $unique = false ) {
			add_post_meta( $id, $this->meta_key_prefix.$key, $value, $unique );
		}

		static function get_meta( $id, $key, $single = false ) {
			return get_post_meta( $id, $this->meta_key_prefix.$key, $single );
		}

		static function update_meta( $id, $key, $value, $prev_value = '' ) {
			update_post_meta( $id, $this->meta_key_prefix.$key, $value, $prev_value );
		}

		static function delete_meta( $id, $key, $value = '' ) {
			delete_post_meta( $id, $this->meta_key_prefix.$key, $value );
		}

		function add() {

		}

		function update() {

		}

		function delete() {

		}

		function search( $query ) {
			$entity = new WP_Query(array(
				'post_type' => rtcrm_post_type_name('contact'),
				'post_status' => 'any',
				'posts_per_page' => 10,
				's' => $query,
			));

			return $entity->posts;
		}
	}
}
