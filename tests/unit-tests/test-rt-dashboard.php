<?php

/**
 * Created by PhpStorm.
 * User: spock
 * Date: 21/1/15
 * Time: 5:43 PM
 */
class Test_Rt_Dashboard extends RT_WP_TestCase {
	var $dashboard;

	function setUp() {
		parent::setUp();
		$this->dashboard = new Rtbiz_Dashboard();
	}

	function test_method_exists() {
		$this->assertTrue( method_exists( $this->dashboard, 'add_dashboard_widgets' ), 'method add_dashboard_widgets does not exist in Rtbiz_Dashboard' );
		$this->assertTrue( method_exists( $this->dashboard, 'dashboard_recent_posts' ), 'method dashboard_recent_posts does not exist in Rtbiz_Dashboard' );
		$this->assertTrue( method_exists( $this->dashboard, 'dashboard_recent_comments_row' ), 'method dashboard_recent_comments_row does not exist in Rtbiz_Dashboard' );
		$this->assertTrue( method_exists( $this->dashboard, 'dashboard_recent_comments' ), 'method dashboard_recent_comments does not exist in Rtbiz_Dashboard' );
		$this->assertTrue( method_exists( $this->dashboard, 'dashboard_site_activity' ), 'method dashboard_site_activity does not exist in Rtbiz_Dashboard' );
		$this->assertTrue( method_exists( $this->dashboard, 'product_wise_contacts' ), 'method product_wise_contacts does not exist in Rtbiz_Dashboard' );
		$this->assertTrue( method_exists( $this->dashboard, 'contact_type_wise_contacts' ), 'method contact_type_wise_contacts does not exist in Rtbiz_Dashboard' );
		$this->assertTrue( method_exists( $this->dashboard, 'department_by_contacts' ), 'method department_by_contacts does not exist in Rtbiz_Dashboard' );
		$this->assertTrue( method_exists( $this->dashboard, 'dashboard_widget_content' ), 'method dashboard_widget_content does not exist in Rtbiz_Dashboard' );

	}
}
