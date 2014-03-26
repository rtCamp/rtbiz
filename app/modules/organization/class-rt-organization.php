<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Description of class-rt-organization
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Organization' ) ) {
	class Rt_Organization extends Rt_Entity {
		public function __construct() {
			parent::__construct( 'rt_account' );
			$this->labels = array(
				'name' => __( 'Organization' ),
				'singular_name' => __( 'Organization' ),
				'menu_name' => __( 'Organizations' ),
				'all_items' => __( 'All Organizations' ),
				'add_new' => __( 'Add New' ),
				'add_new_item' => __( 'Add Organization' ),
				'edit_item' => __( 'Edit Organization' ),
				'new_item' => __( 'New Organization' ),
				'view_item' => __( 'View Organization' ),
				'search_items' => __( 'Search Organization' ),
				'not_found' => __( 'No Organizations found' ),
				'not_found_in_trash' => __( 'No Organizations found in Trash' ),
				'not_found_in_trash' => __( 'No Organizations found in Trash' ),
			);
			$contacts_logo_url = get_site_option( 'rt_contacts_logo_url' );
			if ( empty( $contacts_logo_url ) ) {
				$contacts_logo_url = RT_CONTACTS_URL . 'app/assets/img/contacts-16X16.png';
			}
			$this->register_post_type( $this->post_type, $this->labels, $contacts_logo_url );
			$this->setup_meta_fields();
		}

		function setup_meta_fields() {
			$this->meta_fields = array(
				array(
					'key' => 'account_address',
					'text' => __( 'Address' ),
					'label' => __( 'Enter Address' ),
					'is_multiple' => false,
					'type' => 'textarea',
					'name' => 'account_meta[account_address]',
					'id' => 'account_meta_address',
					'description' => __( 'Organization address.' ),
				),
				array(
					'key' => 'account_country',
					'text' => __( 'Country' ),
					'label' => __( 'Enter Country' ),
					'is_multiple' => false,
					'type' => 'text',
					'name' => 'account_meta[account_country]',
					'id' => 'account_meta_address',
					'description' => __( 'Organization country.' ),
				),
				array(
					'key' => 'account_email',
					'text' => __( 'Email' ),
					'label' => __( 'Enter Email Address' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'account_meta[account_email][]',
					'class' => 'input-multiple',
					'description' => __( 'Valid email address.' ),
				),
				array(
					'key' => 'account_phone',
					'text' => __( 'Phone' ),
					'label' => __( 'Enter Phone Number' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'account_meta[account_phone][]',
					'class' => 'input-multiple',
					'description' => __( 'Phone number.' ),
				),
				array(
					'key' => 'account_fax',
					'text' => __( 'Fax' ),
					'label' => __( 'Enter Fax Number' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'account_meta[account_fax][]',
					'class' => 'input-multiple',
					'description' => __( 'Fax number.' ),
				),
				array(
					'key' => 'account_skype_id',
					'text' => __( 'Skype' ),
					'label' => __( 'Enter Skype Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'account_meta[account_skype_id][]',
					'class' => 'input-multiple',
					'description' => __( 'Skype Id.' ),
				),
				array(
					'key' => 'account_twitter',
					'text' => __( 'Twitter' ),
					'label' => __( 'Enter Twitter Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'account_meta[account_twitter][]',
					'class' => 'input-multiple',
					'description' => __( 'Twitter Id.' ),
				),
				array(
					'key' => 'account_facebook',
					'text' => __( 'Facebook' ),
					'label' => __( 'Enter Facebook Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'account_meta[account_facebook][]',
					'class' => 'input-multiple',
					'description' => __( 'Facebook Id.' ),
				),
				array(
					'key' => 'account_linkedin',
					'text' => __( 'Linked In' ),
					'label' => __( 'Enter LinkedIn Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'account_meta[account_linkedin][]',
					'class' => 'input-multiple',
					'description' => __( 'Twitter Id.' ),
				),
				array(
					'key' => 'account_website',
					'text' => __( 'Website' ),
					'label' => __( 'Enter Website URL' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'account_meta[account_website][]',
					'class' => 'input-multiple',
					'description' => __( 'Website URL.' ),
				),
			);

			$this->meta_fields = apply_filters( 'rt_contacts_organization_meta_fields', $this->meta_fields );
		}

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

		function save_meta_values($post_id) {
			foreach ( $this->meta_fields as $field ) {
				if ( isset( $_POST['account_meta'][$field['key']] ) && !empty( $_POST['account_meta'][$field['key']] ) ) {
					$account_meta[$field['key']] = $_POST['account_meta'][$field['key']];
					if ( isset( $field['is_multiple'] ) && $field['is_multiple'] ) {
						$oldmeta = self::get_meta( $post_id, $field['key'] );
						foreach ( $oldmeta as $ometa ) {
							self::delete_meta( $post_id, $field['key'], $ometa );
						}
						foreach ( $account_meta[$field['key']] as $nmeta ) {
							if ( $nmeta == '' )
								continue;
							self::add_meta( $post_id, $field['key'], $nmeta );
						}
					} else {
						$old_value = self::get_meta( $post_id, $field['key'], true );
						if ( empty( $old_value ) ) {
							self::add_meta( $post_id, $field['key'], $_POST['account_meta'][$field['key']], true );
						} else {
							self::update_meta( $post_id, $field['key'], $_POST['account_meta'][$field['key']] );
						}
					}
				} else {
					$oldmeta = get_post_meta( $post_id, $field['key'] );
					foreach ( $oldmeta as $ometa ) {
						self::delete_meta( $post_id, $field['key'], $ometa );
					}
				}
			}

			parent::save_meta_values( $post_id );
		}

		function add_organization( $name, $note = '', $address = '', $country = '', $meta = array() ) {
			$org_id = wp_insert_post(
				array(
					'post_title' => $name,
					'post_content' => $note,
					'post_type' => $this->post_type,
					'post_status' => 'publish',
				)
			);

			if ( !empty( $address ) ) {
				self::update_meta( $org_id, 'account_address', $address );
			}

			if ( !empty( $country ) ) {
				self::update_meta( $org_id, 'account_country', $country );
			}

			if ( !empty( $meta ) && is_array( $meta ) ) {
				foreach ( $meta as $key => $value ) {
					foreach ( $value as $data ) {
						self::update_meta( $org_id, $key, $data );
					}
				}
			}

			return $org_id;
		}
	}
}
