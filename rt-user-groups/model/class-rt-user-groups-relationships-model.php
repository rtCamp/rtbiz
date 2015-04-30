<?php

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-rt-user-groups-relationships-model
 *
 * @author udit
 */
if ( ! class_exists( 'RT_User_Groups_Relationships_Model' ) ) {

	class RT_User_Groups_Relationships_Model extends RT_DB_Model {

		public function __construct() {
			parent::__construct( 'user_groups_relationships' );
		}

		function get( $columns, $offset = false, $per_page = false, $order_by = 'user_id desc' ) {
			return parent::get( $columns, $offset, $per_page, $order_by );
		}

		function is_user_has_group( $user_id ) {
			$columns         = array( 'user_id' => $user_id, );
			$all_users_group = $this->get( $columns );

			if ( count( $all_users_group ) > 0 ) {
				return true;
			} else {
				return false;
			}
		}

	}

}
