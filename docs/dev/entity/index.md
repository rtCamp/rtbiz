Entity
======

Basically rtBiz deals with two types on entities as of now.

1. Contact
2. Company

Each of the above entity is a Custom Post Type in WordPress.

We have build the base of this plugin such as this entities can be extended or even a new entity can be added later on if required.

We abstracted a parent class for all the entities in rtBiz family named `Rt_Entity`. Other specific entities will inherit this class and automatically get all the common methods/attributes on its own.

So structure would go as follows:

    |-Rt_Entity
        |-Rt_Contact
        |-Rt_Company

### `Class Rt_Entity`

#### Attributes

Common attributes that are defined in `Rt_Entity` class:

##### `$enabled_post_types`

An array of post types with which the entity is to be linked.

##### `$post_type`

Slug of post type for the entity

##### `$labels`

An array of labels for entity post type

##### `$meta_fields`

An array that holds all the custom meta fields that are required for entity

##### `$meta_key_prefix`

Prefix string for all the meta keys of entity.

#### Methods

A few common methods that are defined in `Rt_Entity` class:

##### `__construct()`

Constructor method for the class.

``` php
@uses Rt_Entity::hooks()    - rtBiz Core. Initializes hooks for this class.
```

##### `init_entity()`

Initializes the post_type in WordPress.

``` php
@uses Rt_Entity::register_post_type to register the post type.
```

##### `hooks()`

Common hooks that are needed to be added for entity

``` php
@uses is_admin()                - WordPress Core. To check for admin section.
@uses add_action()              - WordPress Core.
@uses add_filter()              - WordPress Core.
@uses rtbiz_get_redux_settings()  - rtBiz Core. To check for offerings settings.
@uses do_action()               - WordPress Core. To introduce an action hook.

@defined rt_biz_entity_hooks    - Custom WP Action. This can be used to add extra hooks from other plugins/themes.
```

*Example*

``` php
add_action( 'rt_biz_entity_hooks', 'my_entity_hook' );
function my_entity_hook( $entity_obj ) {
    if ( $entity_obj->post_type == 'rt_contact' ) {
        add_action( 'init', 'my_custom_init' );
    }
}

function my_custom_init() {
    echo "Yo ! I got into init function.";
}
```

##### `edit_offering_columns( $offering_columns )`

This method adds additional column into the list table of Offerings Taxonomy. It adds those columns for CPT with which Offerings are linked.

``` php
@param $offering_columns array      - An array of all the columns with their labels for Offerings Taxonomy List Table.
@return $offering_columns array     - An updated array of columns.

@uses apply_filter()                - WordPress Core. To introduce a filter hook.

@defined rt_biz_offerings_columns   - Custom WP Filter. This can be used to add/remove additional column in Offerings List Table.
```

*Example*

``` php
add_filter( 'rt_biz_offerings_columns', 'my_offerings_columns' );

function my_offerings_columns( $columns ) {
    $columns[ 'my-custom-column' ] = __( 'My Custom Column' );
    return $columns;
}
```

##### `add_offering_column_content( $content, $column_name, $term_id )`

This method is used to put content for for each row for all the custom columns that we have added using the above method.

``` php
@param $content string                      - Content to be displayed in the row for column
@param $column_name string                  - Column Slug
@param $term_id int                         - Term ID of the Row

@return $content string                     - New/Updated content for the Term row.

@uses apply_filter()                        - WordPress Core. To add a filter hook.

@defined rt_biz_offering_column_content     - Custom WP Filter. This is used to add custom content in Offerings table column.
```

*Example*

``` php
add_filter( 'rt_biz_offering_column_content', 'my_offering_column_content' );

function my_offering_column_content( $content, $column_name, $term_id ) {
    if ( $column_name == 'my-custom-column' ) {
        $content = 'Yo ! This is new content for new custom column!';
    }
    return $content;
}
```

##### `skip_feed_comments( $where )`

This method filters out `rtBot` comments from the WordPress feeds.

``` php
@param $where string    - Default where clauses coming from WodPress Comments query.

@return @where string   - Updated where clauses
```

##### `preprocess_comment_handler( $commentdata )`

