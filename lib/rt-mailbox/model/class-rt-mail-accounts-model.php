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
 * Description of RtMailAccountsModel
 * email account model for wp_hd_mail_accounts table
 *
 * @author udit
 *
 * @since  rt-Helpdesk 0.1
 */
if ( ! class_exists( 'Rt_Mail_Accounts_Model' ) ) {
	/**
	 * Class Rt_Mail_Accounts_Model
	 */
	class Rt_Mail_Accounts_Model extends RT_DB_Model {
		public function __construct() {
			parent::__construct( 'wp_mail_accounts' );
		}

		/**
		 * add mail account
		 *
		 * @param $data
		 *
		 * @return int
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function add_mail_account( $data ) {
			return parent::insert( $data );
		}

		/**
		 * get mail account
		 *
		 * @param $where
		 *
		 * @return array
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function get_mail_account( $where ) {
			return parent::get( $where );
		}

		/**
		 * update mail account
		 *
		 * @param $data
		 * @param $where
		 *
		 * @return mixed
		 *
		 * @since rt-Helpdesk 0.1
		 *
		 */
		function update_mail_account( $data, $where ) {
			return parent::update( $data, $where );
		}

		/**
		 * remove mail account
		 *
		 * @param $where
		 *
		 * @return int
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function remove_mail_account( $where ) {
			return parent::delete( $where );
		}

		/**
		 * get all mail account
		 *
		 * @return array
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function get_all_mail_accounts() {
			return parent::get( array() );
		}

		function get_unique_modules() {
			global $wpdb;
			$query       = 'SELECT DISTINCT (module) from ' . $this->table_name;
			$all_modules = $wpdb->get_results( $query );
			$modules     = array();
			foreach ( $all_modules as $module ) {
				$modules[] = $module;
			}

			return array_unique( wp_list_pluck( $modules, 'module' ) );
		}
	}
}
