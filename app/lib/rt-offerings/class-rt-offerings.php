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
	 * To sync WooCommerce/EDD Product With Rt_Offerings taxonomy
	 *
	 * @author dipesh
	 */
	class Rt_Offerings {

		/**
		 * Offering taxonomy Slug
		 * @var string
		 */
		static $offering_slug = 'rt-offering';

		static $term_product_id_meta_key = '_offering_id';

		static $term_product_from_meta_key = '_offering_import_from';

		/**
		 * Product taxonomy labels
		 * @var array
		 */
		var $labels = array();

		var $post_types;

		/**
		 * Offering taxonomy Sync enable
		 * @var array
		 */
		var $isSync;

		/**
		 * @var $caps - Capability for taxonomy
		 */
		var $caps = array();
		var $pluginName;

		/**
		 * construct
		 *
		 * @param array $cap
		 * @param       $plugin_name
		 */
		public function __construct( $plugin_name, $cap = array(), $post_types = array() ) {

			global $taxonomy_metadata;

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

			if ( ! is_object( $taxonomy_metadata ) ){
				$taxonomy_metadata = new Rt_Lib_Taxonomy_Metadata\Taxonomy_Metadata();
				$taxonomy_metadata->activate();
			}

			$this->hooks();
		}

		function is_woocommerce_active(){
			if ( 'woocommerce' === $this->pluginName && class_exists( 'WooCommerce' ) ) {
				return true;
			}
			return false;
		}

		function is_edd_active(){
			if ( 'edd' === $this->pluginName && class_exists( 'Easy_Digital_Downloads' ) ) {
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
		 * Auto loader for model classes
		 */
		function auto_loader() {
			if ( ! class_exists( 'Rt_Lib_Taxonomy_Metadata\Taxonomy_Metadata' ) ) {
				include_once trailingslashit( dirname( __FILE__ ) ) . 'taxonomy-metadata.php';
			}
		}

		/**
		 * Get Lable of Offerings taxonomy
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
			$supports = apply_filters( 'rtlib_offerings_support', $this->post_types );
			register_taxonomy( self::$offering_slug, $supports, $arg );
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

				/**
				 * Backward compatibility Fix
				 * Ticket Reference : https://core.trac.wordpress.org/ticket/30999
				 * Changeset : https://core.trac.wordpress.org/changeset/31525
				 */
				if ( is_object( $taxonomy ) ) {
					$taxonomy = $taxonomy->name;
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
				$isSyncOpt = get_option( 'rtbiz_offering_plugin_synx' );
				if ( empty( $isSyncOpt ) ||  'true' === $isSyncOpt ){
					add_action( 'init', array( $this, 'bulk_insert_offerings' ) );
				}
				add_action( 'save_post', array( $this, 'insert_offerings' ) );
			}
			add_action( 'delete_term', array( $this, 'cleanup_meta_after_term_deletion' ), 10, 4 );
			add_action( 'manage_' . self::$offering_slug . '_custom_column', array( $this, 'manage_offering_column_body' ), 10, 3 );
			add_filter( 'manage_edit-' . self::$offering_slug . '_columns', array( $this, 'manage_offering_column_header' ) );
		}

		/**
		 * Filter offering as per store select
		 */
		public function offering_filter( $terms, $taxonomies, $args  ){
			if ( in_array( self::$offering_slug, $taxonomies ) ){
				$terms_filter = array();
				foreach ( $terms as $term ) {
					$product_plugin = Rt_Lib_Taxonomy_Metadata\get_term_meta( $term->term_id, self::$term_product_from_meta_key, true );
					if ( empty( $product_plugin ) || $product_plugin == $this->pluginName ){
						$terms_filter[] = $term;
					}
				}
				$terms = $terms_filter;
			}
			return $terms;
		}

		/**
		 * dd column heading on offering list page
		 * @param $columns
		 *
		 * @return mixed
		 */
		function manage_offering_column_header( $columns ) {
			unset( $columns['posts'] );
			unset( $columns['slug'] );
			$columns['offering_count'] = __( 'Count', RT_HD_TEXT_DOMAIN );
			$columns['product_detail'] = __( 'Product', RT_HD_TEXT_DOMAIN );
			return apply_filters( 'rt_biz_offerings_columns' , $columns );
		}

		/**
		 * UI for group List View custom Columns for offerings
		 *
		 * @param type $display
		 * @param type $column
		 * @param type $term_id
		 *
		 * @return type
		 */
		function manage_offering_column_body( $display, $column, $term_id ) {
			$t = get_term( $term_id, Rt_Offerings::$offering_slug );
			switch ( $column ) {
				case 'product_detail':
					$product_id = Rt_Lib_Taxonomy_Metadata\get_term_meta( $term_id, self::$term_product_id_meta_key, true );
					$product_plugin = Rt_Lib_Taxonomy_Metadata\get_term_meta( $term_id, self::$term_product_from_meta_key, true );
					if ( ! empty( $product_id ) || ! empty( $product_plugin ) ){
						$content = '<span>' . strtoupper( $product_plugin ) . '</span> :- ';
						$content .= '<a class="post-edit-link" href="' . edit_post_link( $product_id ) . '">#' . $product_id . '</a>';
					} else {
						echo '-';
					}
					break;
				case 'offering_count':
					foreach ( $this->post_types as $posttype ) {
						$posts = new WP_Query( array(
							'post_type' => $posttype,
							'post_status' => 'any',
							'nopaging' => true,
							Rt_Offerings::$offering_slug  => $t->slug,
						) );
						$posttype_lable = explode( '_', $posttype );
						$posttype_lable = $posttype_lable[ count( $posttype_lable ) - 1 ];
						$content = strtoupper( $posttype_lable ) . " : <a href='edit.php?post_type=$posttype&". Rt_Offerings::$offering_slug .'='.$t->slug."'>".count( $posts->posts ).'</a><br/>';
					}

					break;
			}
			if ( empty( $content ) ){
				$content = '';
			}
			return apply_filters( 'rt_biz_offering_column_content', $content, $column, $term_id );
		}

		/**
		 * Delete taxonomy meta if taxonomy deleted
		 * @param $term
		 * @param $tt_id
		 * @param $taxonomy
		 * @param $deleted_term
		 */
		function cleanup_meta_after_term_deletion( $term, $tt_id, $taxonomy, $deleted_term ) {
			Rt_Lib_Taxonomy_Metadata\delete_term_meta( $term, self::$term_product_id_meta_key );
			Rt_Lib_Taxonomy_Metadata\delete_term_meta( $term, self::$term_product_from_meta_key );
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
			$taxonomymeta = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}taxonomymeta WHERE meta_key ='".self::$term_product_id_meta_key."' AND meta_value = $post_id " );
			if ( ! empty( $taxonomymeta->taxonomy_id ) && is_numeric( $taxonomymeta->taxonomy_id ) ) {
				return get_term_by( 'id', $taxonomymeta->taxonomy_id, self::$offering_slug );
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

			// If this is just a revision, don't.
			if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || empty( $_POST['post_type'] ) ) {
				return;
			}

			if ( 'publish' != $_POST['post_status'] ) {
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
							self::$offering_slug, // the taxonomy
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
						self::$offering_slug, // the taxonomy
						array(
							'name' => $termname,
							'slug' => $slug,
						) );
				}
				if ( is_array( $term ) ) {
					$term_id = $term['term_id'];
					Rt_Lib_Taxonomy_Metadata\add_term_meta( $term_id, self::$term_product_id_meta_key, $post_id, true );
					if ( ! empty( $this->pluginName ) ){
						Rt_Lib_Taxonomy_Metadata\add_term_meta( $term_id, self::$term_product_from_meta_key, $this->pluginName, true );
					}
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
		 *
		 * @param string $plugin active plugin name
		 *
		 * @return bool
		 */
		public function bulk_insert_offerings( $plugin = '' ) {

			if ( ! empty( $plugin ) ){
				$this->pluginName = $plugin;
			}
			if ( ! $this->is_edd_active() && ! $this->is_woocommerce_active() ) {
				return false;
			}

			$args           = array( 'posts_per_page' => - 1, 'post_type' => $this->get_post_type(), 'post_status' => 'any' );
			$offerings_array = get_posts( $args ); // Get Woo Commerce post object

			foreach ( $offerings_array as $offering ) {
				$term = $this->get_taxonomy( $offering->ID );

				if ( empty( $term ) ) {
					$new_term = wp_insert_term( $offering->post_title, self::$offering_slug, array( 'slug' => $offering->post_name ) );
					if ( ! $new_term instanceof WP_Error ) {
						Rt_Lib_Taxonomy_Metadata\add_term_meta( $new_term['term_id'], self::$term_product_id_meta_key, $offering->ID, true );
						if ( ! empty( $this->pluginName ) ){
							Rt_Lib_Taxonomy_Metadata\add_term_meta( $new_term['term_id'], self::$term_product_from_meta_key, $this->pluginName, true );
						}
					}
				}
			}
			update_option( 'rtbiz_offering_plugin_synx', 'false' );

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
			Rt_Lib_Taxonomy_Metadata\delete_term_meta( $term_id, Rt_Offerings::$term_product_id_meta_key );
		}

	}
}
