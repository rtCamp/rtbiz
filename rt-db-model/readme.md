RT DB Model
==========

### `class RT_DB_Model`

Base class for any Database Model like Media, Album etc.

#### Attributes

##### `$table_name`

database table linked to the model. All the queries will be fired on that table or with the join in this table.

##### `$per_page`

number of rows per page to be displayed

##### `$mu_single_table`

set `true` if common table shared with all the sites in WordPress MultiSite

#### Methods

##### `__construct( $table_name, $is_tablename_with_prefix, $per_page, $mu_single_table )`

Constructor method for the class

``` php
@param string  $table_name Table name for model
@param boolean $is_tablename_with_prefix Set true if $table_name is with prefix otherwise it will prepend WordPress prefix with "rt_"
@param int     $per_page Set number of record per page
@param bool    $mu_single_table set true if common table shared with all the sites in WordPress MultiSite

@uses RT_DB_Model::set_table_name() - Set table name.
@uses RT_DB_Model::set_per_page() - Set per page limit.
```

##### `set_table_name( $table_name, $is_tablename_with_prefix )`

Set table name for the model class.

``` php
@global WPDB  $wpdb

@param string $table_name Table name for model
@param boolean $is_tablename_with_prefix Set true if $table_name is with prefix otherwise it will prepend WordPress prefix with "rt_"
```

##### `set_per_page( $per_page )`

set number of rows per page for pagination

``` php
@param int $per_page Set number of record per page
```

##### `__call( $name, $arguments )`

Magic Method for getting DB rows by particular column.

E.g., get_by_<columnName>(params)

``` php
@global wpdb $wpdb

@param string  $name - Added get_by_<coulmname>(value,pagging=true,page_no=1)
@param array  $arguments

@return array result array
```

##### `insert( $data, $format = null )`

Insert Rows into DB table.

``` php
@global wpdb $wpdb

@param $data array Data to insert (in column => value pairs)
@param $format array  An array of formats to be mapped to each of the value in $data
@return int last inserted id
```

##### `update( $data, $where, $format, $where_format )`

Updates existing rows in the db table.

``` php
@global WPDB $wpdb

@param      $data
@param      $where
@param null $format
@param null $where_format

@return false|int
```

##### `get( $columns, $offset, $per_page, $order_by )`

Get all the rows according to the columns set in $columns parameter.

offset and rows per page can also be passed for pagination.

``` php
@param array   $columns

@param bool   $offset
@param bool   $per_page
@param string $order_by

@global wpdb  $wpdb

@return array
```

##### `delete( $where, $where_format )`

Deletes existing rows from database table.

``` php
@param array $where
@param null  $where_format

@return int
```

#### Hooks

##### Actions

##### Filters

### Example

``` php
$table_name = 'wp_attributes';

/**
 * false because the table string is without prefix.
 * so library will prepend the prefix on its own.
 */
$is_tablename_with_prefix = false;

$per_page = 10;

/**
 * false Bacause, this table needs to be created for each sub-site in MU setup.
 * Not a common single table for all sub-sites.
 */
$mu_single_table = false;

$obj_table_model = new RT_DB_Model( $table_name, $is_tablename_with_prefix, $per_page, $mu_single_table );
```
