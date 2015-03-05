RT Attributes
=============

### Database Schema

#### `wp_attributes`

- `id` - Attribute ID. Primary Key
- `module_name` - Module for which this attribute is registered
- `attribute_name` - Attribute slug
- `attribute_label` - Attribute Label
- `attribute_store_as` - Attribute Store Type. E.g., Taxonomy, Meta etc.
- `attribute_render_type` - Attribute Render Type. E.g., Drop down, radio, textbox etc.
- `attribute_orderby` - Attribute Ordering

#### `wp_attributes_relationship`

- `id` - Relationship ID. Primary Key.
- `attr_id` - Attribute ID from the above table.
- `post_type` - Post Type with which the attribute is registered.
- `settings` - Settings for the relationship, if needed.

### `class RT_Attributes`

This is the main class for Attributes.

This will be responsible for registering taxonomies to given post type and displaying available attributes.

#### Attributes

##### `$module_name`

A unique module name for which this class is called. This can be either plugin slug or plugin name or any unique identifier that will be used to know which plugin or module is calling the library class. And accordingly that can be mapped to attributes.

##### `$parent_page_slug`

Page slug under which the attributes page is to be shown. If null / empty then an individual Menu Page will be added

##### `$page_slug`

Page slug for Attributes Page

##### `$post_type`

If any post type passed, only attributes for those post type will be listed on the page.

##### `$page_cap`

Capability for Attributes Admin Page; if not passed, default cap will be `manage_options`

##### `$attr_cap`

Capability for Attributes (Taxonomy / Terms); if not passed, default cap will be empty array ( array() )

##### `$render_type_required`

Render Type for the attribute; Dropdown, Checklist, Rating Stars etc.,

##### `$storage_type_required`

Storage Type for the attributes; Taxonomy or Meta; if not mentioned then Taxonomy will be registered for the attribute by default.

##### `$orderby_required`

Sorting Order for the attributes; Order by id, name or default etc.

##### `$attributes_db_model`

Attributes DB Model Object

##### `$attributes_relationship_model`

Attributes Relationship DB Model Object

##### `$auto_loader`

`RT_WP_Autoload` class object for Attributes library.

#### Methods

##### `__construct( $module_name )`

Constructor method for the class.

``` php
@param $module_name - A Unique module name to identify for which module / post_types the attributes i.e., taxonomies are to be registered

@uses RT_Attributes::auto_loader() - Autoloads classes for this library
@uses RT_Attributes::db_upgrade() - Updates database tables for the library if required
@uses RT_Attributes::init_db_model() - Initialises database model classes used in the library
```

##### `db_upgrade()`

This plugin calls for `RT_DB_Update` library which handles the database updates part if the database tables in the library are updated.

``` php
@uses RT_DB_Update() - DB update class constructor
@uses RT_DB_Update::get_install_db_version() - Gets the installed version of library.
@uses RT_DB_Update::do_upgrade() - This is the main function which handles actual DB update.
```

##### `init_db_model()`

Initialize the Database model object which will be used for DB transactions

``` php
@uses RT_Attributes_Model() - Attribute Model Class constructor
@uses RT_Attributes_Relationship_Model() - Relationship model class constructor
```

##### `register_attribute_mappings()`

Registers all the taxonomies according to the attributes defined for the post types.

``` php
@uses RT_Attributes_Relationship_Model::get_all_relations() - Gets all the relations from the database table.
@uses RT_Attributes_Model::get_attribute() - Gets the attribute object from the ID.
@uses RT_Attributes::register_taxonomy() - Calling out main function which will register WordPress Taxonomy with post types
```

##### `register_taxonomy( $post_type, $attr_id, $caps )`

This is the main function which will register WordPress Taxonomy with post types i.e., execute `register_taxonomy` function from WordPress.

``` php
@param $post_type - Post Type with which taxonomy needs to be registered
@param $attr_id - Attribute ID for the taxonomy
@param $caps - Capability array for the taxonomy.

@uses RT_Attributes_Model::get_attribute() - Gets the attribute object from ID
@uses RT_Attributes::get_taxonomy_name() - Sanitizes the attribute slug
@uses apply_filters() - WordPress Core. Defines custom filter hooks for modifications.
@uses register_taxonomy() - WordPress Core. Registers taxonomy with given post type.

@defined rt_wp_attributes_show_in_nav_menus - Whether to show this particular attribute in WordPress navigation menu or not.
@defined rt_wp_attributes_taxonomy_objects_<attribute-slug> - Filter the post type for any given attribute.
@defined rt_wp_attributes_taxonomy_args_<attribute-slug> - Filter the arguments passed to `register_taxonomy` function while registering the taxonomy.
```

*Example*

``` php
//TODO
```

##### `update_post_term_count( $terms, $taxonomy )`

