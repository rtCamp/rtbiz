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
 * Description of class-rt-biz-notification-queue-model
 *
 * @author udit
 */
if ( ! class_exists( 'RT_Biz_Notification_Queue_Model' ) ) {

	class RT_Biz_Notification_Queue_Model extends RT_DB_Model {

		public function __construct() {
			parent::__construct( 'rt_biz_notification_queue' );
		}

	}

}
