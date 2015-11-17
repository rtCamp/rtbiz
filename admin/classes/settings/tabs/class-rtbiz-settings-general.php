<?php

/**
 * Created by PhpStorm.
 * User: spock
 * Date: 9/11/15
 * Time: 10:52 AM
 */
if ( ! class_exists( 'rtBiz_Settings_General ' ) ) :
	class rtBiz_Settings_General extends rtBiz_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'rtbiz_contact_general';
			$this->label = __( 'General', RTBIZ_TEXT_DOMAIN );
			add_filter( 'rtbiz_settings_tabs_array', array( $this, 'add_settings_page' ) );
			add_action( 'rtbiz_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'rtbiz_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'rtbiz_update_option_values', array( $this, 'product_sync' ) );
		}

		public function product_sync( $option ) {

			if ( ! isset( $option['rtbiz_product_plugin'] ) ) {
				return;
			}

			$old_value = get_option( 'rtbiz_product_plugin' );

			$new_value = $option['rtbiz_product_plugin'];

			$diff = array_diff_assoc( $new_value, $old_value );
			if ( ! empty( $diff ) ) {
				update_option( 'rt_product_plugin_sync', 'true' );
			}

		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters( 'rtbiz_general_settings', array(

				array(
					'title' => __( 'General Options', RTBIZ_TEXT_DOMAIN ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'general_options'
				),
				array(
					'title'         => __( 'Connected Store', RTBIZ_HD_TEXT_DOMAIN ),
					'id'            => 'rtbiz_product_plugin[]',
					'default'       => '',
					'type'          => 'multicheckbox',
					'options'  => array(
						'woocommerce'  => __( 'woocommerce', RTBIZ_HD_TEXT_DOMAIN ),
						'edd' => __( 'edd', RTBIZ_HD_TEXT_DOMAIN ),
					),
					'autoload'      => true,
				),
				array( 'type' => 'sectionend', 'id' => 'general_options' ),

			) );

			return apply_filters( 'rtbiz_get_settings_' . $this->id, $settings );
		}


	}
endif;

return new rtBiz_Settings_General();