This method filters out `rtBot` comments from default comments query, Dashbord Widgets & comments list table.

``` php
@param $commentdata object      - Comment Object coming from WordPress Core

@return $commentdata object     - Updated comment object prepared for query.
```

##### `save_old_data( $post_id )`

This method gets called when a entity is saved from admin panel. It is used to store the text-diff between previous values and new values of post attributes & meta fields. It is useful for rtBot Comments.

``` php
@param $post_id int                         - Post ID of Entity

@uses get_post()                            - WordPress Core. To get the current post
@uses rtbiz_text_diff()                     - rtBiz Core. To generate the text-diff for two values
@uses rt_biz_get_contact_post_type()        - rtBiz Core. Fetches contact post type
@uses rt_biz_get_company_post_type()        - rtBiz Core. Fetches company post type
@uses rtbiz_is_primary_email_unique()         - rtBiz Core. Checks for duplicate email address
@uses rtbiz_is_primary_email_unique_company() - rtBiz Core. Checks for duplicate email address
@uses Rt_Entity::get_meta()                 - rtBiz Core. fetches meta value for the given key.
@uses wp_get_current_user()                 - WordPress Core. Gets current logged in use.
@uses rtbiz_get_redux_settings()              - rtBiz Core. Fetches Biz settings to get the label.
@uses wp_insert_comment()                   - WordPress Core. Inserts rtBot comment.
```

##### `change_publish_button( $translation, $text )`

Publish button label for entity changed to "Add", since this will not be typical WordPress post that we want to publish

``` php
@param $translation string  - Translated String
@param $text string         - Actual String

@return $translation string - Translated String.

@uses get_post_type()       - WordPress Core. To fetch the current post type
```

##### `enqueue_scripts_styles()`

Loads all the required Scripts/Styles. Though this will be used very less, since the aim will be to use WordPress Native elements at most.

``` php
@uses wp_script_is()        - WordPress Core. To check if script is already enqueued or not.
@uses wp_enqueue_script()   - WordPress Core. To enqueue script.
@uses wp_enqueue_style()    - WordPress Core. To enqueue style.
```

##### `entity_meta_boxes()`

Adds any metaboxes on Edit Post page for entity.

``` php
@uses add_meta_box()    - WordPress Core. To add custom metabox for CPT of entity.
@uses do_action()       - WordPress Core. To define custom WP action.

@defined rt_biz_entity_meta_boxes   - Custom WP Action. This action can be used to extra metaboxes from other plugins/themes.
```

*Example*

``` php
add_action( 'rt_biz_entity_meta_boxes', 'my_custom_metabox' );

function my_custom_metabox( $entity_post_type ) {
    if ( $entity_post_type == 'rt_contact' ) {
        add_meta_box( 'rt-biz-custom-metaboz', __( 'Custom Metabox' ), 'render_custom_metabox', $entity_post_type, 'side', 'default' );
    }
}

function render_custom_metabox( $post ) {
    echo "Yo! Custom Metabox Got Renedered !";
}
```

##### `render_assign_to_meta_box( $post )`

This method renders metabox of Entity's Assignee.

``` php
@param $post object     - WP Post object

@uses rt_biz_get_entity_meta()      - rtBiz Core. Fetches entity meta for given key.
@uses get_user_by()                 - WordPress Core. Get user by given search key.
@uses get_avatar()                  - WordPress Core. Gets user gravatar.
@uses get_edit_user_link()          - WordPress Core. Gets edit link of user profile page.
@uses rt_biz_get_module_employee()  - rtBiz Core. Fetches Employees from Contacts pool.
@uses do_action()                   - WordPress Core. To define new WP Action.

@defined rt_biz_assign_to_metabox_after - Custom WP Action. This action can be used to add custom mark up in assignee metabox for entity.
```

*Example*

``` php
add_action( 'rt_biz_assign_to_metabox_after', 'my_custom_assign_to_metabox_after' );

function my_custom_assign_to_metabox_after( $post, $entity_post_type ) {
    if ( $entity_post_type == 'rt_contact' ) {
        echo "Yo ! Assignee Metabox Additional markup is added here !";
    }
}
```

