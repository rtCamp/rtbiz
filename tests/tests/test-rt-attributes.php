<?php

/**
 * Created by PhpStorm.
 * User: Utkarsh
 * Date: 11/9/14
 * Time: 11:32 AM
 */
class test_rt_attributes extends RT_WP_TestCase {
	/**
	 * @var RT_Attributes reference
	 */
	var $attributes, $atid;

	/**
	 * initialize reference with the obj of RT_Attributes
	 */
	function setUp() {
		parent::setUp();
		$this->attributes = new RT_Attributes( 'rt-biz' );
	}

	/**
	 * check function exists
	 */
	function test_function_existence() {
		$this->assertTrue(
			method_exists( $this->attributes, 'add_attributes_page' ),
			'Class does not have method add_attributes_page' );
	}

	/**
	 * Testing attribute(add update edit ), attribute relations(delete update edit)
	 *
	 */
	function  test_attributes_page() {
		$terms_cap = array(
			'manage_terms' => 'manage_custom_terms',
			'edit_terms'   => 'edit_custom_terms',
			'delete_terms' => 'delete_custom_terms',
			'assign_terms' => 'assign_custom_terms',
		);
		$this->attributes->add_attributes_page( $page_slug = 'my_plugin_attribute_page_slug', $parent_page_slug = 'edit.php?post_type=post', $post_type = 'post', $page_cap = 'manage_attributes', $terms_cap, $render_type = true, $storage_type = true, $orderby = true );
		$attid = $this->attributes->add_attribute( 'my_attribute', 'my_attribute_name' );

		$this->assertEquals( 'my_attribute', $this->attributes->attributes_db_model->get_attribute( $attid )->attribute_label );

		$this->attributes->edit_attribute( $attid, 'my_new_attribute', 'my_new_attribute_name' );
		$this->assertEquals( 'my_new_attribute', $this->attributes->attributes_db_model->get_attribute( $attid )->attribute_label );

		$this->attributes->add_attribute_relations( $attid, array( 'post' ) );

		$val = $this->attributes->attributes_relationship_model->get_relations_by_post_type( 'post' );
		$this->assertEquals( $attid, $val[0]->attr_id );

		$this->attributes->add_attributes_page( $page_slug = 'my_plugin_attribute_page_slug_1', $parent_page_slug = 'edit.php?post_type=post1', $post_type = 'post1', $page_cap = 'manage_attributes1', $terms_cap, $render_type = true, $storage_type = true, $orderby = true );
		$this->attributes->update_attribute_relations( $attid, array( 'post1' ) );
		$val = $this->attributes->attributes_relationship_model->get_relations_by_post_type( 'post1' );
		$this->assertEquals( $attid, $val[0]->attr_id );

		$this->attributes->delete_attribute_relations( $attid );
		$val = $this->attributes->attributes_relationship_model->get_relations_by_post_type( 'post1' );
		$this->assertEquals( 0, sizeof( $val ) );
	}

}
