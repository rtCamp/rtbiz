<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rt_Biz_Mailbox_Migration' ) ) {

	/*
	 * Migration to store User permission in custom table
	 */
	class Rt_Biz_Mailbox_Migration {

		/*
		 * construct
		 */
		public function __construct() {
			if ( strcmp( RT_BIZ_VERSION , '1.2.16' ) >= 0 ) {

				add_action( 'admin_init', array( $this, 'migrate' ), 20 );
			}
		}

		public function migrate(){
			global $rt_imap_server_model;
			$isMigrated = get_option( 'rtbiz_mailbox_migration_1.2.16' );
			if ( empty( $isMigrated ) ||  'false' === $isMigrated ) {

				$data = array(
					'server_name'          => 'Gmail/Google',
				);
				$where = array(
					'server_name'          => 'Google',
					'incoming_imap_server' => 'imap.gmail.com',
				);
				$data = array(
					'server_name'          => 'Outlook/Hotmail',
				);
				$where = array(
					//'server_name'          => 'Outlook',
					'incoming_imap_server' => 'imap-mail.outlook.com',
				);
				$rt_imap_server_model->update_server( $data, $where );
				update_option( 'rtbiz_mailbox_migration_1.2.16', 'true' );
			}
		}

	}
}
