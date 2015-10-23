<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Rt_Reports
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Reports' ) ) {
	class Rt_Reports {

		/**
		 * Data Source for the Charts
		 */
		var $data_source;

		/**
		 * Chart Rendering Opitons
		 */
		var $options;

		/**
		 * Dom Target element where the chart is to be displayed
		 */
		var $dom_element;

		/**
		 * Supported Chart Types
		 */
		var $chart_types;

		var $page_slugs;

		/**
		 *
		 */
		public function __construct( $page_slugs = array() ) {
			$this->chart_types = array(
				'table'        => array(
					'label'      => __( 'Table Chart' ),
					'max_fields' => 0,
				),
				'pie'          => array(
					'label'      => __( 'Pie Chart' ),
					'max_fields' => 1,
				),
				'gauge'        => array(
					'label'      => __( 'Gauge Chart' ),
					'max_fields' => 1,
				),
				'area'         => array(
					'label'      => __( 'Area Chart' ),
					'max_fields' => 1,
				),
				'stepped_area' => array(
					'label'      => __( 'Stepped Area Chart' ),
					'max_fields' => 1,
				),
				'bar'          => array(
					'label'      => __( 'Bar Chart' ),
					'max_fields' => 1,
				),
				'column'       => array(
					'label'      => __( 'Column Chart' ),
					'max_fields' => 1,
				),
				'line'         => array(
					'label'      => __( 'Line Chart' ),
					'max_fields' => 1,
				),
			);

			$this->page_slugs = $page_slugs;

			if ( is_admin() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'admin_head', array( $this, 'print_scripts' ) );
			} else {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'wp_head', array( $this, 'print_scripts' ) );
			}
		}

		function enqueue_scripts() {
			if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], $this->page_slugs ) ) {
				$protocol          = is_ssl() ? 'https' : 'http';
				$google_charts_scr = $protocol . '://www.google.com/jsapi';
				if ( ! wp_script_is( 'rt-google-charts' ) ) {
					wp_enqueue_script( 'rt-google-charts', $google_charts_scr );
				}
			}
		}

		function print_scripts() {
			if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], $this->page_slugs ) ) {
				$charts      = $this->chart_types;
				$core_charts = array( 'pie', 'line', 'bar', 'column', 'area', 'stepped_area' );

				foreach ( $charts as $key => $chart ) {
					if ( in_array( $key, $core_charts ) ) {
						unset( $charts[ $key ] );
					}
				}
				$packages   = array_keys( $charts );
				$packages[] = 'corechart';
				?>
				<script type="text/javascript">
					// Load the Visualization API and the piechart package.
					google.load('visualization', '1.0', {'packages': <?php echo json_encode( $packages ); ?>});
				</script>
				<?php
			}
		}

		/**
		 * Render Charts
		 */
		function render_chart( $charts ) {
			?>
			<script type="text/javascript">
				var rt_reports_charts = <?php echo json_encode( $charts ); ?>;

				google.setOnLoadCallback(rt_reports_draw_charts);

				function rt_reports_validate_data_source(chart, chart_type) {
					if (chart.data_source === null)
						return false;
					if (chart.data_source.cols === null ||
						chart.data_source.cols === undefined ||
						chart.data_source.cols.length <= 0 ||
						chart.data_source.rows === null ||
						chart.data_source.rows === undefined) {
						return false;
					}

					switch (chart_type) {
						case 'pie':
							if (chart.data_source.total_count <= 0)
								return false;
							break;
					}

					return true;
				}

				function rt_reports_draw_single_chart(chart) {
					try {
						switch (chart.chart_type) {
							case 'table':
								var flag = rt_reports_validate_data_source(chart, chart.chart_type);
								if (!flag) {
									jQuery('#' + chart.dom_element).html('Invalid Data Source. Chart could not be drawn.');
									break;
								}

								var data_table = new google.visualization.DataTable();
								jQuery.each(chart.data_source.cols, function (col_id, column) {
									data_table.addColumn(column.type, column.label);
								});


								data_table.addRows(chart.data_source.rows);

								chart_title = '';
								if (chart.options !== null && chart.options !== undefined && chart.options.title !== undefined && chart.options.title !== null && chart.options.title !== '') {
									chart_title = chart.options.title;
								} else {
									chart_title = 'Report - ' + chart.id;
								}
								jQuery('#' + chart.dom_element).parent().siblings('.rt-report-actions').children('.rt-report-title').remove();
								jQuery('#' + chart.dom_element).parent().siblings('.rt-report-actions').prepend('<span class="rt-report-title">' + chart_title + '</span>');

								var chart_table = new google.visualization.Table(document.getElementById(chart.dom_element));
								var option = {
									showRowNumber: true,
									width: '100%',
									allowHtml: true,
									page: 'enable'
								};
								if (chart.options.hasOwnProperty('sortColumn')) {
									option['sortColumn'] = chart.options.sortColumn;
									if (chart.options.hasOwnProperty('sortAscending')) {
										option['sortAscending'] = chart.options.sortAscending == 'true';
									}
									option['sortAscending'] = false;
								}
								chart_table.draw(data_table, option);
								break;
							case 'pie':
							case 'gauge':

								options = {
									width: '100%'
								};

								var flag = rt_reports_validate_data_source(chart, chart.chart_type);
								if (!flag) {
									jQuery('#' + chart.dom_element).html('Invalid Data Source. Chart could not be drawn.');
									break;
								}

								var chart_data_source = [];
								chart_data_source.push(chart.data_source.cols);
								jQuery.each(chart.data_source.rows, function (row_id, row) {
									chart_data_source.push(row);
								});
								var chart_data = google.visualization.arrayToDataTable(chart_data_source);

								chart_title = '';
								if (chart.options !== null && chart.options !== undefined && chart.options.title !== undefined && chart.options.title !== null && chart.options.title !== '') {
									options.title = chart.options.title;
								}
								jQuery('#' + chart.dom_element).parent().siblings('.rt-report-actions').children('.rt-report-title').remove();
								jQuery('#' + chart.dom_element).parent().siblings('.rt-report-actions').prepend('<span class="rt-report-title">' + chart_title + '</span>');

								var chart_obj;
								if (chart.chart_type == 'pie') {
									chart_obj = new google.visualization.PieChart(document.getElementById(chart.dom_element));
								} else if (chart.chart_type == 'gauge') {
									chart_obj = new google.visualization.Gauge(document.getElementById(chart.dom_element));
								}

								chart_obj.draw(chart_data, options);
								break;
							case 'area':
							case 'stepped_area':
							case 'bar':
							case 'column':
							case 'line':
								var flag = rt_reports_validate_data_source(chart, chart.chart_type);
								if (!flag) {
									jQuery('#' + chart.dom_element).html('Invalid Data Source. Chart could not be drawn.');
									break;
								}

								var chart_data_source = [];
								chart_data_source.push(chart.data_source.cols);
								jQuery.each(chart.data_source.rows, function (row_id, row) {
									chart_data_source.push(row);
								});
								var chart_data = google.visualization.arrayToDataTable(chart_data_source);

								chart_title = '';
								if (chart.options !== null && chart.options !== undefined && chart.options.title !== undefined && chart.options.title !== null && chart.options.title !== '') {
									chart_title = chart.options.title;
								} else {
									chart_title = 'Report - ' + chart.id;
								}

								isStacked = false;
								if (chart.options !== null && chart.options !== undefined && chart.options.isStacked !== undefined && chart.options.isStacked !== null && chart.options.isStacked) {
									isStacked = true;
								} else {
									isStacked = false;
								}

								jQuery('#' + chart.dom_element).parent().siblings('.rt-report-actions').children('.rt-report-title').remove();
								jQuery('#' + chart.dom_element).parent().siblings('.rt-report-actions').prepend('<span class="rt-report-title">' + chart_title + '</span>');

								options = {};
								options.vAxis = {};

								if (chart.options !== null && chart.options !== undefined) {

									if (chart.options.colors !== undefined && chart.options.colors !== null && chart.options.colors) {

										options.colors = chart.options.colors;
									}

									if (chart.options.legend !== undefined && chart.options.legend !== null && chart.options.legend) {

										options.legend = chart.options.legend;
									}

									if (chart.options.pointSize !== undefined && chart.options.pointSize !== null && chart.options.pointSize) {

										options.pointSize = chart.options.pointSize;
									}

									if (chart.options.vAxis !== undefined && chart.options.vAxis !== null && chart.options.vAxis) {

										options.vAxis = JSON.parse(chart.options.vAxis);
									}
								}
								options.vAxis.title = chart.data_source.cols[0];
								options.width = '100%';
								options.isStacked = isStacked;

								var chart_obj;
								if (chart.chart_type == 'area') {
									chart_obj = new google.visualization.AreaChart(document.getElementById(chart.dom_element));
								} else if (chart.chart_type == 'stepped_area') {
									chart_obj = new google.visualization.SteppedAreaChart(document.getElementById(chart.dom_element));
								} else if (chart.chart_type == 'bar') {
									chart_obj = new google.visualization.BarChart(document.getElementById(chart.dom_element));
								} else if (chart.chart_type == 'column') {
									chart_obj = new google.visualization.ColumnChart(document.getElementById(chart.dom_element));
								} else if (chart.chart_type == 'line') {
									chart_obj = new google.visualization.LineChart(document.getElementById(chart.dom_element));
								}

								chart_obj.draw(chart_data, options);
								break;
							default:
								jQuery('#' + chart.dom_element).html('You have chosen an Invalid Chart Type which is not supported.');
								break;
						}
					} catch (e) {
						jQuery('#' + chart.dom_element).html(e);
					}
				}

				function rt_reports_draw_charts() {

					if (jQuery(rt_reports_charts).length > 0) {
						jQuery.each(rt_reports_charts, function (chart_id, chart) {
							rt_reports_draw_single_chart(chart);
						});
					}
				}
			</script>
		<?php }
	}
}
