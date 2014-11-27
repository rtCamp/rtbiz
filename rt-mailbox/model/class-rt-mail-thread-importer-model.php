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
 * Description of RtMailThreadImporterModel
 * model for 'wp_hd_mail_thread_importer' table in database
 *
 * @author udit
 *
 * @since  rt-Helpdesk 0.1
 */
if ( ! class_exists( 'Rt_Mail_Thread_Importer_Model' ) ) {
	/**
	 * Class Rt_Mail_Thread_Importer_Model
	 */
	class Rt_Mail_Thread_Importer_Model extends RT_DB_Model {
		public function __construct() {
			parent::__construct( 'rt_wp_mail_thread_importer' );
		}

		/**
		 * add mail thread
		 *
		 * @param $data
		 *
		 * @return int
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function add_thread( $data ) {
			return parent::insert( $data );
		}

		/**
		 * get mail thread
		 *
		 * @param $where
		 *
		 * @return array
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function get_thread( $where ) {
			return parent::get( $where );
		}

		/**
		 * update mail thread
		 *
		 * @param $data
		 * @param $where
		 *
		 * @return mixed
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function update_thread( $data, $where ) {
			return parent::update( $data, $where );
		}
	}
}
