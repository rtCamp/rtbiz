<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Created by PhpStorm.
 * User: udit
 * Date: 11/4/14
 * Time: 8:36 PM
 */

if ( ! class_exists( 'Rt_Mail_Cron' ) ) {

	class Rt_Mail_Cron {

		function __construct( $plugin_path_for_deactivate_cron ) {

			add_filter( 'cron_schedules', array( $this, 'register_custom_schedule' ) );

			add_action( 'init', array( $this, 'setup_schedule' ) );
			register_deactivation_hook( $plugin_path_for_deactivate_cron, array( $this, 'disable_cron_on_deactivation' ) );

			add_action( 'rt_send_email_cron', array( $this, 'rt_send_email' ) );
		}
		function deregister_cron_for_module( $module ) {
			global $rt_mail_accounts_model ;
			$modules = $rt_mail_accounts_model->get_unique_modules();
			if ( ! in_array( $module, $modules ) ){
				wp_clear_scheduled_hook( 'rt_parse_email_cron_'.$module, array( $module ) );
				remove_action( 'rt_parse_email_cron_'.$module,  array( $this, 'rt_parse_email' ), 10 );
			}
		}

		function register_custom_schedule( $schedules ) {

			// add schedule to the existing set
			$schedules['every_minute'] = array(
				'interval' => 60,
				'display' => __( 'Every Minute' ),
			);
			$schedules['every_5_minutes'] = array(
				'interval' => 300,
				'display' => __( 'Every 5 Minutes' ),
			);
			return $schedules;
		}

		/**
		 * disable the cron
		 */
		function disable_cron_on_deactivation() {
			wp_clear_scheduled_hook( 'rt_send_email_cron' );
			global $rt_mail_accounts_model;
			$modules = $rt_mail_accounts_model->get_unique_modules();
			foreach ( $modules as $module ) {
				wp_clear_scheduled_hook( 'rt_parse_email_cron_' . $module, array( $module ) );
			}
		}

		function setup_schedule() {
			//  Migration remove old cron, changed one cron for one module which can have multiple mailbox setup
			wp_clear_scheduled_hook( 'rt_parse_email_cron' );

			global $rt_mail_accounts_model ;
			$modules = $rt_mail_accounts_model->get_unique_modules();
			foreach ( $modules as $module ) {
				add_action( 'rt_parse_email_cron_'.$module, array( $this, 'rt_parse_email' ), 10, 1 );
			}

			// end of migration
			global $rt_mail_accounts_model ;
			$modules = $rt_mail_accounts_model->get_unique_modules();
			foreach ( $modules as $module ){
				if ( ! wp_next_scheduled( 'rt_parse_email_cron_'.$module, array( $module ) ) ) {
					wp_schedule_event( time(), 'every_5_minutes', 'rt_parse_email_cron_'.$module, array( $module ) );
				}
			}
			if ( ! wp_next_scheduled( 'rt_send_email_cron' ) ) {
				wp_schedule_event( time(), 'every_minute', 'rt_send_email_cron' );
			}
		}

		/**
		 * Parse email
		 *
		 * @param $module
		 */
		function rt_parse_email( $module ) {

			global $rt_mail_settings, $rt_mail_accounts_model ;

			//			$emailRow = $rt_mail_settings->get_email_for_sync();
			$emails = $rt_mail_accounts_model->get_mail_account( array( 'module' => $module ) );
			foreach ( $emails as $emailRow ) {
				if ( ! $emailRow ) {
					continue;
				}
				$email = $emailRow->email;
				error_log( "\r\n" . sanitize_email( $email ) . " Selected. \r\n" );

				$rt_mail_settings->update_sync_status( $email, true );
				$last_sync_time = $emailRow->last_mail_time;

				if ( ! $last_sync_time ) {
					$dt = new DateTime( 'now' );
					$dt->sub( new DateInterval( 'P1D' ) );
					$last_sync_time = $dt->format( 'd-M-Y' );
				} else {
					$dt = new DateTime( $last_sync_time );
					$dt->sub( new DateInterval( 'P1D' ) );
					$last_sync_time = $dt->format( 'd-M-Y' );
				}
				global $rt_mail_uid;
				if ( $emailRow->last_mail_uid ) {
					$rt_mail_uid = unserialize( $emailRow->last_mail_uid );
				} else {
					$rt_mail_uid = array();
				}

				$signature    = '';
				$email_type   = '';
				$imap_server  = '';
				$access_token = $rt_mail_settings->get_accesstoken_from_email( $email, $signature, $email_type, $imap_server );

				$rtZendEmail = new Rt_Zend_Mail();

				$rtZendEmail->reademail( sanitize_email( $email ), $email, $access_token, $email_type, $imap_server, $last_sync_time, $emailRow->user_id, $module, $signature );

				$rt_mail_settings->update_sync_status( $email, true );
			}
		}

		function rt_send_email() {
			global $rt_mail_settings;

			$emailRow = $rt_mail_settings->get_new_sent_mail();
			if ( empty( $emailRow ) ) {
				return;
			}
			$rtZendEmail      = new Rt_Zend_Mail();
			$accessTokenArray = array();
			$signature        = '';
			foreach ( $emailRow as $email ) {
				error_log( var_export( $email, true ) );
				if ( ! isset( $accessTokenArray[ $email->fromemail ] ) ) {
					$email_type                            = '';
					$imap_server                           = '';
					$accessTokenArray[ $email->fromemail ] = array(
						'token'       => $rt_mail_settings->get_accesstoken_from_email( $email->fromemail, $signature, $email_type, $imap_server ),
						'email_type'  => $email_type,
						'imap_server' => $imap_server,
					);
				}

				if ( $rt_mail_settings->update_sent_email( $email->id, 'p', 'no' ) > 0 ) {
					$updateFlag = false;
					try {
						$fromname = ( ! empty( $email->fromname ) ) ? $email->fromname : get_bloginfo();
						$result = $rtZendEmail->sendemail( $fromname, $email->fromemail, $accessTokenArray[ $email->fromemail ]['token'], $accessTokenArray[ $email->fromemail ]['email_type'], $accessTokenArray[ $email->fromemail ]['imap_server'], $email->subject, $email->body, unserialize( $email->toemail ), unserialize( $email->ccemail ), unserialize( $email->bccemail ), unserialize( $email->attachement ) );
						error_log( var_export( $result, true ) );
						if ( $result ) {
							$updateFlag = true;
						}
					} catch ( Exception $e ) {
						$updateFlag = false;
					}
					if ( $updateFlag ) {
						$rt_mail_settings->update_sent_email( $email->id, 'yes', 'p' );
					} else {
						$rt_mail_settings->update_sent_email( $email->id, 'error', 'p' );
						echo 'Error: ' . esc_attr( $email->id ). '<br />';
					}
				} else {
					echo 'Error: ' . esc_attr( $email->id ). '<br />';
				}
			}
		}

	}

}
