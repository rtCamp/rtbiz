<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rt_Products' ) ) {

	/**
	 * Description of class-rt-product
	 * To sync WooCommerce/EDD Product With Rt_Products taxonomy
	 *
	 * @author dipesh
	 */
	class Rt_Products {

		/**
		 * Product taxonomy Slug
		 * @var string
		 */
		static $product_slug = 'rt-product';

		static $term_product_id_meta_key = '_product_id';

		static $term_product_from_meta_key = '_product_import_from';

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

			$this->isSync = ( $this->is_woocommerce_active() ) ? true : ( ( $this->is_edd_active() ) ? true : false );

			$this->caps = $cap;

			//Auto load Taxonomy Metadata Plugin
			$this->auto_loader();

			//init Product taxonomy Lable
			$this->get_label();

			//Register Product taxonomy
			add_action( 'init', array( $this, 'register_product_taxonomy' ), 5 );

			if ( ! is_object( $taxonomy_metadata ) ) {
				$taxonomy_metadata = new Rt_Lib_Taxonomy_Metadata\Taxonomy_Metadata();
				$taxonomy_metadata->activate();
			}

			$this->hooks();
		}

		function is_woocommerce_active() {
			if ( ! empty( $this->pluginName ) && in_array( 'woocommerce', $this->pluginName ) && class_exists( 'WooCommerce' ) ) {
				return true;
			}

			return false;
		}

		function is_edd_active() {
			if ( ! empty( $this->pluginName ) && in_array( 'edd', $this->pluginName ) && class_exists( 'Easy_Digital_Downloads' ) ) {
				return true;
			}

			return false;
		}

		function get_post_type() {
			$result = array();
			if ( $this->is_woocommerce_active() ) {
				$result[] = 'product';
			}
			if ( $this->is_edd_active() ) {
				$result[] = 'download';
			}

			return $result;
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
		 * Get Lable of Products taxonomy
		 * @return array
		 */
		public function get_label() {
			return $this->labels = array(
				'name'                       => __( 'Products' ),
				'singular_name'              => __( 'Product' ),
				'menu_name'                  => __( 'Products' ),
				'search_items'               => __( 'Search Products' ),
				'popular_items'              => __( 'Popular Products' ),
				'all_items'                  => __( 'All Products' ),
				'edit_item'                  => __( 'Edit Product' ),
				'update_item'                => __( 'Update Product' ),
				'add_new_item'               => __( 'Add New Product' ),
				'new_item_name'              => __( 'New Product Name' ),
				'separate_items_with_commas' => __( 'Separate product with commas' ),
				'add_or_remove_items'        => __( 'Add or remove product' ),
				'choose_from_most_used'      => __( 'Choose from the most popular product' ),
				'not_found'                  => __( 'No product found.' ),
				'no_terms'                   => __( 'No product' ),
			);
		}

		/**
		 * Register Product taxonomy if not-exist
		 */
		public function register_product_taxonomy() {
			$arg      = array(
				'public'                => false,
				'hierarchical'          => true,
				'update_count_callback' => array( $this, 'update_post_term_count' ),
				'labels'                => $this->labels,
				'show_ui'               => true,
				'query_var'             => true,
				'capabilities'          => $this->caps,
				'show_in_nav_menus'     => true,
				'show_admin_column'     => true,
				//'rewrite' 				=> array( 'slug' => $product_attribute_base . sanitize_title( $tax->attribute_name ), 'with_front' => false, 'hierarchical' => $hierarchical ),
				'rewrite'               => false,
			);
			$supports = apply_filters( 'rtlib_products_support', $this->post_types );
			register_taxonomy( self::$product_slug, $supports, $arg );
		}

		public function update_post_term_count( $terms, $taxonomy ) {
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
				$isSyncOpt = get_option( 'rt_product_plugin_sync' );
				if ( empty( $isSyncOpt ) || 'true' === $isSyncOpt ) {
					add_action( 'init', array( $this, 'bulk_insert_products' ) );
				}
				add_action( 'save_post', array( $this, 'insert_products' ) );
			}
			add_action( 'delete_term', array( $this, 'cleanup_meta_after_term_deletion' ), 10, 4 );
			add_action( 'manage_' . self::$product_slug . '_custom_column', array(
				$this,
				'manage_product_column_body',
			), 10, 3 );
			add_filter( 'manage_edit-' . self::$product_slug . '_columns', array(
				$this,
				'manage_product_column_header',
			) );
		}

		/**
		 * Filter product as per store select
		 */
		public function product_filter( $terms, $taxonomies, $args ) {
			if ( in_array( self::$product_slug, $taxonomies ) ) {
				$terms_filter = array();
				foreach ( $terms as $term ) {
					$product_plugin = Rt_Lib_Taxonomy_Metadata\get_term_meta( $term->term_id, self::$term_product_from_meta_key, true );
					if ( empty( $product_plugin ) || in_array( $product_plugin, $this->pluginName ) ) {
						$terms_filter[] = $term;
					}
				}
				$terms = $terms_filter;
			}

			return $terms;
		}

		/**
		 * dd column heading on product list page
		 *
		 * @param $columns
		 *
		 * @return mixed
		 */
		function manage_product_column_header( $columns ) {
			if ( isset( $_GET['post_type'] ) && 'product' == $_GET['post_type'] ) {
				return $columns;
			}
			unset( $columns['posts'] );
			unset( $columns['slug'] );
			$columns['product_count']  = __( 'Count' );
			$columns['product_detail'] = __( 'Product' );

			return apply_filters( 'rt_product_columns', $columns );
		}

		/**
		 * UI for group List View custom Columns for products
		 *
		 * @param type $display
		 * @param type $column
		 * @param type $term_id
		 *
		 * @return type
		 */
		function manage_product_column_body( $display, $column, $term_id ) {
			$t       = get_term( $term_id, Rt_Products::$product_slug );
			$content = '';
			switch ( $column ) {
				case 'product_detail':
					$product_id     = Rt_Lib_Taxonomy_Metadata\get_term_meta( $term_id, self::$term_product_id_meta_key, true );
					$product_plugin = Rt_Lib_Taxonomy_Metadata\get_term_meta( $term_id, self::$term_product_from_meta_key, true );
					if ( ! empty( $product_id ) || ! empty( $product_plugin ) ) {
						$content = '<span>' . ucfirst( $product_plugin ) . '</span> ';
						$content .= '<a class="post-edit-link" href="' . get_edit_post_link( $product_id ) . '">#' . $product_id . '</a>';
					} else {
						echo '-';
					}
					break;
				case 'product_count':
					$posttypes = $this->post_types;
					if ( isset( $_REQUEST['post_type'] ) && in_array( $_REQUEST['post_type'], $posttypes ) ) {
						$posttypes = array( $_REQUEST['post_type'] );
					}

					foreach ( $posttypes as $posttype ) {
						$posts          = new WP_Query( array(
							'post_type'                => $posttype,
							'post_status'              => 'any',
							'nopaging'                 => true,
							Rt_Products::$product_slug => $t->slug,
						) );
						$posttype_lable = explode( '_', $posttype );
						$posttype_lable = $posttype_lable[ count( $posttype_lable ) - 1 ];
						$content .= ucfirst( $posttype_lable . 's' ) . " -  <a href='edit.php?post_type=$posttype&" . Rt_Products::$product_slug . '=' . $t->slug . "'>" . count( $posts->posts ) . '</a><br/>';
					}

					break;
			}

			return apply_filters( 'rt_product_column_content', $content, $column, $term_id );
		}

		/**
		 * Delete taxonomy meta if taxonomy deleted
		 *
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
		 * Get taxonomy form product ID
		 *
		 * @access public
		 *
		 * @param $post_id
		 *
		 * @return int/void
		 */
		public function get_taxonomy( $post_id ) {
			global $wpdb;
			if ( ! empty( $wpdb->termmeta ) ) {
				$table_name = $wpdb->termmeta;
				$column     = 'term_id';
			} else {
				$table_name = "{$wpdb->prefix}taxonomymeta";
				$column     = 'taxonomy_id';
			}
			$t_id = $wpdb->get_var( "SELECT $column FROM $table_name WHERE meta_key ='" . self::$term_product_id_meta_key . "' AND meta_value = $post_id limit 1" );

			if ( ! empty( $t_id ) && is_numeric( $t_id ) ) {
				return get_term_by( 'id', $t_id, self::$product_slug );
			}

			return false;
		}

		/**
		 * insert_products function.
		 *
		 * @param $post_id
		 *
		 * @access public
		 * @return void
		 */
		public function insert_products( $post_id ) {

			// If this is just a revision, don't.
			if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || empty( $_POST['post_type'] ) ) {
				return;
			}

			if ( empty( $_POST['post_status'] ) || 'publish' != $_POST['post_status'] ) {
				return;
			}

			// If this isn't a 'product' or 'download' post, don't update it.
			if ( ! ( ( $this->is_woocommerce_active() && 'product' === $_POST['post_type'] ) || ( $this->is_edd_active() && 'download' === $_POST['post_type'] ) ) ) {
				return;
			}

			if ( false !== $this->get_taxonomy( $post_id ) ) {
				return;
			}

			$termname = $_POST['post_title'];
			$termid   = $this->check_postid_term_exist( $_POST['post_ID'] );

			if ( ! empty( $post_id ) ) {
				$post = get_post( $post_id );
				$slug = sanitize_title( $termname );
				if ( false == $termid ) {

					$i = '';
					do {
						$term = wp_insert_term(
							$termname, // the term
							self::$product_slug, // the taxonomy
							array(
								'slug' => $slug . $i,
							)
						);
						if ( $term instanceof WP_Error ) {
							$i = rand( 1, 1000 );
							$i = '-' . $i;
						}
					} while ( $term instanceof WP_Error );
				} else {
					$term = wp_update_term(
						$termid, // the term
						self::$product_slug, // the taxonomy
						array(
							'name' => $termname,
							'slug' => $slug,
						) );
				}
				if ( is_array( $term ) ) {
					$term_id = $term['term_id'];
					Rt_Lib_Taxonomy_Metadata\add_term_meta( $term_id, self::$term_product_id_meta_key, $post_id, true );
					if ( ! empty( $this->pluginName ) ) {
						Rt_Lib_Taxonomy_Metadata\add_term_meta( $term_id, self::$term_product_from_meta_key, $this->get_product_plugin_by_post( $post_id ), true );
					}
				}
			}

		}

		public function get_product_plugin_by_post( $post_id ) {
			$post_type = get_post_type( $post_id );
			if ( 'download' == $post_type ) {
				return 'edd';
			} else if ( 'product' == $post_type ) {
				return 'woocommerce';
			}

			return '';
		}

		public function check_postid_term_exist( $post_id ) {
			global $wpdb;
			if ( ! empty( $wpdb->termmeta ) ) {
				$querystr = "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_value = {$post_id} limit 1";
			} else {
				$querystr = 'SELECT taxonomy_id FROM ' . $wpdb->prefix . 'taxonomymeta WHERE meta_value = ' . $post_id . ' limit 1';
			}
			$result = $wpdb->get_var( $querystr );
			if ( ! empty ( $result ) ) {
				return $result;
			}

			return false;
		}

		/**
		 * bulk_insert_products function.
		 *
		 * @access public
		 *
		 * @param string $plugin active plugin name
		 *
		 * @return bool
		 */
		public function bulk_insert_products( $plugin = array() ) {

			if ( ! empty( $plugin ) ) {
				$this->pluginName = $plugin;
			}
			if ( ! $this->is_edd_active() && ! $this->is_woocommerce_active() ) {
				return false;
			}

			$args          = array(
				'posts_per_page' => - 1,
				'post_type'      => $this->get_post_type(),
				'post_status'    => 'publish'
			);
			$product_array = get_posts( $args ); // Get Woo Commerce post object

			foreach ( $product_array as $product ) {
				$term = $this->get_taxonomy( $product->ID );

				if ( empty( $term ) ) {
					$new_term = wp_insert_term( $product->post_title, self::$product_slug, array( 'slug' => $product->post_name ) );
					if ( ! $new_term instanceof WP_Error ) {
						Rt_Lib_Taxonomy_Metadata\add_term_meta( $new_term['term_id'], self::$term_product_id_meta_key, $product->ID, true );
						if ( ! empty( $this->pluginName ) ) {
							Rt_Lib_Taxonomy_Metadata\add_term_meta( $new_term['term_id'], self::$term_product_from_meta_key, $this->get_product_plugin_by_post( $product->ID ), true );
						}
					}
				}
			}
			update_option( 'rt_product_plugin_sync', 'false' );

		}

		/**
		 *
		 * delete_products_meta function.
		 *
		 * @param $term_id
		 *
		 * @access public
		 * @return void
		 */
		public function delete_products_meta( $term_id ) {
			Rt_Lib_Taxonomy_Metadata\delete_term_meta( $term_id, Rt_Products::$term_product_id_meta_key );
		}

	}
}
