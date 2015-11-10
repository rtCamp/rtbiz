<?php

/**
 * Created by PhpStorm.
 * User: spock
 * Date: 9/11/15
 * Time: 10:52 AM
 */
if ( !class_exists('rtBiz_Settings_Mailbox ')) :
class rtBiz_Settings_Mailbox extends rtBiz_Settings_Page{

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'rtbiz_mailbox';
		$this->label = __( 'Mailbox', RTBIZ_TEXT_DOMAIN );
		add_filter( 'rtbiz_settings_tabs_array', array( $this, 'add_settings_page' ) );
		add_action( 'rtbiz_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'rtbiz_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = apply_filters( 'rtbiz_mailbox_settings', array(

			array( 'title' => __( 'Mailbox', RTBIZ_TEXT_DOMAIN ), 'type' => 'title', 'desc' => '', 'id' => 'mailbox_option' ),

			array(
				'id'            => 'rtbiz_mailbox',
				'default'       => 'no',
				'type'          => 'rtbiz_mailbox',
				'autoload'      => false
			),

			array( 'type' => 'sectionend', 'id' => 'mailbox_option'),

		) );

		return apply_filters( 'rtbiz_get_settings_' . $this->id, $settings );
	}


}
endif;

return new rtBiz_Settings_Mailbox();
