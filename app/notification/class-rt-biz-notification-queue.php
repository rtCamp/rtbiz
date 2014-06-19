<?php

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-rt-biz-notification-queue
 *
 * @author udit
 */
if ( ! class_exists( 'RT_Biz_Notification_Queue' ) ) {

	class RT_Biz_Notification_Queue {

		public function __construct() {
			add_action( 'init', array( $this, 'activate' ) );
			add_filter( 'cron_schedules', array( $this, 'notification_queue_cron_interval' ) );
		}

		function activate() {
			if ( ! wp_next_scheduled( 'rtpm_notification_queue_cron_hook' ) ) {
				wp_schedule_event( time(), 'rtpm_notification_queue_cron_time', 'rtpm_notification_queue_cron_hook' );
			}
		}

		// add cron time interval
		function notification_queue_cron_interval( $interval ) {
			$interval[ 'rtpm_notification_queue_cron_time' ] = array( 'interval' => 60, 'display' => __( 'Every minute' ) );
			return $interval;
		}

		static function execute_notification_queue_cron() {
			$rt_biz_notification_queue_model = new RT_Biz_Notification_Queue_Model();
			$notifications = $rt_biz_notification_queue_model->get_by_sent('no');

			if ( $notifications['total'] && ! empty( $notifications['result'] ) ) {
				foreach ( $notifications['result'] as $n ) {
					$flag = false;
					try {
						$flag = self::send_notification( $n['user'], $n['timestamp'], $n['subject'], $n['message'], $n['attachment'], $n['module'], $n['entity'], $n['entity_id'] );
					} catch (Exception $e) {
						$flag = false;
					}
					error_log(var_export($flag,true));
					if ( $flag ) {
						$rt_biz_notification_queue_model->update( array( 'sent' => 'yes' ), array( 'id' => $n['id'] ) );
					} else {
						$rt_biz_notification_queue_model->update( array( 'sent' => 'error' ), array( 'id' => $n['id'] ) );
					}
				}
			}
		}

		static function send_notification( $user_id, $timestamp, $subject, $message, $attachment, $module, $entity, $entity_id ) {

			// As of now only email - so send email. Later on other methods will be added such as sms, in-app notification etc.
			return self::send_email( $user_id, $timestamp, $subject, $message, $attachment, $module, $entity, $entity_id );
		}

		static function send_email( $user_id, $timestamp, $subject, $message, $attachment, $module, $entity, $entity_id ) {

			// as of now only email preference. later on user preference will be added for how he/she wants to get notified.
			$user = get_user_by( 'id', $user_id );
			$headers = 'Content-Type: text/html';
			return wp_mail($user->user_email, $subject, $message, $headers, $attachment);
		}

	}

}
