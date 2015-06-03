RT DB Update
==========

### `class RT_DB_Update`

#### Attributes

##### `$db_version`

Current Version of plugin that is latest.

##### `$install_db_version`

Installed version of plugin that is stored in database

##### `$schema_path = '/../schema/'`

Path to all the schema files for DB tables.

##### `$plugin_path = '/../rt-lib.php'`

Path to plugin's index file. Required to fetch plugin information.

##### `$db_version_option_name`

Database key string for which the plugin version is stored in database.

##### `$rt_plugin_info`

RT_Plugin_Info object that holds plugin information from the plugin index file.

##### `$mu_single_table`

Multisite flag. `true` if you want to create single table in multisite

##### `$debugMode`

Debug mode flag. if `true`, the library logs the error into WordPress error log file.

#### Methods

##### `__construct( $plugin_path, $schema_path, $mu_single_table, $current_version )`

Constructor method for the class.

Set db current and installed version and also plugin info.

``` php
@param bool $current_version plugin latest version
@param bool $plugin_path absolute plugin loader file path
@param bool $schema_path absolute schema directory path which contains .schema files
@param bool $mu_single_table true if you want to create single table in multisite

@internal param string $type $current_version Optional if not defined then will use plugin version

@uses RT_Theme_Info() - If the update is for theme then call for Theme info class to fetch theme information
@uses RT_Plugin_Info() - If the update is for plugin then call for plugin info class to fetch plugin information
@uses RT_DB_Update::get_db_version_option_name()
@uses RT_DB_Update::get_install_db_version()
```

##### `create_table( $sql )`

``` php
@param $sql
```

##### `get_db_version_option_name()`

``` php
@return string
```

#### `get_install_db_version()`

``` php
@return mixed|void
```

##### `check_upgrade()`

``` php
@return mixed
```

##### `do_upgrade()`

``` php
@defined rt_db_update_finished - An action hook when DB upgrade is finished.
@defined rt_db_update_finished_<plugin-slug> - A plugin specific action hook when DB upgrade is finished.
```

##### `update_version()`

``` php
// TODO
```

##### `table_exists( $table_name )`

``` php
@param $table_name

@internal param $table

@return bool
```

##### `genrate_sql( $file_name, $file_content )`

``` php
@param $file_name
@param $file_content

@return string

@uses RT_DB_Update::genrate_table_name()
```

##### `genrate_table_name( $file_name )`

``` php
@global WPDB $wpdb

@param $file_name

@return string
```

#### Hooks

##### Actions

###### `rt_db_update_finished`

###### `rt_db_update_finished_<plugin-slug>`

##### Methods

### Example

Database schema updater class, Just add following code in `register_activation_hook`

```
register_activation_hook( __FILE__, 'myplugin_activate' );

function myplugin_activate() {
	// index file of the plugin.
	$plugin_path = __FILE__;
	// schema folder containing all the schema files for db tables.
	// NOTE: schema file should have extension ".schema"
	// NOTE: Each table defination must have one column named "id" and that should be primary key of the table. Otherwise this library won't work with your DB tables along with `RT_DB_Model`.
	$schema_path = trailingslashit( dirname( __FILE__ ) ) . 'schema/';
	// Common Single table for all sub-sites in Multisite or not.
	$mu_single_table = false;
	// Current version which is latest. If not passed, library will take it from plugin/theme information.
	$current_version = false;
	$rt_db_update = new RT_DB_Update( $plugin_path , $schema_path , $mu_single_table, $current_version );
}
```
