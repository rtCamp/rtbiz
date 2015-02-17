Attributes
==========

### `Class Rt_Biz_Attributes`

This class initiates Attributes library used in rtBiz and adds a few functionality around custom attributes.

#### Class Attributes

##### `public static $attributes_page_slug = 'rtbiz-attributes';`

This variable is the page slug string for attributes' page in rtBiz plugin.

#### Methods

##### `__construct()`

Construcor method for the class. It initializes the functionality of attributes module.

``` php
@uses add_action() - Adds action methods for WordPress actin hooks.
```

##### `init_attributes()`

This method calls for Attributes module from rtLib library. It adds up an admin sub menu in rtBiz plugin.

``` php
@uses RT_Attributes() - Attributes library constructor from library.
@uses rt_biz_get_access_role_cap() - Gets user role capebilities.
@uses RT_Attributes::add_attributes_page() - Registers an admin submenu page for rtBiz.
```

##### `convert_term_id_to_term_slug_for_attributes_filter( $query )`

This method converts term id into term slug and pass it on to WP_Query. So eventually it filters out posts based on passes attribute term id.

``` php
@params $query - Default current query for WordPress

@uses rt_biz_get_contact_post_type() - Get rtBiz contact post type
@uses rt_biz_get_company_post_type() - Get rtBiz Company post type
@uses RT_Attributes_Model() - Attributes model class for DB operations.
@uses RT_Attributes_Relationship_Model() - Attributes Relationship model class for DB operations.
@uses RT_Attributes_Relationship_Model::get_relations_by_post_type() - Get relations based on post type.
@uses RT_Attributes_Model::get_attribute() - get attribute detaild for given attribute ID.
@uses RT_Attributes::get_taxonomy_name() - Get taxonomy name from attribute name.
```

##### `restrict_entity_by_attributes()`

This method adds different filters based on new attributes defined in rtBiz environment. So that user can filter the entities based on defined attributes.

``` php
@uses rt_biz_get_contact_post_type() - Get rtBiz contact post type
@uses rt_biz_get_company_post_type() - Get rtBiz Company post type
@uses RT_Attributes_Model() - Attributes model class for DB operations.
@uses RT_Attributes_Relationship_Model() - Attributes Relationship model class for DB operations.
@uses RT_Attributes_Relationship_Model::get_relations_by_post_type() - Get relations based on post type.
@uses RT_Attributes_Model::get_attribute() - get attribute detaild for given attribute ID.
@uses RT_Attributes::get_taxonomy_name() - Get taxonomy name from attribute name.
@uses wp_dropdown_categories() - Displays a dropdown for given categories.
```

#### Hooks

##### Actions

##### Filters
