<?php

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Description of class-rt-company
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Company' ) ) {

	/**
	 * Class Rt_Company
	 */
	class Rt_Company extends Rt_Entity {

		static $primary_email = 'account_primary_email';

		/**
		 *
		 */
		public function __construct() {
			parent::__construct( 'rt_account' );
			$this->labels = array(
				'name' => __( 'Companies' ),
				'singular_name' => __( 'Company' ),
				'menu_name' => __( 'Companies' ),
				'all_items' => __( 'All Companies' ),
				'add_new' => __( 'Add New' ),
				'add_new_item' => __( 'Add Company' ),
				'edit_item' => __( 'Edit Company' ),
				'new_item' => __( 'New Company' ),
				'view_item' => __( 'View Company' ),
				'search_items' => __( 'Search Company' ),
				'not_found' => __( 'No Companies found' ),
				'not_found_in_trash' => __( 'No Companies found in Trash' ),
			);
			$this->setup_meta_fields();
			add_action( 'init', array( $this, 'init_entity' ) );
		}


		/**
		 *  Init Meta Fields
		 */
		function setup_meta_fields() {
			$this->meta_fields = array(
				array(
					'key' => 'account_address',
					'text' => __( 'Address' ),
					'label' => __( 'Address' ),
					'is_multiple' => false,
					'type' => 'textarea',
					'name' => 'account_meta[account_address]',
					'id' => 'account_meta_address',
					'description' => __( 'Organization address.' ),
				    'category' => 'Contact',
				),
				array(
					'key' => 'account_country',
					'text' => __( 'Country' ),
					'label' => __( 'Country' ),
					'is_multiple' => false,
					'type' => 'text',
					'name' => 'account_meta[account_country]',
					'id' => 'account_meta_address',
					'description' => __( 'Organization country.' ),
					'category' => 'Contact',
				),
				array(
					'key' => 'account_phone',
					'text' => __( 'Phone' ),
					'label' => __( 'Phone Number' ),
					'is_multiple' => true,
					'type' => 'tel',
					'name' => 'account_meta[account_phone][]',
					'class' => 'input-multiple',
					'description' => __( 'Phone number.' ),
					'category' => 'Contact',
				),
				array(
					'key' => 'account_fax',
					'text' => __( 'Fax' ),
					'label' => __( 'Fax Number' ),
					'is_multiple' => true,
					'type' => 'tel',
					'name' => 'account_meta[account_fax][]',
					'class' => 'input-multiple',
					'description' => __( 'Fax number.' ),
					'category' => 'Contact',
				),
				array(
					'key' => 'account_primary_email',
					'text' => __( 'Email' ),
					'label' => __( 'Primary Email Address' ),
					'is_multiple' => false,
					'type' => 'email',
					'name' => 'account_meta[account_primary_email]',
					'description' => __( 'Valid email address.' ),
					'category' => 'Contact',
				),
				array(
					'key' => 'account_email',
					'text' => __( 'Email' ),
					'label' => __( 'Email Address' ),
					'is_multiple' => true,
					'type' => 'email',
					'name' => 'account_meta[account_email][]',
					'class' => 'input-multiple',
					'description' => __( 'Valid email address.' ),
					'category' => 'Contact',
				),
				array(
					'key' => 'account_website',
					'text' => __( 'Website' ),
					'label' => __( 'Website URL' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'account_meta[account_website][]',
					'class' => 'input-multiple',
					'description' => __( 'Website URL.' ),
					'category' => 'Contact',
				),
				array(
					'key' => 'account_skype_id',
					'text' => __( 'Skype' ),
					'label' => __( 'Skype Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'account_meta[account_skype_id][]',
					'class' => 'input-multiple',
					'description' => __( 'Skype Id.' ),
					'category' => 'Social',
				),
				array(
					'key' => 'account_twitter',
					'text' => __( 'Twitter' ),
					'label' => __( 'Twitter Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'account_meta[account_twitter][]',
					'class' => 'input-multiple',
					'description' => __( 'Twitter Id.' ),
					'category' => 'Social',
				),
				array(
					'key' => 'account_facebook',
					'text' => __( 'Facebook' ),
					'label' => __( 'Facebook Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'account_meta[account_facebook][]',
					'class' => 'input-multiple',
					'description' => __( 'Facebook Id.' ),
					'category' => 'Social',
				),
				array(
					'key' => 'account_linkedin',
					'text' => __( 'Linked In' ),
					'label' => __( 'LinkedIn Id' ),
					'is_multiple' => true,
					'type' => 'text',
					'name' => 'account_meta[account_linkedin][]',
					'class' => 'input-multiple',
					'description' => __( 'Linked IN Id.' ),
					'category' => 'Social',
				),
			);

			$this->meta_fields = apply_filters( 'rt_biz_organization_meta_fields', $this->meta_fields );
		}

		/**
		 *  Print JS for Additional Info MetaBox
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

		/**
		 *
		 * Save Meta Fields
		 *
		 * @param $post_id
		 */
		function save_meta_values( $post_id ) {
			foreach ( $this->meta_fields as $field ) {
				if ( isset( $_POST['account_meta'][ $field['key'] ] ) && ! empty( $_POST['account_meta'][ $field['key'] ] ) ) {
					if ( $field['key'] == self::$primary_email ) {
						if ( ! biz_is_primary_email_unique_company( $_POST['account_meta'][ $field['key'] ] ) ) {
							continue;
						}
					}
					$account_meta[ $field['key'] ] = $_POST['account_meta'][ $field['key'] ];
					if ( isset( $field['is_multiple'] ) && $field['is_multiple'] ) {
						$oldmeta = self::get_meta( $post_id, $field['key'] );
						foreach ( $oldmeta as $ometa ) {
							self::delete_meta( $post_id, $field['key'], $ometa );
						}
						foreach ( $account_meta[ $field['key'] ] as $nmeta ) {
							if ( $nmeta == '' ){
								continue;
							}
							self::add_meta( $post_id, $field['key'], $nmeta );
						}
					} else {
						self::update_meta( $post_id, $field['key'], $_POST['account_meta'][ $field['key'] ] );
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

			$cols = array();
			$cols['cb'] = $columns['cb'];
			$cols['title'] = __( 'Name' );
			global $rtbiz_offerings;
			if ( isset( $rtbiz_offerings ) ){
				$cols[ 'taxonomy-'.Rt_Offerings::$offering_slug ] = $columns[ 'taxonomy-'.Rt_Offerings::$offering_slug ];
			}
			$cols['author'] = $columns['author'];
			$cols['country'] = __( 'Country' );
			$cols['date'] = $columns['date'];

			unset( $columns['title'] );
			unset( $columns['author'] );
			unset( $columns['date'] );
			unset( $columns['comments'] );
			unset( $columns[ 'taxonomy-'.Rt_Offerings::$offering_slug ] );

			$cols = array_merge( $cols, $columns );
			$cols = parent::post_table_columns( $cols );
			return $cols;

		}
		/**
		 * @param $name
		 * @param string $note
		 * @param string $address
		 * @param string $country
		 * @param array $meta
		 * @return int|WP_Error
		 */
		function add_company( $name, $note = '', $address = '', $country = '', $meta = array() ) {
			$org_id = wp_insert_post( array(
				                          'post_title'   => $name,
				                          'post_content' => $note,
				                          'post_type'    => $this->post_type,
				                          'post_status'  => 'publish',
			                          ) );

			if ( ! empty( $address ) ) {
				self::update_meta( $org_id, 'account_address', $address );
			}

			if ( ! empty( $country ) ) {
				self::update_meta( $org_id, 'account_country', $country );
			}

			if ( ! empty( $meta ) && is_array( $meta ) ) {
				foreach ( $meta as $key => $value ) {
					foreach ( $value as $data ) {
						self::update_meta( $org_id, $key, $data );
					}
				}
			}

			return $org_id;
		}

		function get_company() {
			return get_posts( array(
				                  'post_type'   => $this->post_type,
				                  'post_status' => 'any',
				                  'nopaging'    => true,
			                  ) );
		}

	}

}
