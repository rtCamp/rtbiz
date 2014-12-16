<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rt_Offerings' ) ) {

	/**
	 * Description of class-rt-offering
	 * To sync WooCommerce Product With Rt_Offerings taxonomy
	 *
	 * @author dipesh
	 */
	class Rt_Offerings {

		/**
		 * Offering taxonomy Slug
		 * @var string
		 */
		var $offering_slug = 'rt-offering';

		static $term_meta_key = '_offering_id';

		/**
		 * Product taxonomy labels
		 * @var array
		 */
		var $labels = array();

		var $post_types;

		/**
		 * Product taxonomy Sync enable
		 * @var array
		 */
		var $isSync;

		/**
		 * @var $caps - Capability for taxonomy
		 */
		var $caps = array();
		var $pluginName;

		function is_woocommerce_active(){
			if ( 'woocommerce' === $this->pluginName && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				return true;
			}
			return false;
		}

		function is_edd_active(){
			if ( 'edd' === $this->pluginName && is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
				return true;
			}
			return false;
		}

		function get_post_type(){
			if ( $this->is_woocommerce_active( ) ) {
				return 'product';
			} else if ( $this->is_edd_active( ) ) {
				return 'download';
			}
			return '';
		}

		/**
		 * construct
		 *
		 * @param array $cap
		 * @param       $plugin_name
		 */
		public function __construct( $plugin_name, $cap = array(), $post_types = array() ) {
			$this->pluginName = $plugin_name;

			$this->post_types = $post_types;

			$this->isSync = ( $this->is_woocommerce_active() ) ? true : ( $this->is_edd_active() ) ? true : false;

			$this->caps = $cap;

			//Auto load Taxonomy Metadata Plugin
			$this->auto_loader();

			//init Product taxonomy Lable
			$this->get_label();

			//Register Product taxonomy
			add_action( 'init', array( $this, 'register_offering_taxonomy' ), 5 );

			$taxonomy_metadata = new Rt_Lib_Taxonomy_Metadata\Taxonomy_Metadata();
			$taxonomy_metadata->activate();
			$this->hooks();
		}

		/**
		 * Auto loader for model classes
		 */
		function auto_loader() {
			if ( ! class_exists( 'Rt_Lib_Taxonomy_Metadata\Taxonomy_Metadata' ) ) {
				include_once trailingslashit( dirname( __FILE__ ) ) . 'taxonomy-metadata.php';
			}
		}

		/**
		 * Get Lable of Product taxonomy
		 * @return array
		 */
		public function get_label(){
			return $this->labels = array(
				'name' => __( 'Offerings' ),
				'singular_name' => __( 'Offering' ),
				'menu_name' => __( 'Offerings' ),
				'search_items' => __( 'Search Offerings' ),
				'popular_items' => __( 'Popular Offerings' ),
				'all_items' => __( 'All Offerings' ),
				'edit_item' => __( 'Edit Offering' ),
				'update_item' => __( 'Update Offering' ),
				'add_new_item' => __( 'Add New Offering' ),
				'new_item_name' => __( 'New Offering Name' ),
				'separate_items_with_commas' => __( 'Separate offering with commas' ),
				'add_or_remove_items' => __( 'Add or remove offering' ),
				'choose_from_most_used' => __( 'Choose from the most popular offerings' ),
			);
		}

		/**
		 * Register Product taxonomy if not-exist
		 */
		public function register_offering_taxonomy(){
			$arg = array(
				'hierarchical' 				=> true,
				'update_count_callback' 	=> array( $this, 'update_post_term_count' ),
				'labels'                    => $this->labels,
				'show_ui' 					=> true,
				'query_var' 				=> true,
				'capabilities'				=> $this->caps,
				'show_in_nav_menus' 		=> true,
				'show_admin_column'			=> true,
				//'rewrite' 				=> array( 'slug' => $product_attribute_base . sanitize_title( $tax->attribute_name ), 'with_front' => false, 'hierarchical' => $hierarchical ),
				'rewrite'                   => true,
			);
			$supports = apply_filters( 'rtlib_product_support', $this->post_types );
			register_taxonomy( $this->offering_slug, $supports, $arg );
		}

		public function update_post_term_count( $terms, $taxonomy ){
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
		 * hooks function.
		 * Call all hooks :)
		 *
		 * @access public
		 * @return void
		 */
		public function hooks() {
			if ( true === $this->isSync ) {
				add_action( 'init', array( $this, 'existing_offerings_sync' ) );
				add_action( 'save_post', array( $this, 'insert_offerings' ) );
			}
			add_action( 'delete_term', array( $this, 'cleanup_meta_after_term_deletion' ), 10, 4 );
		}

		function cleanup_meta_after_term_deletion( $term, $tt_id, $taxonomy, $deleted_term ) {
			Rt_Lib_Taxonomy_Metadata\delete_term_meta( $term, self::$term_meta_key );
		}

		/**
		 * old_offerings_synchronization_enabled function.
		 *
		 * @access public
		 * @return void
		 */
		public function existing_offerings_sync() {
			if ( true === $this->isSync && ( $this->is_edd_active() || $this->is_woocommerce_active() ) ) {
				$this->bulk_insert_offerings();
			}
		}

		/**
		 * Get taxonomy form offering ID
		 *
		 * @access public
		 * @param $post_id
		 * @return void
		 */
		public function get_taxonomy( $post_id ){
			global $wpdb;
			$taxonomymeta = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}taxonomymeta WHERE meta_key ='".self::$term_meta_key."' AND meta_value = $post_id " );
			if ( ! empty( $taxonomymeta->taxonomy_id ) && is_numeric( $taxonomymeta->taxonomy_id ) ) {
				return get_term_by( 'id', $taxonomymeta->taxonomy_id, $this->offering_slug );
			}
			return false;
		}

		/**
		 * insert_offerings function.
		 *
		 * @param $post_id
		 *
		 * @access public
		 * @return void
		 */
		public function insert_offerings( $post_id ) {

			$key    = self::$term_meta_key;

			// If this is just a revision, don't.
			if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || empty( $_POST['post_type'] ) ) {
				return;
			}

			// If this isn't a 'product' or 'download' post, don't update it.
			if ( ! ( ( $this->is_woocommerce_active() && 'product' === $_POST['post_type'] ) || ( $this->is_edd_active() && 'download' === $_POST['post_type'] ) ) ) {
				return;
			}

			if ( false !== $this->get_taxonomy( $post_id ) ){
				return;
			}

			$termname     = $_POST['post_title'];
			$termid = $this->check_postid_term_exist( $_POST['post_ID'] );

			if ( ! empty( $post_id ) ) {
				$post = get_post( $post_id );
				$slug = sanitize_title( $termname );
				if ( false == $termid ) {

					$i = '';
					do {
						$term = wp_insert_term(
							$termname, // the term
							$this->offering_slug, // the taxonomy
							array(
								'slug' => $slug.$i,
							)
						);
						if ( $term instanceof WP_Error ) {
							$i = rand( 1, 1000 );
							$i = '-'.$i;
						}
					} while ( $term instanceof WP_Error );
				} else {
					$term = wp_update_term(
						$termid, // the term
						$this->offering_slug, // the taxonomy
						array(
							'name' => $termname,
							'slug' => $slug,
						) );
				}
				if ( is_array( $term ) ) {
					$term_id = $term['term_id'];
					Rt_Lib_Taxonomy_Metadata\add_term_meta( $term_id, $key, $post_id, true );
				}
			}

		}

		public function check_postid_term_exist( $post_id ){
			global $wpdb;
			$querystr = 'SELECT taxonomy_id FROM '.$wpdb->prefix.'taxonomymeta WHERE meta_value = '.$post_id.' limit 1';
			$result = $wpdb -> get_results( $querystr );
			if ( isset( $result[0]->taxonomy_id ) ) {
				return $result[0]->taxonomy_id;
			}
			return false;
		}

		/**
		 * bulk_insert_offerings function.
		 *
		 * @access public
		 * @return void
		 */
		public function bulk_insert_offerings() {

			$args           = array( 'posts_per_page' => - 1, 'post_type' => $this->get_post_type(), 'post_status' => 'any' );
			$offerings_array = get_posts( $args ); // Get Woo Commerce post object

			foreach ( $offerings_array as $offering ) {
				$term = $this->get_taxonomy( $offering->ID );

				if ( empty( $term ) ) {
					$new_term = wp_insert_term( $offering->post_title, $this->offering_slug, array( 'slug' => $offering->post_name ) );
					if ( ! $new_term instanceof WP_Error ) {
						Rt_Lib_Taxonomy_Metadata\add_term_meta( $new_term['term_id'], self::$term_meta_key, $offering->ID, true );
					}
				}
			}
		}

		/**
		 *
		 * delete_offerings_meta function.
		 *
		 * @param $term_id
		 *
		 * @access public
		 * @return void
		 */
		public function delete_offerings_meta( $term_id ) {
			Rt_Lib_Taxonomy_Metadata\delete_term_meta( $term_id, Rt_Offerings::$term_meta_key );
		}

	}
}
