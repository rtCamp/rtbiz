Access Control
==============

### `Class Rt_Access_Control`

This is the main class that handles the access control given to all the users within rtBiz environment.

This class works as the base for all the permissions, user access for rtBiz family plugins.
All the plugins including rtBiz will register with this class for the Access Control that it requires & from there Rt_Access_Controll will take it over.

It will map a matrix for permissions for Plugin Modules => User Groups (Departments) and from there user access can be set as per requirement.

#### Attributes

##### `public static $permissions;`

These are more of the permission roles for the access control. By default, rtBiz plugin sets following permissions to start with. It can be changed or more permissions can be added as per need from any other plugin/theme.

``` php
self::$permissions = apply_filters( 'rt_biz_permissions', array(
	'no_access' => array(
		'value' => 0,
		'name' => __( 'No Role' ),
		'tooltip' => __( 'No Access' ),
	),
	'author' => array(
		'value' => 10,
		'name' => __( 'Author' ),
		'tooltip' => __( 'Read/Write (Self)' ),
	),
	'editor' => array(
		'value' => 20,
		'name' => __( 'Editor' ),
		'tooltip' => __( 'Read/Write (Everything)' ),
	),
	'admin' => array(
		'value' => 30,
		'name' => __( 'Admin' ),
		'tooltip' => __( 'Read/Write (Everything) + Settings' ),
	),
) );
```

##### `public static $modules = array();`

An array of registered plugin modules with rtBiz for Rt_Access_Control

#### Methods

##### `__construct()`

Constructor method for class.

``` php
@uses add_action() - WordPress core. Adds new action for existing action hooks.
@uses add_filter() - WordPress core. Adds new filter for existing action hooks.
```

##### `filter_caps( $all_caps, $required_caps, $args, $user )`

This is the main crux method of rtBiz ACL. This method actually defines who should have how much access of what.

``` php
@param $all_caps array - An array of all the role's capabilities
@param $required_caps array - Actual capabilities for meta capability. This is the array for which capability needs to be checked.
@param $args array - Optional parameters passed to `has_cap()`, typically object ID
@param $user object - The WordPress user object

@return $all_caps array - An updated array of capabilities and their values with filtered access control.

@uses Rt_Access_Control::get_admin_post_caps() - rtBiz core. Get all valid capabilities for admin role for given post type.
@uses rt_biz_get_contact_for_wp_user() - rtBiz Core. get rtBiz contact for given WP User ID.
@uses get_post_meta() - WordPress core. Gets meta value for given key for a contact. This is needed for Profile Level permissions.
@uses Rt_Access_Control::get_role_key() - rtBiz core. Gets permission slug from the given permission value.
@uses Rt_Access_Control::get_capability_from_access_role() - rtBiz core. To get the capability slug for given module and given role.
@uses get_site_option() - WordPress core. To fetch site options. For Group level permissions.
@uses rt_biz_get_user_department() - rtBiz core. Gets department for the current user.
```

##### `init_acl()`

Initilize ACL on `plugins_loaded` with 15 priority. So that before this gets executed; other addon plugins get chance to hook into it and register themselves.

``` php
@uses apply_filters() - WordPress Core. To define custom wordpress filter hooks.

@defined rt_biz_modules - Custom Filter to register new plugin modules with rtBiz for ACL.
@defined rt_biz_permissions - Custom Filter to change default rtBiz ACL permissions.
```

*Example*

``` php
/**
 *  Filter for other addons to register.
 *  Array Structure is array( 'module_slug' => 'label' )
 */
add_action( 'plugins_loaded', 'my_custom_init', 15 );

function my_custom_init() {
	add_filter( 'rt_biz_modules', 'my_custom_module' )
	add_filter( 'rt_biz_permissions', 'my_custom_permission' );
}

function my_custom_module( $modules ) {
	$biz_module = array(
		RT_BIZ_TEXT_DOMAIN => array(
			'label' => __( 'rtBiz' ),
			'post_types' => array( 'post', 'page', 'rt_contact', 'rt_lead', ),
		),
	);
	$modules[] = $biz_module;

	return $modules
}

function my_custom_permission( $permissions ) {
	$permissions['moderator'] = array(
		'value' => 25,
		'name' => __( 'Moderator' ),
		'tooltip' => __( 'Read/Write (Everything) + A few of the settings.' ),
	);
	return $permissions;
}
```

