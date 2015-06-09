<?php
/**
 * Created by PhpStorm.
 * User: spock
 * Date: 21/1/15
 * Time: 5:36 PM
 */

class Test_Rt_Access_Control extends RT_WP_TestCase  {

	var $access_control;
	function setUp() {
		parent::setUp();
		$this->access_control = new Rtbiz_Access_Control();
	}

	function test_functions(){
		$this->assertTrue( method_exists( $this->access_control, 'filter_caps' ), 'method filter_caps does not exist in Rt_Access_Control' );
		$this->assertTrue( method_exists( $this->access_control, 'init_acl' ), 'method init_acl does not exist in Rt_Access_Control' );
		$this->assertTrue( method_exists( $this->access_control, 'get_capability_from_access_role' ), 'method get_capability_from_access_role does not exist in Rt_Access_Control' );
		$this->assertTrue( method_exists( $this->access_control, 'get_role_key' ), 'method get_role_key does not exist in Rt_Access_Control' );
		$this->assertTrue( method_exists( $this->access_control, 'get_no_access_post_caps' ), 'method get_no_access_post_caps does not exist in Rt_Access_Control' );
		$this->assertTrue( method_exists( $this->access_control, 'get_author_post_caps' ), 'method get_author_post_caps does not exist in Rt_Access_Control' );
		$this->assertTrue( method_exists( $this->access_control, 'get_editor_post_caps' ), 'method get_editor_post_caps does not exist in Rt_Access_Control' );
		$this->assertTrue( method_exists( $this->access_control, 'get_admin_post_caps' ), 'method get_admin_post_caps does not exist in Rt_Access_Control' );
		$this->assertTrue( method_exists( $this->access_control, 'get_module_users' ), 'method get_module_users does not exist in Rt_Access_Control' );
		$this->assertTrue( method_exists( $this->access_control, 'acl_settings_ui' ), 'method acl_settings_ui does not exist in Rt_Access_Control' );
		$this->assertTrue( method_exists( $this->access_control, 'profile_level_permission' ), 'method profile_level_permission does not exist in Rt_Access_Control' );
		$this->assertTrue( method_exists( $this->access_control, 'save_profile_level_permission' ), 'method save_profile_level_permission does not exist in Rt_Access_Control' );
		$this->assertTrue( method_exists( $this->access_control, 'add_department_support' ), 'method add_department_support does not exist in Rt_Access_Control' );

	}
}
