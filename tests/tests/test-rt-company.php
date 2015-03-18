<?php


class Test_Rt_company extends RT_WP_TestCase {

	var $Rt_Company;
	function setUp() {
		parent::setUp();
		$this->Rt_Company = new Rt_Company();
	}

	function test_constructor(){
		$this->assertTrue( post_type_exists( rt_biz_get_company_post_type() ), 'Something is wrong in rt_account check class post type not exist' );
	}

	function test_functions(){
		$this->assertTrue( method_exists( $this->Rt_Company, 'setup_meta_fields' ), 'method setup_meta_fields does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->Rt_Company, 'print_metabox_js' ), 'method print_metabox_js does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->Rt_Company, 'post_table_columns' ), 'method post_table_columns does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->Rt_Company, 'save_meta_values' ), 'method save_meta_values does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->Rt_Company, 'add_company' ), 'method add_company does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->Rt_Company, 'get_company' ), 'method get_company does not exist in rtbiz');
	}

	function test_add_comapany(){
		$this->assertEquals( 0, sizeof( $this->Rt_Company->get_company() ) );
		$this->Rt_Company->add_company('Spy');
		$this->assertEquals( 1, sizeof( $this->Rt_Company->get_company() ) );
	}

	function test_primary_email(){
		$this->Rt_Company->add_company('Spy');
		$posts = $this->Rt_Company->get_company();
		update_post_meta( $posts[0]->ID, Rt_Entity::$meta_key_prefix.Rt_Company::$primary_email, '221b_backer@street.com' );
		$this->assertEquals( false, rt_biz_is_primary_email_unique_company( '221b_backer@street.com' ) );
		$this->assertEquals( true, rt_biz_is_primary_email_unique_company( 'james_moriart@woman.com' ) );
	}
}





