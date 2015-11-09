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
			$this->label = __( 'General', 'rtbiz_idea' );
			add_filter( 'rtbiz_settings_tabs_array', array( $this, 'add_settings_page' ) );
			add_action( 'rtbiz_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'rtbiz_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'rtbiz_update_option_values', array( $this, 'product_sync' ) );
		}

		public function product_sync( $option ) {

			if ( ! isset( $option['rtbiz_product_plugin_woo'], $option['rtbiz_product_plugin_edd'] ) ) {
				return;
			}

			$product = array(
				get_option( 'rtbiz_product_plugin_woo' ),
				get_option( 'rtbiz_product_plugin_edd' )
			);

			$before_save = array( $option['rtbiz_product_plugin_woo'], $option['rtbiz_product_plugin_edd'] );

			$diff = array_diff_assoc( $before_save, $product );
			if ( ! empty( $diff ) ) {
				update_option( 'rt_product_plugin_sync', 'true' );
			} else {
				update_option( 'rt_product_plugin_sync', 'false' );
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
					'title'         => __( 'Product Sync Option', RTBIZ_TEXT_DOMAIN ),
					'desc'          => __( 'WooCommerce', RTBIZ_TEXT_DOMAIN ),
					'id'            => 'rtbiz_product_plugin_woo',
					'default'       => 'no',
					'type'          => 'checkbox',
					'checkboxgroup' => 'start',
					'autoload'      => true
				),
				array(
					'desc'          => __( 'Easy Digital Downloads (EDD)', RTBIZ_TEXT_DOMAIN ),
					'id'            => 'rtbiz_product_plugin_edd',
					'default'       => 'no',
					'type'          => 'checkbox',
					'checkboxgroup' => 'end',
					'desc_tip'      => __( 'The option you choose here will define which existing products needs to be taken from either WooCommerce or Easy Digital Downloads and synchronize them with the terms of this special attribute taxonomy Products. So that rtBiz / any other plugin can assign these products to any custom post types that are registered with this taxonomy.', RTBIZ_TEXT_DOMAIN ),
					'autoload'      => true
				),
				array( 'type' => 'sectionend', 'id' => 'general_options' ),

			) );

			return apply_filters( 'rtbiz_get_settings_' . $this->id, $settings );
		}


	}
endif;

return new rtBiz_Settings_General();
