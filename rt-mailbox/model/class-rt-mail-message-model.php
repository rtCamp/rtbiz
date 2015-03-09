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
 * Description of RtMailMessageModel
 * model for wp_hd_mail_messageids table in database
 *
 * @author udit
 *
 * @since  rt-Helpdesk 0.1
 */
if ( ! class_exists( 'Rt_Mail_Message_Model' ) ) {
	/**
	 * Class Rt_Mail_Message_Model
	 */
	class Rt_Mail_Message_Model extends RT_DB_Model {
		public function __construct() {
			parent::__construct( 'wp_mail_messageids' );
		}

		/**
		 * get mail message
		 *
		 * @param $where
		 *
		 * @return array
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function get_message( $where ) {
			return parent::get( $where );
		}

		/**
		 * add mail message
		 *
		 * @param $data
		 *
		 * @return int
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function add_message( $data ) {
			return parent::insert( $data );
		}
	}
}