##### `save_meta_assign_to( $post )`

This method saves assignee value for Entity.

``` php
@param $post object     - WP Post Object

$uses rt_biz_update_entity_meta()   - rtBiz Core. Updates meta value of an entity for given key.
```

##### `render_additional_details_meta_box( $post )`

Common metabox that will be rendered for Entity. This will give you additional general information about the entity.

``` php
@param $post object     - WP Post Object

@uses do_action()                   - WordPress Core. To define new actions.
@uses wp_get_post_terms()           - WordPress Core. Gets post terms for the given post id & category.
@uses apply_filters()               - WordPress Core. To define new filters.
@uses Rt_Entity::get_meta()         - rtBiz Core. Gets meta value of entity for given key.
@uses wp_nonce_field()              - WordPress Core. Generates a nonce value for the form.
@uses Rt_Entity::print_metabox_js() - rtBiz Core. Prints JS code in script tag if added.

@defined rt_biz_before_render_meta_fields   - This action is used when additional markup needs to be added before meta fields.
@defined rt_entity_fields_loop_single_field - This filter is used when any field attributes needs to be changed/filtered.
@defined rt_biz_after_render_meta_fields    - This action is useful when extra markup is required to add after meta fields.
@defined rt_biz_print_metabox_js            - This action lets you add any additional JS script in the markup.
```

*Example*

``` php
add_action( 'rt_biz_before_render_meta_fields', 'my_custom_before_meta_fields' );
add_action( 'rt_biz_after_render_meta_fields', 'my_custom_after_meta_fields' );
add_action( 'rt_biz_print_metabox_js', 'my_custom_metabox_js' );
add_filter( 'rt_entity_fields_loop_single_field', 'my_custom_loop_sigle_field' )

function my_custom_before_meta_fields( $post, $entity_object ) {
    echo "Yo! This gets added at the start of meta field markup !";
}

function my_custom_after_meta_fields( $post, $entity_object ) {
    echo "Yo! This gets added at the end of meta field markup !";
}

function my_custom_metabox_js( $post, $entity_object ) {
    echo "<script>console.log("Yo ! This is the right place to add custom JS !");</script>";
}

function my_custom_loop_sigle_field( $field ) {
    // Remove contact fax field
    if ( $field['key'] == 'contact_fax' ) {
        return array();
    }
    return $field;
}
```

##### `print_metabox_js()`

Prints JS related to Metabox, if at all required. This method can be overridden in child classes.

##### `save_entity_details( $post_id )`

Saves any additional details from the metabox, if they are used.

``` php
@param $post_id int - WordPress Post ID

@uses wp_verify_nonce()                 - WordPress Core. This verifies the valid nonce.
@uses Rt_Entity::save_meta_assign_to()  - rtBiz Core. Save assignee of entity.
@uses Rt_Entity::save_meta_values()     - rtBiz Core. Save other meta values of entity.
```

##### `save_meta_values( $post_id )`

Saves any meta values, if they are used.

``` php
@param $post_id int     - WordPress Post ID

@uses do_action()   - WordPress Core. To define new action.

@defined rt_biz_save_entity_meta    - This action is used to save any extra meta values for entity.
```

##### `post_table_columns( $columns )`

This defines any custom columns for WordPress List View page of Entity

``` php
@param $columns array       - Columns array for Entity List Table.

@uses apply_filters()       - To add new WP filter hook.

@defined rt_entity_columns  - This filter is used to add/update custom columns for Entity List Table.
```

##### `manage_post_table_columns( $column, $post_id )`

This method lets you add custom data for the custom column that you defined in above method.

``` php
@param $column string   - Column Slug
@param $post_id int     - WordPress Post ID

@uses do_action()       - WordPress Core. To add new action hook.

@defined rt_entity_manage_columns   - This is used to manage column value for Entity List Table.
```

##### `init_connection( $post_type, $label )`

rtBiz gives you power to connect this entities with any other post type of your choice. This methods initializes this connection.

``` php
@param $post_type string    - WordPress Post Type to connect with entity.
@param $label string        - Label to define for P2P connection

@uses add_action()  - WordPress Core. Initialize P2P Connection.
```