##### `get_capability_from_access_role( $module_key, $role )`

This method return the capability name text if you pass the module_slug & role.
It will return proper cap if passed module_key is registered with Rt_Access_Control & passed role is valid.

Other wise it will return empty string.

Example :

``` php
$module_key = 'rt_biz', $role = 'author'
$access_role = Rt_Access_Control::get_capability_from_access_role( $module_key, $role );
// Return String - 'rt_biz_author'
```

``` php
@param $module_key string - Slug for module
@param $role string - Slug for User role.

@return string - capability name for given module & role. Empty String if invalid module / role.

@uses rt_biz_sanitize_module_key() - rtbiz core. Sanitizes module slug string.
```

##### `get_role_key()`

This method returns role slug string if valid numeric role value is passed.

``` php
@param $role_value int - Numeric role value.

@return string - slug string of valid role. Empty string if invalid value is passed.
```

##### `get_no_access_post_caps( $post_type )`

Returns an array of all the available capabilities for `no_access` role for given post type.

``` php
@param $post_type string - Post type slug

@return array - Array of all the available capabilities for `no_access` role for given post type.
```

##### `get_author_post_caps( $post_type )`

Returns an array of all the available capabilities for `author` role for given post type.

``` php
@param $post_type string - Post type slug

@return array - Array of all the available capabilities for `author` role for given post type.
```

##### `get_editor_post_caps( $post_type )`

Returns an array of all the available capabilities for `editor` role for given post type.

``` php
@param $post_type string - Post type slug

@return array - Array of all the available capabilities for `editor` role for given post type.
```

##### `get_admin_post_caps( $post_type )`

Returns an array of all the available capabilities for `admin` role for given post type.

``` php
@param $post_type string - Post type slug

@return array - Array of all the available capabilities for `admin` role for given post type.
```

##### `get_module_users( $module_key, $category_slug )`

Fetches all the users from a particular module and user category if, term slug is passed.

``` php
@param $module_key - Module Slug
@param $category_slug - Terms slug for User category.

@return array - An array of sorted & filtered user objects based on passed module key & category slug.

@uses get_users() - Fetch all the users from WordPress admin role.
@uses wpdb::get_results() - Executes custom query to fetch valid users with profile level permissions.
@uses get_post_meta() - To fetch profile level permission of a contact.
@uses has_term() - To check if contact belongs to given user group.
@uses rt_biz_get_wp_user_for_contact() - Get WP user for given contact id.
@uses rt_biz_get_department() - Fetches all existing departments.
@uses get_site_option() - Get group level permissions.
@uses rt_biz_get_module_department_users() - Get all users from given department.
```

##### `save_acl_settings()`

Saves new ACL Permission Matrix to the Database

``` php
@uses update_site_option() - Updates new ACL matrix to database.
```

##### `acl_settings_ui()`

Take Action according to permission saved from the form & then display the ACL Settings UI

``` php
@uses Rt_Access_Control::save_acl_settings() - Saves new ACL Permission Matrix to the Database
@uses rt_biz_get_template() - Loads ACL UI template.
```

##### `profile_level_permission( $post )`

Shows UI for profile level permissions.

``` php
@param $post object - rtBiz Contact Post Object

@uses WP_User() - Get user for given ID.
@uses get_current_user_id() - get current logged in user's ID
@uses WP_User::has_cap() - check for permission to moderate ACL.
@uses p2p_connection_exists() - Check if Contact is mapped with WP User or not.
@uses rt_biz_get_modules() - Get all rtBiz modules.
@uses rt_biz_get_acl_permissions() - Get all rtBiz ACL permissions.
@uses get_post_meta() - get profile level permission for current contact.
@uses biz_get_redux_settings() - Get redux settings to get the module label.
```

##### `save_profile_level_permission( $post_id )`

This method saves profile level permissions for rtBiz Contact.

``` php
@param $post_id int - rtBiz Contact Post ID

@uses current_user_can() - Checks if current user can moderate rtBiz ACL.
@uses update_post_meta() - Updates new permissions into meta.
```

##### `add_department_support()`

Adds department support for rtBiz plugin.

``` php
@param $supports array - supported value.

@return $supports array - updated value of supports variable.
```

#### Hooks

##### Actions

##### Filters

###### `rt_biz_modules`

This filter is used to register new plugin modules to rtBiz for its ACL.

###### `rt_biz_permissions`

This filter is used to add / update default rtBiz permissions.
