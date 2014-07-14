RT DB Model
==========

```php
$obj_attributes = new RT_Attributes();
// This is optional. You can pass empty array.
$terms_cap = array(
	'manage_terms' => 'manage_custom_terms',
	'edit_terms' => 'edit_custom_terms',
	'delete_terms' => 'delete_custom_terms',
	'assign_terms' => 'assign_custom_terms',
);
$obj_attributes->add_attributes_page( $page_slug = 'my_plugin_attribute_page_slug', $parent_page_slug = 'edit.php?post_type=post', $post_type = 'post', $page_cap = 'manage_attributes', $terms_caps,$render_type = true, $storage_type = true, $orderby = true );
```
