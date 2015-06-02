<?php
/**
 * Created by PhpStorm.
 * User: spock
 * Date: 20/1/15
 * Time: 4:00 PM
 */

class Test_Rt_Biz_Setting extends RT_WP_TestCase  {
	var $setting;
	function setUp() {
		parent::setUp();
		$this->setting = rtbiz_get_redux_settings();
	}
	function test_setting_name(){
	}

}
