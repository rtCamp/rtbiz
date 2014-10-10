<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RT_Product_Sync' ) ) {

	/**
	 * Description of class-rt-product-sync
	 * To sync WooCommerce Product With rt-product-sync taxonomy
	 *
	 * @author dipesh
	 */
	class RT_Product_Sync {

		/**
		 * Product taxonomy Slug
		 * @var string
		 */
		var $product_slug = 'rt_wc_product';

		/**
		 * Product taxonomy labels
		 * @var array
		 */
		var $labels = array();

		/**
		 * Product taxonomy Sync enable
		 * @var array
		 */
		var $isSync = true;

		/**
		 * @var $caps - Capability for taxonomy
		 */
		var $caps = array();
		var $pluginName;

		function is_woocommerce_active(){
			if ( $this->pluginName == 'woocommerce' ) {
				return true;
			}
			return false;
		}
		function is_edd_active(){
			if ( $this->pluginName == 'edd' ) {
				return true;
			}
			return false;
		}
		function get_post_type(){
			if ( $this->is_woocommerce_active( ) ) {
				return 'product';
			}
			else if ( $this->is_edd_active( ) ) {
				return 'download';
			}
		}

		/**
		 * construct
		 *
		 * @param array $cap
		 * @param       $plugin_name
		 */
		public function __construct( $cap = array(), $plugin_name ) {
			$this->pluginName = $plugin_name;

			$this->caps = $cap;

			//Auto load Taxonomy Metadata Plugin
			$this->auto_loader();

			//init Product taxonomy Lable
			$this->get_label();

			//Register Product taxonomy
			add_action( 'init', array( $this, 'register_product_taxonomy' ), 5 );

			$taxonomy_metadata = new Rt_Lib_Taxonomy_Metadata\Taxonomy_Metadata();
			$taxonomy_metadata->activate();
			$this->hooks();
		}

		/**
		 * Auto loader for model classes
		 */
		function auto_loader() {
			include_once trailingslashit( dirname( __FILE__ ) ) . 'taxonomy-metadata.php';
		}

		/**
		 * Get Lable of Product taxonomy
		 * @return array
		 */
		public function get_label(){
			return $this->labels = array(
				'name' => __( 'WC Products' ),
				'singular_name' => __( 'WC Product' ),
				'menu_name' => __( 'WC Products' ),
				'search_items' => __( 'Search WC Products' ),
				'popular_items' => __( 'Popular WC Products' ),
				'all_items' => __( 'All WCProducts' ),
				'edit_item' => __( 'Edit WC Product' ),
				'update_item' => __( 'Update WC Product' ),
				'add_new_item' => __( 'Add New WC Product' ),
				'new_item_name' => __( 'New WC Product Name' ),
				'separate_items_with_commas' => __( 'Separate WC products with commas' ),
				'add_or_remove_items' => __( 'Add or remove WC products' ),
				'choose_from_most_used' => __( 'Choose from the most popular WC products' ),
			);
		}

		/**
		 * Register Product taxonomy if not-exist
		 */
		public function register_product_taxonomy(){
			$arg = array(
				'hierarchical' 				=> false,
				//'update_count_callback' 	=> array( $this, 'update_post_term_count' ),
				'labels' => $this->labels,
				'show_ui' 					=> true,
				'query_var' 				=> true,
				'capabilities'				=> $this->caps,
				'show_in_nav_menus' 		=> true,
				'show_admin_column'			=> true,
				//'rewrite' 					=> array( 'slug' => $product_attribute_base . sanitize_title( $tax->attribute_name ), 'with_front' => false, 'hierarchical' => $hierarchical ),
				'rewrite' => true,
			);
			$supports = apply_filters( 'rtlib_wc_product_support', array() );
			register_taxonomy( $this->product_slug, $supports, $arg );
		}

		/**
		 * hooks function.
		 * Call all hooks :)
		 *
		 * @access public
		 * @return void
		 */
		public function hooks() {
			if ( $this->isSync == true ) {
				add_action( 'init', array( $this, 'old_product_synchronization_enabled' ) );
				add_action( 'save_post', array( $this, 'insert_products' ) );
				add_action( 'wp_untrash_post', array( $this, 'insert_products' ) );
			}
		}

		/**
		 * old_product_synchronization_enabled function.
		 *
		 * @access public
		 * @return void
		 */
		public function old_product_synchronization_enabled() {
			if ( $this->isSync == true ) {
				$this->bulk_insert_products();
				$this->delete_products();
			}
		}

		/**
		 * Get taxonomy form product ID
		 *
		 * @access public
		 * @param $post_id
		 * @return void
		 */
		public function get_taxonomy( $post_id ){
			global $wpdb;
			$taxonomymeta = $wpdb->get_row( "SELECT * FROM $wpdb->taxonomymeta WHERE meta_key ='_product_id' AND meta_value = $post_id " );
			if ( ! empty( $taxonomymeta->taxonomy_id ) && is_numeric( $taxonomymeta->taxonomy_id ) ) {
				return get_term_by( 'id', $taxonomymeta->taxonomy_id, $this->product_slug );
			}
			return false;
		}

		/**
		 * insert_products function.
		 *
		 * @access public
		 * @return void
		 */
		public function insert_products( $post_id ) {
			global $wpdb;
			$key    = '_product_id';
			$single = 'true';

			// If this is just a revision, don't.
			if ( (wp_is_post_revision( $post_id ) && wp_is_post_autosave( $post_id ) ) || empty( $_POST['post_type'] ) ) {
				return;
			}

			// If this isn't a 'product' or 'download' post, don't update it.
			if ( 'product' != $_POST['post_type'] && 'download' != $_POST['post_type'] ) {
				return;
			}

			if ( $this->get_taxonomy( $post_id ) != false ){
				return;
			}

			/*// Rt_Lib_Taxonomy_Metadata\get_term_meta($term_id, $key, $single);
			$taxonomymeta = $wpdb->get_row( "SELECT * FROM $wpdb->taxonomymeta WHERE meta_key ='_product_id' AND meta_value = $post_id " );
			//print_r($taxonomymeta); die();

			// If this isn't a 'product' post, don't update it.
			if ( ! empty( $taxonomymeta->taxonomy_id ) && is_numeric( $taxonomymeta->taxonomy_id ) ) {
				return;
			}*/

			$args           = array( 'posts_per_page' => - 1, 'post_type' => $this->get_post_type(), );
			$products_array = get_posts( $args ); // Get Woo Commerce post object
			$product_names  = wp_list_pluck( $products_array, 'post_title' ); // Get Woo Commerce post_title
			$product_ids    = wp_list_pluck( $products_array, 'ID' ); // Get Woo Commerce Post ID

			$termname     = sanitize_title( $_POST['post_title'] );
			$termid = $this->check_postid_term_exist( $_POST['post_ID'] );

			if ( ! empty( $post_id ) ) {
				$post = get_post( $post_id );
				$slug = $post->post_name;
				if ( false == $termid ) {
					$term = wp_insert_term(
						$termname, // the term
						$this->product_slug, // the taxonomy
						array(
							'slug' => $slug,
						)
					);
				}
				else {
					$term = wp_update_term(
						$termid, // the term
						$this->product_slug, // the taxonomy
						array(
							'name' => $termname,
							'slug' => $slug,
						) );
				}
				if ( is_array( $term ) ) {
					$term_id = $term['term_id'];
					Rt_Lib_Taxonomy_Metadata\add_term_meta( $term_id, $key, $post_id, true ); // todo: need to fetch product_id
				}
			}

		}

		public function check_postid_term_exist( $post_id ){
			global $wpdb;
			$querystr = 'SELECT taxonomy_id FROM '.$wpdb->prefix.'taxonomymeta WHERE meta_value = '.$post_id.' limit 1';
			$result = $wpdb -> get_results( $querystr );
			if ( isset ( $result[0]->taxonomy_id ) ) {
				return $result[0]->taxonomy_id;
			}
			return false;
		}

		/**
		 * bulk_insert_products function.
		 *
		 * @access public
		 * @return void
		 */
		public function bulk_insert_products() {

			$args           = array( 'posts_per_page' => - 1, 'post_type' => $this->get_post_type(), );
			$products_array = get_posts( $args ); // Get Woo Commerce post object
			$product_names  = wp_list_pluck( $products_array, 'post_title' ); // Get Woo Commerce post_title
			$product_ids    = wp_list_pluck( $products_array, 'ID' ); // Get Woo Commerce Post ID

			$taxonomies = array(
				'product'    => $product_names,
				'product_id' => $product_ids,

			);

			$count            = 0;
			$i                = 0;
			$product_array    = array();
			$product_id_array = array();

			foreach ( $taxonomies as $taxonomy => $terms ) {
				$count ++;
				foreach ( $terms as $term ) {
					if ( $count == 1 ) {
						$product_array[] = $term;

					}
					if ( $count == 2 ) {
						$product_id_array[] = $term;
					}
				}
				if ( $count == 1 ) {
					$i = count( $product_array );
				}
			}

			while ( $i > 0 ) {
				$i --;

				$term = sanitize_title( $product_array[ $i ] );

				if ( ! empty( $product_id_array[ $i ] ) ) {
					$post = get_post( $product_id_array[ $i ] );
					$slug = $post->post_name;
					$term = wp_insert_term(
						$term, // the term
						$this->product_slug, // the taxonomy
						array(
							'slug' => $slug,
						)
					);
					if ( is_array( $term ) ) {
						$term_id = $term['term_id'];
						Rt_Lib_Taxonomy_Metadata\add_term_meta( $term_id, '_product_id', $product_id_array[ $i ], true ); // todo: need to fetch product_id
					}
				}
			}

		}

		/**
		 * delete_products function.
		 *
		 * @access public
		 * @return void
		 */
		public function delete_products() {
			$args           = array(
				'posts_per_page' => - 1,
				'post_type'      => $this->get_post_type(),
			); // get all woo commerce product
			$products_array = get_posts( $args );
			$product_names  = wp_list_pluck( $products_array, 'post_name' );
			$product_taxonomies     = get_terms( $this->product_slug, 'hide_empty=0' ); // Get all the product list from product taxonomy under Ideas
			$product_taxonomy_names = wp_list_pluck( $product_taxonomies, 'slug' );
			$product_taxonomies_to_delete = array_diff( $product_taxonomy_names, $product_names ); // Do a array diff

			foreach ( $product_taxonomies_to_delete as $product_taxonomy_to_delete ) {
				$product_taxonomies_obj = get_term_by( 'slug', $product_taxonomy_to_delete, $this->product_slug );
				wp_delete_term( $product_taxonomies_obj->term_id, $this->product_slug ); // Now Delete those products which are not present in woo-commerce product section.
				Rt_Lib_Taxonomy_Metadata\delete_term_meta( $product_taxonomies_obj->term_id, '_product_id' );
			}
		}

		/**
		 * delete_products_meta function.
		 *
		 * @access public
		 * @return void
		 */
		public function delete_products_meta( $term_id ) {
			Rt_Lib_Taxonomy_Metadata\delete_term_meta( $term_id, '_product_id' );
		}

	}
}