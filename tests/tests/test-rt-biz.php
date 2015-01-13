<?php
/**
 * Created by PhpStorm.
 * User: udit
 * Date: 12/9/14
 * Time: 8:36 PM
 */

class Test_Rt_Biz extends RT_WP_TestCase {
	var $rtbiz;
	function setUp() {
		parent::setUp();
		$this->rtbiz = rtbiz();
	}

	function test_admin_notice(){
		$this->assertTrue( method_exists( $this->rtbiz, 'init_menu_order' ), 'method init_menu_order does not exist in rtbiz');
	}

}
