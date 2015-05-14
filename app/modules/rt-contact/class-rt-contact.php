<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Description of class-rt-contact
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Contact' ) ) {

	/**
	 * Class Rt_Contact
	 */
	class Rt_Contact extends Rt_Entity {

		/**
		 * @var string
		 */
		public $email_key           = 'contact_email';
		/**
		 * @var string
		 */
		static $primary_email_key   = 'contact_primary_email';
		/**
		 * @var string
		 */
		public $website_url_key     = 'contact_website';

		/**
		 * @var string
		 */
		/*static $user_category_taxonomy = 'rt-contact-group';*/
		/**
		 * @var string
		 */
		/*static $employees_category_slug = 'employees';*/
		/**
		 * @var string
		 */
		/*static $customer_category_slug = 'customers';*/
		/**
		 * @var string
		 */
		/*static $vendor_category_slug = 'vendors';*/

		/**
		 * Labels array for Contact Group Taxonomy
		 *
		 * @var array
		 */
		/*public $contact_group_labels;*/

		/**
		 *
		 */
		public function __construct() {
			parent::__construct( 'rt_contact' );

			// lable for contact
			add_action( 'init', array( $this, 'init_labels' ), 9 );

			// Register CPT & remove meta is not needed
			add_action( 'init', array( $this, 'init_entity' ) );

			// init meta field
			add_action( 'init', array( $this, 'setup_meta_fields' ) );

			/**
			 * New User Creation Sync With Person. Whenever a WP_User is created a new contact person will also be created.
			 */
			add_action( 'user_register', array( $this, 'contact_create_for_wp_user' ) );

			/**
			 * connect p2p connnection between contact with user
			 */
			add_action( 'p2p_init', array( $this, 'contact_user_p2p' ) );

			/**
			 * Add ACL meta box
			 */
			if ( ! empty( $_REQUEST['post'] ) ) {
				$_REQUEST['post_type'] = get_post_type( $_REQUEST['post'] );
			}
			if ( ! empty( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] = $this->post_type && current_user_can( 'create_users' ) ) {
				global $rt_access_control;
				add_action( 'rt_biz_entity_meta_boxes', array( $this, 'contact_acl_meta_boxes' ) );
				add_action( 'rt_biz_save_entity_meta', array( $rt_access_control, 'save_profile_level_permission' ) );
			}
			add_action('add_meta_boxes_' . $this->post_type , array( $this, 'metabox_rearrenge' ));

			// Admin primary Notice
			add_action( 'admin_notices', array( $this, 'check_primary_email_for_admin_notice' ) );
			add_action( 'admin_notices', array( $this, 'exported_admin_notice' ) );

			// For User setting page
			add_action( 'manage_users_custom_column', array( $this, 'manage_export_user_columns' ), 15, 3 );
			add_action( 'wp_ajax_rtbiz_export_contact', array( $this, 'rtbiz_export_contact' ) );
			add_action( 'wp_ajax_rtbiz_export_all_contacts', array( $this, 'rtbiz_export_all_contacts' ) );
			// for bulk action
			add_action( 'admin_footer-users.php',  array( $this, 'add_export_user_bulk_action' ) );
			add_action( 'load-users.php', array( $this, 'callback_rtbiz_bulk_action' ) );

			add_action( 'pre_get_posts', array( $this, 'contact_posts_filter' ) );

			// ajax for user search
			add_action( 'wp_ajax_search_user_from_name', array( $this, 'ajax_serch_user' ) );

			/*add_action( 'init', array( $this, 'register_tax' ), 9 );*/

			/*add_action( 'init', array( $this, 'add_defualt_categories_on_activate' ), 11 );*/
			/*add_filter( 'views_edit-'.$this->post_type, array( $this, 'edit_view_filters' ) );*/

			/*add_action( 'manage_' . self::$user_category_taxonomy . '_custom_column', array( $this, 'manage_contact_column_body' ), 10, 3 );
			add_filter( 'manage_edit-' . self::$user_category_taxonomy . '_columns', array( $this, 'manage_contact_column_header' ) );*/

			// Delete contact
			add_action( 'before_delete_post', array( $this, 'before_contact_deleted' ) );
			//add_action( 'wp_trash_post', array( $this, 'before_contact_trashed' ) );
		}

		/**
		 * up[date metabox order
		 */
		public function metabox_rearrenge(){
			global $wp_meta_boxes;
			$custom_order['submitdiv'] = $wp_meta_boxes[ $this->post_type ]['side']['core']['submitdiv'];
			$custom_order['p2p-from-' . $this->post_type . '_to_user'] = $wp_meta_boxes[ $this->post_type ]['side']['default']['p2p-from-' . $this->post_type . '_to_user'];
			$custom_order['rt-biz-acl-details'] = $wp_meta_boxes[ $this->post_type ]['side']['default']['rt-biz-acl-details'];
			$custom_order['rt-offeringdiv'] = $wp_meta_boxes[ $this->post_type ]['side']['core']['rt-offeringdiv'];
			$custom_order['p2p-to-' . Rt_HD_Module::$post_type . '_to_' . $this->post_type ] = $wp_meta_boxes[ $this->post_type ]['side']['default']['p2p-to-' . Rt_HD_Module::$post_type . '_to_' . $this->post_type ];
			$custom_order['rt-departmentdiv'] = $wp_meta_boxes[ $this->post_type ]['side']['core']['rt-departmentdiv'];
			$wp_meta_boxes[ $this->post_type ]['side']['core'] = $custom_order;
			unset( $wp_meta_boxes[ $this->post_type ]['side']['default']['rt-biz-acl-details'] );
			unset( $wp_meta_boxes[ $this->post_type ]['side']['default']['p2p-from-' . $this->post_type . '_to_user'] );
			unset( $wp_meta_boxes[ $this->post_type ]['side']['default']['p2p-to-' . Rt_HD_Module::$post_type . '_to_' . $this->post_type ] );
		}


		/**
		 * Filter contact
		 * @param $query
		 */
		function contact_posts_filter( $query ){
			global $wpdb, $rt_biz_acl_model;
			if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == $this->post_type && $query->is_main_query() ) {
				$module_where = isset( $_GET['module'] ) ? "acl.module =  '" . $_GET['module'] . "' and" : '';
				$sql = 'SELECT DISTINCT(posts.ID) FROM '.$rt_biz_acl_model->table_name.' as acl INNER JOIN '.$wpdb->prefix.'p2p as p2p on ( acl.userid = p2p.p2p_to ) INNER JOIN '.$wpdb->posts.' as posts on (p2p.p2p_from = posts.ID )  where ' . $module_where . " acl.permission > 0 and p2p.p2p_type = '".rt_biz_get_contact_post_type()."_to_user' and posts.post_status= 'publish' and posts.post_type= '".rt_biz_get_contact_post_type()."' ";
				$contacts = $wpdb->get_col( $sql );
				$module_user = get_users( array( 'fields' => 'ID', 'role' => 'administrator' ) );
				$admin_contact = rt_biz_get_contact_for_wp_user( $module_user );
				foreach ( $admin_contact as $contact ) {
					$contacts[] = $contact->ID;
				}
				if ( isset( $_GET['rt_contact_group'] ) && 'staff' == $_GET['rt_contact_group'] ) {
					if ( empty( $contacts ) ) {
						$contacts = array( -1 );
					}
					$query->set( 'post__in', $contacts );
				} elseif ( isset( $_GET['rt_contact_group'] ) && 'customer' == $_GET['rt_contact_group'] && ! empty( $contacts ) ) {
					$query->set( 'post__not_in', $contacts );
				}
			}
		}


		/**
		 * init label for contact
		 */
		function init_labels() {
			$this->labels = apply_filters( 'rt_biz_contact_labels', array(
				'name' => __( 'Contacts' ),
				'singular_name' => __( 'Contact' ),
				'menu_name' => __( 'Contact' ),
				'all_items' => __( 'All Contacts' ),
				'add_new' => __( 'Add New' ),
				'add_new_item' => __( 'Add Contact' ),
				'edit_item' => __( 'Edit Contact' ),
				'new_item' => __( 'New Contact' ),
				'view_item' => __( 'View Contact' ),
				'search_items' => __( 'Search Contact' ),
				'not_found' => __( 'No Contact found' ),
				'not_found_in_trash' => __( 'No Contact found in Trash' ),
			) );
		}

		/**
		 * admin notice for exported users
		 */
		function exported_admin_notice() {
			global $pagenow;

			if ( 'users.php' == $pagenow && isset( $_REQUEST['exported'] ) && (int) $_REQUEST['exported'] ) {
				$message = sprintf( _n( 'Contacts exported to rtbiz Contacts.', '%s Contacts exported to rtbiz Contacts.', $_REQUEST['exported'] ), number_format_i18n( $_REQUEST['exported'] ) );
				echo "<div class='updated'><p>{$message}</p></div>";
			}
		}

		/**
		 * Call back method for bulk export action from users.php to export rtbiz contacts
		 */
		function callback_rtbiz_bulk_action() {
			if ( empty( $_REQUEST['users'] ) || empty( $_REQUEST['action'] ) || 'rtexport' != $_REQUEST['action'] ) {
				return ;
			}
			check_admin_referer( 'bulk-users' );
			$userids = $_REQUEST['users'];
			if ( ! empty( $userids ) ) {
				$this->export_biz_contacts( $userids );
			}
			$redirect = 'users.php';
			$sendback = add_query_arg( array( 'exported' => count( $userids ) ), $redirect );

			wp_redirect( $sendback );
			exit();
		}

		/**
		 * This method adds bulk action option to export WordPress users to rtBiz Contacts via JS hack.
		 */
		function add_export_user_bulk_action() {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('<option>').val('rtexport').text('<?php _e( 'Export to contacts' )?>').appendTo("select[name='action']");
				});
			</script>

			<?php
		}

		/**
		 * AJAX callback for single user export from user.php
		 */
		function rtbiz_export_contact() {
			check_ajax_referer( 'rt-biz-export-'.$_POST['id'], 'nonce' );
			$return_array = array();
			$postid = $this->export_biz_contact( $_POST['id'] );
			if ( ! empty( $postid ) ) {
				$post = get_post( $postid );
				$return_array['html'] = '<a href="'.get_edit_post_link( $postid ).'">'.$post->post_title.'</a>';
				$return_array['status'] = true;
			}
			echo json_encode( $return_array );
			die();
		}

		/**
		 * Export call contacts from wp_users to rtcontact
		 */
		function rtbiz_export_all_contacts() {
			check_ajax_referer( 'rt-biz-export-all', 'nonce' );
			$return = array();
			$return['complete'] = false;
			//			$return['message'] = '';
			$offset = 0;
			if ( ! empty( $_POST['offset'] ) ) {
				$offset = intval( $_POST['offset'] );
			}
			$limit = 25;
			$users = new WP_User_Query( array( 'fields' => 'ID', 'number' => $limit, 'offset' => $offset ) );

			$count = $this->export_biz_contacts( $users->get_results() );
			$return['count'] = $count;
			$return['offset'] = $limit + $offset;
			$return['contact_processed'] = count( $users->get_results() );
			if ( $users->get_total() <= $return['offset'] ) {
				$return['complete'] = true;
			}
			echo json_encode( $return );
			die();
		}

		/**
		 * @param array $ids
		 *  bulk Map users to rtbiz contacts
		 *
		 * @return int count
		 */
		function export_biz_contacts( $ids ){
			$count = 0;
			foreach ( $ids as $id ) {
				$possts = rt_biz_get_contact_for_wp_user( $id );

				if ( empty( $possts ) ) {
					$postid = $this->export_biz_contact( $id );
					if ( $postid ) {
						$count = $count + 1;
					}
				}
			}
			return $count;
		}

		/**
		 * @param $id int WP_USER ID
		 * export single contact from user to rtbiz contact
		 * it will check if contact exists then it will map or else create new contact and will map with p2p
		 * @return mixed|null
		 */
		function export_biz_contact( $id ) {
			$user = get_user_by( 'id', $id );
			$email = $user->user_email;
			$post_id = null;
			$meta_query_args = array(
				array(
					'key'   => Rt_Entity::$meta_key_prefix . self::$primary_email_key,
					'value' => $email,
				),
			);
			$args = array( 'post_type' => rt_biz_get_contact_post_type(), 'meta_query' => $meta_query_args );
			$posts = get_posts( $args );

			if ( rt_biz_is_primary_email_unique( $email ) && empty( $posts ) ) {
				$post_id = rt_biz_add_contact( $user->display_name, '',$email );
			} else if ( ! empty( $posts ) ) {
				$post_id = $posts[0]->ID;
			}
			if ( ! empty( $post_id ) ) {
				$this->connect_contact_to_user( $post_id, $id );
			}
			return $post_id;
		}

		/**
		 * @param $value
		 * @param $column_name
		 * @param $id
		 *  Call back for managing user columns for exporter
		 * @return string
		 */
		function manage_export_user_columns( $value, $column_name, $id ) {
			if ( 'p2p-to-'.$this->post_type.'_to_user' == $column_name ) {
				$posts = rt_biz_get_contact_for_wp_user( $id );
				if ( ! empty( $posts ) ) {
					return '<a href="'.get_edit_post_link( $posts[0]->ID ).'">'.$posts[0]->post_title.'</a>';
				} else {
					$nonce = wp_create_nonce( 'rt-biz-export-'.$id );
					return '<button type="button" class="rtbiz-export button" data-id="'.$id.'">Export</button><input type="hidden" class="rtbiz-export-nonce" value="'.$nonce.'">';
				}
			}

		}

		function check_primary_email_for_admin_notice() {
			if ( isset( $_REQUEST['post'] ) && get_post_type( $_REQUEST['post'] ) == rt_biz_get_contact_post_type() ) {
				if ( $primary_unique_meta = get_user_meta( get_current_user_id(), Rt_Entity::$meta_key_prefix . 'unique_primary_email_' . $_REQUEST['post'], true ) ) {
					add_action( 'admin_notices', array( $this, 'primary_email_not_unique' ) );
					delete_user_meta( get_current_user_id(), Rt_Entity::$meta_key_prefix . 'unique_primary_email_' . $_REQUEST['post'] );
				} else if ( $primary_empty_meta = get_user_meta( get_current_user_id(), Rt_Entity::$meta_key_prefix . 'empty_primary_email_' . $_REQUEST['post'], true ) ) {
					add_action( 'admin_notices', array( $this, 'primary_email_empty' ) );
					delete_user_meta( get_current_user_id(), Rt_Entity::$meta_key_prefix . 'unique_primary_email_' . $_REQUEST['post'] );
				}
			}
		}

		function manage_contact_column_header( $columns ) {
			unset( $columns['posts'] );
			$columns['posts'] = $this->labels['name'];
			return $columns;
		}

		function manage_contact_column_body( $display, $column, $term_id ) {
		}

		/**
		 * Registers Meta Box for Rt_contact Meta Fields
		 */
		function contact_acl_meta_boxes() {
			global $rt_access_control;
			add_meta_box( 'rt-biz-acl-details', __( 'Profile-level Access' ), array( $rt_access_control, 'profile_level_permission' ), $this->post_type, 'side', 'default' );
		}

		/**
		 * Create Contact user p2p connection defination
		 */
		function contact_user_p2p() {
			$contact_labels = $this->labels;
			p2p_register_connection_type( array(
				'name' => $this->post_type . '_to_user',
				'from' => $this->post_type,
				'to' => 'user',
				'cardinality' => 'one-to-one',
				'admin_column' => 'any',
				'from_labels' => array(
					'column_title' => 'User',
				),
				'to_labels' => array(
					'column_title' => $contact_labels['singular_name'],
				),
			) );
		}

		/**
		 * Create connection contact and user
		 * @param string $from
		 * @param string $to
		 */
		function connect_contact_to_user( $from = '', $to = '' ) {
			if ( ! p2p_connection_exists( $this->post_type . '_to_user', array( 'from' => $from, 'to' => $to ) ) && is_object( p2p_type( $this->post_type . '_to_user' ) ) ) {
				p2p_type( $this->post_type . '_to_user' )->connect( $from, $to, array(
					'date' => current_time( 'mysql' )
				) );
			}
		}

		/**
		 * remove connection of comtact and user
		 * @param string $from
		 * @param string $to
		 */
		function remove_contact_to_user( $from = '', $to = '' ) {
			if ( p2p_connection_exists( $this->post_type . '_to_user', array( 'from' => $from, 'to' => $to ) ) ) {
				p2p_type( $this->post_type . '_to_user' )->disconnect( $from, $to );
			}
		}

		/**
		 * @param $views
		 *
		 * @return array
		 */
		function edit_view_filters($views){

			/*$terms             = get_terms( self::$user_category_taxonomy, array( 'hide_empty' => false, ) );
			$subsubsub         = array();
			$checkreq          = false;
			$allflag           = false;
			$check_post_status = false;
			if ( isset( $_REQUEST[ self::$user_category_taxonomy ] ) ){
				$checkreq = true;
			}
			else if ( isset( $_REQUEST['post_status'] ) ){
				$check_post_status = true;
			}
			else {
				$allflag = true;
			}
			foreach ( $terms as $term ){
				$current = '';
				if ( $checkreq && $_REQUEST[ self::$user_category_taxonomy ] == $term->slug ){
					$current  = 'current';
					$checkreq = false;
				}
				$posts = new WP_Query( array(
					                       'post_type' => $this->post_type,
					                       'post_status' => 'any',
					                       'nopaging' => true,
					                       self::$user_category_taxonomy => $term->slug,
				                       ) );

				$subsubsub[ $term->slug ] = "<li><a href='edit.php?post_type=".rt_biz_get_contact_post_type().'&'.self::$user_category_taxonomy.'='.$term->slug."' class='".$current."'>".__( $term->name )."<span class='count'> (".count( $posts->posts ).')</span></a></li>';
			}

			// We are removing publish status since we don't need it. All contacts will be in publish status. So it doen't make any sense.
			if ( ! empty( $views['publish'] ) ) {
				unset( $views['publish'] );
			}

			// Check if it's request for all contacts. If it's a custom filter request for any user category then remove current class from "All" link.
			if ( ! $allflag && ! empty( $views['all'] ) ) {
				$views['all'] = str_replace( 'current', '', $views['all'] );
			}

			// Merge Custom filter links with existing views.
			$views = array_merge( $views, $subsubsub );

			// Return new array.
			return $views;*/
		}


		/**
		 *
		 */
		function register_tax(){

			/*$this->contact_group_labels = apply_filters( 'rt_contact_groups_labels', array(
				'name'                       => __( 'Contact Groups' ),
				'singular_name'              => __( 'Contact Group' ),
				'menu_name'                  => __( 'Contact Groups' ),
				'search_items'               => __( 'Search Contact Groups' ),
				'popular_items'              => __( 'Popular Contact Groups' ),
				'all_items'                  => __( 'All User Contact Groups' ),
				'edit_item'                  => __( 'Edit Contact Group' ),
				'update_item'                => __( 'Update Contact Group' ),
				'add_new_item'               => __( 'Add New Contact Group' ),
				'new_item_name'              => __( 'New Contact Group Name' ),
				'separate_items_with_commas' => __( 'Separate Contact Groups with commas' ),
				'add_or_remove_items'        => __( 'Add or remove Contact Groups' ),
				'choose_from_most_used'      => __( 'Choose from the most popular Contact Groups' ),
			) );

			$editor_cap = rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'editor' );
			$caps = array(
				'manage_terms' => $editor_cap,
				'edit_terms'   => $editor_cap,
				'delete_terms' => $editor_cap,
				'assign_terms' => $editor_cap,
			);
			register_taxonomy(
				self::$user_category_taxonomy,
				rt_biz_get_contact_post_type(),
				array(
					'labels' => $this->contact_group_labels,
					'rewrite' => array( 'slug' => self::$user_category_taxonomy ),
					'hierarchical' => true,
					'show_admin_column' => true,
					'capabilities' => $caps,
				)
			);*/
		}

		/**
		 *
		 */
		function add_defualt_categories_on_activate(){
			/*$isSyncOpt = get_option( 'rtbiz_contact_category_default' );
			if ( empty( $isSyncOpt ) || 'true' === $isSyncOpt ){
				$default_categories = array(
					array(
						'name' => 'Employees',
						'slug' => self::$employees_category_slug,
					),
					array(
						'name' => 'Customers',
						'slug' => self::$customer_category_slug,
					),
					array(
						'name' => 'Vendors',
						'slug' => self::$vendor_category_slug,
					),
				);

				foreach ( $default_categories as $category ) {
					if ( ! term_exists( $category['name'], self::$user_category_taxonomy ) ){
						wp_insert_term(
							$category['name'], // the term
							self::$user_category_taxonomy, // the taxonomy
							array(
								'slug' => $category['slug'],
							)
						);
					}
				}
				update_option( 'rtbiz_contact_category_default', 'false' );
			}*/
		}
		/**
		 *  Init Meta Fields
		 */
		function setup_meta_fields() {
			$this->meta_fields = array(
				array(
					'key' => 'contact_primary_email',
					'text' => __( 'Email' ),
					'label' => __( 'Primary Email Address' ),
					'is_multiple' => false,
					'type' => 'email',
					'name' => 'contact_meta[contact_primary_email]',
					'description' => __( 'Valid email address.' ),
					'category' => 'Contact',
				),
				array(
					'key' => 'contact_email',
					'text' => __( 'Email' ),
					'label' => __( 'Secondary Email Address' ),
					'is_multiple' => true,
					'type' => 'email',
					'name' => 'contact_meta[contact_email][]',
					'class' => 'input-multiple',
					'description' => __( 'Valid email address.' ),
					'category' => 'Contact',
				),
				array(
					'key' => 'contact_phone',
					'text' => __( 'Phone' ),
					'label' => __( 'Phone Number' ),
					'is_multiple' => true,
					'type' => 'tel',
					'name' => 'contact_meta[contact_phone][]',
					'class' => 'input-multiple',
					'description' => __( 'Phone number.' ),
					'category' => 'Contact',
				),
				array(
					'key' => 'contact_fax',
					'text' => __( 'Fax' ),
					'label' => __( 'Fax Number' ),
					'is_multiple' => true,
					'type' => 'tel',
					'name' => 'contact_meta[contact_fax][]',
					'class' => 'input-multiple',
					'description' => __( 'Fax number.' ),
					'category' => 'Contact',
				),
				array(
					'key' => 'contact_website',
					'text' => __( 'Website' ),
					'label' => __( 'Website URL' ),
					'is_multiple' => true,
					'type' => 'url',
					'name' => 'contact_meta[contact_website][]',
					'class' => 'input-multiple',
					'description' => __( 'Website URL.' ),
					'category' => 'Contact',
				),
				array(
					'key' => 'contact_dob',
					'text' => __( 'Date of Birth' ),
					'label' => __( 'Date of Birth' ),
					'is_multiple' => false,
					'is_datepicker' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_dob]',
					'id' => 'contact_meta_contact_dob',
					'description' => __( 'Date of Birth.' ),
					'category' => 'Contact',
				),
				array(
					'key' => 'contact_address',
					'text' => __( 'Address' ),
					'label' => __( 'Address' ),
					'is_multiple' => false,
					'type' => 'textarea',
					'name' => 'contact_meta[contact_address]',
					'class' => '',
					'description' => __( 'Address.' ),
					'hide_for_client' => true,
					'category' => 'Contact',
				),
				array(
					'key' => 'contact_postal_address',
					'text' => __( 'Postal Address' ),
					'label' => __( 'Postal Address' ),
					'is_multiple' => false,
					'type' => 'textarea',
					'name' => 'contact_meta[contact_postal_address]',
					'class' => '',
					'description' => __( 'Postal Address.' ),
					'hide_for_client' => true,
					'category' => 'Contact',
				),
				array(
					'key' => 'contact_tfn',
					'text' => __( 'Tax File Number' ),
					'label' => __( 'Tax File Number' ),
					'is_multiple' => false,
					'type' => 'text',
					'name' => 'contact_meta[contact_tfn]',
					'id' => 'contact_meta_contact_tfn',
					'description' => __( 'Tax File Number.' ),
					'hide_for_client' => true,
					'category' => 'HR',
				),
				array(
					'key' => 'contact_skype_id',
					'text' => __( 'Skype' ),
					'label' => __( 'Skype Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_skype_id][]',
					'class' => 'input-multiple',
					'description' => __( 'Skype Id.' ),
					'category' => 'Social',
				),
				array(
					'key' => 'contact_twitter',
					'text' => __( 'Twitter' ),
					'label' => __( 'Twitter Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_twitter][]',
					'class' => 'input-multiple',
					'description' => __( 'Twitter Id.' ),
					'category' => 'Social',
				),
				array(
					'key' => 'contact_facebook',
					'text' => __( 'Facebook' ),
					'label' => __( 'Facebook Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_facebook][]',
					'class' => 'input-multiple',
					'description' => __( 'Facebook Id.' ),
					'category' => 'Social',

				),
				array(
					'key' => 'contact_linkedin',
					'text' => __( 'Linked In' ),
					'label' => __( 'LinkedIn Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_linkedin][]',
					'class' => 'input-multiple',
					'description' => __( 'Twitter Id.' ),
					'category' => 'Social',

				),
				array(
					'key' => 'contact_kin_name',
					'text' => __( 'Next of Kin Name' ),
					'label' => __( 'Next of Kin Name' ),
					'is_multiple' => false,
					'type' => 'text',
					'name' => 'contact_meta[contact_kin_name]',
					'id' => 'contact_meta_contact_kin_name',
					'description' => __( 'Next of Kin Name.' ),
					'hide_for_client' => true,
					'category' => 'HR',
				),
				array(
					'key' => 'contact_kin_address',
					'text' => __( 'Next of Kin Address' ),
					'label' => __( 'Next of Kin Address' ),
					'is_multiple' => false,
					'type' => 'textarea',
					'name' => 'contact_meta[contact_kin_address]',
					'id' => 'contact_meta_contact_kin_address',
					'description' => __( 'Next of Kin Address.' ),
					'hide_for_client' => true,
					'category' => 'HR',
				),
				array(
					'key' => 'contact_kin_number',
					'text' => __( 'Next of Kin Number' ),
					'label' => __( 'Next of Kin Number' ),
					'is_multiple' => false,
					'type' => 'text',
					'name' => 'contact_meta[contact_kin_number]',
					'id' => 'contact_meta_contact_kin_number',
					'description' => __( 'Next of Kin Number.' ),
					'hide_for_client' => true,
					'category' => 'HR',
				),
				array(
					'key' => 'contact_kin_relation',
					'text' => __( 'Next of Kin Relation' ),
					'label' => __( 'Next of Kin Relation' ),
					'is_multiple' => false,
					'type' => 'text',
					'name' => 'contact_meta[contact_kin_relation]',
					'id' => 'contact_meta_contact_kin_relation',
					'description' => __( 'Next of Kin Relation.' ),
					'hide_for_client' => true,
					'category' => 'HR',
				),
				array(
					'key' => 'contact_commence_date',
					'text' => __( 'Date of Commencement' ),
					'label' => __( 'Date of Commencement' ),
					'is_multiple' => false,
					'is_datepicker' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_commence_date]',
					'id' => 'contact_meta_contact_commence_date',
					'description' => __( 'Date of Commencement.' ),
					'hide_for_client' => true,
					'category' => 'HR',
				),
				array(
					'key' => 'contact_terminate_date',
					'text' => __( 'Date of Termination' ),
					'label' => __( 'Date of Termination' ),
					'is_multiple' => false,
					'is_datepicker' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_terminate_date]',
					'id' => 'contact_meta_contact_terminate_date',
					'description' => __( 'Date of Termination.' ),
					'hide_for_client' => true,
					'category' => 'HR',
				),
			);

			$this->meta_fields = apply_filters( 'rt_biz_contact_meta_fields', $this->meta_fields );
		}

		/**
		 *
		 */
		function print_metabox_js() {
			?>
			<script>

				function IsEmail( email ) {
					var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
					if ( ! regex.test( email ) ) {
						return false;
					} else {
						return true;
					}
				}

				jQuery( document ).ready( function( $ ) {

					if ( jQuery( ".user-autocomplete" ).length > 0 ) {
						jQuery( ".user-autocomplete" ).autocomplete( {
							source: function( request, response ) {
								$.ajax( {
									url: ajaxurl,
									dataType: "json",
									type: 'post',
									data: {
										action: "search_user_from_name",
										maxRows: 10,
										query: request.term
									},
									success: function( data ) {
										response( $.map( data, function( item ) {
											return {
												id: item.id,
												imghtml: item.imghtml,
												label: item.label
											}
										} ) );
									}
								} );
							}, minLength: 2,
							select: function( event, ui ) {
								jQuery( "#selected-user-contact" ).html( "<div id='subscribe-auth-" + ui.item.id + "'>" + ui.item.imghtml + ui.item.label + " &nbsp;<a href='#deleteContactUser'>X</a></div>" )
								jQuery( ".user-autocomplete" ).val( "" );
								jQuery( "#contact_meta_userid" ).val( ui.item.id );
								return false;
							}
						} ).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
							return $( "<li></li>" ).data( "ui-autocomplete-item", item ).append( "<a class='ac-subscribe-selected'>" + item.imghtml + "&nbsp;" + item.label + "</a>" ).appendTo( ul );
						};

						$( document ).on( "click", "a[href=#deleteContactUser]", function() {
							$( this ).parent().remove();
							jQuery( "#contact_meta_userid" ).val( "" );
						} );
					}

					jQuery( document ).on( 'click', ".delete-multiple", function( e ) {
						$( this ).prev().remove();
						$( this ).remove();
					} );
					function addError( selector, msg ){
						$( selector ).next().next().html( msg );
						$( selector ).next().next().addClass('rtbiz-error');
					}
					function removeError( selector ){
						$( selector ).next().next().html( '' );
						$( selector ).next().next().removeClass( 'rtbiz-error' );
					}
					jQuery( document ).on( 'click', ".add-multiple", function( e ) {
						var tempVal = $( this ).prev().val();
						var name = $( this ).prev().attr( "name" );
						if ( tempVal == '' ){
							addError( this, 'You must enter value to add more' );
							return;
						}
						else{
							removeError( this );
						}
						if ( $( this ).data( "type" ) != undefined ) {
							if ( $( this ).data( "type" ) == 'email' ) {
								if ( ! IsEmail( tempVal ) ) {
									addError( this, 'Please enter valid email address' );
									return;
								} else {
									removeError( this );
								}
							} else if ( $( this ).data( "type" ) == 'tel' ) {
								if ( ! validatePhone( tempVal ) ) {
									addError( this, 'Please Enter Valid Number' );
									return;
								} else {
									removeError( this );
								}
							}
						}

						$( this ).prev().val( '' );

						$( this ).after( "<button type='button' class='button delete-multiple'> - </button>" );
						$( this ).after( "<input type='text' name='" + name + "' value='" + tempVal + "' class='input-multiple' />" );
					} );
				} );
			</script>
			<?php
		}

		function primary_email_empty(){
			?>
			<div class="error">
				<p><?php _e( 'Primary email is necessary.', RT_BIZ_TEXT_DOMAIN ); ?></p>
			</div>
		<?php
		}

		function primary_email_not_unique(){
			?>
			<div class="error">
				<p><?php _e( 'Primary email is required to be unique.', RT_BIZ_TEXT_DOMAIN ); ?></p>
			</div>
		<?php
		}

		/**
		 * @param $post_id
		 */
		function save_meta_values( $post_id ) {
			if ( isset( $_POST['contact_meta'][ self::$primary_email_key ] ) && empty( $_POST['contact_meta'][ self::$primary_email_key ] ) ) {
				update_user_meta( get_current_user_id(), Rt_Entity::$meta_key_prefix . 'empty_primary_email_' . $_POST['post_ID'], true );
			} else {
				delete_user_meta( get_current_user_id(), Rt_Entity::$meta_key_prefix . 'empty_primary_email_' . $_POST['post_ID'] );
			}
			foreach ( $this->meta_fields as $field ) {
				if ( isset( $_POST['contact_meta'][ $field['key'] ] ) && ! empty( $_POST['contact_meta'][ $field['key'] ] ) ) {
					if ( $field['key'] == self::$primary_email_key ) {
						if ( ! rt_biz_is_primary_email_unique( $_POST['contact_meta'][ $field['key'] ], $_POST['post_ID'] ) ) {
							update_user_meta( get_current_user_id(), Rt_Entity::$meta_key_prefix . 'unique_primary_email_' . $_POST['post_ID'], true );
							continue;
						} else {
							delete_user_meta( get_current_user_id(), Rt_Entity::$meta_key_prefix . 'unique_primary_email_' . $_POST['post_ID'] );
						}
					}
					$contact_meta[ $field['key'] ] = $_POST['contact_meta'][ $field['key'] ];
					if ( isset( $field['is_multiple'] ) && $field['is_multiple'] ) {
						$oldmeta = self::get_meta( $post_id, $field['key'] );
						foreach ( $oldmeta as $ometa ) {
							self::delete_meta( $post_id, $field['key'], $ometa );
						}
						foreach ( $contact_meta[ $field['key'] ] as $nmeta ) {
							if ( '' == $nmeta ) {
								continue;
							}
							self::add_meta( $post_id, $field['key'], $nmeta );
						}
					} else {
						self::update_meta( $post_id, $field['key'], $_POST['contact_meta'][ $field['key'] ] );
					}
				} else {
					$oldmeta = self::get_meta( $post_id, $field['key'] );
					foreach ( $oldmeta as $ometa ) {
						self::delete_meta( $post_id, $field['key'], $ometa );
					}
				}
			}
			parent::save_meta_values( $post_id );
		}

		/**
		 *
		 * Columns in List View
		 *
		 * @param $columns
		 * @return mixed|void
		 */
		function post_table_columns( $columns ) {

			global $rt_company, $rt_contact, $rtbiz_offerings;

			$cols = array();
			$cols['cb'] = $columns['cb'];
			$cols['title'] = __( 'Name' );
			if ( $rtbiz_offerings ) {
				$cols[ 'taxonomy-' . Rt_Offerings::$offering_slug ] = $columns[ 'taxonomy-' . Rt_Offerings::$offering_slug ];
			}
			$cols[ 'p2p-from-' . $rt_contact->post_type . '_to_user' ] = __( 'User' );

			/*$cols[ 'taxonomy-' . Rt_Contact::$user_category_taxonomy ] = $columns[ 'taxonomy-' . Rt_Contact::$user_category_taxonomy ];*/
			/*$cols['author'] = $columns['author'];*/
			/*$cols['contact_Assignee'] = __( 'Assigned To' );*/
			/*$cols[ 'p2p-to-' . $rt_company->post_type . '_to_' . $rt_contact->post_type ] = $rt_company->labels['singular_name'];*/
			/*$cols['date'] = $columns['date'];*/

			/*$cols['contact_phone'] = __( 'Phone Number' );*/
			/*$cols['contact_email'] = __( 'Email ID' );*/

			unset( $columns['title'] );
			unset( $columns['author'] );
			unset( $columns['date'] );
			unset( $columns[ 'taxonomy-' . Rt_Offerings::$offering_slug ] );
			unset( $columns[ 'p2p-to-' . $rt_company->post_type . '_to_' . $rt_contact->post_type ] );
			unset( $columns[ 'p2p-from-' . $rt_contact->post_type . '_to_user' ] );
			unset( $columns['comments'] );
			/*unset( $columns[ 'taxonomy-' . Rt_Contact::$user_category_taxonomy ] );*/

			$cols = array_merge( $cols, $columns );

			$cols = parent::post_table_columns( $cols );
			return $cols;
		}

		/**
		 *
		 * Manage Columns for List View
		 *
		 * @param $column
		 * @param $post_id
		 */
		function manage_post_table_columns( $column, $post_id ) {

			switch ( $column ) {
				case 'contact_phone':
					$val = self::get_meta( $post_id, 'contact_phone' );
					if ( ! empty( $val ) ) {
						echo implode( ' , ', $val );
					}
					break;

				case 'contact_email':
					$val = self::get_meta( $post_id, self::$primary_email_key );
					if ( ! empty( $val ) ) {
						$emails = array();
						foreach ( $val as $e ) {
							$emails[] = '<a href="mailto:' . $e . '">' . $e . '</a>';
						}
						echo implode( ' , ', $emails );
					}
					break;
				case 'contact_Assignee':
					$val = self::get_meta( $post_id, 'assgin_to' );
					foreach ( $val as $e ) {
						$user_id = $e;
					}
					if ( ! empty( $user_id ) ) {
						$user_info = get_userdata( $user_id );
						$url       = esc_url(
							add_query_arg(
								array(
									'post_type'  => $this->post_type,
									'created_by' => $user_id,
								), 'edit.php' ) );
								printf( "<a href='%s'>%s</a>", $url, $user_info->display_name );
					}
					break;
			}

			parent::manage_post_table_columns( $column, $post_id );
		}

		/**
		 * create contact
		 *
		 * @param        $name
		 * @param string $description
		 * @param string $email
		 *
		 * @return int|WP_Error
		 */
		function add_contact( $name, $description = '', $email = '' ) {
			$contact_id = wp_insert_post( array(
				'post_title'   => $name,
				'post_excerpt' => $description,
				'post_type'    => $this->post_type,
				'post_status'  => 'publish',
			) );
			if ( ! empty( $email ) ) {
				rt_biz_update_entity_meta( $contact_id, self::$primary_email_key, $email );
			}
			return $contact_id;
		}

		/**
		 * Returns a contact if found with passed email.
		 *
		 * @param $email
		 * @return array
		 */
		function get_by_email( $email ) {
			return ( ! empty( $email ) ) ? get_posts( array(
				'meta_key'    => self::$meta_key_prefix . self::$primary_email_key,
				// primary email
				'meta_value'  => $email,
				'post_type'   => $this->post_type,
				'post_status' => 'any',
				'nopaging'    => true,
			) ) : array();
		}

		/**
		 * get contact from wp_user ids
		 *
		 * @param $user_id
		 *
		 * @return mixed
		 */
		function get_contact_for_wp_user( $user_id ) {
			return get_posts( array(
				'connected_type'  => $this->post_type . '_to_user',
				'connected_items' => $user_id,
				'post_type'       => $this->post_type,
				'post_status'     => 'any',
				'nopaging'        => true,
			) );
		}

		/**
		 * Get Wp_user from contact ids
		 *
		 * @param $contact_id
		 *
		 * @return mixed
		 */
		function get_wp_user_for_contact( $contact_id ) {
			return get_users(
				array(
					'connected_type' => $this->post_type . '_to_user',
					'connected_items' => $contact_id,
				)
			);
		}

		/**
		 * Get list of contact by category
		 *
		 * @param $category_slug
		 *
		 * @return mixed
		 */
		function get_contact_by_category( $category_slug ) {
			/*return get_posts(
				array(
					'tax_query' => array(
						array(
							'taxonomy' => self::$user_category_taxonomy,
							'field'    => 'slug',
							'terms'    => $category_slug,
						),
					),
					'post_type' => $this->post_type,
					'post_status' => 'any',
					'nopaging' => true,
				)
			);*/
		}

		/**
		 * Create Contact for given wp_user
		 *
		 * @param $user_id
		 */
		function contact_create_for_wp_user( $user_id ) {
			$contact_id = '';
			$user = get_user_by( 'id', $user_id );
			/* Check for existing contact using contact primary email. */
			$check_exist_contact = get_posts(
				array(
					'post_type' 	=> $this->post_type,
					'meta_key' 		=> self::$meta_key_prefix.self::$primary_email_key,
					'meta_value' 	=> $user->user_email,
				)
			);
			if ( count( $check_exist_contact ) > 0 ) {
				$contact_id = $check_exist_contact[0]->ID;
			} else {
				$contact_id = rt_biz_add_contact( $user->display_name, '', $user->user_email );
			}
			$this->connect_contact_to_user( $contact_id, $user_id );
			Rt_Contact::update_meta( $contact_id, $this->website_url_key, $user->user_url );
		}

		function ajax_serch_user() {
			if ( ! isset( $_POST['query'] ) ) {
				wp_die( 'Invalid request Data' );
			}
			$query = $_POST['query'];
			global $wpdb;

			$results = $wpdb->get_results( "select ID,display_name,user_email from $wpdb->users where user_email like '%{$query}%' or display_name like '%{$query}%' or user_nicename like '%{$query}%' ;" );
			$arrReturn = array();
			foreach ( $results as $author ) {
				$arrReturn[] = array( 'id' => $author->ID, 'label' => $author->display_name, 'imghtml' => get_avatar( $author->user_email, 25 ), 'editlink' => rt_biz_get_contact_edit_link( $author->user_email ) );
			}
			header( 'Content-Type: application/json' );
			echo json_encode( $arrReturn );
			die( 0 );
		}

		function before_contact_deleted( $contactid ){
			// remove acl table entry
			global $rt_biz_acl_model, $wpdb;
			$query = $wpdb->prepare( "SELECT `p2p_to` FROM $wpdb->p2p WHERE `p2p_type` = '%s' and `p2p_from` = %d", $this->post_type . '_to_user', $contactid );
			$userid = $wpdb->get_col( $query );
			if ( !empty( $userid ) )  {
				$userid = $userid[0];
				do_action( 'rtbiz_before_delete_contact_acl_remove', $contactid, $userid );
				$sql = $wpdb->prepare( "select module, max( permission ) as permission from $rt_biz_acl_model->table_name where userid = %d group by module", $userid );
				$permissions = $rt_biz_acl_model->get_result_by_query( $sql );
				//$rt_biz_acl_model->delete( array( 'userid' => $userid ) );
				foreach ( $permissions as $permission ) {
					do_action( 'rtbiz_after_delete_staff_acl_remove-' . $permission->module, $contactid, $userid, $permission->permission );
				}
				do_action( 'rtbiz_after_delete_contact_acl_remove', $contactid, $userid );
			}
			do_action( 'rtbiz_before_delete_contact', $contactid, $userid );
		}

	}

}
