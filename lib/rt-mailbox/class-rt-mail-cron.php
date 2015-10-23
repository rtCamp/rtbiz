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

		function __construct( $plugin_path_for_cron ) {

			register_activation_hook( $plugin_path_for_cron, array( $this, 'enable_cron_on_activation' ) );

			add_filter( 'cron_schedules', array( $this, 'register_custom_schedule' ) );

			add_action( 'init', array( $this, 'setup_schedule' ) );

			register_deactivation_hook( $plugin_path_for_cron, array( $this, 'disable_cron_on_deactivation' ) );

		}
		function deregister_cron_for_module( $module ) {
			wp_clear_scheduled_hook( 'rt_parse_email_cron', array( $module ) );
			wp_clear_scheduled_hook( 'rt_send_email_cron' );
			remove_action( 'rt_parse_email_cron', array( $this, 'rt_parse_email' ) );
			remove_action( 'rt_send_email_cron', array( $this, 'rt_send_email' ) );
		}

		function register_custom_schedule( $schedules ) {
			// add schedule to the existing set
			$schedules['every_minute']    = array(
				'interval' => 60,
				'display'  => __( 'Every Minute' ),
			);
			$schedules['every_5_minutes'] = array(
				'interval' => 300,
				'display'  => __( 'Every 5 Minutes' ),
			);

			return $schedules;
		}

		/**
		 * disable the cron
		 */
		function disable_cron_on_deactivation() {
			wp_clear_scheduled_hook( 'rt_send_email_cron' );
			wp_clear_scheduled_hook( 'rt_parse_email_cron' );
		}

		function enable_cron_on_activation(){
			//ACTIVATION HOOK
		}

		function setup_schedule(){
			add_action( 'rt_parse_email_cron', array( $this, 'rt_parse_email' ) );
			add_action( 'rt_send_email_cron', array( $this, 'rt_send_email' ) );
			if ( ! wp_next_scheduled( 'rt_parse_email_cron' ) ) {
				wp_schedule_event( time(), 'every_5_minutes', 'rt_parse_email_cron' );
			}
			if ( ! wp_next_scheduled( 'rt_send_email_cron' ) ) {
				wp_schedule_event( time(), 'every_minute', 'rt_send_email_cron' );
			}
		}

		/**
		 * Parse email
		 *
		 * @internal param $module
		 */
		function rt_parse_email() {

			global $rt_mail_settings, $rt_mail_accounts_model;

			$emails = $rt_mail_accounts_model->get_all_mail_accounts();
			foreach ( $emails as $emailRow ) {
				if ( ! $emailRow ) {
					continue;
				}
				$email = $emailRow->email;
				rt_log( sanitize_email( $email ) . " Selected. \r\n" );

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

				$rtZendEmail->reademail( sanitize_email( $email ), $email, $access_token, $email_type, $imap_server, $last_sync_time, $emailRow->user_id, $emailRow->module, $signature );

				$rt_mail_settings->update_sync_status( $email, true );
			}
		}

		function rt_send_email() {
			global $rt_mail_settings;

			$emailRows = $rt_mail_settings->get_new_sent_mail();
			if ( empty( $emailRows ) ) {
				return;
			}
			$rtZendEmail      = new Rt_Zend_Mail();
			$accessTokenArray = array();
			$signature        = '';
			foreach ( $emailRows as $email ) {
				// skip sending email filter.
				$do_send = apply_filters( 'rt_lib_before_sending_email', true, $email );
				if ( ! $do_send ) {
					rt_log( 'Skip email : ' . var_export( $email->id, true ) );
					$rt_mail_settings->update_sent_email( $email->id, 'skip', 'no' );
					continue;
				}
				rt_log( 'Sending email : ' . var_export( $email->id, true ) );
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
						$result   = $rtZendEmail->sendemail( $fromname, $email->fromemail, $accessTokenArray[ $email->fromemail ]['token'], $accessTokenArray[ $email->fromemail ]['email_type'], $accessTokenArray[ $email->fromemail ]['imap_server'], $email->subject, $email->body, unserialize( $email->toemail ), unserialize( $email->ccemail ), unserialize( $email->bccemail ), unserialize( $email->attachement ), $email );
						rt_log( var_export( 'Email id :' . $email->id . ' Status :' . $result, true ) );
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
						rt_log( 'Error: ' . esc_attr( $email->id ) . '<br />' );
					}
				} else {
					rt_log( 'Error: ' . esc_attr( $email->id ) . '<br />' );
				}
			}
		}

	}

}
