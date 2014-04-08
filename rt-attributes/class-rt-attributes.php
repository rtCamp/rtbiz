<?php
/**
 * Created by PhpStorm.
 * User: faishal
 * Date: 24/02/14
 * Time: 3:45 PM
 */
/**
 * Description of RT_Attributes
 * Main class for Attributes.
 * This will be responsible for registering taxonomies to given post type
 * and displaying available attributes
 *
 * @author Udit Desai <desaiuditd@gmail.com>
 */

if ( ! class_exists( 'RT_Attributes' ) ) {
	/**
	 * Class RT_WP_Attributes
	 */
	class RT_Attributes {

		/**
		 * @var $module_id - unique module id for which this class is called. This can be either plugin slug or plugin name or any unique identifier that will be used to know which plugin or module is calling the library class. And accordingly that can be mapped to attributes.
		 */
		var $module_name;

		/**
		 * @var $parent_page_slug - Page slug under which the attributes page is to be shown. If null / empty then an individual Menu Page will be added
		 */
		var $parent_page_slug;

		/**
		 * @var $page_slug - Page slug for Attributes Page
		 */
		var $page_slug;

		/**
		 * @var $post_type - If any post type passed, only attributes for those post type will be listed on the page.
		 */
		var $post_type;

		/**
		 * @var $page_cap - Capability for Attributes Admin Page; if not passed, default cap will be 'manage_options'
		 */
		var $page_cap;

		/**
		 * @var $attr_cap - Capability for Attributes (Taxonomy / Terms); if not passed, default cap will be empty array ( array() )
		 */
		var $attr_cap;

		/**
		 * @var $render_type_required - Render Type for the attribute; Dropdown, Checklist, Rating Stars etc.,
		 */
		var $render_type_required;

		/**
		 * @var $storage_type_required - Storage Type for the attributes; Taxonomy or Meta; if not mentioned then Taxonomy will be registered for the attribute by default.
		 */
		var $storage_type_required;

		/**
		 * @var $orderby_required - Sorting Order for the attributes; Order by id, name or default etc.
		 */
		var $orderby_required;

		/**
		 * @var $attributes_db_model - Attributes DB Model Object
		 */
		var $attributes_db_model;

		/**
		 * @var $attributes_relationship_model - Attributes Relationship DB Model Object
		 */
		var $attributes_relationship_model;

		/**
		 * @var $auto_loader - RT_WP_Autoload
		 */
		var $auto_loader;


		/**
		 * @param $module_name - A Unique module name to identify for which module / post_types the attributes i.e., taxonomies are to be registered
		 */
		public function __construct( $module_name ) {
			//Register AutoLoader for attributes
			$this->auto_loader();
			// Database upgrade if required
			$this->db_upgrade();

			$this->module_name = $module_name;
			$this->init_db_model();
		}

		/**
		 *
		 */
		function db_upgrade() {
			$updateDB = new  RT_DB_Update( WP_HELPER_FILE, trailingslashit( dirname( __FILE__ ) ) . 'schema/' );
			$updateDB->db_version_option_name .= '_ATTRIBUTES';
			$updateDB->install_db_version = $updateDB->get_install_db_version();
			$updateDB->do_upgrade();
		}

		/**
		 * Initialize the Database model object which will be used for DB transactions
		 */
		function init_db_model() {
			$this->attributes_db_model           = new RT_Attributes_Model();
			$this->attributes_relationship_model = new RT_Attributes_Relationship_Model();
		}

		function register_attribute_mappings() {
			$relations = $this->attributes_relationship_model->get_all_relations();
			foreach ( $relations as $relation ) {
				$attr = $this->attributes_db_model->get_attribute( $relation->attr_id );
				if ( $attr->attribute_store_as == 'taxonomy' && $attr->module_name == $this->module_name ) {
					$this->register_taxonomy( $relation->post_type, $relation->attr_id, $this->attr_cap );
				}
			}
		}

		function register_taxonomy( $post_type, $attr_id, $caps ) {
			$tax = $this->attributes_db_model->get_attribute( $attr_id );
			$name = $this->get_taxonomy_name( $tax->attribute_name );
			$hierarchical = true;
			if ( $name ) {
				$label = ( isset( $tax->attribute_label ) && $tax->attribute_label ) ? $tax->attribute_label : $tax->attribute_name;
				$show_in_nav_menus = apply_filters( 'rt_wp_attributes_show_in_nav_menus', false, $name );

				register_taxonomy(
					$name,
					apply_filters( 'rt_wp_attributes_taxonomy_objects_' . $name, $post_type ),
					apply_filters(
						'rt_wp_attributes_taxonomy_args_' . $name,
						array(
						'hierarchical' 				=> $hierarchical,
						'update_count_callback' 	=> array( $this, 'update_post_term_count' ),
						'labels' => array(
								'name' 						=> $label,
								'singular_name' 			=> $label,
								'search_items' 				=> __( 'Search' ) . ' ' . $label,
								'all_items' 				=> __( 'All' ) . ' ' . $label,
								'parent_item' 				=> __( 'Parent' ) . ' ' . $label,
								'parent_item_colon' 		=> __( 'Parent' ) . ' ' . $label . ':',
								'edit_item' 				=> __( 'Edit' ) . ' ' . $label,
								'update_item' 				=> __( 'Update' ) . ' ' . $label,
								'add_new_item' 				=> __( 'Add New' ) . ' ' . $label,
								'new_item_name' 			=> __( 'New' ) . ' ' . $label,
							),
						'show_ui' 					=> true,
						'query_var' 				=> true,
						'capabilities'				=> $caps,
						'show_in_nav_menus' 		=> $show_in_nav_menus,
						//'rewrite' 					=> array( 'slug' => $product_attribute_base . sanitize_title( $tax->attribute_name ), 'with_front' => false, 'hierarchical' => $hierarchical ),
						'rewrite' => true,
						)
					)
				);
			}
		}

		function update_post_term_count( $terms, $taxonomy ) {
			global $wpdb;

			$object_types = (array) $taxonomy->object_type;

			foreach ( $object_types as &$object_type ) {
				list( $object_type ) = explode( ':', $object_type );
			}

			$object_types = array_unique( $object_types );

			if ( false !== ( $check_attachments = array_search( 'attachment', $object_types ) ) ) {
				unset( $object_types[ $check_attachments ] );
				$check_attachments = true;
			}

			if ( $object_types ) {
				$object_types = esc_sql( array_filter( $object_types, 'post_type_exists' ) );
			}

			foreach ( (array) $terms as $term ) {
				$count = 0;

				// Attachments can be 'inherit' status, we need to base count off the parent's status if so
				if ( $check_attachments ) {
					$count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts p1 WHERE p1.ID = $wpdb->term_relationships.object_id  AND post_type = 'attachment' AND term_taxonomy_id = %d", $term ) );
				}

				if ( $object_types ) {
					$count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id  AND post_type IN ('" . implode( "', '", $object_types ) . "') AND term_taxonomy_id = %d", $term ) );
				}

				do_action( 'edit_term_taxonomy', $term, $taxonomy );
				$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
				do_action( 'edited_term_taxonomy', $term, $taxonomy );
			}
		}

		/**
		 * @param string $page_slug
		 * @param string $post_type
		 * @param string $cap
		 * @param bool $render_type_required
		 * @param bool $storage_type_required
		 * @param bool $orderby_required
		 */
		function add_attributes_page( $page_slug, $parent_page_slug = '', $post_type = '', $page_cap = 'manage_options', $attr_cap = array(), $render_type_required = false, $storage_type_required = false, $orderby_required = false ) {

			$this->page_slug             = $page_slug;
			$this->parent_page_slug      = $parent_page_slug;
			$this->post_type             = $post_type;
			$this->page_cap              = $page_cap;
			$this->attr_cap              = $attr_cap;
			$this->render_type_required  = $render_type_required;
			$this->storage_type_required = $storage_type_required;
			$this->orderby_required      = $orderby_required;

			add_action( 'admin_menu', array( $this, 'register_attribute_menu' ) );

			$this->register_attribute_mappings();
		}

		/**
		 * This method registers an attributes menu page which will list all the available attributes that can be linked to any post types as per need.
		 */
		function register_attribute_menu() {
			if ( ! empty( $this->parent_page_slug ) ) {
				add_submenu_page( $this->parent_page_slug, __( 'Attributes' ), __( 'Attributes' ), $this->page_cap, $this->page_slug, array( $this, 'render_attributes_page' ) );
			} else {
				add_menu_page( __( 'Attributes' ), __( 'Attributes' ), $this->page_cap, $this->page_slug, array( $this, 'render_attributes_page' ) );
			}
		}

		/**
		 * Renders the Attributes Listing Page
		 */
		function render_attributes_page() {
			// Perform Any action according to query variable passed in the REQUEST
			$action_completed = $this->perform_action();

			// If an attribute was added, edited or deleted: then redirect to the attributes page
			if ( ! empty( $action_completed ) ) {
				wp_redirect( admin_url( 'admin.php?page=' . $this->page_slug ) );
			}

			// Show admin interface
			if ( ! empty( $_GET[ 'edit' ] ) ){
				$this->edit_attribute_ui();
			} else {
				$this->add_attribute_ui();
			}
		}

		/**
		 * Edit Attribute Screen
		 */
		function edit_attribute_ui() {
			include 'templates/template-rt-edit-attribute.php';
		}

		/**
		 * Add Attribute Screen
		 */
		function add_attribute_ui() {
			include 'templates/template-rt-add-attribute.php';
		}

		/**
		 * Sanitizes taxonomy name
		 *
		 * @param $taxonomy
		 *
		 * @return mixed|string
		 */
		function sanitize_taxonomy( $taxonomy ) {
			$taxonomy = strtolower( stripslashes( strip_tags( $taxonomy ) ) );
			$taxonomy = preg_replace( '/&.+?;/', '', $taxonomy ); // Kill entities
			$taxonomy = str_replace( array( '.', '\'', '"' ), '', $taxonomy ); // Kill quotes and full stops.
			$taxonomy = str_replace( array( ' ', '_' ), '-', $taxonomy ); // Replace spaces and underscores.

			return $taxonomy;
		}

		/**
		 * @param $attribute
		 * @return string
		 */
		function get_taxonomy_name( $attribute ) {
			return 'rt_' . $this->sanitize_taxonomy( $attribute );
		}

		/**
		 * Validates Attribute Name, Render Type & Attribute Storage Type
		 *
		 * @param string $action
		 * @param string $attribute_id
		 * @param string $attribute_name
		 * @param string $attribute_render_type
		 * @param string $attribute_store_as
		 *
		 * @return string|void
		 */
		function error_check( $action = '', $attribute_id = '', $attribute_name = '', $attribute_render_type = '', $attribute_store_as = 'taxonomy' ) {
			// Forbidden attribute names
			// http://codex.wordpress.org/Function_Reference/register_taxonomy#Reserved_Terms
			$reserved_terms = array( 'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category', 'category__and', 'category__in', 'category__not_in', 'category_name', 'comments_per_page', 'comments_popup', 'cpage', 'day', 'debug', 'error', 'exact', 'feed', 'hour', 'link_category', 'm', 'minute', 'monthnum', 'more', 'name', 'nav_menu', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id', 'paged', 'pagename', 'pb', 'perm', 'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type', 'post_status', 'post_tag', 'post_type', 'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 's', 'search', 'second', 'sentence', 'showposts', 'static', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in', 'tag__not_in', 'tag_id', 'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb', 'term', 'type', 'w', 'withcomments', 'withoutcomments', 'year', );

			$error = '';

			if ( ! $attribute_name || ! $attribute_name || ( $this->render_type_required && ! $attribute_render_type ) || ( $this->storage_type_required && ! $attribute_store_as ) ) {
				$error = __( 'Please, provide an attribute name, slug, storage type and render type.' );
			} elseif ( strlen( $attribute_name ) >= 28 ) {
				$error = sprintf( __( 'Slug “%s” is too long (28 characters max). Shorten it, please.' ), sanitize_title( $attribute_name ) );
			} elseif ( in_array( $attribute_name, $reserved_terms ) ) {
				$error = sprintf( __( 'Slug “%s” is not allowed because it is a reserved term. Change it, please.' ), sanitize_title( $attribute_name ) );
			} else {
				$taxonomy_exists = $this->attributes_db_model->attribute_exists( $this->sanitize_taxonomy( $attribute_name ) );

				if ( 'add' === $action && $taxonomy_exists ) {
					$error = sprintf( __( 'Slug “%s” is already in use. Change it, please.' ), sanitize_title( $attribute_name ) );
				}
				if ( 'edit' === $action ) {
					$old_attribute_name = $this->attributes_db_model->get_attribute_name( $attribute_id );
					if ( $old_attribute_name != $attribute_name && $this->sanitize_taxonomy( $old_attribute_name ) != $attribute_name && $taxonomy_exists ) {
						$error = sprintf( __( 'Slug “%s” is already in use. Change it, please.' ), sanitize_title( $attribute_name ) );
					}
				}
			}

			return $error;
		}

		/**
		 * Add Attribute to Post Types Mapping. Save it to database
		 * @param $attribute_id
		 * @param $post_types
		 */
		function add_attribute_relations( $attribute_id, $post_types ) {
			foreach ( $post_types as $pt ) {
				$data = array(
					'attr_id' => $attribute_id,
					'post_type' => $pt,
				);
				$this->attributes_relationship_model->add_relation( $data );
			}
			do_action( 'rt_attributes_relations_added', $attribute_id, $post_types );
		}

		function update_attribute_relations( $attribute_id, $post_types ) {
			$old_relations = $this->attributes_relationship_model->get_relations_by_attribute( $attribute_id );
			foreach ( $old_relations as $or ) {
				$this->attributes_relationship_model->delete_relation( array( 'attr_id' => $or->attr_id, 'post_type' => $or->post_type ) );
			}
			foreach ( $post_types as $pt ) {
				$data = array(
					'attr_id' => $attribute_id,
					'post_type' => $pt,
				);
				$this->attributes_relationship_model->add_relation( $data );
			}
			do_action( 'rt_attributes_relations_updated', $attribute_id, $post_types );
		}

		function delete_attribute_relations( $attribute_id ) {
			$relations = $this->attributes_relationship_model->get_relations_by_attribute( $attribute_id );
			foreach ( $relations as $r ) {
				$this->attributes_relationship_model->delete_relation( array( 'attr_id' => $r->attr_id, 'post_type' => $r->post_type ) );
			}
			do_action( 'rt_attributes_relations_deleted', $attribute_id );
		}

		/**
		 * Add attribute in database
		 *
		 * @param $attribute_label
		 * @param $attribute_name
		 * @param $attribute_store_as
		 * @param $attribute_render_type
		 * @param $attribute_orderby
		 */
		function add_attribute( $attribute_label, $attribute_name, $attribute_store_as = 'taxonomy', $attribute_render_type = '', $attribute_orderby = '' ) {

			$attribute = array(
				'module_name' => $this->module_name,
				'attribute_label' => $attribute_label,
				'attribute_name' => $attribute_name,
				'attribute_store_as' => $attribute_store_as,
				'attribute_render_type' => $attribute_render_type,
				'attribute_orderby' => $attribute_orderby,
			);

			return $this->attributes_db_model->add_attribute( $attribute );
		}

		/**
		 * Edit attribute in database
		 *
		 * @param $attribute_id
		 * @param $attribute_label
		 * @param $attribute_name
		 * @param $attribute_store_as
		 * @param $attribute_render_type
		 * @param $attribute_orderby
		 */
		function edit_attribute( $attribute_id, $attribute_label, $attribute_name, $attribute_store_as = 'taxonomy', $attribute_render_type = '', $attribute_orderby = '' ) {

			$attribute = array(
				'attribute_label' => $attribute_label,
				'attribute_name' => $attribute_name,
				'attribute_store_as' => $attribute_store_as,
				'attribute_render_type' => $attribute_render_type,
				'attribute_orderby' => $attribute_orderby,
			);

			$this->attributes_db_model->update_attribute( $attribute, array( 'id' => $attribute_id ) );
		}

		/**
		 * Performs Add, Save, Delete Attributes
		 */
		function perform_action() {
			global $wpdb;

			$action_completed = false;

			// Action to perform: add, edit, delete or none
			$action = '';
			if ( ! empty( $_POST[ 'add_new_attribute' ] ) ) {
				$action = 'add';
			} elseif ( ! empty( $_POST[ 'save_attribute' ] ) && ! empty( $_GET[ 'edit' ] ) ) {
				$action = 'edit';
			} elseif ( ! empty( $_GET[ 'delete' ] ) ) {
				$action = 'delete';
			}

			// Add or edit an attribute
			if ( 'add' === $action || 'edit' === $action ) {

				$attribute_id = '';
				if ( 'edit' === $action ) {
					$attribute_id = absint( $_GET[ 'edit' ] );
				}

				// Grab the submitted data
				$attribute_label       = ( isset( $_POST[ 'attribute_label' ] ) ) ? (string)stripslashes( $_POST[ 'attribute_label' ] ) : '';
				$attribute_name        = ( isset( $_POST[ 'attribute_name' ] ) ) ? $this->sanitize_taxonomy( stripslashes( (string)$_POST[ 'attribute_name' ] ) ) : '';
				$attribute_store_as    = ( $this->storage_type_required && isset( $_POST[ 'attribute_store_as' ] ) ) ? (string)stripslashes( $_POST[ 'attribute_store_as' ] ) : 'taxonomy';
				$attribute_render_type = ( $this->render_type_required && isset( $_POST[ 'attribute_render_type' ] ) ) ? (string)stripslashes( $_POST[ 'attribute_render_type' ] ) : '';
				$attribute_orderby     = ( $this->orderby_required && isset( $_POST[ 'attribute_orderby' ] ) ) ? (string)stripslashes( $_POST[ 'attribute_orderby' ] ) : '';
				$attribute_post_types  = ( isset( $_POST[ 'attribute_post_types' ] ) ) ? (array) $_POST[ 'attribute_post_types' ] : array();

				// Auto-generate the label or slug if only one of both was provided
				if ( ! $attribute_label ) {
					$attribute_label = ucwords( $attribute_name );
				} elseif ( ! $attribute_name ) {
					$attribute_name = $this->sanitize_taxonomy( stripslashes( $attribute_label ) );
				}

				// Error checking
				$error = $this->error_check( $action, $attribute_id, $attribute_name, $attribute_render_type, $attribute_store_as );

				// Show the error message if any
				if ( ! empty( $error ) ) {
					echo '<div id="rt_wp_attributes_errors" class="error fade"><p>' . $error . '</p></div>';
				} else {

					// Add new attribute
					if ( 'add' === $action ) {
						$attribute_id = $this->add_attribute( $attribute_label, $attribute_name, $attribute_store_as, $attribute_render_type, $attribute_orderby );
						$this->add_attribute_relations( $attribute_id, $attribute_post_types );
						do_action( 'rt_wp_attribute_added', $wpdb->insert_id );
						$action_completed = true;
					}

					// Edit existing attribute
					if ( 'edit' === $action ) {
						$this->edit_attribute( $attribute_id, $attribute_label, $attribute_name, $attribute_store_as, $attribute_render_type, $attribute_orderby );

						$old_attribute_name = $this->attributes_db_model->get_attribute_name( $attribute_id );

						if ( $old_attribute_name != $attribute_name && ! empty( $old_attribute_name ) ) {
							// Update taxonomies in the wp term taxonomy table
							$wpdb->update( $wpdb->term_taxonomy, array( 'taxonomy' => $this->sanitize_taxonomy( $attribute_name ) ), array( 'taxonomy' => $this->get_taxonomy_name( $old_attribute_name ) ) );
						}

						$this->update_attribute_relations( $attribute_id, $attribute_post_types );

						do_action( 'rt_wp_attribute_updated', $attribute_id, $old_attribute_name );

						$action_completed = true;
					}
					flush_rewrite_rules();
				}
			}

			// Delete an attribute
			if ( 'delete' === $action ) {
				$attribute_id = absint( $_GET[ 'delete' ] );

				$attribute_name = $this->attributes_db_model->get_attribute_name( $attribute_id );

				if ( $attribute_name && $this->attributes_db_model->delete( array( 'id' => $attribute_id ) ) ) {

					$taxonomy = $this->sanitize_taxonomy( $attribute_name );

					if ( taxonomy_exists( $taxonomy ) ) {
						$terms = get_terms( $taxonomy, 'orderby=name&hide_empty=0' );
						foreach ( $terms as $term ) {
							wp_delete_term( $term->term_id, $taxonomy );
						}
					}

					$this->delete_attribute_relations( $attribute_id );

					do_action( 'rt_wp_attribute_deleted', $attribute_id, $attribute_name, $taxonomy );

					$action_completed = true;
				}
			}
			return $action_completed;
		}

		/**
		 * Register AutoLoader for rt-attributes
		 */
		function auto_loader() {
			$this->auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'model/' );
		}
	} //end class
}
