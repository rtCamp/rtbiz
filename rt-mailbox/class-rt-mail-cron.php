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

		function __construct() {

			add_filter( 'cron_schedules', array($this, 'register_custom_schedule') );

			add_action( 'init', array( $this, 'setup_schedule' ) );
			register_deactivation_hook( trailingslashit( RT_HD_PATH ) . 'rtbiz-helpdesk.php', array( $this, 'disable_cron_on_deactivation' ) );

			add_action( 'rt_hd_parse_email_cron', array( $this, 'rt_hd_parse_email' ) );
			add_action( 'rt_hd_send_email_cron', array( $this, 'rt_hd_send_email' ) );
		}

		function register_custom_schedule( $schedules ) {

			// add schedule to the existing set
			$schedules['every_minute'] = array(
				'interval' => 60,
				'display' => __('Every Minute'),
			);
			$schedules['every_5_minutes'] = array(
				'interval' => 300,
				'display' => __('Every 5 Minutes'),
			);
			return $schedules;
		}

		function disable_cron_on_deactivation() {
			wp_clear_scheduled_hook( 'rt_hd_parse_email_cron' );
			wp_clear_scheduled_hook( 'rt_hd_send_email_cron' );
		}

		function setup_schedule() {
			if ( ! wp_next_scheduled( 'rt_hd_parse_email_cron' ) ) {
				wp_schedule_event( time(), 'every_5_minutes', 'rt_hd_parse_email_cron');
			}

			if ( ! wp_next_scheduled( 'rt_hd_send_email_cron' ) ) {
				wp_schedule_event( time(), 'every_minute', 'rt_hd_send_email_cron');
			}
		}

		function rt_hd_parse_email() {

			global $rt_mail_settings, $redux_helpdesk_settings;

			if ( empty( $redux_helpdesk_settings['rthd_enable_reply_by_email'] ) || $redux_helpdesk_settings['rthd_enable_reply_by_email'] != 1 ) {
				return;
			}

			$emailRow = $rt_mail_settings->get_email_for_sync();
			if ( ! $emailRow ) {
				return;
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

			$hdZendEmail = new Rt_Zend_Mail();
			//System Mail
			$isSystemMail = false;
			if ( rthd_is_system_email( $email ) ) {
				$isSystemMail = true;
			}
			$hdZendEmail->reademail( $email, $access_token, $email_type, $imap_server, $last_sync_time, $emailRow->user_id, $isSystemMail, $signature );

			$rt_mail_settings->update_sync_status( $email, true );
			//thread Importer
			$hdZendEmail->reademail( $email, $access_token, $email_type, $imap_server, $last_sync_time, $emailRow->user_id, $isSystemMail, $signature, true );
			$rt_mail_settings->update_sync_status( $email, false );
		}

		function rt_hd_send_email() {
			global $rt_mail_settings;

//			$settings = rthd_get_redux_settings();

//			if ( empty( $settings['rthd_outgoing_email_delivery'] ) || $settings['rthd_outgoing_email_delivery'] != 'user_mail_login' ) {
//				return;
//			}

			$emailRow = $rt_mail_settings->get_new_sent_mail();
			if ( empty( $emailRow ) ) {
				return;
			}
			$hdZendEmail      = new Rt_Zend_Mail();
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
						$result = $hdZendEmail->sendemail( $fromname, $email->fromemail, $accessTokenArray[ $email->fromemail ]['token'], $accessTokenArray[ $email->fromemail ]['email_type'], $accessTokenArray[ $email->fromemail ]['imap_server'], $email->subject, $email->body, unserialize( $email->toemail ), unserialize( $email->ccemail ), unserialize( $email->bccemail ), unserialize( $email->attachement ) );
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
						echo 'Error: ' . esc_attr( $email->id  ). '<br />';
					}
				} else {
					echo 'Error: ' . esc_attr( $email->id  ). '<br />';
				}
			}
		}

	}

}