<?php
/**
 * Created by PhpStorm.
 * User: spock
 * Date: 10/9/14
 * Time: 3:06 PM
 */

class Test_RT_User_Groups extends RT_WP_TestCase{
    var $RT_User_Groups;
    function setUp() {
        parent::setUp();
        //        remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
        //        remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
        $terms_cap = array(
            'manage_terms' => 'manage_custom_terms',
            'edit_terms' => 'edit_custom_terms',
            'delete_terms' => 'delete_custom_terms',
            'assign_terms' => 'assign_custom_terms',
        );

        $this->RT_User_Groups =  new RT_User_Groups('user-group', array(
                'name' => __( 'Departments' ),
                'singular_name' => __( 'Departmet' ),
                'menu_name' => __( 'Departments' ),
                'search_items' => __( 'Search Departments' ),
                'popular_items' => __( 'Popular Departments' ),
                'all_items' => __( 'All User Departments' ),
                'edit_item' => __( 'Edit Department' ),
                'update_item' => __( 'Update Department' ),
                'add_new_item' => __( 'Add New Department' ),
                'new_item_name' => __( 'New Department Name' ),
                'separate_items_with_commas' => __( 'Separate departments with commas' ),
                'add_or_remove_items' => __( 'Add or remove departments' ),
                'choose_from_most_used' => __( 'Choose from the most popular departments' ),
            ), $terms_cap
        );
    }

    function test_construct() {
        $this->assertEquals( 'user-group', $this->RT_User_Groups->user_group_slug, 'Slug value is not set or invalid' );

        $this->assertEquals( array(
            'name' => __( 'Departments' ),
            'singular_name' => __( 'Departmet' ),
            'menu_name' => __( 'Departments' ),
            'search_items' => __( 'Search Departments' ),
            'popular_items' => __( 'Popular Departments' ),
            'all_items' => __( 'All User Departments' ),
            'edit_item' => __( 'Edit Department' ),
            'update_item' => __( 'Update Department' ),
            'add_new_item' => __( 'Add New Department' ),
            'new_item_name' => __( 'New Department Name' ),
            'separate_items_with_commas' => __( 'Separate departments with commas' ),
            'add_or_remove_items' => __( 'Add or remove departments' ),
            'choose_from_most_used' => __( 'Choose from the most popular departments' ),
        ), $this->RT_User_Groups->labels, 'label is invalid' );

        $this->assertEquals( array(
            'manage_terms' => 'manage_custom_terms',
            'edit_terms' => 'edit_custom_terms',
            'delete_terms' => 'delete_custom_terms',
            'assign_terms' => 'assign_custom_terms',
        ), $this->RT_User_Groups->caps, 'caps value is not set or invalid' );
    }

    function test_db_upgrade() {
        //$this -> RT_User_Groups -> db_upgrade();

    }

    function test_register_user_group(){
        $this->RT_User_Groups->register_user_group();
        $this->assertTrue( taxonomy_exists( $this->RT_User_Groups->user_group_slug ) );
    }

    function test_user_group(){
       $termsid= wp_insert_term(
            'Apple group', // the term
            'user-group', // the taxonomy
            array(
                'description'=> 'A yummy apple.',
                'slug' => 'apple',
            ) );
        $this -> RT_User_Groups -> set_user_group( 1,'apple' );

        $this -> assertEquals('1',$this->RT_User_Groups->get_user_by_group_slug('apple')[0]);

        $this ->assertTrue($this -> RT_User_Groups -> is_user_has_group( 1, $termsid['term_taxonomy_id'] ));
        $this-> assertEquals('apple', $this -> RT_User_Groups -> get_user_groups(1)[0] -> slug );

        $this -> RT_User_Groups -> remove_user_group(1,'apple');
        $this ->assertFalse($this -> RT_User_Groups -> is_user_has_group( 1, $termsid['term_taxonomy_id'] ));
    }

    function test_remove_user_groups(){
        $termsid1 = wp_insert_term(
            'Orange group', // the term
            'user-group', // the taxonomy
            array(
                'description'=> 'A yummy Orange.',
                'slug' => 'Orange',
            ) );
        $this -> RT_User_Groups -> set_user_group( 1,'Orange' );

        $termsid2 = wp_insert_term(
            'Banana group', // the term
            'user-group', // the taxonomy
            array(
                'description'=> 'A yummy Banana.',
                'slug' => 'Banana',
            ) );
        $this -> RT_User_Groups -> set_user_group( 1,'Banana' );

        $this ->assertTrue($this -> RT_User_Groups -> is_user_has_group( 1, $termsid1['term_taxonomy_id'] ));
        $this ->assertTrue($this -> RT_User_Groups -> is_user_has_group( 1, $termsid2['term_taxonomy_id'] ));

        $this -> RT_User_Groups -> remove_all_user_groups( 1 );

        $this ->assertFalse($this -> RT_User_Groups -> is_user_has_group( 1, $termsid1['term_taxonomy_id'] ));
        $this ->assertFalse($this -> RT_User_Groups -> is_user_has_group( 1, $termsid2['term_taxonomy_id'] ));
    }
} 