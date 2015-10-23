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
 * Description of RtIMAPServerModel
 * IMAP server model for database table
 *
 * @author udit
 *
 * @since  rt-Helpdesk 0.1
 */
if ( ! class_exists( 'Rt_IMAP_Server_Model' ) ) {
	/**
	 * Class Rt_IMAP_Server_Model
	 */
	class Rt_IMAP_Server_Model extends RT_DB_Model {
		public function __construct() {
			parent::__construct( 'wp_imap_server' );
		}

		/**
		 * get all servers
		 *
		 * @return array
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function get_all_servers() {
			return parent::get( array(), false, false, 'ID asc' );
		}

		/**
		 * delete server from id
		 *
		 * @param $id
		 *
		 * @return int
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function delete_server( $id ) {
			return parent::delete( array( 'id' => $id ) );
		}

		/**
		 * add server
		 *
		 * @param $data
		 *
		 * @return int
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function add_server( $data ) {
			return parent::insert( $data );
		}

		/**
		 * update server
		 *
		 * @param $data
		 * @param $id
		 *
		 * @return mixed
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function update_server( $data, $where ) {
			return parent::update( $data, $where );
		}

		/**
		 * get server
		 *
		 * @param $id
		 *
		 * @return bool
		 *
		 * @since rt-Helpdesk 0.1
		 *
		 */
		function get_server_by_id( $id ) {
			$servers = $this->get_servers( array( 'id' => $id ) );
			$server  = false;
			if ( ! empty( $servers ) ) {
				$server = $servers[0];
			}

			return $server;
		}

		function get_servers( $data ) {
			$servers = parent::get( $data );

			return $servers;
		}
	}
}
