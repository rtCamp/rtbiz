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

	function test_class_exist(){
		$this->assertTrue( class_exists( 'Rt_Access_Control' ), 'Class Rt_Access_Control does not exist' );
		$this->assertTrue( class_exists( 'Rt_Biz_Attributes' ), 'Class Rt_Biz_Attributes does not exist' );
		$this->assertTrue( class_exists( 'Rt_Biz_Dashboard' ), 'Class Rt_Biz_Dashboard does not exist' );
		$this->assertTrue( class_exists( 'Rt_Biz_Help' ), 'Class Rt_Biz_Help does not exist' );
		$this->assertTrue( class_exists( 'Rt_Biz_Plugin_Upgrader_Skin' ), 'Class Rt_Biz_Plugin_Upgrader_Skin does not exist' );
		$this->assertTrue( class_exists( 'Rt_Entity' ), 'Class Rt_Entity does not exist' );
		$this->assertTrue( class_exists( 'Rt_Biz_Text_Diff' ), 'Class Rt_Biz_Text_Diff does not exist' );
		$this->assertTrue( class_exists( 'Rt_Company' ), 'Class Rt_Company does not exist' );
		$this->assertTrue( class_exists( 'Rt_Contact' ), 'Class Rt_Contact does not exist' );
		$this->assertTrue( class_exists( 'Rt_Biz_Setting' ), 'Class Rt_Biz_Setting does not exist' );
	}

	function test_rtbiz_functions(){
		$this->assertTrue( method_exists( $this->rtbiz, 'init_menu_order' ), 'method init_menu_order does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'includes' ), 'method includes does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'load_textdomain' ), 'method load_textdomain does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'welcome' ), 'method welcome does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'plugin_activation_redirect' ), 'method plugin_activation_redirect does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'init_rt_mailbox' ), 'method init_rt_mailbox does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'init_attributes' ), 'method init_attributes does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'custom_pages_order' ), 'method custom_pages_order does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'init_dashboard' ), 'method init_dashboard does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'init_department' ), 'method init_department does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'init_wc_product_taxonomy' ), 'method init_wc_product_taxonomy does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'init_tour' ), 'method init_tour does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'init_help' ), 'method init_help does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'init_importer' ), 'method init_importer does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'hooks' ), 'method hooks does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'rtbiz_quide_tour' ), 'method rtbiz_quide_tour does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'rtbiz_admin_notices' ), 'method rtbiz_admin_notices does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'plugin_action_links' ), 'method plugin_action_links does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'plugin_row_meta' ), 'method plugin_row_meta does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'load_styles_scripts' ), 'method load_styles_scripts does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'register_menu' ), 'method register_menu does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'dashboard_ui' ), 'method dashboard_ui does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'check_p2p_dependency' ), 'method check_p2p_dependency does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'plugins_dependency_enque_js' ), 'method plugins_dependency_enque_js does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'admin_notice_rtbiz_plugin_not_installed' ), 'method admin_notice_rtbiz_plugin_not_installed does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'get_path_for_rtbiz_plugin' ), 'method get_path_for_rtbiz_plugin does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'is_rtbiz_plugin_active' ), 'method is_rtbiz_plugin_active does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'is_rtbiz_plugin_installed' ), 'method is_rtbiz_plugin_installed does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'rtbiz_install_plugin_ajax' ), 'method rtbiz_install_plugin_ajax does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'rtbiz_install_plugin' ), 'method rtbiz_install_plugin does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'rtbiz_activate_plugin_ajax' ), 'method rtbiz_activate_plugin_ajax does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'rtbiz_activate_plugin' ), 'method rtbiz_activate_plugin does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'init_modules' ), 'method init_modules does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'init_access_control' ), 'method init_access_control does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'init_settings' ), 'method init_settings does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'register_rt_biz_module' ), 'method register_rt_biz_module does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'register_company_contact_connection' ), 'method register_company_contact_connection does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'company_contact_connection' ), 'method company_contact_connection does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'connect_company_to_contact' ), 'method connect_company_to_contact does not exist in rtbiz');
		$this->assertTrue( method_exists( $this->rtbiz, 'get_company_to_contact_connection' ), 'method get_company_to_contact_connection does not exist in rtbiz');
	}

}
