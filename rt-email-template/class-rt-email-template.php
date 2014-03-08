<?php

/**
 * Created by PhpStorm.
 * User: faishal
 * Date: 05/03/14
 * Time: 2:52 PM
 */
class RT_Email_Template {
	var $custom_css;
	var $background_color;
	function __construct(){
		$this->custom_css       = '';
		$this->background_color = '#fff';
	}
	function get_header() {
		$content = $this->get_template_content( 'header' );
		$content = str_replace( '{rt_mail_background}', $this->background_color,  $content );

		return str_replace( '{rt_custom_css}', $this->custom_css,  $content );
	}

	function get_footer() {
		return $this->get_template_content( 'footer' );
	}

	function get_body() {
		return '';
	}

	function get_template_content( $template_name ) {
		return file_get_contents( dirname( __FILE__ ) . '/templates/' . $template_name . '.html' );
	}
}