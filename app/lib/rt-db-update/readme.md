RT DB Update
==========

Database schema updater class, Just add following code in `register_activation_hook`

```
$rt_db_update = new RT_DB_Update( $plugin_loader_file_path , $schema_path , $mu_single_table );
```