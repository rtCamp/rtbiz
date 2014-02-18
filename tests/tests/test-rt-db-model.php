<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of test_RTDBModel
 *
 * @author faishal
 */
class test_RTDBModel extends RT_WP_TestCase
{
	//put your code here
	var $rtdbmodel;

	/**
	 * Setup Class Object and Parent Test Suite
	 *
	 * @depends test_RTDBUpdate::test_do_upgrade
	 */
	function setUp()
	{
		parent::setUp();
		$this->rtdbupdate = new RT_DB_Update( false, realpath( dirname( __FILE__ ) . '/../schema/' ) );
		$this->rtdbupdate->do_upgrade();
		$this->rtdbmodel = new RT_DB_Model( 'test_table' );
	}

	/**
	 * Check table name with Default withprefix Paramater which is false And set MultiSite Wordpress Signle Table true
	 *
	 * @global type $wpdb Global WordPress DB object to get prefix
	 */
	function test_set_table_name_witout_prefix_param_mu_single()
	{
		global $wpdb;
		$this->rtdbmodel->mu_single_table = true;
		$this->rtdbmodel->set_table_name( 'test_table' );
		$this->assertEquals( $wpdb->base_prefix . 'rt_' . 'test_table', $this->rtdbmodel->table_name );
	}

	/**
	 * Check table name with Default withprefix Paramater which is false And set MultiSite Wordpress Signle Table false
	 *
	 * @global type $wpdb Global WordPress DB object to get prefix
	 */
	function test_set_table_name_witout_prefix_param_mu_single_false()
	{
		global $wpdb;
		$this->rtdbmodel->mu_single_table = false;
		$this->rtdbmodel->set_table_name( 'test_table' );
		$this->assertEquals( $wpdb->prefix . 'rt_' . 'test_table', $this->rtdbmodel->table_name );
	}

	/**
	 * Check table name by set withPrefix Parameter to false And also set MultiSite Wordpress Single Table true
	 */
	function test_set_table_name_with_prefix_param_mu_single()
	{
		$this->rtdbmodel->mu_single_table = true;
		$this->rtdbmodel->set_table_name( 'test_table', true );
		$this->assertEquals( 'test_table', $this->rtdbmodel->table_name );
	}

	/**
	 * Check table name by set withPrefix Parameter to false And also set MultiSite Wordpress Single Table false
	 */
	function test_set_table_name_with_prefix_param_mu_single_false()
	{
		$this->rtdbmodel->mu_single_table = false;
		$this->rtdbmodel->set_table_name( 'test_table', true );
		$this->assertEquals( 'test_table', $this->rtdbmodel->table_name );
	}

	function test_get()
	{
		$this->rtdbmodel->set_table_name( 'test_table' );
		$this->rtdbmodel->insert( array( 'media_id' => 1, 'meta_key' => 'test_key', 'meta_value' => 'test_value' ) );

		$result = $this->rtdbmodel->get( array( 'meta_key' => 'test_key' ) );
		$this->assertGreaterThan( 0, count( $result ) );

		$result = $this->rtdbmodel->get( array( 'meta_key' => 'test_key' ), 'asdf' );
		$this->assertGreaterThan( 0, count( $result ) );

		$result = $this->rtdbmodel->get( array( 'meta_key' => 'test_key' ), '1' );
		$this->assertGreaterThan( 0, count( $result ) );

		$result = $this->rtdbmodel->get( array( 'meta_key' => 'test_key' ), '-9999' );
		$this->assertGreaterThan( 0, count( $result ) );

		$result = $this->rtdbmodel->get( array( 'meta_key' => 'test_key' ), - 9999 );
		$this->assertGreaterThan( 0, count( $result ) );

		$this->inset_media( 20 ); //Inset bulk media

		$result = $this->rtdbmodel->get( array( 'meta_key' => 'test_key' ), 1 );
		$this->assertGreaterThan( 0, count( $result ) );

		$result = $this->rtdbmodel->get( array( 'meta_key' => 'test_key' ), 1, 1 );
		$this->assertLessThanOrEqual( 1, count( $result ) );

		$result = $this->rtdbmodel->get( array( 'meta_key' => 'test_key' ), 1, - 121 );
		$this->assertLessThanOrEqual( 10, count( $result ) );

		$result = $this->rtdbmodel->get( array( 'meta_key' => 'test_key' ), 1, 'asdfasdf' );
		$this->assertLessThanOrEqual( 10, count( $result ) );

		$result = $this->rtdbmodel->get( array( 'meta_key' => 'test_key' ), 1, '10' );
		$this->assertLessThanOrEqual( 10, count( $result ) );

		$result = $this->rtdbmodel->get( array( 'meta_key' => 'test_key' ) );
		$this->assertGreaterThan( 0, count( $result ) );
	}

	function test_delete()
	{
		$this->rtdbmodel->set_table_name( 'test_table' );
		$this->inset_media( 20 );
		$result = $this->rtdbmodel->delete( array( 'meta_key' => 'test_key' ) );
		$this->assertEquals( 1, count( $result ) );

		$result = $this->rtdbmodel->delete( array( 'meta_key' => 'test_key' ) );
		$this->assertEquals( 1, count( $result ) );
	}

	function test_update()
	{
		$this->inset_media( 1 );

		$result = $this->rtdbmodel->update( array( 'meta_key' => 'test_keys' ), array( 'meta_key' => 'test_key' ) );

		$result = $this->rtdbmodel->get( array( 'meta_key' => 'test_key' ) );
		$this->assertEquals( 0, count( $result ) );

		$result = $this->rtdbmodel->get( array( 'meta_key' => 'test_keys' ) );
		$this->assertGreaterThan( 0, count( $result ) );
		$this->assertEquals( 'test_keys', $result[ 0 ]->meta_key );
	}

	function inset_media( $count = 1 )
	{
		while ( $count > 0 ) {
			$this->rtdbmodel->insert( array( 'media_id' => $count, 'meta_key' => 'test_key', 'meta_value' => 'test_value' ) );
			$count --;
		}
	}

	function test_insert_with_right_input()
	{
		$this->rtdbmodel->set_table_name( 'test_table' );
		$this->assertGreaterThan( 0, $this->rtdbmodel->insert( array( 'media_id' => 1, 'meta_key' => 'test_key', 'meta_value' => 'test_value' ) ) );
	}


	function test_get_by_column_name()
	{
		$this->rtdbmodel->delete( array( 'meta_key' => 'test_key' ) );
		$this->rtdbmodel->delete( array( 'meta_key' => 'test_keys' ) );

		$this->inset_media();

		$result = $this->rtdbmodel->get_by_meta_key( 'test_key' );
		$this->assertEquals( 1, count( $result[ 'result' ] ) );

		$this->assertEquals( 1, $result[ 'result' ][ 0 ][ 'media_id' ] );
	}
}