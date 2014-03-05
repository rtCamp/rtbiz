<?php

/**
 * Description of RT_DB_Model
 * Base class for any Database Model like Media, Album etc.
 *
 * @author faishal <faishal.saiyed@rtcamp.com>
 */

if ( ! class_exists( 'RT_DB_Model' ) ) {
	/**
	 * Class RT_DB_Model
	 */
	class RT_DB_Model
	{

		/**
		 *
		 * @var string
		 *
		 * $table_name - database table linked to the model.
		 *                All the queries will be fired on that table or with the join in this table.
		 * $per_page - number of rows per page to be displayed
		 */
		public $table_name;
		/**
		 * @var int
		 */
		public $per_page;
		/**
		 * @var bool
		 */
		public $mu_single_table;

		/**
		 *
		 * @param string  $table_name Table name for model
		 * @param boolean $is_tablename_with_prefix Set true if $table_name is with prefix otherwise it will prepend WordPress prefix with "rt_"
		 * @param int     $per_page Set number of record per page
		 * @param bool    $mu_single_table set true if common table shared with all the sites in WordPress MultiSite
		 */
		function __construct( $table_name, $is_tablename_with_prefix = false, $per_page = 10, $mu_single_table = false ) {
			$this->mu_single_table = $mu_single_table;
			$this->set_table_name( $table_name, $is_tablename_with_prefix );
			$this->set_per_page( $per_page );
		}

		/**
		 *
		 * @global WPDB  $wpdb
		 *
		 * @param string $table_name Table name for model
		 * @param boolean $is_tablename_with_prefix Set true if $table_name is with prefix otherwise it will prepend WordPress prefix with "rt_"
		 */
		public function set_table_name( $table_name, $is_tablename_with_prefix = false ) {
			global $wpdb;
			if ( ! $is_tablename_with_prefix ) {
				$table_name = ( ( $this->mu_single_table ) ? $wpdb->base_prefix : $wpdb->prefix ) . 'rt_' . $table_name;
			}
			$this->table_name = $table_name;
		}

		/**
		 * set number of rows per page for pagination
		 *
		 * @param int $per_page Set number of record per page
		 */
		public function set_per_page( $per_page ) {
			$this->per_page = $per_page;
		}

		/**
		 * Magic Method for getting DB rows by particular column.
		 * E.g., get_by_<columnName>(params)
		 *
		 * @global wpdb $wpdb
		 *
		 * @param string  $name - Added get_by_<coulmname>(value,pagging=true,page_no=1)
		 * @param array  $arguments
		 *
		 * @return array result array
		 */
		function __call( $name, $arguments )
		{
			if ( $arguments && ! empty( $arguments ) ){
				$column_name = str_replace( 'get_by_', '', strtolower( $name ) );

				if ( ! isset( $arguments[ 1 ] ) ){
					$paging = true;
				} else {
					$paging = $arguments[ 1 ];
				}

				if ( ! isset( $arguments[ 2 ] ) ){
					$page = 1;
				} else {
					$page = $arguments[ 2 ];
				}

				$this->per_page           = apply_filters( 'rt_db_model_per_page', $this->per_page, $this->table_name );
				$return_array             = array();
				$return_array[ 'result' ] = false;
				global $wpdb;
				$return_array[ 'total' ] = intval( $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $this->table_name . ' WHERE `' . $column_name . '`= %s', $arguments[ 0 ] ) ) );
				if ( $return_array[ 'total' ] > 0 ){
					$other = '';
					if ( $paging ){
						if ( intval( $this->per_page ) < 0 ){
							$this->per_page = 1;
						}

						$offset = ( $page - 1 ) * $this->per_page;

						if ( ! is_integer( $offset ) ){
							$offset = 0;
						}
						if ( intval( $offset ) < 0 ){
							$offset = 0;
						}

						if ( $offset <= $return_array[ 'total' ] ){
							$other = ' LIMIT ' . $offset . ',' . $this->per_page;
						} else {
							return false;
						}
					}
					//echo $wpdb->prepare("SELECT * FROM " . $this->table_name . " WHERE {$column_name} = %s {$other}", $arguments[0]);
					$return_array[ 'result' ] = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM  `' . $this->table_name . '` WHERE `' . $column_name . '` = %s ' . $other , $arguments[ 0 ] ), ARRAY_A );
				}

				return $return_array;
			} else {
				return false;
			}
		}

		/**
		 *
		 * @param $data array Data to insert (in column => value pairs)
		 * @param $format array  An array of formats to be mapped to each of the value in $data
		 * @return int last inserted id
		 */
		function insert( $data, $format = null )
		{
			global $wpdb;
			$insertdata = array();
			foreach ( $data as $key => $val ) {
				// Remove NULL value
				if ( $val !== null ){
					$insertdata[ $key ] = $val;
				}
			}

			$wpdb->insert( $this->table_name, $insertdata, $format );

			return $wpdb->insert_id;
		}

		/**
		 *
		 * @global wpdb $wpdb
		 *
		 * @param array  $data
		 * @param array  $where
		 */
		function update( $data, $where, $format = null, $where_format = null )
		{
			global $wpdb;
			return $wpdb->update( $this->table_name, $data, $where, $format, $where_format );
		}

		/**
		 * Get all the rows according to the columns set in $columns parameter.
		 * offset and rows per page can also be passed for pagination.
		 *
		 * @param array   $columns
		 *
		 * @param bool   $offset
		 * @param bool   $per_page
		 * @param string $order_by
		 *
		 * @global wpdb  $wpdb
		 *
		 * @return array
		 */
		function get( $columns, $offset = false, $per_page = false, $order_by = 'id desc' )
		{
			$select = 'SELECT * FROM ' . $this->table_name ;
			$where  = ' where 2=2 ';
			foreach ( $columns as $colname => $colvalue ) {
				if ( is_array( $colvalue ) ){
					if ( ! isset ( $colvalue[ 'compare' ] ) ){
						$compare = 'IN';
					} else {
						$compare = $colvalue[ 'compare' ];
					}
					if ( ! isset ( $colvalue[ 'value' ] ) ){
						$colvalue[ 'value' ] = $colvalue;
					}
					$col_val_comapare = ( $colvalue[ 'value' ] ) ? '(\'' . implode( "','", $colvalue[ 'value' ] ) . '\')' : '';
					$where .= " AND {$this->table_name}.{$colname} {$compare} {$col_val_comapare}";
				} else {
					$where .= " AND {$this->table_name}.{$colname} = '{$colvalue}'";
				}
			}
			$sql = $select . $where;

			$sql .= " ORDER BY {$this->table_name}.$order_by";
			if ( $offset !== false ){
				if ( ! is_integer( $offset ) ) {
					$offset = 0;
				}
				if ( intval( $offset ) < 0 ) {
					$offset = 0;
				}

				if ( ! is_integer( $per_page ) ) {
					$per_page = 1;
				}
				if ( intval( $per_page ) < 0 ) {
					$per_page = 1;
				}
				$sql .= ' LIMIT ' . $offset . ',' . $per_page;

			}
			global $wpdb;
			return $wpdb->get_results( $sql );
		}

		/**
		 *
		 * @param array $where
		 *
		 * @param null  $where_format
		 *
		 * @return int
		 */
		function delete( $where, $where_format = null )
		{
			global $wpdb;

			return $wpdb->delete( $this->table_name, $where, $where_format );
		}


	}
}
