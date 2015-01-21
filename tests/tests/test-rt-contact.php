<?php
/**
 * Created by PhpStorm.
 * User: spock
 * Date: 20/1/15
 * Time: 1:55 PM
 */

class Test_Rt_Contact extends RT_WP_TestCase {
	var $Rt_Contact;
	function setUp() {
		parent::setUp();
		$this->Rt_Contact = new Rt_Contact();
	}

	function test_tax_exist(){
		$this->assertEquals( true, taxonomy_exists( Rt_Contact::$user_category_taxonomy ) );
	}

	function test_tax(){
		$cust = term_exists( Rt_Contact::$customer_category_slug, Rt_Contact::$user_category_taxonomy );
		$this->assertTrue( ! empty( $cust  ), 'term customer not exist in user category' );

		$emp = term_exists( Rt_Contact::$employees_category_slug, Rt_Contact::$user_category_taxonomy );
		$this->assertTrue( ! empty( $emp ), 'term Employee not exist in user category' );

		$ven = term_exists( Rt_Contact::$vendor_category_slug, Rt_Contact::$user_category_taxonomy );
		$this->assertTrue( ! empty( $ven ), 'term vendor not exist in user category' );
	}

	function test_constructor(){
		$this->assertTrue( post_type_exists( rt_biz_get_contact_post_type() ), 'Something is wrong in rt_contact check class post type not exist' );
	}

	function test_add_contact(){
		$this->assertEquals( 0, sizeof( get_posts( array( 'post_type'=> rt_biz_get_contact_post_type() ) ) ) );
		$this->Rt_Contact->add_contact( 'sherlock holmes','','221b_backer@street.com' );
		$posts = get_posts( array( 'post_type' => rt_biz_get_contact_post_type() ) );
		$this->assertEquals( 1, sizeof( $posts ) ) ;
		$this->assertEquals( false, biz_is_primary_email_unique( '221b_backer@street.com' ) );
		$this->assertEquals( true, biz_is_primary_email_unique( 'james_moriart@woman.com' ) );
	}

	function test_get_contact_by_email(){
		$this->Rt_Contact->add_contact( 'sherlock holmes' );
		$this->Rt_Contact->add_contact( 'John Watson' );
		$posts = get_posts( array( 'post_type' => rt_biz_get_contact_post_type() ) );
		$this->assertEquals( 2, sizeof( $posts ) ) ;
		update_post_meta( $posts[1]->ID, Rt_Entity::$meta_key_prefix.$this->Rt_Contact->primary_email_key, '221b_backer@street.com' );
		$contact = rt_biz_get_contact_by_email('221b_backer@street.com');
		$this->assertEquals( 'sherlock holmes', $contact[0]->post_title ) ;
	}

	function test_functions(){
		$this->assertTrue( method_exists( $this->Rt_Contact, 'check_primary_email_for_admin_notice' ), 'method setup_meta_fields does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'manage_contact_column_header' ), 'method print_metabox_js does not exist in rtbiz') ;
		$this->assertTrue( method_exists( $this->Rt_Contact, 'manage_contact_column_body' ), 'method post_table_columns does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'contact_meta_boxes' ), 'method contact_meta_boxes does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'contact_user_p2p' ), 'method contact_user_p2p does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'connect_contact_to_user' ), 'method connect_contact_to_user does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'remove_contact_to_user' ), 'method remove_contact_to_user does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'edit_view_filters' ), 'method edit_view_filters does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'register_tax' ), 'method register_tax does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'add_defualt_categories_on_activate' ), 'method add_defualt_categories_on_activate does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'setup_meta_fields' ), 'method setup_meta_fields does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'print_metabox_js' ), 'method print_metabox_js does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'primary_email_empty' ), 'method primary_email_empty does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'primary_email_not_unique' ), 'method primary_email_not_unique does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'save_meta_values' ), 'method save_meta_values does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'post_table_columns' ), 'method post_table_columns does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'manage_post_table_columns' ), 'method manage_post_table_columns does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'add_contact' ), 'method add_contact does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'get_by_email' ), 'method get_by_email does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'get_contact_for_wp_user' ), 'method get_contact_for_wp_user does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'get_wp_user_for_contact' ), 'method get_wp_user_for_contact does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'get_contact_by_category' ), 'method get_contact_by_category does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'contact_create_for_wp_user' ), 'method contact_create_for_wp_user does not exist in rtbiz' );
		$this->assertTrue( method_exists( $this->Rt_Contact, 'get_user_from_name' ), 'method get_user_from_name does not exist in rtbiz' );
	}


}
