<?php

/**
 * Created by PhpStorm.
 * User: spock
 * Date: 9/11/15
 * Time: 10:52 AM
 */
if ( !class_exists('rtBiz_Settings_Importer ')) :
class rtBiz_Settings_Importer extends rtBiz_Settings_Page{

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'rtbiz_contact_importer';
		$this->label = __( 'Contact Importer', 'rtbiz_idea' );
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

		$settings = apply_filters( 'rtbiz_contact_importer_settings', array(

			array( 'title' => __( 'Importer', RTBIZ_TEXT_DOMAIN ), 'type' => 'title', 'desc' => '', 'id' => 'option_contact_importer' ),

			array(
				'title'         => __( 'Contact Importer', RTBIZ_TEXT_DOMAIN),
				'id'            => 'rtbiz_contact_importer',
				'default'       => 'no',
				'type'          => 'contact_importer',
				'autoload'      => false
			),

			array( 'type' => 'sectionend', 'id' => 'option_contact_importer'),

		) );

		return apply_filters( 'rtbiz_get_settings_' . $this->id, $settings );
	}


}
endif;

return new rtBiz_Settings_Importer();
