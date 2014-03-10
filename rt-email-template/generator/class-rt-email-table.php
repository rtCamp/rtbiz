<?php
/**
 * Created by PhpStorm.
 * User: faishal
 * Date: 08/03/14
 * Time: 8:03 PM
 */
if ( ! class_exists( 'RT_Email_Table' ) ) {
	/**
	 * Class RT_Email_Table
	 */
	class RT_Email_Table {

		/**
		 * @var string
		 */
		public $header;
		/**
		 * @var
		 */
		public $body;

		/**
		 * @var
		 */
		public $body_row_count;
		/**
		 * @var
		 */
		public $footer;

		/**
		 *
		 */
		function __construct() {
			$this->header         = '';
			$this->body           = '';
			$this->footer         = '';
			$this->body_row_count = 0;
		}

		/**
		 * @var string
		 */
		static $cell_css = 'padding: 5px 20px;font-size: 12px;border:1px solid #ffffff;text-align:center;';

		/**
		 * @param $data_array
		 */
		function add_header_row( $data_array ) {
			$this->header .= $this->generate_row( $data_array );
		}

		/**
		 * @param $data_array
		 */
		function add_footer_row( $data_array ) {
			$this->footer .= $this->generate_row( $data_array );
		}

		/**
		 * @param $data_array
		 */
		function add_body_row( $data_array ) {
			$this->body_row_count ++;
			$this->body .= $this->generate_row( $data_array, true, $this->body_row_count );
		}

		/**
		 * @return string
		 */
		function get_header() {
			return '<thead style="background:#F5F5F5;">' . $this->header . '</thead>';
		}

		/**
		 * @return string
		 */
		function get_footer() {
			return '<tfoot style="background:#F5F5F5;">' . $this->footer . '</tfoot>';
		}

		/**
		 * @return string
		 */
		function get_body() {
			return '<tbody>' . $this->body . '</tbody>';
		}

		/**
		 * @return string
		 */
		function get_html() {
			$return_html = '<table style="border: 1px solid lightgrey;margin-top: 20px;border-collapse:separate;width:100%;" cellspacing=0 cellpadding=5 >';
			$return_html .= $this->get_header();
			$return_html .= $this->get_body();
			$return_html .= $this->get_footer();
			$return_html .= '</table>';

			return $return_html;
		}

		/**
		 *
		 * @param array $data_array
		 * @param bool  $alternate
		 *
		 * @param int   $count
		 *
		 * @return string
		 */
		function generate_row( $data_array, $alternate = false, $count = 0 ) {
			$return_html = '<tr>';

			if ( $alternate ) {
				if ( $count % 2 === 0 ) {
					$td_css = self::$cell_css . 'background:#F9F9F9';
				} else {
					$td_css = self::$cell_css . 'background:#FFF';
				}
			} else {
				$td_css = self::$cell_css;
			}

			foreach ( $data_array as $td_data ) {
				$return_html .= '<td style="' . esc_attr( $td_css ). '">' . esc_html( $td_data ) . '</td>';
			}
			$return_html .= '</tr>';

			return $return_html;
		}
	}
}
