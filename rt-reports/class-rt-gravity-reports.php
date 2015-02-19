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
 * Description of Rt_Gravity_Reports
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Gravity_Reports' ) ) {
	class Rt_Gravity_Reports {

		static $data_type_map = array(
			'text' => 'string',
			'email' => 'string',
			'select' => 'string',
			'radio' => 'string',
			'number' => 'number',
			'multiselect' => 'string',
			'date' => 'string',
			'time' => 'string',
			'list' => 'string',
		);

		public function __construct() {

		}

		static function get_field_inputs( $field ) {
			$inputs = array();
			if ( isset( $field['inputs'] ) && ! empty( $field['inputs'] ) ) {
				$inputs = $field['inputs'];
			}
			return $inputs;
		}

		static function get_field_by_id( $id, $fields ) {
			foreach ( $fields as $field ) {
				if ( $field['id'] == $id ) {
					return $field;
				}
			}
			return false;
		}

		static function get_field_choices( $field ) {
			$choices = array();
			if ( isset( $field['choices'] ) && ! empty($field['choices']) ) {
				$choices = $field['choices'];
			}
			return $choices;
		}

		static function generate_data_source( $form_id, $chart_type, $selected_fields, $labels = array() ) {
			// save user options
			// generate data source - make a query
			// Save chart in user meta / flat table

			$leads = array();
			$fields = array();
			if ( class_exists( 'GFFormsModel' ) && class_exists( 'RGFormsModel' ) ) {
				$leads = GFFormsModel::get_leads( $form_id );
				$meta = RGFormsModel::get_form_meta( $form_id );
				$fields = ( isset( $meta['fields'] ) ) ? $meta['fields'] : array();
			}

			$data_source = array();

			switch ( $chart_type ) {
				case 'table':
					$cols = array();
					foreach ( $fields as $f_id => $field ) {
						if ( 'all_fields_table' !== $selected_fields && ! in_array( $field['id'], $selected_fields ) ) {
							continue;
						}
						switch ( $field['type'] ) {
							case 'address':
							case 'name':
								$inputs = self::get_field_inputs( $field );
								foreach ( $inputs as $i_id => $input ) {
									$cols[] = array(
										'type' => self::$data_type_map['text'],
										'label' => isset( $input['label'] ) ? $input['label'] : 'Input '.( $i_id + 1 ),
									);
								}
								break;
							case 'select':
							case 'radio':
							case 'multiselect':
							case 'checkbox':
								$choices = self::get_field_choices( $field );
								foreach ( $choices as $ch_id => $choice ) {
									$cols[] = array(
										'type' => self::$data_type_map['text'],
										'label' => isset( $choice['text'] ) ? $choice['text'] : 'Choice '.( $ch_id + 1 ),
									);
								}
								if ( isset( $field['enableOtherChoice'] ) && $field['enableOtherChoice'] ) {
									$cols[] = array(
										'type' => self::$data_type_map['text'],
										'label' => __( 'Others' ),
									);
								}
								if ( 'checkbox' == $field['type'] && 1 === sizeof( $choices ) ) {
									$cols[] = array(
										'type' => self::$data_type_map['text'],
										'label' => __( 'Others' ),
									);
								}
								break;
							default:
								$cols[] = array(
									'type' => ( class_exists( 'GFFormsModel' ) ) ? self::$data_type_map[ GFFormsModel::get_input_type( $field ) ] : self::$data_type_map['text'],
									'label' => isset( $field['label'] ) ? $field['label'] : 'Column '.( $f_id + 1 ),
								);
								break;
						}
					}

					$rows = array();
					foreach ( $leads as $lead ) {
						$temp = array();
						foreach ( $fields as $field ) {
							if ( 'all_fields_table' !== $selected_fields && ! in_array( $field['id'], $selected_fields ) ) {
								continue;
							}
							switch ( $field['type'] ) {
								case 'checkbox':
									$choices = self::get_field_choices( $field );
									$inputs = self::get_field_inputs( $field );
									$options = rtbp_kpi_array_merge_recursive_distinct( $choices, $inputs );
									foreach ( $options as $option ) {
										$val = ( isset( $lead[ strval( $option['id'] ) ] ) ) ? $lead[ strval( $option['id'] ) ] : '';
										if ( isset( $option['value'] ) && $val == $option['value'] ) {
											$temp[] = '&#10004;';
										} else {
											$temp[] = '';
										}
									}
									if ( 1 === sizeof( $options ) ) {
										if ( ! isset( $lead[ strval( $options[0]['id'] ) ] ) ||
											empty( $lead[ strval( $options[0]['id'] ) ] ) ) {
											$temp[] = '&#10004;';
										} else {
											$temp[] = '';
										}
									}
									break;
								case 'address':
								case 'name':
									$inputs = self::get_field_inputs( $field );
									foreach ( $inputs as $input ) {
										$temp[] = ( isset( $lead[ strval( $input['id'] ) ] ) ) ? $lead[ strval( $input['id'] ) ] : 'NA';
									}
									break;
								case 'multiselect':
									$vals = ( isset( $lead[ $field['id'] ] ) ) ? explode( ',', $lead[ $field['id'] ] ) : array();
									$choices = self::get_field_choices( $field );
									foreach ( $choices as $choice ) {
										if ( isset( $choice['value'] ) && in_array( $choice['value'], $vals ) ) {
											$temp[] = '&#10004;';
										} else {
											$temp[] = '';
										}
									}
									break;
								case 'select':
								case 'radio':
									$val = isset( $lead[ $field['id'] ] ) ? $lead [ $field['id'] ] : '';
									$choices = self::get_field_choices( $field );
									$others_flag = false;
									foreach ( $choices as $choice ) {
										if ( isset( $choice['value'] ) && $val == $choice['value'] ) {
											$temp[] = '&#10004;';
											$others_flag = true;
										} else {
											$temp[] = '';
										}
									}
									if ( isset( $field['enableOtherChoice'] ) && $field['enableOtherChoice'] ) {
										if ( ! $others_flag ) {
											$temp[] = '&#10004;';
										} else {
											$temp[] = '';
										}
									}
									break;
								default:
									$temp[] = isset( $lead[ $field['id'] ] ) ? ( $field['type'] == 'number' ) ? floatval( $lead[ $field['id'] ] ) : $lead[ $field['id'] ] : 'NA';
									break;
							}
						}
						$rows[] = $temp;
					}

					$data_source['cols'] = $cols;
					$data_source['rows'] = $rows;

					break;
				case 'pie':
				case 'gauge':
					$field = self::get_field_by_id( $selected_fields[0], $fields );
					$cols = array( $field['label'], __( 'Count' ) );

					$counts = array();

					switch ( $field['type'] ) {
						case 'select':
						case 'radio':
						case 'multiselect':
							$choices = self::get_field_choices( $field );
							foreach ( $choices as $choice ) {
								$counts[] = array(
									'label' => $choice['text'],
									'value' => $choice['value'],
									'count' => 0,
								);
							}
							if ( isset( $field['enableOtherChoice'] ) && $field['enableOtherChoice'] ) {
								$counts[] = array(
									'label' => __( 'Others' ),
									'value' => 'others',
									'count' => 0,
								);
							}
							break;
						case 'checkbox':
							$choices = self::get_field_choices( $field );
							$inputs = self::get_field_inputs( $field );
							$options = rtbp_kpi_array_merge_recursive_distinct( $choices, $inputs );
							foreach ( $options as $option ) {
								$counts[] = array(
									'id' => $option['id'],
									'label' => $option['label'],
									'value' => $option['value'],
									'count' => 0,
								);
							}
							if ( 1 === sizeof( $options ) ) {
								$counts['others'] = array(
									'id' => 'others',
									'label' => __( 'Others' ),
									'value' => '',
									'count' => 0,
								);
							}
							break;
						case 'number':
							if ( empty( $labels ) ) {
								$data_source = null;
								break;
							}
							$label = self::get_field_by_id( $labels[0], $fields );
							switch ( $label['type'] ) {
								case 'select':
								case 'radio':
								case 'multiselect':
									$choices = self::get_field_choices( $label );
									foreach ( $choices as $choice ) {
										$counts[] = array(
											'label' => $choice['text'],
											'value' => $choice['value'],
											'count' => 0,
										);
									}
									if ( isset( $label['enableOtherChoice'] ) && $label['enableOtherChoice'] ) {
										$counts[] = array(
											'label' => __( 'Others' ),
											'value' => 'others',
											'count' => 0,
										);
									}
									break;
								case 'checkbox':
									$choices = self::get_field_choices( $label );
									$inputs = self::get_field_inputs( $label );
									$options = rtbp_kpi_array_merge_recursive_distinct( $choices, $inputs );
									foreach ( $options as $option ) {
										$counts[] = array(
											'id' => $option['id'],
											'label' => $option['label'],
											'value' => $option['value'],
											'count' => 0,
										);
									}
									if ( 1 === sizeof( $options ) ) {
										$counts['others'] = array(
											'id' => 'others',
											'label' => __( 'Others' ),
											'value' => '',
											'count' => 0,
										);
									}
									break;
								default:
									$data_source = null;
									break;
							}
							break;
						default:
							$data_source = null;
							break;
					}

					foreach ( $leads as $lead ) {

						switch ( $field['type'] ) {
							case 'multiselect':
								$vals = ( isset( $lead[ $field['id'] ] ) ) ? explode( ',', $lead[ $field['id'] ] ) : array();
								foreach ( $counts as $key => $count ) {
									if ( in_array( $count['value'], $vals ) ) {
										$counts[ $key ]['count']++;
									}
								}
								break;
							case 'select':
							case 'radio':
								$others_flag = false;
								foreach ( $counts as $key => $count ) {
									if ( isset( $lead[ $field['id'] ] ) && $lead[ $field['id'] ] == $count['value'] ) {
										$counts[ $key ]['count']++;
										$others_flag = true;
									}
								}
								if ( isset( $field['enableOtherChoice'] ) && $field['enableOtherChoice'] && ! $others_flag ) {
									foreach ( $counts as $key => $count ) {
										if ( 'others' == $count['value'] ) {
											$counts[ $key ]['count']++;
										}
									}
								}
								break;
							case 'checkbox':
								if ( array_key_exists( 'others', $counts ) ) {
									if ( isset( $lead[ $counts[0]['id'] ] ) && $lead[ $counts[0]['id'] ] == $counts[0]['value'] ) {
										$counts[0]['count']++;
									} else {
										$counts['others']['count']++;
									}
								} else {
									foreach ( $counts as $key => $count ) {
										if ( isset( $lead[ $count['id'] ] ) && $lead[ $count['id'] ] == $count['value'] ) {
											$counts[ $key ]['count']++;
										}
									}
								}
								break;
							case 'number':
								if ( empty( $labels ) ) {
									$data_source = null;
									break;
								}
								$label = self::get_field_by_id( $labels[0], $fields );
								switch ( $label['type'] ) {
									case 'multiselect':
										$vals = ( isset( $lead[ $label['id'] ] ) ) ? explode( ',', $lead[ $label['id'] ] ) : array();
										foreach ( $counts as $key => $count ) {
											if ( in_array( $count['value'], $vals ) ) {
												$counts[ $key ]['count'] += ( isset( $lead[ $field['id'] ] ) && ! empty( $lead[ $field['id'] ] ) ) ? $lead[ $field['id'] ] : 0 ;
											}
										}
										break;
									case 'select':
									case 'radio':
										$others_flag = false;
										foreach ( $counts as $key => $count ) {
											if ( isset( $lead[ $label['id'] ] ) && $lead[ $label['id'] ] == $count['value'] ) {
												$counts[ $key ]['count'] += ( isset( $lead[ $field['id'] ] ) && ! empty( $lead[ $field['id'] ] ) ) ? $lead[ $field['id'] ] : 0 ;
												$others_flag = true;
											}
										}
										if ( isset( $label['enableOtherChoice'] ) && $label['enableOtherChoice'] && ! $others_flag ) {
											foreach ( $counts as $key => $count ) {
												if ( 'others' == $count['value'] ) {
													$counts[ $key ]['count'] += ( isset( $lead[ $field['id'] ] ) && ! empty( $lead[ $field['id'] ] ) ) ? $lead[ $field['id'] ] : 0 ;												}
											}
										}
										break;
									case 'checkbox':
										if ( array_key_exists( 'others', $counts ) ) {
											if ( isset( $lead[ $counts[0]['id'] ] ) && $lead[ $counts[0]['id'] ] == $counts[0]['value'] ) {
												$counts[0]['count'] += ( isset( $lead[ $field['id'] ] ) && ! empty( $lead[ $field['id'] ] ) ) ? $lead[ $field['id'] ] : 0 ;
											} else {
												$counts['others']['count'] += ( isset( $lead[ $field['id'] ] ) && ! empty( $lead[ $field['id'] ] ) ) ? $lead[ $field['id'] ] : 0 ;
											}
										} else {
											foreach ( $counts as $key => $count ) {
												if ( isset( $lead[ $count['id'] ] ) && $lead[ $count['id'] ] == $count['value'] ) {
													$counts[ $key ]['count'] += ( isset( $lead[ $field['id'] ] ) && ! empty( $lead[ $field['id'] ] ) ) ? $lead[ $field['id'] ] : 0 ;
												}
											}
										}
										break;
									default:
										$data_source = null;
										break;
								}
								break;
							default:
								$data_source = null;
								break;
						}
					}

					$rows = array();
					$data_source['total_count'] = 0;
					foreach ( $counts as $count ) {
						$rows[] = array( $count['label'], $count['count'] );
						$data_source['total_count'] += $count['count'];
					}

					if ( 'gauge' == $chart_type ) {
						foreach ( $rows as $key => $value ) {
							$rows[ $key ][1] = round( ( $rows[ $key ][1] * 100 ) / $data_source['total_count'], 2 );
						}
					}

					$data_source['cols'] = $cols;
					$data_source['rows'] = $rows;

					break;
				case 'line':
				case 'bar':
				case 'column':
				case 'area':
				case 'stepped_area':

					if ( 'all_fields_table' === $selected_fields || ! is_array( $selected_fields ) ) {
						$data_source = null;
						break;
					}

					$cols = array( array() );
					$counts = array();
					foreach ( $selected_fields as $selected_field ) {

						$field = self::get_field_by_id( $selected_field, $fields );

						switch ( $field['type'] ) {
							case 'select':
							case 'radio':
							case 'multiselect':
								$cols[0][] = $field['label'];
								$cols[] = __( 'Count' );
								$choices = self::get_field_choices( $field );
								foreach ( $choices as $choice ) {
									$counts[] = array(
										'label' => $choice['text'],
										'value' => $choice['value'],
										'count' => 0,
									);
								}
								if ( isset( $field['enableOtherChoice'] ) && $field['enableOtherChoice'] ) {
									$counts[] = array(
										'label' => __( 'Others' ),
										'value' => 'others',
										'count' => 0,
									);
								}
								break;
							case 'checkbox':
								$cols[0][] = $field['label'];
								$cols[] = __( 'Count' );
								$choices = self::get_field_choices( $field );
								$inputs = self::get_field_inputs( $field );
								$options = rtbp_kpi_array_merge_recursive_distinct( $choices, $inputs );
								foreach ( $options as $option ) {
									$counts[] = array(
										'id' => $option['id'],
										'label' => $option['label'],
										'value' => $option['value'],
										'count' => 0,
									);
								}
								if ( 1 === sizeof( $options ) ) {
									$counts['others'] = array(
										'id' => 'others',
										'label' => __( 'Others' ),
										'value' => '',
										'count' => 0,
									);
								}
								break;
							case 'number':
								$cols[0][] = $field['label'];
								$cols[] = __( $field['label'] );
								break;
							default:
								$data_source = null;
								break;
						}
					}

					foreach ( $leads as $lead ) {

						$temp = array();
						foreach ( $selected_fields as $selected_field ) {

							$field = self::get_field_by_id( $selected_field, $fields );

							switch ( $field['type'] ) {
								case 'multiselect':
									$vals = ( isset( $lead[ $field['id'] ] ) ) ? explode( ',', $lead[ $field['id'] ] ) : array();
									foreach ( $counts as $key => $count ) {
										if ( in_array( $count['value'], $vals ) ) {
											$counts[ $key ]['count']++;
										}
									}
									break;
								case 'select':
								case 'radio':
									$others_flag = false;
									foreach ( $counts as $key => $count ) {
										if ( isset( $lead[ $field['id'] ] ) && $lead[ $field['id'] ] == $count['value'] ) {
											$counts[ $key ]['count']++;
											$others_flag = true;
										}
									}
									if ( isset( $field['enableOtherChoice'] ) && $field['enableOtherChoice'] && ! $others_flag ) {
										foreach ( $counts as $key => $count ) {
											if ( 'others' == $count['value'] ) {
												$counts[ $key ]['count']++;
											}
										}
									}
									break;
								case 'checkbox':
									if ( array_key_exists( 'others', $counts ) ) {
										if ( isset( $lead[ $counts[0]['id'] ] ) && $lead[ $counts[0]['id'] ] == $counts[0]['value'] ) {
											$counts[0]['count']++;
										} else {
											$counts['others']['count']++;
										}
									} else {
										foreach ( $counts as $key => $count ) {
											if ( isset( $lead[ $count['id'] ] ) && $lead[ $count['id'] ] == $count['value'] ) {
												$counts[ $key ]['count']++;
											}
										}
									}
									break;
								case 'number':
									$temp[] = floatval( $lead[ $field['id'] ] );
									break;
								default:
									$data_source = null;
									break;
							}
						}

						if ( ! empty( $temp ) ) {
							$date = new DateTime( $lead['date_created'] );
							$user = get_user_by( 'id', $lead['created_by'] );

							$counts[] = array(
								'label' => __( ( isset( $user->display_name ) ? $user->display_name : 'Annonymous' ).' - '.$date->format( 'j M Y' ) ),
								'count' => $temp,
							);
							$temp = array();
						}
					}

					$rows = array();
					foreach ( $counts as $count ) {
						if ( is_array( $count['count'] ) ) {
							$rows[] = array_merge( array( $count['label'] ), $count['count'] );
						} else {
							$rows[] = array( $count['label'], $count['count'] );
						}
					}

					$cols[0] = implode( ' & ', $cols[0] );
					$data_source['cols'] = $cols;
					$data_source['rows'] = $rows;
					break;
				default:
					$data_source = null;
					break;
			}

			return $data_source;
		}

		static function get_form_fields( $form_id, $field_type = 'others' ) {
			$fields = array();
			if ( class_exists( 'GFFormsModel' ) && class_exists( 'RGFormsModel' ) ) {
				$meta = RGFormsModel::get_form_meta( $form_id );
				$fields = ( isset( $meta['fields'] ) ) ? $meta['fields'] : array();
			}

			$return_fields = array();

			foreach ( $fields as $field ) {
				switch ( $field_type ) {
					case 'enum':
						if ( in_array( $field['type'], array( 'radio', 'select', 'checkbox', 'multiselect' ) ) ) {
							$return_fields[] = array(
								'id' => $field['id'],
								'label' => $field['label'],
								'type' => 'enum',
							);
						}
						break;
						//					case 'poll':
						//						var_dump($field);
						//						break;
					case 'numerics':
						if ( 'number' === $field['type'] ) {
							$return_fields[] = array(
								'id' => $field['id'],
								'label' => $field['label'],
								'type' => 'numerics',
							);
						}
						break;
					case 'range':
						if ( in_array( $field['type'], array( 'date', 'time' ) ) ) {
							$return_fields[] = array(
								'id' => $field['id'],
								'label' => $field['label'],
								'type' => 'range',
							);
						}
						break;
					default:
						if ( ! in_array( $field['type'], array( 'radio', 'select', 'checkbox', 'multiselect', 'number', 'date', 'time' ) ) ) {
							$return_fields[] = array(
								'id' => $field['id'],
								'label' => $field['label'],
								'type' => 'others',
							);
						}
						break;
				}
			}

			if ( 'range' == $field_type ) {
				$return_fields[] = array(
					'id' => '-1',
					'label' => __( 'Form Entry Date' ),
					'type' => 'range',
				);
			}

			return $return_fields;
		}
	}
}
