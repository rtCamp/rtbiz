<?php

/**
 * Created by PhpStorm.
 * User: faishal
 * Date: 05/03/14
 * Time: 2:52 PM
 */
if ( ! class_exists( 'RT_Email_Template' ) ) {
	/**
	 * Class for generate html markup for email
	 * Class RT_Email_Template
	 */
	class RT_Email_Template {
		/**
		 * @var string any custom css that required in email formatting
		 */
		var $custom_css;
		/**
		 * @var string email body background color hex for
		 */
		var $background_color;

		/**
		 * Initialize custom_css and background_color with default value
		 */
		function __construct() {
			$this->custom_css       = '';
			$this->background_color = '#fff';
			$this->auto_loader();
		}

		/**
		 * Will return default header with including required css
		 * @return string html content of header
		 */
		function get_header() {
			$content = $this->get_template_content( 'header' );
			$content = str_replace( '{rt_mail_background}', $this->background_color, $content );

			return str_replace( '{rt_custom_css}', $this->custom_css, $content );
		}

		/**
		 * Will return default footer
		 * @return string html content of footer
		 */
		function get_footer() {
			return $this->get_template_content( 'footer' );
		}

		/**
		 * @TODO: not implemented
		 * @return string
		 */
		function get_body() {
			return '';
		}

		/**
		 * Return template content by reading that file
		 * @param $template_name string header/footer
		 *
		 * @return string content of template file
		 */
		function get_template_content( $template_name ) {
			return file_get_contents( dirname( __FILE__ ) . '/templates/' . $template_name . '.html' );
		}

		/**
		 * Register AutoLoader for email-template
		 */
		function auto_loader()
		{
			$this->auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'generator/' );
		}
	}
}