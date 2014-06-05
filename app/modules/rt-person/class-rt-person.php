<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Description of class-rt-person
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Person' ) ) {
	/**
	 * Class Rt_Person
	 */
	class Rt_Person extends Rt_Entity {

		/**
		 * @var string
		 */
		public $email_key = 'contact_email';

		public $user_id_key = 'contact_user_id';

		static $our_team_mate_key = 'is_our_team_mate';

		/**
		 *
		 */
		public function __construct() {
			parent::__construct( 'rt_contact' );
			$this->labels = array(
				'name' => __( 'Person' ),
				'singular_name' => __( 'Person' ),
				'menu_name' => __( 'People' ),
				'all_items' => __( 'All People' ),
				'add_new' => __( 'Add New' ),
				'add_new_item' => __( 'Add Person' ),
				'edit_item' => __( 'Edit Person' ),
				'new_item' => __( 'New Person' ),
				'view_item' => __( 'View Person' ),
				'search_items' => __( 'Search Person' ),
				'not_found' => __( 'No people found' ),
				'not_found_in_trash' => __( 'No people found in Trash' ),
				'not_found_in_trash' => __( 'No people found in Trash' ),
			);
			$this->setup_meta_fields();
			add_action( 'init', array( $this, 'init_entity' ) );
			add_action( 'init', array( $this, 'check_filters' ) );

			/**
			 * Is Our Team Mate MetaBox for Person - Uses Titan Framework That's why on plugins_loaded
			 */
			add_action( 'plugins_loaded', array( $this, 'person_meta_box' ), 22 );
		}

		/**
		 * Filters Persons on My Team Page - List View
		 * Only When it's My Team Page.
		 */
		function check_filters() {
			if ( isset( $_REQUEST['rt-biz-my-team'] ) && $_REQUEST['rt-biz-my-team'] ) {
				add_filter( 'posts_where', array( $this,'filter_our_team_where' ), 10, 2 );
				add_filter( 'posts_join', array( $this,'filter_our_team_join' ), 10, 2 );
			}
		}

		/**
		 *
		 * WHERE Filter of WP_Query
		 * Filter Persons on Our Team Page - List View
		 *
		 * @param $where
		 * @param $query_obj
		 * @return string
		 */
		function filter_our_team_where( $where, $query_obj ) {
			global $wpdb;
			$where .= " AND {$wpdb->postmeta}.meta_key = '" . self::$meta_key_prefix . self::$our_team_mate_key ."' AND {$wpdb->postmeta}.meta_value = '1'";
			return $where;
		}

		/**
		 *
		 * JOIN Filter of WP_Query
		 * Filter Persons on Our Team Page - List View
		 *
		 * @param $join
		 * @param $query_obj
		 * @return string
		 */
		function filter_our_team_join( $join, $query_obj ) {
			global $wpdb;
			$join .= " JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id";
			return $join;
		}

		/**
		 *  Init Settings for Team Mate MetaBox. Titan
		 */
		function person_meta_box() {

			if ( ! isset( Rt_Biz_Settings::$titan_obj ) || empty( Rt_Biz_Settings::$titan_obj ) ) {
				return;
			}

			$our_team_mate = Rt_Biz_Settings::$titan_obj->createMetaBox(array(
				'name' => __( 'Our Team-mate' ), // Name of the menu item
				// 'parent' => null, // slug of parent, if blank, then this is a top level menu
				'id' => 'rt-biz-person-our-team-mate', // Unique ID of the menu item
				// 'capability' => 'manage_options', // User role
				// 'icon' => 'dashicons-admin-generic', // Menu icon for top level menus only
				// 'position' => 100.01 // Menu position for top level menus only
				'post_type' => $this->post_type, // Post type, can be an array of post types
				'context' => 'side', // normal, advanced, or side
				'hide_custom_fields' => true, // If true, the custom fields box will not be shown
			));
			$our_team_mate->createOption( array(
				'name' => __( 'Is our team mate ?' ), // Name of the option
				'desc' => 'This is a checkbox which decides this contacts is part of our team or not.', // Description of the option
				'id' => self::$our_team_mate_key, // Unique ID of the option
				'type' => 'checkbox', //
				'default' => 0, // Menu icon for top level menus only
				'example' => '', // An example value for this field, will be displayed in a <code>
				'livepreview' => '', // jQuery script to update something in the site. For theme customizer only
			) );
		}

		/**
		 *  Init Meta Fields
		 */
		function setup_meta_fields() {
			$this->meta_fields = array(
				array(
					'key' => 'contact_email',
					'text' => __( 'Email' ),
					'label' => __( 'Enter Email Address' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'contact_meta[contact_email][]',
					'class' => 'input-multiple',
					'description' => __( 'Valid email address.' ),
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
				),
				array(
					'key' => 'contact_user_id',
					'text' => __( 'WordPress User' ),
					'label' => __( 'Select user for contact' ),
					'is_autocomplete' => true,
					'data_source' => 'WP_User',
					'autocomplete_class' => 'user-autocomplete',
					'selected_div_id' => 'selected-user-contact',
					'selected_item_id_prefix' => 'subscribe-auth-',
					'remove_item_href' => '#deleteContactUser',
					'type' => 'hidden',
					'name' => 'contact_meta[contact_user_id]',
					'id' => 'contact_meta_userid',
					'class' => '',
					'description' => __( 'User to which this contact belongs.' ),
				),
			);

			$this->meta_fields = apply_filters( 'rt_biz_person_meta_fields', $this->meta_fields );
		}

		/**
		 *
		 */
		function print_metabox_js() { ?>
			<script>

				function IsEmail(email) {
					var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
					if (!regex.test(email)) {
						return false;
					} else {
						return true;
					}
				}

				jQuery(document).ready(function($) {

					if(jQuery(".user-autocomplete").length > 0){
						jQuery(".user-autocomplete").autocomplete({
							source: function( request, response ) {
							$.ajax({
							  url: ajaxurl,
							  dataType: "json",
							  type:'post',
							  data: {
								action: "seach_user_from_name",
								maxRows: 10,
								query: request.term
							  },
							  success: function( data ) {
								response( $.map( data, function( item ) {
								  return {
									id: item.id ,
									imghtml: item.imghtml,
									label:item.label
								  }
								}));
							  }
							});
						  },minLength: 2,
							select: function(event, ui) {
								jQuery("#selected-user-contact").html("<div id='subscribe-auth-" + ui.item.id + "'>" +  ui.item.imghtml + ui.item.label + " &nbsp;<a href='#deleteContactUser'>X</a></div>")
								jQuery(".user-autocomplete").val("");
								jQuery("#contact_meta_userid").val(ui.item.id);
								return false;
							}
						}).data("ui-autocomplete")._renderItem = function(ul, item) {
							return $("<li></li>").data("ui-autocomplete-item", item).append("<a class='ac-subscribe-selected'>" + item.imghtml + "&nbsp;" + item.label + "</a>").appendTo(ul);
						};

						$(document).on("click", "a[href=#deleteContactUser]", function() {
							$(this).parent().remove();
							jQuery("#contact_meta_userid").val("");
						});
					}

					jQuery(document).on('click', ".delete-multiple", function(e) {
						$(this).prev().remove();
						$(this).remove();
					});
					jQuery(document).on('click', ".add-multiple", function(e) {
						var tempVal = $(this).prev().val();
						var name = $(this).prev().attr("name")
						if (tempVal == '')
							return;
						if ($(this).data("type") != undefined) {
							if ($(this).data("type") == 'email') {
								if (!IsEmail(tempVal))
									return;
							}
						}

						$(this).prev().val('');

						$(this).after("<button type='button' class='button delete-multiple'> - </button>");
						$(this).after("<input type='text' name='" + name + "' value='" + tempVal + "' class='input-multiple' />");
					});
				});
			</script>
		<?php }

		/**
		 * @param $post_id
		 */
		function save_meta_values($post_id) {
			foreach ( $this->meta_fields as $field ) {
				if ( isset( $_POST['contact_meta'][$field['key']] ) && ! empty( $_POST['contact_meta'][$field['key']] ) ) {
					$contact_meta[$field['key']] = $_POST['contact_meta'][$field['key']];
					if ( isset( $field['is_multiple'] ) && $field['is_multiple'] ) {
						$oldmeta = self::get_meta( $post_id, $field['key'] );
						foreach ( $oldmeta as $ometa ) {
							self::delete_meta( $post_id, $field['key'], $ometa );
						}
						foreach ( $contact_meta[$field['key']] as $nmeta ) {
							if ( $nmeta == '' )
								continue;
							self::add_meta( $post_id, $field['key'], $nmeta );
						}
					} else {
						self::update_meta( $post_id, $field['key'], $_POST['contact_meta'][$field['key']] );
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
		 * @param $name
		 * @param string $description
		 * @return int|WP_Error
		 */
		function add_person( $name, $description = '' ) {
			$person_id = wp_insert_post(
				array(
					'post_title' => $name,
					'post_content' => $description,
					'post_type' => $this->post_type,
					'post_status' => 'publish',
				)
			);

			return $person_id;
		}

		/**
		 * Returns a person if found with passed email.
		 *
		 * @param $email
		 * @return array
		 */
		function get_by_email( $email ) {
			return get_posts(
				array(
					'meta_key' => self::$meta_key_prefix.$this->email_key,
					'meta_value' => $email,
					'post_type' => $this->post_type,
					'post_status' => 'any',
					'nopaging' => true,
				)
			);
		}

		function get_contact_for_wp_user( $user_id ) {
			return get_posts(
				array(
					'meta_query' => array(
						array(
							'key' => self::$meta_key_prefix.self::$our_team_mate_key,
							'value' => '1',
						),
						array(
							'key' => self::$meta_key_prefix.$this->user_id_key,
							'value' => $user_id,
						),
					),
					'post_type' => $this->post_type,
					'post_status' => 'any',
					'nopaging' => true,
				)
			);
		}

		function get_wp_user_for_person( $person_id ) {
			$user_id = self::get_meta( $person_id, $this->user_id_key, true );
			return $user_id;
		}

		function get_employees() {
			return get_posts(
				array(
					'meta_key' => self::$meta_key_prefix.self::$our_team_mate_key,
					'meta_value' => '1',
					'post_type' => $this->post_type,
					'post_status' => 'any',
					'nopaging' => true,
				)
			);
		}

        function get_clients() {
            return get_posts(
                array(
                    'meta_key' => self::$meta_key_prefix.self::$our_team_mate_key,
                    'meta_value' => '0',
                    'post_type' => $this->post_type,
                    'post_status' => 'any',
                    'nopaging' => true,
                )
            );
        }
	}
}
