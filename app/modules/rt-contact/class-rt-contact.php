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
		public $primary_email_key   = 'contact_primary_email';
		/**
		 * @var string
		 */
		public $website_url_key     = 'contact_website';

		/**
		 * @var string
		 */
		static $user_category_taxonomy = 'rt-contact-group';
		/**
		 * @var string
		 */
		static $employees_category_slug = 'employees';
		/**
		 * @var string
		 */
		static $customer_category_slug = 'customers';
		/**
		 * @var string
		 */
		static $vendor_category_slug = 'vendors';


		/**
		 *
		 */
		public function __construct() {
			parent::__construct( 'rt_contact' );
			$this->labels = array(
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
			);
			$this->setup_meta_fields();
			add_action( 'init', array( $this, 'init_entity' ) );

			add_action( 'wp_ajax_seach_user_from_name', array( $this, 'get_user_from_name' ) );

			/**
			 * Is Our Team Mate MetaBox for Person - Uses Titan Framework That's why on plugins_loaded
			 */
			//			add_action( 'plugins_loaded', array( $this, 'person_meta_box' ), 27 );

			/**
			 * New User Creation Sync With Person. Whenever a WP_User is created a new contact person will also be created.
			 */
			add_action( 'user_register', array( $this, 'contact_create_for_wp_user' ) );

			add_action( 'init', array( $this, 'register_tax' ), 9 );

			add_action( 'init', array( $this, 'add_defualt_categories_on_activate' ), 11 );
			add_filter( 'views_edit-rt_contact', array( $this, 'edit_view_filters' ) );
			add_action( 'p2p_init', array( $this, 'contact_user_p2p' ) );

			/**
			 * Add ACL meta box
			 */
			global $rt_access_control;
			$current_user = new WP_User( get_current_user_id() );
			if ( $current_user->has_cap( 'create_users' ) ){
				add_action( 'rt_biz_entity_meta_boxes', array( $this, 'contact_meta_boxes' ) );
				add_action( 'rt_biz_save_entity_meta', array( $rt_access_control, 'save_profile_level_permission' ) );
			}

		}

		/**
		 * Registers Meta Box for Rt_contact Meta Fields
		 */
		function contact_meta_boxes( ) {
			global $rt_access_control;
			add_meta_box( 'rt-biz-acl-details', __( 'Profile-level Access' ), array( $rt_access_control, 'profile_level_permission' ), $this->post_type, 'side', 'default' );
		}

		/**
		 * Create Contact user p2p connection defination
		 */
		function contact_user_p2p(){
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
				                                                    'column_title' => 'Contact',
															  ),
										) );
		}

		/**
		 * Create connection contact and user
		 * @param string $from
		 * @param string $to
		 */
		function connect_contact_to_user( $from = '', $to = '' ) {
			if ( ! p2p_connection_exists( $this->post_type . '_to_user', array( 'from' => $from, 'to' => $to ) ) ) {
				p2p_type( $this->post_type . '_to_user' )->connect( $from, $to, array(
					'date' => current_time('mysql')
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
		 */
		function edit_view_filters($views){

			$terms      = get_terms( self::$user_category_taxonomy, array( 'hide_empty' => false, ));
			$subsubsub  = array();
			$checkreq   = false;
			$allflag    = false;
			if ( isset( $_REQUEST[ self::$user_category_taxonomy ] ) ){
				$checkreq = true;
			}
			else{
				$allflag = true;
			}
			foreach ($terms as $term){
				$current='';
				if( $checkreq && $_REQUEST[ self::$user_category_taxonomy ] == $term->slug ){
					$current ='current';
					$checkreq =false;
				}
				$posts = new WP_Query( array(
					                       'post_type' => $this->post_type,
					                       'post_status' => 'any',
					                       'nopaging' => true,
					                       self::$user_category_taxonomy => $term->slug,
				                       ) );

				$subsubsub[] = "<li><a href='edit.php?post_type=rt_contact&".self::$user_category_taxonomy."=".$term->slug."' class='".$current."'>".__( $term->name )."<span class='count'> (".count($posts->posts).")</span></a></li>";
			}
			$current='';
			if( $allflag ){
				$current ='current';
			}
			$something = wp_count_posts('rt_contact');
			$top = array( "<a href='edit.php?post_type=rt_contact' class='".$current."'>".__('All')." <span class='count'> (".$something->publish.")</span></a>" );
			echo '<ul class="subsubsub">';
			echo implode(" | ", array_merge( $top, $subsubsub ) );
			echo '</ul>';
			//			return $views;
		}


		/**
		 *
		 */
		function register_tax(){
			register_taxonomy(
				self::$user_category_taxonomy,
				'rt_contact',
				array(
					'label' => __( 'Contact Group' ),
					'rewrite' => array( 'slug' => 'rt-user-group' ),
					'hierarchical' => true,
					'show_admin_column' => true,
				)
			);
		}

		/**
		 *
		 */
		function add_defualt_categories_on_activate(){

			$default_categories = array(
				array(
					'name' => 'Employees',
					'slug' => self::$employees_category_slug
				),
				array(
					'name' => 'Customers',
					'slug' => self::$customer_category_slug
				),
				array(
					'name' => 'Vendors',
					'slug' =>  self::$vendor_category_slug
				),
			);

			foreach ( $default_categories as $category ) {

				wp_insert_term(
					$category['name'], // the term
					self::$user_category_taxonomy, // the taxonomy
					array(
						'slug' => $category['slug'],
					)
				);
			}

		}
		/**
		 *  Init Meta Fields
		 */
		function setup_meta_fields() {
			$this->meta_fields = array(
				array(
					'key' => 'contact_dob',
					'text' => __( 'Date of Birth' ),
					'label' => __( 'Enter Date of Birth' ),
					'is_multiple' => false,
					'is_datepicker' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_dob]',
					'id' => 'contact_meta_contact_dob',
					'description' => __( 'Date of Birth.' ),
					'category' => 'contact',
				),
				array(
					'key' => 'contact_address',
					'text' => __( 'Address' ),
					'label' => __( 'Enter Address' ),
					'is_multiple' => false,
					'type' => 'textarea',
					'name' => 'contact_meta[contact_address]',
					'class' => '',
					'description' => __( 'Address.' ),
					'hide_for_client' => true,
					'category' => 'contact',
				),
				array(
					'key' => 'contact_postal_address',
					'text' => __( 'Postal Address' ),
					'label' => __( 'Enter Postal Address' ),
					'is_multiple' => false,
					'type' => 'textarea',
					'name' => 'contact_meta[contact_postal_address]',
					'class' => '',
					'description' => __( 'Postal Address.' ),
					'hide_for_client' => true,
					'category' => 'contact',
				),
				array(
					'key' => 'contact_tfn',
					'text' => __( 'Tax File Number' ),
					'label' => __( 'Enter Tax File Number' ),
					'is_multiple' => false,
					'type' => 'text',
					'name' => 'contact_meta[contact_tfn]',
					'id' => 'contact_meta_contact_tfn',
					'description' => __( 'Tax File Number.' ),
					'hide_for_client' => true,
					'category' => 'hr',
				),
				array(
					'key' => 'contact_phone',
					'text' => __( 'Phone' ),
					'label' => __( 'Enter Phone Number' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_phone][]',
					'class' => 'input-multiple',
					'description' => __( 'Phone number.' ),
					'category' => 'contact',
				),
				array(
					'key' => 'contact_fax',
					'text' => __( 'Fax' ),
					'label' => __( 'Enter Fax Number' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_fax][]',
					'class' => 'input-multiple',
					'description' => __( 'Fax number.' ),
					'category' => 'contact',
				),
				array(
					'key' => 'contact_primary_email',
					'text' => __( 'Email' ),
					'label' => __( 'Enter Primary Email Address' ),
					'is_multiple' => false,
					'type' => 'text',
					'name' => 'contact_meta[contact_primary_email]',
					'description' => __( 'Valid email address.' ),
					'category' => 'contact',
				),
				array(
					'key' => 'contact_email',
					'text' => __( 'Email' ),
					'label' => __( 'Enter Email Address' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_email][]',
					'class' => 'input-multiple',
					'description' => __( 'Valid email address.' ),
					'category' => 'contact',
				),
				array(
					'key' => 'contact_website',
					'text' => __( 'Website' ),
					'label' => __( 'Enter Website URL' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_website][]',
					'class' => 'input-multiple',
					'description' => __( 'Website URL.' ),
					'category' => 'contact',
				),
				array(
					'key' => 'contact_skype_id',
					'text' => __( 'Skype' ),
					'label' => __( 'Enter Skype Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_skype_id][]',
					'class' => 'input-multiple',
					'description' => __( 'Skype Id.' ),
					'category' => 'social',
				),
				array(
					'key' => 'contact_twitter',
					'text' => __( 'Twitter' ),
					'label' => __( 'Enter Twitter Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_twitter][]',
					'class' => 'input-multiple',
					'description' => __( 'Twitter Id.' ),
					'category' => 'social',
				),
				array(
					'key' => 'contact_facebook',
					'text' => __( 'Facebook' ),
					'label' => __( 'Enter Facebook Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_facebook][]',
					'class' => 'input-multiple',
					'description' => __( 'Facebook Id.' ),
					'category' => 'social',

				),
				array(
					'key' => 'contact_linkedin',
					'text' => __( 'Linked In' ),
					'label' => __( 'Enter LinkedIn Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_linkedin][]',
					'class' => 'input-multiple',
					'description' => __( 'Twitter Id.' ),
					'category' => 'social',

				),
				array(
					'key' => 'contact_kin_name',
					'text' => __( 'Next of Kin Name' ),
					'label' => __( 'Enter Next of Kin Name' ),
					'is_multiple' => false,
					'type' => 'text',
					'name' => 'contact_meta[contact_kin_name]',
					'id' => 'contact_meta_contact_kin_name',
					'description' => __( 'Next of Kin Name.' ),
					'hide_for_client' => true,
					'category' => 'hr',
				),
				array(
					'key' => 'contact_kin_address',
					'text' => __( 'Next of Kin Address' ),
					'label' => __( 'Enter Next of Kin Address' ),
					'is_multiple' => false,
					'type' => 'textarea',
					'name' => 'contact_meta[contact_kin_address]',
					'id' => 'contact_meta_contact_kin_address',
					'description' => __( 'Next of Kin Address.' ),
					'hide_for_client' => true,
					'category' => 'hr',
				),
				array(
					'key' => 'contact_kin_number',
					'text' => __( 'Next of Kin Number' ),
					'label' => __( 'Enter Next of Kin Number' ),
					'is_multiple' => false,
					'type' => 'text',
					'name' => 'contact_meta[contact_kin_number]',
					'id' => 'contact_meta_contact_kin_number',
					'description' => __( 'Next of Kin Number.' ),
					'hide_for_client' => true,
					'category' => 'hr',
				),
				array(
					'key' => 'contact_kin_relation',
					'text' => __( 'Next of Kin Relation' ),
					'label' => __( 'Enter Next of Kin Relation' ),
					'is_multiple' => false,
					'type' => 'text',
					'name' => 'contact_meta[contact_kin_relation]',
					'id' => 'contact_meta_contact_kin_relation',
					'description' => __( 'Next of Kin Relation.' ),
					'hide_for_client' => true,
					'category' => 'hr',
				),
				array(
					'key' => 'contact_commence_date',
					'text' => __( 'Date of Commencement' ),
					'label' => __( 'Enter Date of Commencement' ),
					'is_multiple' => false,
					'is_datepicker' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_commence_date]',
					'id' => 'contact_meta_contact_commence_date',
					'description' => __( 'Date of Commencement.' ),
					'hide_for_client' => true,
					'category' => 'hr',
				),
				array(
					'key' => 'contact_terminate_date',
					'text' => __( 'Date of Termination' ),
					'label' => __( 'Enter Date of Termination' ),
					'is_multiple' => false,
					'is_datepicker' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_terminate_date]',
					'id' => 'contact_meta_contact_terminate_date',
					'description' => __( 'Date of Termination.' ),
					'hide_for_client' => true,
					'category' => 'hr',
				),
			);

			$this->meta_fields = apply_filters( 'rt_biz_person_meta_fields', $this->meta_fields );
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
										action: "seach_user_from_name",
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
					jQuery( document ).on( 'click', ".add-multiple", function( e ) {
						var tempVal = $( this ).prev().val();
						var name = $( this ).prev().attr( "name" )
						if ( tempVal == '' )
							return;
						if ( $( this ).data( "type" ) != undefined ) {
							if ( $( this ).data( "type" ) == 'email' ) {
								if ( ! IsEmail( tempVal ) )
									return;
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

		/**
		 * @param $post_id
		 */
		function save_meta_values( $post_id ) {
			foreach ( $this->meta_fields as $field ) {
				if ( isset( $_POST[ 'contact_meta' ][ $field[ 'key' ] ] ) && ! empty( $_POST[ 'contact_meta' ][ $field[ 'key' ] ] ) ) {
					if( $field['key'] == $this->primary_email_key ){
						if (! biz_is_primary_email_unique($_POST[ 'contact_meta' ][ $field[ 'key' ]])){
							continue;
						}
					}
					$contact_meta[ $field[ 'key' ] ] = $_POST[ 'contact_meta' ][ $field[ 'key' ] ];
					if ( isset( $field[ 'is_multiple' ] ) && $field[ 'is_multiple' ] ) {
						$oldmeta = self::get_meta( $post_id, $field[ 'key' ] );
						foreach ( $oldmeta as $ometa ) {
							self::delete_meta( $post_id, $field[ 'key' ], $ometa );
						}
						foreach ( $contact_meta[ $field[ 'key' ] ] as $nmeta ) {
							if ( $nmeta == '' )
								continue;
							self::add_meta( $post_id, $field[ 'key' ], $nmeta );
						}
					} else {
						self::update_meta( $post_id, $field[ 'key' ], $_POST[ 'contact_meta' ][ $field[ 'key' ] ] );
					}
				} else {
					$oldmeta = self::get_meta( $post_id, $field[ 'key' ] );
					foreach ( $oldmeta as $ometa ) {
						self::delete_meta( $post_id, $field[ 'key' ], $ometa );
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
			$cols['taxonomy-' . Rt_Contact::$user_category_taxonomy ] = $columns['taxonomy-' . Rt_Contact::$user_category_taxonomy ];
			$cols['author'] = $columns['author'];
			$cols['contact_Assignee'] = __( 'Assigned To' );
			if ( $rtbiz_offerings ){
				$cols['taxonomy-' . Rt_Offerings::$offering_slug ] = $columns['taxonomy-' . Rt_Offerings::$offering_slug ];
			}
			$cols['p2p-to-' . $rt_company->post_type . '_to_' . $rt_contact->post_type ] = $rt_company->labels['singular_name'];
			$cols['date'] = $columns['date'];
			$cols['p2p-to-' . $rt_contact->post_type . '_to_user'] = __( 'User' );
			$cols[ 'contact_phone' ] = __( 'Phone Number' );
			$cols[ 'contact_email' ] = __( 'Email ID' );

			unset( $columns['title'] );
			unset( $columns['taxonomy-' . Rt_Contact::$user_category_taxonomy ] );
			unset( $columns['author'] );
			unset( $columns['date'] );
			unset( $columns['taxonomy-' . Rt_Offerings::$offering_slug ] );
			unset( $columns['p2p-to-' . $rt_company->post_type . '_to_' . $rt_contact->post_type ] );
			unset( $columns['p2p-to-' . $rt_contact->post_type . '_to_user'] );
			unset( $columns['comments'] );

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
					$val = self::get_meta( $post_id, $this->primary_email_key );
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
		 * @param $name
		 * @param string $description
		 * @return int|WP_Error
		 */
		function add_contact( $name, $description = '' ) {
			$contact_id = wp_insert_post(
					array(
						'post_title' => $name,
						'post_content' => $description,
						'post_type' => $this->post_type,
						'post_status' => 'publish',
					)
			);

			return $contact_id;
		}

		/**
		 * Returns a contact if found with passed email.
		 *
		 * @param $email
		 * @return array
		 */
		function get_by_email( $email ) {
			return ( ! empty( $email ) ) ? get_posts(
							array(
								'meta_key' => self::$meta_key_prefix . $this->primary_email_key, // primary email
								'meta_value' => $email,
								'post_type' => $this->post_type,
								'post_status' => 'any',
								'nopaging' => true,
							)
					) : array();
		}

		/**
		 * get contact from wp_user ids
		 *
		 * @param $user_id
		 *
		 * @return mixed
		 */
		function get_contact_for_wp_user( $user_id ) {
			return get_posts(
					array(
						'connected_type' => $this->post_type . '_to_user',
						'connected_items' => $user_id,
						'post_type' => $this->post_type,
						'post_status' => 'any',
						'nopaging' => true,
					)
			);
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
			return get_posts(
				array(
					'tax_query' => array(
						'taxonomy' => self::$user_category_taxonomy,
						'field'    => 'slug',
						'terms'    => $category_slug,
					),
					'post_type' => $this->post_type,
					'post_status' => 'any',
					'nopaging' => true,
				)
			);
		}

		/**
		 * Create Contact for given wp_user
		 *
		 * @param $user_id
		 */
		function contact_create_for_wp_user( $user_id ) {
			$user = get_user_by( 'id', $user_id );
			$contact_id = $this->add_contact( $user->display_name );
			$this->connect_contact_to_user( $contact_id, $user_id );
			Rt_Contact::add_meta( $contact_id, $this->primary_email_key, $user->user_email );
			Rt_Contact::add_meta( $contact_id, $this->website_url_key, $user->user_url );
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
