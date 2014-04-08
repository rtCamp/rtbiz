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
	class RT_Attributes
	{

		/**
		 * @var $module_id - unique module id for which this class is called. This can be either plugin slug or plugin name or any unique identifier that will be used to know which plugin or module is calling the library class. And accordingly that can be mapped to attributes.
		 */
		var $module_name;

		/**
		 * @var $page_slug - Page slug under which the attributes page is to be shown. If null / empty then an individual Menu Page will be added
		 */
		var $page_slug;

		/**
		 * @var $post_type - If any post type passed, only attributes for those post type will be listed on the page.
		 */
		var $post_type;

		/**
		 * @var - Attributes DB Model Object
		 */
		var $attributes_db_model;

		/**
		 * @var - Attributes Relationship DB Model Object
		 */
		var $attributes_relationship_model;

		/**
		 * @var RT_WP_Autoload
		 */
		var $auto_loader;

		/**
		 * @param $module_name - A Unique module name to identify for which module / post_types the attributes i.e., taxonomies are to be registered
		 */
		public function __construct( $module_name )
		{
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
		function db_upgrade()
		{
			$updateDB = new  RT_DB_Update( WP_HELPER_FILE, trailingslashit( dirname( __FILE__ ) ) . 'schema/' );
			$updateDB->db_version_option_name .= '_ATTRIBUTES';
			$updateDB->install_db_version = $updateDB->get_install_db_version();
			$updateDB->do_upgrade();
		}

		/**
		 * Initialize the Database model object which will be used for DB transactions
		 */
		function init_db_model()
		{
			$this->attributes_db_model           = new RT_Attributes_Model();
			$this->attributes_relationship_model = new RT_Attributes_Relationship_Model();
		}

		/**
		 * Registers all the taxonomies to the post type according to given module id
		 *
		 * @internal param $module_id
		 */
		function register_taxonomies()
		{

		}

		/**
		 * This method registers an attributes menu page which will list all the available attributes that can be linked to any post types as per need.
		 *
		 * @param string $page_slug - slug of page on under which the attributes page is to be shown
		 * @param string $post_type - post type for which the attributes are to be listed
		 */
		function add_attributes_page( $page_slug = '', $post_type = '' )
		{

			$this->page_slug = $page_slug;
			$this->post_type = $post_type;

			if ( ! empty( $page_slug ) ) {
				add_submenu_page( $page_slug, __( 'Attributes' ), __( 'Attributes' ), 'manage_options', 'rt_attributes', array( $this, 'render_attributes_page' ) );
			} else {
				add_menu_page( __( 'Attributes' ), __( 'Attributes' ), 'manage_options', 'rt-wp-attributes', array( $this, 'render_attributes_page' ) );
			}
		}

		/**
		 * Renders the Attributes Listing Page
		 */
		function render_attributes_page()
		{
			// Perform Any action according to query variable passed in the REQUEST
			$action_completed = $this->perform_action();

			// If an attribute was added, edited or deleted: then redirect to the attributes page
			if ( ! empty( $action_completed ) ) {
				wp_redirect( admin_url( 'admin.php?page=rt-wp-attributes' ) );
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
		function edit_attribute_ui()
		{
			$edit = absint( $_GET[ 'edit' ] );

			$attribute_to_edit = $this->attributes_db_model->get_attribute( $edit );

			$att_store_as    = $attribute_to_edit->attribute_store_as;
			$att_render_type = $attribute_to_edit->attribute_render_type;
			$att_label       = $attribute_to_edit->attribute_label;
			$att_name        = $attribute_to_edit->attribute_name;
			$att_orderby     = $attribute_to_edit->attribute_orderby;

			include 'templates/template-rt-edit-attribute.php';
		}

		/**
		 * Add Attribute Screen
		 */
		function add_attribute_ui()
		{
			$rt_wp_attributes_model = $this->attributes_db_model;

			include 'templates/template-rt-add-attribute.php';
		}

		/**
		 * Sanitizes taxonomy name
		 *
		 * @param $taxonomy
		 *
		 * @return mixed|string
		 */
		function sanitize_taxonomy( $taxonomy )
		{
			$taxonomy = strtolower( stripslashes( strip_tags( $taxonomy ) ) );
			$taxonomy = preg_replace( '/&.+?;/', '', $taxonomy ); // Kill entities
			$taxonomy = str_replace( array( '.', '\'', '"' ), '', $taxonomy ); // Kill quotes and full stops.
			$taxonomy = str_replace( array( ' ', '_' ), '-', $taxonomy ); // Replace spaces and underscores.

			return $taxonomy;
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
		function error_check( $action = '', $attribute_id = '', $attribute_name = '', $attribute_render_type = '', $attribute_store_as = '' )
		{
			// Forbidden attribute names
			// http://codex.wordpress.org/Function_Reference/register_taxonomy#Reserved_Terms
			$reserved_terms = array( 'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category', 'category__and', 'category__in', 'category__not_in', 'category_name', 'comments_per_page', 'comments_popup', 'cpage', 'day', 'debug', 'error', 'exact', 'feed', 'hour', 'link_category', 'm', 'minute', 'monthnum', 'more', 'name', 'nav_menu', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id', 'paged', 'pagename', 'pb', 'perm', 'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type', 'post_status', 'post_tag', 'post_type', 'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 's', 'search', 'second', 'sentence', 'showposts', 'static', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in', 'tag__not_in', 'tag_id', 'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb', 'term', 'type', 'w', 'withcomments', 'withoutcomments', 'year', );

			$error = '';

			if ( ! $attribute_name || ! $attribute_name || ! $attribute_render_type || ! $attribute_store_as ) {
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
		 * Add attribute in database
		 *
		 * @param $attribute_label
		 * @param $attribute_name
		 * @param $attribute_store_as
		 * @param $attribute_render_type
		 * @param $attribute_orderby
		 */
		function add_attribute( $attribute_label, $attribute_name, $attribute_store_as, $attribute_render_type, $attribute_orderby )
		{

			$attribute = array( 'attribute_label' => $attribute_label, 'attribute_name' => $attribute_name, 'attribute_store_as' => $attribute_store_as, 'attribute_render_type' => $attribute_render_type, 'attribute_orderby' => $attribute_orderby, );

			$this->attributes_db_model->add_attribute( $attribute );
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
		function edit_attribute( $attribute_id, $attribute_label, $attribute_name, $attribute_store_as, $attribute_render_type, $attribute_orderby )
		{

			$attribute = array( 'attribute_label' => $attribute_label, 'attribute_name' => $attribute_name, 'attribute_store_as' => $attribute_store_as, 'attribute_render_type' => $attribute_render_type, 'attribute_orderby' => $attribute_orderby, );

			$this->attributes_db_model->update_attribute( $attribute, array( 'id' => $attribute_id ) );
		}

		/**
		 * Performs Add, Save, Delete Attributes
		 */
		function perform_action()
		{
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
				$attribute_store_as    = ( isset( $_POST[ 'attribute_store_as' ] ) ) ? (string)stripslashes( $_POST[ 'attribute_store_as' ] ) : '';
				$attribute_render_type = ( isset( $_POST[ 'attribute_render_type' ] ) ) ? (string)stripslashes( $_POST[ 'attribute_render_type' ] ) : '';
				$attribute_orderby     = ( isset( $_POST[ 'attribute_orderby' ] ) ) ? (string)stripslashes( $_POST[ 'attribute_orderby' ] ) : '';

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
						$this->add_attribute( $attribute_label, $attribute_name, $attribute_store_as, $attribute_render_type, $attribute_orderby );
						do_action( 'rt_wp_attribute_added', $wpdb->insert_id );
						$action_completed = true;
					}

					// Edit existing attribute
					if ( 'edit' === $action ) {
						$this->edit_attribute( $attribute_id, $attribute_label, $attribute_name, $attribute_store_as, $attribute_render_type, $attribute_orderby );

						$old_attribute_name = $this->attributes_db_model->get_attribute_name( $attribute_id );

						do_action( 'rt_wp_attribute_updated', $attribute_id, $old_attribute_name );

						if ( $old_attribute_name != $attribute_name && ! empty( $old_attribute_name ) ) {
							// Update taxonomies in the wp term taxonomy table
							$wpdb->update( $wpdb->term_taxonomy, array( 'taxonomy' => $this->sanitize_taxonomy( $attribute_name ) ), array( 'taxonomy' => 'rt_' . $old_attribute_name ) );
						}
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

					do_action( 'rt_wp_attribute_deleted', $attribute_id, $attribute_name, $taxonomy );

					$action_completed = true;
				}
			}

			return $action_completed;
		}

		/**
		 * Register AutoLoader for rt-attributes
		 */
		function auto_loader()
		{
			$this->auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'model/' );
		}
	} //end class
}