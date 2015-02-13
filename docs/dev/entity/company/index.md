Company
=======

### `Class Rt_Company`

Just like `Rt_Contact`, `Rt_Company` is also inherited from `Rt_Entity`.

#### Attributes

##### `static $primary_email = 'account_primary_email'`

This key is used to store account's primary email in meta.

#### Methods

##### `__construct()`

Constructor method for the class.

``` php
@uses Rt_Entity::__construct()			- rtBiz core. Calling parent class constructor.
@uses Rt_Company::setup_meta_fields()	- rtBiz core. Settings up meta fields for company.
@uses add_action()						- WordPress core. To add init action for initialize entity i.e., company.
```

##### `setup_meta_fields()`

This method defines all the meta fields needed for `Company` entity.

``` php
@uses apply_filters()	- WordPress core. To define new filter hook.

@defined rt_biz_company_meta_fields	- Custom WP Filter to add extra fields for company.
```

*Example*

``` php
add_filter( 'rt_biz_company_meta_fields', 'my_custom_company_fields' );

function my_custom_company_fields( $fields ) {
	$fields[] = array(
		'key' => 'company_pan_number',
		'text' => __( 'PAN Number' ),
		'label' => __( 'Company PAN Number' ),
		'is_multiple' => false,
		'type' => 'number',
		'name' => 'company_pan_number',
		'description' => __( 'Enter Company PAN Number' ),
		'category' => 'Contact',
	);
	return $fields;
}
```

##### `print_metabox_js()`

Overriden from `Rt_Entity` parent class. It is adding JS codes for email validations & other features.

##### `save_meta_values()`

Overriden from `Rt_Entity` parent class. Here all the meta values will be saved for Company.

##### `post_table_columns()`

Adds additional columns for Person list table. ( Country ). Overriden from `Rt_Entity` parent class.

##### `manage_post_table_columns()`

Manages additional columns mentioned above.

##### `add_organization()`

Adds new organization in the database.

##### `get_organizations()`

Returns Organizations post objects if found according to arguments passed and empty array if not found.

#### Hooks

##### Actions

##### Filters

###### `rt_biz_organization_meta_fields`

This filter can be used to add additional custom meta fields.