##### `create_connection()`

This method creates the relation between two posts types mentioned in above method.

``` php
@uses p2p_register_connection_type()    - P2P Plugin. Registers new connection between two entities.
```

##### `clear_post_connections_to_entity()`

Wipe out any existing connections between two post objects of posts types that are explained above.

``` php
@param $post_type string    - WordPress Post Type
@param $from mixed          - Either Post Object or Post ID to clear the P2P connection

@uses p2p_delete_connections()  - P2P Plugin. deletes existing connection for given post type and post.
```

##### `connect_post_to_entity( $post_type, $from = '', $to = '' )`

Links two post object of given post types.

``` php
@param $post_type string    - WordPress Post Type
@param $from mixed          - WordPress Post ID or Post Object
@param $to mixed            - WordPress Post ID or Post Object

@uses p2p_connection_exists()   - P2P Plugin. Check if connection exists or not.
@uses p2p_create_connection()   - P2P Plugin. creates new connection between two entities.
```

##### `connection_to_string( $post_id, $connection, $term_seperator = ' , ' )`

Converts a connection object into a string. Used for getting a difference between two relations.

``` php
@param $post_id int             - WordPress Post ID
@param $connection string       - Connection Type
@param $term_seperator string   - seperator charater. Default: ' , '

@uses get_post()    - WordPress Core.
@uses get_posts()   - WordPress Core.
```

##### `register_post_type( $name, $labels )`

Register a WordPress post type for entity.

``` php
@param $name string     - Post type slug
@param $labels array    - Array of Label Strings for Post Type

@uses register_post_type()  - WordPress Core.
```

##### `get_posts_for_entity( $post_id, $post_type, $fetch_entity )`

This fetches list of posts connected with a single post object.

``` php
@param $post_id int         - WordPress Post ID
@param $post_type string    - Post Type to check with
@param $fetch_entity bool   - entity flag. Default: false. If true is passed then this function will return list of entities. Otherwise list of connected posts.

@uses get_posts()   - WordPress Core.
```

##### `get_post_type_capabilities()`

Gives you an array of required capabilities for a entity. Used in rtBiz ACL.

``` php
@return array - Array of available capabilities for Entity.
```

##### `add_meta( $id, $key, $value, $unique )`

Adds a meta value.

``` php
@param $id int          - WordPress Post ID
@param $key string      - Meta Key
@param $value string    - Meta value
@param $unique bool     - Whether the same key should not be added. Default false.

@uses add_post_meta()   - WordPress Core.
```

##### `get_meta( $id, $key, $single )`

Gets a meta value.

``` php
@param $id int      - WordPress Post ID
@param $key string  - Meta Key
@param $single bool - Whether to return a single value. Default false.

@uses get_post_meta()   - WordPress Core.

@return mixed - Meta value of entity for the given meta key.
```

##### `update_meta( $id, $key, $value, $prev_value )`

Updates a meta value.

``` php
@param $id int              - WordPress Post Id
@param $key string          - Meta Key
@param $value mixed         - New Meta Value
@param $prev_value mixed    - Old Meta value

@uses update_post_meta()    - WordPress Core.
```

##### `delete_meta( $id, $key, $value )`

Deletes a meta value.

``` php
@param $id int      - WordPress Post ID
@param $key string  - Meta Key
@param $value mixed - Meta value

@uses delete_post_meta()    - WordPress Core.
```

##### `search( $query, $args )`

Search for entity for given term

``` php
@param $query string    - Keyword to search
@param $args array      - WordPress WP_Query arguments.

@uses WP_Query  - WordPress Core.
```


#### Hooks

Hooks available for this class:

##### Actions

###### `rt_biz_entity_hooks`

###### `rt_biz_entity_meta_boxes`

###### `rt_biz_before_render_meta_fields`

###### `rt_biz_after_render_meta_fields`

###### `rt_biz_print_metabox_js`

###### `rt_biz_save_entity_meta`

###### `rt_entity_manage_columns`


##### Filters

###### `rt_entity_fields_loop_single_field`

###### `rt_entity_columns`


**NOTE:** You can check the methods documentation above on how to make use of these hooks ( actions and filters ).