This method gets called whenever a term is added/edited/deleted from WordPress environment. It will update the post counts for that particular term of a taxonomy.

``` php
@uses do_action() - WordPress Core. Defines custom action hooks for modifications.

@defined edit_term_taxonomy - This is WordPress core action. We are just re-using it for our own custom workflow.
@defined edited_term_taxonomy - This is WordPress core action. We are just re-using it for our own custom workflow.
```

##### `add_attributes_page( $page_slug, $parent_page_slug = '', $post_type = '', $page_cap = 'manage_options', $attr_cap = array(), $render_type_required = false, $storage_type_required = false, $orderby_required = false, $admin_menu = true )`

```
@param        $page_slug
@param string $parent_page_slug
@param string $post_type
@param string $page_cap
@param array  $attr_cap
@param bool   $render_type_required
@param bool   $storage_type_required
@param bool   $orderby_required
@param bool   $admin_menu

@uses RT_Attributes::register_attribute_mappings() - Maps all the attributes with post types.
```

##### `register_attribute_menu()`

This method registers an attributes menu page which will list all the available attributes that can be linked to any post types as per need.

``` php
//TODO
```

##### `render_attributes_page()`

Renders the Attributes Listing Page

``` php
@uses RT_Attributes::perform_action() - Perform actions based on query string passed
@uses wp_redirect() - Redirects back to the attributes UI
@uses RT_Attributes::edit_attribute_ui() - Show Edit Atributes UI
@uses RT_Attributes::add_attribute_ui() - Show Add Attribute UI
```

##### `edit_attribute_ui()`

Renders Edit Attribute UI

##### `add_attribute_ui()`

Renders Add Attribute UI

##### `sanitize_taxonomy( $taxonomy )`

Sanitizes taxonomy name

``` php
@param $taxonomy

@return mixed|string
```

##### `get_taxonomy_name( $taxonomy )`

Get Taxonomy Name

``` php
@param $attribute

@return string

@uses RT_Attributes::sanitize_taxonomy() - To sanitize the name
```

##### `error_check( $action = '', $attribute_id = '', $attribute_name = '', $attribute_render_type = '', $attribute_store_as = 'taxonomy' )`

Validates Attribute Name, Render Type & Attribute Storage Type

``` php
@param string $action
@param string $attribute_id
@param string $attribute_name
@param string $attribute_render_type
@param string $attribute_store_as

@return string|void
```

##### `add_attribute_relations( $attribute_id, $post_types )`

Add Attribute to Post Types Mapping. Save it to database

``` php
@param $attribute_id
@param $post_types


@uses RT_Attributes_Relationship_Model::add_relation()

@defined rt_attributes_relations_added - Custom action hook when relation is added
```

##### `update_attribute_relations( $attribute_id, $post_types )`

``` php
@param $attribute_id
@param $post_types

@uses RT_Attributes_Relationship_Model::get_relations_by_attribute()
@uses RT_Attributes_Relationship_Model::delete_relation()

@defined rt_attributes_relations_deleted - Custom action hook when relation is defined
```

##### `add_attribute( $attribute_label, $attribute_name, $attribute_store_as = 'taxonomy', $attribute_render_type = '', $attribute_orderby = '' )`

Add attribute in database

``` php
@param $attribute_label
@param $attribute_name
@param $attribute_store_as
@param $attribute_render_type
@param $attribute_orderby

@uses RT_Attributes_Model::add_attribute()
```

##### `edit_attribute( $attribute_id, $attribute_label, $attribute_name, $attribute_store_as = 'taxonomy', $attribute_render_type = '', $attribute_orderby = '' )`

Edit attribute in database

``` php
@param $attribute_id
@param $attribute_label
@param $attribute_name
@param $attribute_store_as
@param $attribute_render_type
@param $attribute_orderby

@uses RT_Attributes_Model::update_attribute()
```

##### `perform_action()`

Performs Add, Save, Delete Attributes

``` php
@uses RT_Attributes::sanitize_taxonomy() - To sanitize the name
@uses RT_Attributes::error_check() - Checks for error in taxonomy name
@uses RT_Attributes::add_attribute() - Adds new attribute
@uses RT_Attributes::add_attribute_relations() 0 Adds new attribute relation
@uses RT_Attributes::edit_attribute() - Edits  existing attribute
@uses RT_Attributes_Relationship_Model::get_attribute_name()
@uses RT_Attributes::get_taxonomy_name()
@uses RT_Attributes::update_attribute_relations()


@defined rt_wp_attribute_added - Custom Action hook defined when new attribute is added
@defined rt_wp_attribute_updated - Custom action hook defined when attiribute is updated
```

#### Hooks

##### Actions

// TODO

##### Filters

// TODO

### Example

``` php
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
