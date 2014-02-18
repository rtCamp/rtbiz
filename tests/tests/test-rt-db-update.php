<?php

class test_RTDBUpdate extends RT_WP_TestCase
{
	public $rtdbupdate;

	function setUp()
	{
		parent::setUp();
		$this->rtdbupdate = new RT_DB_Update( false, realpath( dirname( __FILE__ ) . '/../schema/' ) );
	}

	function test_construct()
	{
		$this->assertEquals( 'RT_WP_HELPERS_DB_VERSIONS', $this->rtdbupdate->db_version_option_name, 'DB options value is invalid' );
		$this->assertEquals( '0.0', $this->rtdbupdate->install_db_version, 'DB Version is invalid' );

	}

	function test_genrate_table_name()
	{

	}

	function test_do_upgrade()
	{
		$this->assertTrue( $this->rtdbupdate->check_upgrade() );
		$this->rtdbupdate->do_upgrade();
	}


}