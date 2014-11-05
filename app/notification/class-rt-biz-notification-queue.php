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

			register_deactivation_hook( trailingslashit( RT_BIZ_PATH ) . 'index.php', array( $this, 'disable_cron_ondeactivation' ) );

			add_action( 'rtbiz_notification_queue_cron_hook', array( $this, 'execute_notification_queue_cron' ) );
		}

		function activate() {
			if ( ! wp_next_scheduled( 'rtbiz_notification_queue_cron_hook' ) ) {
				wp_schedule_event( time(), 'every5minute', 'rtbiz_notification_queue_cron_hook' );
			}
		}

		function disable_cron_on_deactivation() {
			wp_clear_scheduled_hook( 'rtbiz_notification_queue_cron_hook' );
		}

		// add cron time interval
		function notification_queue_cron_interval( $interval ) {
			$interval[ 'everyminute' ] = array( 'interval' => 60, 'display' => __( 'Every Minute' ) );
			$interval[ 'every5minute' ] = array( 'interval' => 5*60, 'display' => __( 'Every 5 Minute' ) );
			return $interval;
		}

		function execute_notification_queue_cron() {
			global $rt_biz_notification_queue_model;
			$notifications = $rt_biz_notification_queue_model->get_by_sent('no');

			if ( $notifications['total'] && ! empty( $notifications['result'] ) ) {
				foreach ( $notifications['result'] as $n ) {
					$flag = false;
					try {
						$flag = $this->send_notification( $n['user'], $n['timestamp'], $n['subject'], $n['message'], $n['attachment'], $n['module'], $n['entity'], $n['entity_id'] );
					} catch (Exception $e) {
						$flag = false;
					}

					if ( $flag ) {
						$rt_biz_notification_queue_model->update( array( 'sent' => 'yes' ), array( 'id' => $n['id'] ) );
					} else {
						$rt_biz_notification_queue_model->update( array( 'sent' => 'error' ), array( 'id' => $n['id'] ) );
					}
				}
			}
		}

		function send_notification( $user_id, $timestamp, $subject, $message, $attachment, $module, $entity, $entity_id ) {

			// As of now only email - so send email. Later on other methods will be added such as sms, in-app notification etc.
			return $this->send_email( $user_id, $timestamp, $subject, $message, $attachment, $module, $entity, $entity_id );
		}

		function send_email( $user_id, $timestamp, $subject, $message, $attachment, $module, $entity, $entity_id ) {

			// as of now only email preference. later on user preference will be added for how he/she wants to get notified.
			$user = get_user_by( 'id', $user_id );
			$headers = 'Content-Type: text/html';
			return wp_mail($user->user_email, $subject, $message, $headers, $attachment);
		}

	}

}
