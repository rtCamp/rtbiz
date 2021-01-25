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
if ( ! class_exists( 'Rtbiz_Contact' ) ) {

	/**
	 * Class Rtbiz_Contact
	 */
	class Rtbiz_Contact extends Rtbiz_Entity {

		/**
		 * @var string
		 */
		public $email_key = 'contact_email';

		/**
		 * @var string
		 */
		static $primary_email_key = 'contact_primary_email';
		/**
		 * @var string
		 */
		public $website_url_key = 'contact_website';

		public function __construct() {
			parent::__construct( 'contact' );

			$this->setup_meta_fields();

			Rtbiz::$loader->add_action( 'init', $this, 'init_labels', 9 );
			Rtbiz::$loader->add_action( 'init', $this, 'init_entity' );

			//Rtbiz::$loader->add_action( 'add_meta_boxes_' . $this->post_type, $this, 'metabox_rearrange' );

			// Admin primary Notice
			Rtbiz::$loader->add_action( 'admin_notices', $this, 'check_primary_email_for_admin_notice' );

			Rtbiz::$loader->add_action( 'pre_get_posts', $this, 'contact_posts_filter' );

			Rtbiz::$loader->add_action( 'before_delete_post', $this, 'before_contact_deleted' );

			Rtbiz::$loader->add_action( 'wp_ajax_search_user_from_name', $this, 'search_user_ajax' );

			/**
			 * Add ACL meta box
			 */
			Rtbiz::$loader->add_action( 'rtbiz_entity_meta_boxes-' . $this->post_type, $this, 'acl_meta_boxes' );

			//User integration
			Rtbiz::$loader->add_action( 'user_register', $this, 'contact_create_for_wp_user' );
			Rtbiz::$loader->add_action( 'manage_users_custom_column', $this, 'manage_export_user_columns', 15, 3 );
			Rtbiz::$loader->add_action( 'wp_ajax_rtbiz_export_contact', $this, 'export_contact' );
			Rtbiz::$loader->add_action( 'wp_ajax_rtbiz_export_all_contacts', $this, 'export_all_contacts' );
			Rtbiz::$loader->add_action( 'admin_notices', $this, 'exported_admin_notice' );

			// for bulk action
			Rtbiz::$loader->add_action( 'admin_footer-users.php', $this, 'add_export_user_bulk_action' );
			Rtbiz::$loader->add_action( 'load-users.php', $this, 'callback_bulk_action' );

			rtbiz_register_p2p_connection( $this->post_type, 'user', array(
				'cardinality'  => 'one-to-one',
				'admin_column' => 'any',
				'title'        => '',
				'from_labels'  => array(
					'column_title' => 'User',
				),
				'to_labels'    => array(
					'column_title' => 'Staff / Customer',
				),
			) );

		}


		/**
		 *  Init Meta Fields
		 */
		function setup_meta_fields() {
			$this->meta_fields = array(
				array(
					'key'         => 'contact_primary_email',
					'text'        => __( 'Email' ),
					'label'       => __( 'Primary Email Address' ),
					'is_multiple' => false,
					'type'        => 'email',
					'name'        => 'contact_meta[contact_primary_email]',
					'description' => __( 'Valid email address.' ),
					'category'    => 'Contact',
				),
				array(
					'key'         => 'contact_email',
					'text'        => __( 'Email' ),
					'label'       => __( 'Secondary Email Address' ),
					'is_multiple' => true,
					'type'        => 'email',
					'name'        => 'contact_meta[contact_email][]',
					'class'       => 'input-multiple',
					'description' => __( 'Valid email address.' ),
					'category'    => 'Contact',
				),
				array(
					'key'         => 'contact_phone',
					'text'        => __( 'Phone' ),
					'label'       => __( 'Phone Number' ),
					'is_multiple' => true,
					'type'        => 'tel',
					'name'        => 'contact_meta[contact_phone][]',
					'class'       => 'input-multiple',
					'description' => __( 'Phone number.' ),
					'category'    => 'Contact',
				),
				array(
					'key'         => 'contact_fax',
					'text'        => __( 'Fax' ),
					'label'       => __( 'Fax Number' ),
					'is_multiple' => true,
					'type'        => 'tel',
					'name'        => 'contact_meta[contact_fax][]',
					'class'       => 'input-multiple',
					'description' => __( 'Fax number.' ),
					'category'    => 'Contact',
				),
				array(
					'key'         => 'contact_website',
					'text'        => __( 'Website' ),
					'label'       => __( 'Website URL' ),
					'is_multiple' => true,
					'type'        => 'url',
					'name'        => 'contact_meta[contact_website][]',
					'class'       => 'input-multiple',
					'description' => __( 'Website URL.' ),
					'category'    => 'Contact',
				),
				array(
					'key'           => 'contact_dob',
					'text'          => __( 'Date of Birth' ),
					'label'         => __( 'Date of Birth' ),
					'is_multiple'   => false,
					'is_datepicker' => true,
					'type'          => 'text',
					'name'          => 'contact_meta[contact_dob]',
					'id'            => 'contact_meta_contact_dob',
					'description'   => __( 'Date of Birth.' ),
					'category'      => 'Contact',
				),
				array(
					'key'             => 'contact_address',
					'text'            => __( 'Address' ),
					'label'           => __( 'Address' ),
					'is_multiple'     => false,
					'type'            => 'textarea',
					'name'            => 'contact_meta[contact_address]',
					'class'           => '',
					'description'     => __( 'Address.' ),
					'hide_for_client' => true,
					'category'        => 'Contact',
				),
				array(
					'key'             => 'contact_postal_address',
					'text'            => __( 'Postal Address' ),
					'label'           => __( 'Postal Address' ),
					'is_multiple'     => false,
					'type'            => 'textarea',
					'name'            => 'contact_meta[contact_postal_address]',
					'class'           => '',
					'description'     => __( 'Postal Address.' ),
					'hide_for_client' => true,
					'category'        => 'Contact',
				),
				array(
					'key'             => 'contact_tfn',
					'text'            => __( 'Tax File Number' ),
					'label'           => __( 'Tax File Number' ),
					'is_multiple'     => false,
					'type'            => 'text',
					'name'            => 'contact_meta[contact_tfn]',
					'id'              => 'contact_meta_contact_tfn',
					'description'     => __( 'Tax File Number.' ),
					'hide_for_client' => true,
					'category'        => 'HR',
				),
				array(
					'key'         => 'contact_skype_id',
					'text'        => __( 'Skype' ),
					'label'       => __( 'Skype Id' ),
					'is_multiple' => true,
					'type'        => 'text',
					'name'        => 'contact_meta[contact_skype_id][]',
					'class'       => 'input-multiple',
					'description' => __( 'Skype Id.' ),
					'category'    => 'Social',
				),
				array(
					'key'         => 'contact_twitter',
					'text'        => __( 'Twitter' ),
					'label'       => __( 'Twitter Id' ),
					'is_multiple' => true,
					'type'        => 'text',
					'name'        => 'contact_meta[contact_twitter][]',
					'class'       => 'input-multiple',
					'description' => __( 'Twitter Id.' ),
					'category'    => 'Social',
				),
				array(
					'key'         => 'contact_facebook',
					'text'        => __( 'Facebook' ),
					'label'       => __( 'Facebook Id' ),
					'is_multiple' => true,
					'type'        => 'text',
					'name'        => 'contact_meta[contact_facebook][]',
					'class'       => 'input-multiple',
					'description' => __( 'Facebook Id.' ),
					'category'    => 'Social',

				),
				array(
					'key'         => 'contact_linkedin',
					'text'        => __( 'Linked In' ),
					'label'       => __( 'LinkedIn Id' ),
					'is_multiple' => true,
					'type'        => 'text',
					'name'        => 'contact_meta[contact_linkedin][]',
					'class'       => 'input-multiple',
					'description' => __( 'Twitter Id.' ),
					'category'    => 'Social',

				),
				array(
					'key'             => 'contact_kin_name',
					'text'            => __( 'Next of Kin Name' ),
					'label'           => __( 'Next of Kin Name' ),
					'is_multiple'     => false,
					'type'            => 'text',
					'name'            => 'contact_meta[contact_kin_name]',
					'id'              => 'contact_meta_contact_kin_name',
					'description'     => __( 'Next of Kin Name.' ),
					'hide_for_client' => true,
					'category'        => 'HR',
				),
				array(
					'key'             => 'contact_kin_address',
					'text'            => __( 'Next of Kin Address' ),
					'label'           => __( 'Next of Kin Address' ),
					'is_multiple'     => false,
					'type'            => 'textarea',
					'name'            => 'contact_meta[contact_kin_address]',
					'id'              => 'contact_meta_contact_kin_address',
					'description'     => __( 'Next of Kin Address.' ),
					'hide_for_client' => true,
					'category'        => 'HR',
				),
				array(
					'key'             => 'contact_kin_number',
					'text'            => __( 'Next of Kin Number' ),
					'label'           => __( 'Next of Kin Number' ),
					'is_multiple'     => false,
					'type'            => 'text',
					'name'            => 'contact_meta[contact_kin_number]',
					'id'              => 'contact_meta_contact_kin_number',
					'description'     => __( 'Next of Kin Number.' ),
					'hide_for_client' => true,
					'category'        => 'HR',
				),
				array(
					'key'             => 'contact_kin_relation',
					'text'            => __( 'Next of Kin Relation' ),
					'label'           => __( 'Next of Kin Relation' ),
					'is_multiple'     => false,
					'type'            => 'text',
					'name'            => 'contact_meta[contact_kin_relation]',
					'id'              => 'contact_meta_contact_kin_relation',
					'description'     => __( 'Next of Kin Relation.' ),
					'hide_for_client' => true,
					'category'        => 'HR',
				),
				array(
					'key'             => 'contact_commence_date',
					'text'            => __( 'Date of Commencement' ),
					'label'           => __( 'Date of Commencement' ),
					'is_multiple'     => false,
					'is_datepicker'   => true,
					'type'            => 'text',
					'name'            => 'contact_meta[contact_commence_date]',
					'id'              => 'contact_meta_contact_commence_date',
					'description'     => __( 'Date of Commencement.' ),
					'hide_for_client' => true,
					'category'        => 'HR',
				),
				array(
					'key'             => 'contact_terminate_date',
					'text'            => __( 'Date of Termination' ),
					'label'           => __( 'Date of Termination' ),
					'is_multiple'     => false,
					'is_datepicker'   => true,
					'type'            => 'text',
					'name'            => 'contact_meta[contact_terminate_date]',
					'id'              => 'contact_meta_contact_terminate_date',
					'description'     => __( 'Date of Termination.' ),
					'hide_for_client' => true,
					'category'        => 'HR',
				),
			);
		}

		public function get_meta_fields() {
			return apply_filters( 'rtbiz_contact_meta_fields', $this->meta_fields );
		}

		/**
		 * init label for contact
		 */
		function init_labels() {
			$this->labels = apply_filters( 'rtbiz_contact_labels', array(
				'name'               => __( 'Contacts' ),
				'singular_name'      => __( 'Contact' ),
				'menu_name'          => __( 'Contact' ),
				'all_items'          => __( 'All Contacts' ),
				'add_new'            => __( 'Add New' ),
				'add_new_item'       => __( 'Add Contact' ),
				'edit_item'          => __( 'Edit Contact' ),
				'new_item'           => __( 'New Contact' ),
				'view_item'          => __( 'View Contact' ),
				'search_items'       => __( 'Search Contact' ),
				'not_found'          => __( 'No Contact found' ),
				'not_found_in_trash' => __( 'No Contact found in Trash' ),
			) );
		}

		/**
		 * up[date metabox order
		 */
		public function metabox_rearrange() {
			global $wp_meta_boxes;
			$custom_order['submitdiv']                                   = $wp_meta_boxes[ $this->post_type ]['side']['core']['submitdiv'];
			$custom_order[ 'p2p-from-' . $this->post_type . '_to_user' ] = $wp_meta_boxes[ $this->post_type ]['side']['default'][ 'p2p-from-' . $this->post_type . '_to_user' ];
			$admin_cap                                                   = rtbiz_get_access_role_cap( RTBIZ_TEXT_DOMAIN, 'admin' );
			if ( current_user_can( $admin_cap ) ) {
				$custom_order['rt-biz-acl-details'] = $wp_meta_boxes[ $this->post_type ]['side']['default']['rt-biz-acl-details'];
			}
			$custom_order['productdiv'] = $wp_meta_boxes[ $this->post_type ]['side']['core']['productdiv'];
			if ( isset( $wp_meta_boxes[ $this->post_type ]['side']['core']['rt-teamdiv'] ) ) {
				$custom_order['teamdiv'] = $wp_meta_boxes[ $this->post_type ]['side']['core']['teamdiv'];
			}
			$wp_meta_boxes[ $this->post_type ]['side']['core'] = $custom_order;
			unset( $wp_meta_boxes[ $this->post_type ]['side']['default']['rt-biz-acl-details'] );
			unset( $wp_meta_boxes[ $this->post_type ]['side']['default'][ 'p2p-from-' . $this->post_type . '_to_user' ] );
		}

		function check_primary_email_for_admin_notice() {
			if ( isset( $_REQUEST['post'] ) && get_post_type( $_REQUEST['post'] ) == rtbiz_get_contact_post_type() ) {
				if ( $primary_unique_meta = get_user_meta( get_current_user_id(), Rtbiz_Entity::$meta_key_prefix . 'unique_primary_email_' . $_REQUEST['post'], true ) ) {
					add_action( 'admin_notices', array( $this, 'primary_email_not_unique' ), 20 );
					delete_user_meta( get_current_user_id(), Rtbiz_Entity::$meta_key_prefix . 'unique_primary_email_' . $_REQUEST['post'] );
				} else if ( $primary_empty_meta = get_user_meta( get_current_user_id(), Rtbiz_Entity::$meta_key_prefix . 'empty_primary_email_' . $_REQUEST['post'], true ) ) {
					add_action( 'admin_notices', array( $this, 'primary_email_empty' ), 20 );
					delete_user_meta( get_current_user_id(), Rtbiz_Entity::$meta_key_prefix . 'empty_primary_email_' . $_REQUEST['post'] );
				}
				if ( $wp_user_not_connected = get_user_meta( get_current_user_id(), Rtbiz_Entity::$meta_key_prefix . 'wp_user_not_connected_' . $_REQUEST['post'], true ) ) {
					add_action( 'admin_notices', array( $this, 'wp_user_not_connected' ), 20 );
					delete_user_meta( get_current_user_id(), Rtbiz_Entity::$meta_key_prefix . 'wp_user_not_connected_' . $_REQUEST['post'] );
				}
			}
		}

		function wp_user_not_connected() {
			?>
			<div class="error">
				<p><?php _e( 'There is no Wordpress User with primary email.', 'rtbiz' ); ?></p>
			</div>
			<?php
		}

		function primary_email_empty() {
			?>
			<div class="error">
				<p><?php _e( 'Primary email is necessary.', 'rtbiz' ); ?></p>
			</div>
			<?php
		}

		function primary_email_not_unique() {
			?>
			<div class="error">
				<p><?php _e( 'Primary email is required to be unique.', 'rtbiz' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Filter contact
		 *
		 * @param $query
		 */
		function contact_posts_filter( $query ) {
			global $wpdb, $rtbiz_acl_model;

			if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == $this->post_type && rtbiz_get_contact_post_type() == $query->get( 'post_type' ) ) {

				if ( ! empty( $_GET['fall_back'] ) ) {
					return;
				}
				$_GET['fall_back'] = 'yes';

				$module_where = isset( $_GET['module'] ) ? "acl.module =  '" . $_GET['module'] . "' and" : '';
				$sql          = 'SELECT DISTINCT(posts.ID) FROM ' . $rtbiz_acl_model->table_name . ' as acl INNER JOIN ' . $wpdb->prefix . 'p2p as p2p on ( acl.userid = p2p.p2p_to ) INNER JOIN ' . $wpdb->posts . ' as posts on (p2p.p2p_from = posts.ID )  where ' . $module_where . " acl.permission > 0 and p2p.p2p_type = '" . rtbiz_get_contact_post_type() . "_to_user' and posts.post_status= 'publish' and posts.post_type= '" . rtbiz_get_contact_post_type() . "' ";
				$contacts     = $wpdb->get_col( $sql );

				$module_user   = get_users( array( 'fields' => 'ID', 'role' => 'administrator' ) );
				$admin_contact = rtbiz_get_contact_for_wp_user( $module_user );
				foreach ( $admin_contact as $contact ) {
					$contacts[] = $contact->ID;
				}

				if ( isset( $_GET['contact_group'] ) && 'staff' == $_GET['contact_group'] ) {
					if ( empty( $contacts ) ) {
						$contacts = array( - 1 );
					}
					$query->set( 'post__in', $contacts );
				} elseif ( isset( $_GET['contact_group'] ) && 'customer' == $_GET['contact_group'] && ! empty( $contacts ) ) {
					$query->set( 'post__not_in', $contacts );
				}

				$_GET['fall_back'] = '';
			}
		}

		/**
		 * remove acl before contact deleted
		 *
		 * @param $contactid
		 */
		function before_contact_deleted( $contactid ) {
			// remove acl table entry
			global $rtbiz_acl_model, $wpdb;
			$query  = $wpdb->prepare( "SELECT `p2p_to` FROM $wpdb->p2p WHERE `p2p_type` = '%s' and `p2p_from` = %d", $this->post_type . '_to_user', $contactid );
			$userid = $wpdb->get_col( $query );
			if ( ! empty( $userid ) ) {
				$userid = $userid[0];
				do_action( 'rtbiz_before_delete_contact_acl_remove', $contactid, $userid );
				$sql         = $wpdb->prepare( "select module, max( permission ) as permission from $rtbiz_acl_model->table_name where userid = %d group by module", $userid );
				$permissions = $rtbiz_acl_model->get_result_by_query( $sql );
				//$rtbiz_acl_model->delete( array( 'userid' => $userid ) );
				foreach ( $permissions as $permission ) {
					do_action( 'rtbiz_after_delete_staff_acl_remove-' . $permission->module, $contactid, $userid, $permission->permission );
				}
				do_action( 'rtbiz_after_delete_contact_acl_remove', $contactid, $userid );
			}
			do_action( 'rtbiz_before_delete_contact', $contactid, $userid );
		}


		/************************************* Overide Method *************************************/

		/**
		 *
		 * Columns in List View
		 *
		 * @param $columns
		 *
		 * @return mixed|void
		 */
		function post_table_columns( $columns ) {

			global $rtbiz_company, $rtbiz_contact, $rtbiz_products;

			$cols          = array();
			$cols['cb']    = $columns['cb'];
			$cols['title'] = __( 'Name' );
			if ( $rtbiz_products ) {
				$cols[ 'taxonomy-' . Rt_Products::$product_slug ] = $columns[ 'taxonomy-' . Rt_Products::$product_slug ];
			}
			$cols[ 'p2p-from-' . $rtbiz_contact->post_type . '_to_user' ] = __( 'User' );

			/*$cols[ 'taxonomy-' . Rtbiz_Contact::$user_category_taxonomy ] = $columns[ 'taxonomy-' . Rtbiz_Contact::$user_category_taxonomy ];*/
			/*$cols['author'] = $columns['author'];*/
			/*$cols['contact_Assignee'] = __( 'Assigned To' );*/
			/*$cols[ 'p2p-to-' . $rtbiz_company->post_type . '_to_' . $rtbiz_contact->post_type ] = $rtbiz_company->labels['singular_name'];*/
			/*$cols['date'] = $columns['date'];*/

			/*$cols['contact_phone'] = __( 'Phone Number' );*/
			/*$cols['contact_email'] = __( 'Email ID' );*/

			unset( $columns['title'] );
			unset( $columns['author'] );
			unset( $columns['date'] );
			unset( $columns[ 'taxonomy-' . Rt_Products::$product_slug ] );
			unset( $columns[ 'p2p-to-' . $rtbiz_company->post_type . '_to_' . $rtbiz_contact->post_type ] );
			unset( $columns[ 'p2p-from-' . $rtbiz_contact->post_type . '_to_user' ] );
			unset( $columns['comments'] );
			/*unset( $columns[ 'taxonomy-' . Rtbiz_Contact::$user_category_taxonomy ] );*/

			$cols = array_merge( $cols, $columns );

			return parent::post_table_columns( $cols );
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
						$url       = esc_url( add_query_arg( array(
							'post_type'  => $this->post_type,
							'created_by' => $user_id,
						), 'edit.php' ) );

						printf( "<a href='%s'>%s</a>", $url, $user_info->display_name );
					}
					break;
			}

			parent::manage_post_table_columns( $column, $post_id );
		}

		function rearrange_columns( $columns ) {
			return parent::rearrange_columns( $columns );
		}

		/**
		 * @param $post_id
		 */
		function save_meta_values( $post_id, $post ) {

			$meta_fields = $this->get_meta_fields();
			if ( isset( $_POST['post_ID'] ) && ! empty ( $_REQUEST['rtbiz_is_staff_member'] ) && 'yes' == $_REQUEST['rtbiz_is_staff_member'] ) {
				$users = rtbiz_get_wp_user_for_contact( $_POST['post_ID'] );
				if ( empty( $users[0] ) ) {
					$primary_email = rtbiz_get_entity_meta( $_POST['post_ID'], self::$primary_email_key, true );
					$userid        = email_exists( $primary_email );
					if ( rtbiz_is_primary_email_unique( $primary_email, $_POST['post_ID'] ) && ! is_wp_error( $userid ) && ! p2p_connection_exists( $this->post_type . '_to_user', array( 'to' => $userid ) ) ) {
						rtbiz_connect_contact_to_user( $_POST['post_ID'], $userid );
					} else {
						update_user_meta( get_current_user_id(), Rtbiz_Entity::$meta_key_prefix . 'wp_user_not_connected_' . $_POST['post_ID'], true );
					}
				}
			}

			if ( isset( $_POST['contact_meta'][ self::$primary_email_key ] ) && empty( $_POST['contact_meta'][ self::$primary_email_key ] ) ) {
				update_user_meta( get_current_user_id(), Rtbiz_Entity::$meta_key_prefix . 'empty_primary_email_' . $_POST['post_ID'], true );
			} else {
				delete_user_meta( get_current_user_id(), Rtbiz_Entity::$meta_key_prefix . 'empty_primary_email_' . $_POST['post_ID'] );
			}
			foreach ( $meta_fields as $field ) {
				if ( isset( $_POST['contact_meta'][ $field['key'] ] ) && ! empty( $_POST['contact_meta'][ $field['key'] ] ) ) {
					if ( $field['key'] == self::$primary_email_key ) {
						if ( ! rtbiz_is_primary_email_unique( $_POST['contact_meta'][ $field['key'] ], $_POST['post_ID'] ) ) {
							update_user_meta( get_current_user_id(), Rtbiz_Entity::$meta_key_prefix . 'unique_primary_email_' . $_POST['post_ID'], true );
							continue;
						} else {
							delete_user_meta( get_current_user_id(), Rtbiz_Entity::$meta_key_prefix . 'unique_primary_email_' . $_POST['post_ID'] );
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
			Rtbiz_Contact_Profile_Access::save( $post_id, null );
			parent::save_meta_values( $post_id, $post );
		}

		public function print_metabox_js() {
			?>
			<script>

				function IsEmail(email) {
					var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
					if (!regex.test(email)) {
						return false;
					} else {
						return true;
					}
				}

				jQuery(document).ready(function ($) {

					if (jQuery(".user-autocomplete").length > 0) {
						jQuery(".user-autocomplete").autocomplete({
							source: function (request, response) {
								$.ajax({
									url: ajaxurl,
									dataType: "json",
									type: 'post',
									data: {
										action: "search_user_from_name",
										maxRows: 10,
										query: request.term
									},
									success: function (data) {
										response($.map(data, function (item) {
											return {
												id: item.id,
												imghtml: item.imghtml,
												label: item.label
											}
										}));
									}
								});
							}, minLength: 2,
							select: function (event, ui) {
								jQuery("#selected-user-contact").html("<div id='subscribe-auth-" + ui.item.id + "'>" + ui.item.imghtml + ui.item.label + " &nbsp;<a href='#deleteContactUser'>X</a></div>")
								jQuery(".user-autocomplete").val("");
								jQuery("#contact_meta_userid").val(ui.item.id);
								return false;
							}
						}).data("ui-autocomplete")._renderItem = function (ul, item) {
							return $("<li></li>").data("ui-autocomplete-item", item).append("<a class='ac-subscribe-selected'>" + item.imghtml + "&nbsp;" + item.label + "</a>").appendTo(ul);
						};

						$(document).on("click", "a[href='#deleteContactUser']", function () {
							$(this).parent().remove();
							jQuery("#contact_meta_userid").val("");
						});
					}

					jQuery(document).on('click', ".delete-multiple", function (e) {
						$(this).prev().remove();
						$(this).remove();
					});
					function addError(selector, msg) {
						$(selector).next().next().html(msg);
						$(selector).next().next().addClass('rtbiz-error');
					}

					function removeError(selector) {
						$(selector).next().next().html('');
						$(selector).next().next().removeClass('rtbiz-error');
					}

					jQuery(document).on('click', ".add-multiple", function (e) {
						var tempVal = $(this).prev().val();
						var name = $(this).prev().attr("name");
						if (tempVal == '') {
							addError(this, 'You must enter value to add more');
							return;
						}
						else {
							removeError(this);
						}
						if ($(this).data("type") != undefined) {
							if ($(this).data("type") == 'email') {
								if (!IsEmail(tempVal)) {
									addError(this, 'Please enter valid email address');
									return;
								} else {
									removeError(this);
								}
							} else if ($(this).data("type") == 'tel') {
								if (!validatePhone(tempVal)) {
									addError(this, 'Please Enter Valid Number');
									return;
								} else {
									removeError(this);
								}
							}
						}

						$(this).prev().val('');

						$(this).after("<button type='button' class='button delete-multiple'> - </button>");
						$(this).after("<input type='text' name='" + name + "' value='" + tempVal + "' class='input-multiple' />");
					});
				});
			</script>
			<?php
		}

		/************************************* Helper Method *************************************/

		/*
		 * Search user from name
		 */
		function search_user_ajax() {
			if ( ! isset( $_POST['query'] ) ) {
				wp_die( 'Invalid request Data' );
			}
			$query = $_POST['query'];
			global $wpdb;

			$results   = $wpdb->get_results( "select ID,display_name,user_email from $wpdb->users where user_email like '%{$query}%' or display_name like '%{$query}%' or user_nicename like '%{$query}%' ;" );
			$arrReturn = array();
			foreach ( $results as $author ) {
				$arrReturn[] = array( 'id'       => $author->ID,
				                      'label'    => $author->display_name,
				                      'imghtml'  => get_avatar( $author->user_email, 25 ),
				                      'editlink' => rtbiz_get_contact_edit_link( $author->user_email )
				);
			}
			header( 'Content-Type: application/json' );
			echo json_encode( $arrReturn );
			die( 0 );
		}


		/**
		 * Create Contact for given wp_user
		 *
		 * @param $user_id
		 */
		function contact_create_for_wp_user( $user_id ) {
			$contact_id = '';
			$user       = get_user_by( 'id', $user_id );
			/* Check for existing contact using contact primary email. */
			$check_exist_contact = get_posts(
				array(
					'post_type'  => $this->post_type,
					'meta_key'   => self::$meta_key_prefix . self::$primary_email_key,
					'meta_value' => $user->user_email,
				)
			);
			if ( count( $check_exist_contact ) > 0 ) {
				$contact_id = $check_exist_contact[0]->ID;
			} else {
				$contact_id = rtbiz_add_contact( $user->display_name, '', $user->user_email );
			}
			rtbiz_connect_contact_to_user( $contact_id, $user_id );
			Rtbiz_Contact::update_meta( $contact_id, $this->website_url_key, $user->user_url );
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
				rtbiz_update_entity_meta( $contact_id, self::$primary_email_key, $email );
			}

			return $contact_id;
		}

		/**
		 * Returns a contact if found with passed email.
		 *
		 * @param $email
		 *
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
			remove_action( 'pre_get_posts', array( $this, 'contact_posts_filter' ) );
			$posts = get_posts( array(
				'connected_type'  => $this->post_type . '_to_user',
				'connected_items' => $user_id,
				'post_type'       => $this->post_type,
				'post_status'     => 'any',
				'nopaging'        => true,
			) );
			add_action( 'pre_get_posts', array( $this, 'contact_posts_filter' ) );

			return $posts;
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
					'connected_type'  => $this->post_type . '_to_user',
					'connected_items' => $contact_id,
				)
			);
		}

		/************************************* user related Method *************************************/
		/**
		 * @param $value
		 * @param $column_name
		 * @param $id
		 *  Call back for managing user columns for exporter
		 *
		 * @return string
		 */
		function manage_export_user_columns( $value, $column_name, $id ) {
			if ( 'p2p-to-' . $this->post_type . '_to_user' == $column_name ) {
				$posts = rtbiz_get_contact_for_wp_user( $id );
				if ( ! empty( $posts ) ) {
					return '<a href="' . get_edit_post_link( $posts[0]->ID ) . '">' . $posts[0]->post_title . '</a>';
				} else {
					$nonce = wp_create_nonce( 'rt-biz-export-' . $id );

					return '<button type="button" class="rtbiz-export button" data-id="' . $id . '">Export</button><input type="hidden" class="rtbiz-export-nonce" value="' . $nonce . '">';
				}
			}

		}

		/**
		 * AJAX callback for single user export from user.php
		 */
		function export_contact() {
			check_ajax_referer( 'rt-biz-export-' . $_POST['id'], 'nonce' );
			$return_array = array();
			$postid       = $this->export_biz_contact( $_POST['id'] );
			if ( ! empty( $postid ) ) {
				$post                   = get_post( $postid );
				$return_array['html']   = '<a href="' . get_edit_post_link( $postid ) . '">' . $post->post_title . '</a>';
				$return_array['status'] = true;
			}
			echo json_encode( $return_array );
			die();
		}

		/**
		 * Export call contacts from wp_users to rtcontact
		 */
		function export_all_contacts() {
			check_ajax_referer( 'rt-biz-export-all', 'nonce' );
			$return             = array();
			$return['complete'] = false;
			//			$return['message'] = '';
			$offset = 0;
			if ( ! empty( $_POST['offset'] ) ) {
				$offset = intval( $_POST['offset'] );
			}
			$limit = 25;
			$users = new WP_User_Query( array( 'fields' => 'ID', 'number' => $limit, 'offset' => $offset ) );

			$count                       = $this->export_biz_contacts( $users->get_results() );
			$return['count']             = $count;
			$return['offset']            = $limit + $offset;
			$return['contact_processed'] = count( $users->get_results() );
			if ( $users->get_total() <= $return['offset'] ) {
				$return['complete'] = true;
			}
			echo json_encode( $return );
			die();
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
		function callback_bulk_action() {
			if ( empty( $_REQUEST['users'] ) || empty( $_REQUEST['action'] ) || 'rtexport' != $_REQUEST['action'] ) {
				return;
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
		 * @param array $ids
		 *  bulk Map users to rtbiz contacts
		 *
		 * @return int count
		 */
		function export_biz_contacts( $ids ) {
			$count = 0;
			foreach ( $ids as $id ) {
				$possts = rtbiz_get_contact_for_wp_user( $id );

				if ( empty( $possts ) ) {
					$postid = rtbiz_export_contact( $id );
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
		 *
		 * @return mixed|null
		 */
		function export_biz_contact( $id ) {

			$user = get_userdata( $id );
			if ( empty( $user ) ) {
				return false;
			}
			$email           = $user->user_email;
			$post_id         = null;
			$meta_query_args = array(
				array(
					'key'   => Rtbiz_Entity::$meta_key_prefix . self::$primary_email_key,
					'value' => $email,
				),
			);
			$args            = array( 'post_type' => rtbiz_get_contact_post_type(), 'meta_query' => $meta_query_args );
			$posts           = get_posts( $args );

			if ( rtbiz_is_primary_email_unique( $email ) && empty( $posts ) ) {
				$post_id = rtbiz_add_contact( $user->display_name, '', $email );
			} else if ( ! empty( $posts ) ) {
				$post_id = $posts[0]->ID;
			}
			if ( ! empty( $post_id ) ) {
				rtbiz_connect_contact_to_user( $post_id, $id );
			}

			return $post_id;
		}


		/**
		 * This method adds bulk action option to export WordPress users to rtBiz Contacts via JS hack.
		 */
		function add_export_user_bulk_action() {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					jQuery('<option>').val('rtexport').text('<?php _e( 'Export to contacts' )?>').appendTo("select[name='action']");
				});
			</script>

			<?php
		}

		/************************************* Metabox Method *************************************/

		/**
		 * Registers Meta Box for Rt_contact Meta Fields
		 */
		function acl_meta_boxes() {
			add_meta_box( 'rt-biz-acl-details', __( 'Profile-level Access' ), 'Rtbiz_Contact_Profile_Access::ui', $this->post_type, 'side', 'default' );
		}

	}

}
