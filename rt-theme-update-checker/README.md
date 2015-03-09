RT Theme Updater
==========
Add following code in theme `functions.php`
```php
if( class_exists("RT_Theme_Update_Checker") ) {
    $theme_directory_name = basename( dirname( __FILE__ ) );
    $obj_theme_updater = new RT_Theme_Update_Checker(  $theme_directory_name, $theme_update_slug, $metadataUrl );
}
```
