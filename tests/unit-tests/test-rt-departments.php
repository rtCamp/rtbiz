<?php

/**
 * Created by PhpStorm.
 * User: spock
 * Date: 21/1/15
 * Time: 5:50 PM
 */
class Test_RT_Departments extends RT_WP_TestCase {
	var $dept;

	function setUp() {
		parent::setUp();
		$this->dept = new RT_Departments();
	}

	function test_dept_exist() {
		$this->assertEquals( true, taxonomy_exists( RT_Departments::$slug ) );
	}

	function test_method_exist() {
		$this->assertTrue( method_exists( $this->dept, 'get_lables' ), 'method get_lables does not exist in RT_Departments' );
		$this->assertTrue( method_exists( $this->dept, 'row_actions' ), 'method row_actions does not exist in RT_Departments' );
		$this->assertTrue( method_exists( $this->dept, 'save_department' ), 'method save_department does not exist in RT_Departments' );
		$this->assertTrue( method_exists( $this->dept, 'row_actions' ), 'method row_actions does not exist in RT_Departments' );
		$this->assertTrue( method_exists( $this->dept, 'manage_department_column_header' ), 'method manage_department_column_header does not exist in RT_Departments' );
		$this->assertTrue( method_exists( $this->dept, 'manage_department_column_body' ), 'method manage_department_column_body does not exist in RT_Departments' );
		$this->assertTrue( method_exists( $this->dept, 'is_edit_department' ), 'method is_edit_department does not exist in RT_Departments' );
		$this->assertTrue( method_exists( $this->dept, 'js_includes' ), 'method js_includes does not exist in RT_Departments' );
		$this->assertTrue( method_exists( $this->dept, 'css_includes' ), 'method css_includes does not exist in RT_Departments' );
		$this->assertTrue( method_exists( $this->dept, 'colorpicker' ), 'method colorpicker does not exist in RT_Departments' );
		$this->assertTrue( method_exists( $this->dept, 'hide_slug' ), 'method hide_slug does not exist in RT_Departments' );
		$this->assertTrue( method_exists( $this->dept, 'add_remove_department_field' ), 'method add_remove_department_field does not exist in RT_Departments' );
		$this->assertTrue( method_exists( $this->dept, 'get_department_meta' ), 'method get_department_meta does not exist in RT_Departments' );
		$this->assertTrue( method_exists( $this->dept, 'department_add_custom_field' ), 'method department_add_custom_field does not exist in RT_Departments' );
		$this->assertTrue( method_exists( $this->dept, 'add_manage_acl_button' ), 'method add_manage_acl_button does not exist in RT_Departments' );

	}

}
