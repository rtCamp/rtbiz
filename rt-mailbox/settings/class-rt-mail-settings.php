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
 * Description of Rt_Mail_Settings
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Mail_Settings' ) ) {

	/**
	 * Class Rt_Mail_Settings
	 * @since rt-Helpdesk 0.1
	 */
	class Rt_Mail_Settings {

		/**
		 * @var string
		 * sync time
		 * @since rt-Helpdesk 0.1
		 */
		public $sync_period = '00:00:00'; //"h:m:s"

		/**
		 * constructor
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function __construct() {
		}

		/**
		 * update sync meta time
		 *
		 * @param $email
		 * @param $replytime
		 *
		 * @return bool
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function update_sync_meta_time( $email, $replytime ) {
			global $rt_mail_accounts_model;
			$rows_affected = $rt_mail_accounts_model->update_mail_account( array( 'last_sync_time' => $replytime ), array( 'email' => $email ) );

			return ( ! empty( $rows_affected ) );
		}

		/**
		 * change status of sync
		 *
		 * @param $email
		 * @param $isSyncing
		 *
		 * @return bool
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function update_sync_status( $email, $isSyncing ) {
			$status = 'synced';
			if ( $isSyncing ) {
				$status = 'syncing';
			}
			global $rt_mail_accounts_model;
			$rows_affected = $rt_mail_accounts_model->update_mail_account( array( 'sync_status' => $status ), array( 'email' => $email ) );

			return ( ! empty( $rows_affected ) );
		}

		/**
		 * get email account
		 *
		 * @param $email
		 *
		 * @return bool
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function get_email_acc( $email, $module ) {
			global $rt_mail_accounts_model;
			$emails = $rt_mail_accounts_model->get_mail_account( array( 'email' => $email, 'module' => $module ) );
			$email  = false;
			if ( ! empty( $emails ) ) {
				$email = $emails[0];
			}

			return $email;
		}

		/**
		 * get user google account
		 *
		 * @return mixed
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function get_user_google_ac( $module ) {
			global $rt_mail_accounts_model;
			if ( ! empty( $module ) ) {
				return $rt_mail_accounts_model->get_mail_account( $module );
			} else {
				return $rt_mail_accounts_model->get_mail_account();
			}
		}

		/**
		 * get access token from email Gmail
		 *
		 * @param $email
		 * @param $signature
		 * @param $email_type
		 * @param $imap_server
		 *
		 * @return bool
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function get_accesstoken_from_email( $email, &$signature, &$email_type, &$imap_server ) {
			global $rt_mail_accounts_model;
			$ac = $rt_mail_accounts_model->get_mail_account( array( 'email' => $email ) );
			if ( isset( $ac[0] ) ) {
				$ac = $ac[0];
			} else {
				$signature = '';

				// Terminating because no email is found
				return false;
			}
			$signature   = $ac->signature;
			$email_type  = $ac->type;
			$imap_server = $ac->imap_server;
			if ( ! $signature ) {
				$signature = '';
			}
			$redirect_url = get_site_option( 'rthd_googleapi_redirecturl' );
			if ( ! $redirect_url ) {
				$redirect_url = admin_url( 'admin.php?page=rthd-settings&tab=my-settings&type=personal' );
				update_site_option( 'rthd_googleapi_redirecturl', $redirect_url );
			}

			$ac->email_data = unserialize( $ac->email_data );

			$email = filter_var( $ac->email_data['email'], FILTER_SANITIZE_EMAIL );

			$access_token = $ac->outh_token;

			if ( 'goauth' == $ac->type ) {

				$google_client_id           = get_site_option( 'rthd_googleapi_clientid', '' );
				$google_client_secret       = get_site_option( 'rthd_googleapi_clientsecret', '' );
				$google_client_redirect_url = get_site_option( 'rthd_googleapi_redirecturl', '' );

				include_once RT_HD_PATH_VENDOR . 'google-api-php-client/Google_Client.php';
				include_once RT_HD_PATH_VENDOR . 'google-api-php-client/contrib/Google_Oauth2Service.php';

				$client = new Google_Client();
				$client->setApplicationName( 'Helpdesk Studio' );
				$client->setClientId( $google_client_id );
				$client->setClientSecret( $google_client_secret );
				$client->setRedirectUri( $google_client_redirect_url );
				$client->setScopes( array(
					'https://mail.google.com/',
					'https://www.googleapis.com/auth/userinfo.email',
					'https://www.googleapis.com/auth/userinfo.profile',
				) );
				$client->setAccessType( 'offline' );

				$token = json_decode( $ac->outh_token );
				$client->setAccessToken( $ac->outh_token );

				if ( $client->isAccessTokenExpired() ) {
					$client->refreshToken( $token->refresh_token );
					$oauth2 = new Google_Oauth2Service( $client );
					$user   = $oauth2->userinfo_v2_me->get();
					$email  = filter_var( $user['email'], FILTER_SANITIZE_EMAIL );
					if ( isset( $ac->email_data['inbox_folder'] ) ) {
						$user['inbox_folder'] = $ac->email_data['inbox_folder'];
					}
					if ( isset( $ac->email_data['mail_folders'] ) ) {
						$user['mail_folders'] = $ac->email_data['mail_folders'];
					}
					$this->update_user_google_ac( $client->getAccessToken(), $email, serialize( $user ) );
					$ac->email_data = $user;
					$token          = json_decode( $client->getAccessToken() );
				}

				$access_token = $token->access_token;
			}

			return $access_token;
		}

		/**
		 * count gmail account
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function update_gmail_ac_count() {
			global $rt_mail_accounts_model;
			$accounts = $rt_mail_accounts_model->get_all_mail_accounts();
			$this->set_accounts( sizeof( $accounts ) );
		}

		public function set_accounts( $rCount ) {
			global $rt_lib;
			$log_file = realpath( dirname( $rt_lib['rt_lib_path'] ) ) . '/mailaccount.txt';
			if ( $fp = fopen( $log_file, 'w+' ) ) {
				fwrite( $fp, $rCount );
				fclose( $fp );
			}
		}

		/**
		 * add Gmail user account
		 *
		 * @param        $outh_token
		 * @param        $email
		 * @param        $email_data
		 * @param        $user_id
		 * @param string $type
		 * @param null $imap_server
		 *
		 * @return bool
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function add_user_google_ac( $outh_token, $email, $email_data, $user_id = - 1, $type = 'goauth', $imap_server = null, $module = null ) {
			global $rt_mail_accounts_model;
			if ( - 1 == $user_id ) {
				$user_id = get_current_user_id();
			}

			$args = array(
				'user_id'    => $user_id,
				'email'      => $email,
				'outh_token' => $outh_token,
				'email_data' => $email_data,
				'type'       => $type,
				'flag'       => 'Y',
			);

			if ( ! empty( $module ) ) {
				$args['module'] = $module;
			}

			if ( null != $imap_server ) {
				$args['imap_server'] = $imap_server;
			}

			$rows_affected = $rt_mail_accounts_model->add_mail_account( $args );
			$this->update_gmail_ac_count();

			return $rows_affected;
		}

		/**
		 * update user google account
		 *
		 * @param $outh_token
		 * @param $email
		 * @param $email_data
		 *
		 * @return bool
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function update_user_google_ac( $outh_token, $email, $email_data, $user_id = - 1, $type = 'goauth', $imap_server = null, $module = null, $moduleid = '' ) {
			global $rt_mail_accounts_model;
			$args = array(
				'email'      => $email,
				'outh_token' => $outh_token,
				'type'       => $type,
				'flag'       => 'Y',
			);

			if ( ! empty( $email_data ) ) {
				$args['email_data'] = $email_data;
			}

			if ( null != $imap_server ) {
				$args['imap_server'] = $imap_server;
			}

			if ( ! empty( $moduleid ) ) {
				$where = array(
					'id'     => $moduleid,
					'module' => $module,
				);
			} else {
				$where = array(
					'email'  => $email,
					'module' => $module,
				);
			}

			return $rt_mail_accounts_model->update_mail_account( $args, $where );
		}

		/**
		 * remove user google account
		 *
		 * @param $email
		 * @param $user_id
		 *
		 * @return mixed
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function delete_user_google_ac( $email, $module, $mailboxid = '', $user_id = '' ) {
			if ( - 1 == $user_id ) {
				$user_id = get_current_user_id();
			}
			if ( empty( $email ) && empty( $mailboxid ) ) {
				return false;
			}
			$args = array(
				'module' => $module,
			);

			if ( ! empty( $email ) ) {
				$args['email'] = $email;
			}

			if ( ! empty( $mailboxid ) ) {
				$args['id'] = $mailboxid;
			}

			global $rt_mail_accounts_model;
			$result = $rt_mail_accounts_model->remove_mail_account( $args );
			$this->update_gmail_ac_count();

			return $result;
		}


		/**
		 * update mail access
		 *
		 * @param      $email
		 * @param null $token
		 * @param null $email_data
		 * @param null $imap_server
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function update_mail_acl( $module, $email, $token = null, $email_data = null, $imap_server = null, $user_id = null ) {
			global $rt_mail_accounts_model;

			$args = array();
			if ( null != $email_data ) {
				$args['email_data'] = $email_data;
			}
			if ( null != $token ) {
				$args['outh_token'] = $token;
			}
			if ( null != $imap_server ) {
				$args['imap_server'] = $imap_server;
			}

			if ( isset( $user_id ) ) {
				$args['user_id'] = $user_id;
			}

			$rows_affected = $rt_mail_accounts_model->update_mail_account( $args, array( 'email' => $email, 'module' => $module, ) );
		}

		/**
		 * get email access
		 *
		 * @param $email
		 *
		 * @return mixed
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function get_email_acl( $email ) {
			global $rt_hd_mail_acl_model;

			return $rt_hd_mail_acl_model->get_acl( array( 'email' => $email ) );
		}

		/**
		 * get allow email address
		 *
		 * @return mixed
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function get_allow_email_address() {
			$user_id = get_current_user_id();
			global $wpdb, $rt_mail_accounts_model, $rt_hd_mail_acl_model;
			$sql = $wpdb->prepare( "(select * from {$rt_mail_accounts_model->table_name} where user_id=%d)
                                union (select a.* from {$rt_hd_mail_acl_model->table_name} b inner join
                                {$rt_mail_accounts_model->table_name} a on a.email=b.email where b.allow_user=%d)", $user_id, $user_id );

			return $wpdb->get_results( $sql );
		}

		/**
		 * get all email address
		 *
		 * @return mixed
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function get_all_email_address() {
			global $rt_mail_accounts_model;

			return $rt_mail_accounts_model->get_all_mail_accounts();
		}

		/**
		 * get email for sync debug
		 *
		 * @param $email
		 *
		 * @return mixed
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function get_email_for_sync_debug( $email ) {
			global $wpdb, $rt_mail_accounts_model;
			$sql = $wpdb->prepare( "select * from $rt_mail_accounts_model->table_name where email = %s", $email );
			$row = $wpdb->get_row( $sql );

			return $row;
		}

		/**
		 * get email for sync
		 *
		 * @return mixed
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function get_email_for_sync() {
			sleep( 5 );
			global $wpdb, $rt_mail_accounts_model;
			$sql = $wpdb->prepare( "select * from $rt_mail_accounts_model->table_name where sync_status in ( 'syncing' ) and ( last_sync_time is NULL or addtime( last_sync_time, %s ) < NOW() ) order by last_sync_time DESC limit 1", $this->sync_period );

			$row = $wpdb->get_row( $sql );
			if ( ! $row ) {
				$sql = "select * from $rt_mail_accounts_model->table_name where not sync_status in ( 'syncing' ) order by last_sync_time limit 1";
				$row = $wpdb->get_row( $sql );
			} else {
				$this->update_sync_meta_time( $row->email, current_time( 'mysql' ) );
			}

			return $row;
		}

		/**
		 * send email
		 *
		 * @param        $fromemail
		 * @param        $subject
		 * @param        $body
		 * @param array $toemail
		 * @param array $ccemail
		 * @param array $bccemail
		 * @param array $attachement
		 * @param int $refrence_id
		 * @param string $refrence_type
		 *
		 * @return mixed
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function insert_new_send_email( $fromemail, $subject, $body, $toemail = array(), $ccemail = array(), $bccemail = array(), $attachement = array(), $refrence_id = 0, $refrence_type = 'notification' ) {

			$user_id = get_current_user_id();
			global $rt_outbound_model;
			$args = array(
				'user_id'       => $user_id,
				'fromemail'     => $fromemail,
				'toemail'       => serialize( $toemail ),
				'ccemail'       => serialize( $ccemail ),
				'bccemail'      => serialize( $bccemail ),
				'subject'       => $subject,
				'body'          => $body,
				'attachement'   => serialize( $attachement ),
				'refrence_id'   => $refrence_id,
				'refrence_type' => $refrence_type,
			);

			return $rt_outbound_model->add_outbound_mail( $args );
		}

		/**
		 * get send mails
		 *
		 * @return mixed
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function get_new_sent_mail() {
			global $rt_outbound_model;

			return $rt_outbound_model->get_outbound_mail( array( 'sent' => 'no' ) );
		}

		/**
		 * update send box
		 *
		 * @param        $sentEmailID
		 * @param string $status
		 * @param string $oldStatus
		 *
		 * @return mixed
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function update_sent_email( $sentEmailID, $status = 'yes', $oldStatus = 'no' ) {
			global $rt_outbound_model;
			$rows_affected = $rt_outbound_model->update_outbound_mail( array( 'sent' => $status ), array(
				'id'   => $sentEmailID,
				'sent' => $oldStatus,
			) );

			return $rows_affected;
		}

		/**
		 * Get all mailbox
		 *
		 * @return mixed
		 *
		 */
		public function get_all_mailbox() {
			global $rt_mail_accounts_model;

			return $rt_mail_accounts_model->get_all_mail_accounts();
		}
	}
}
