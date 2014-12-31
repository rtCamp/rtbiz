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

Common attributes that are defined in `Rt_Entity` class:

- `$enabled_post_types` : An array of post types with which the entity is to be linked.
- `$post_type` : Slug of post type for the entity
- `$labels` : An array of labels for entity post typs
- `$meta_fields` : An array that holds all the custom meta fields that are required for entity
- `$meta_key_prefix` : Prefix string for all the meta keys of entity.

A few common methods that are defined in `Rt_Entity` class:

- `init_entity()` : Initializes the post_type in WordPress.
- `hooks()` : Common hooks that are needed to be added for entity
- `change_publish_button()` : Publish button label for entity changed to "Add", since this will not be typical WordPress post that we want to publish
- `enqueue_scripts_styles()` : Loads all the required Scripts/Styles. Though this will be used very less, since the aim will be to use WordPress Native elements at most.
- `entity_meta_boxes()` : Adds any metaboxes on Edit Post page for entity.
- `render_additional_details_meta_box()` : Common metabox that will be rendered for Entity. This will give you additional general information about the entity.
- `print_metabox_js()` : Prints JS related to Metabox, if at all required.
- `save_entity_details()` : Saves any additional details, if they are used.
- `save_meta_values()` : Saves any meta values, if they are used.
- `post_table_columns()` : This defines any custom columns for WordPress List View page of Entity
- `manage_post_table_columns()` : This method lets you add custom data for the custom column that you defined in above method.
- `init_connection()` : rtBiz gives you power to connect this entities with any other post type of your choice. This methods initializes this connection.
- `create_connection()` : This method creates the relation between two posts types mentioned in above method.
- `clear_post_connections_to_entity()` : Wipe out any existing connections between two post objects of posts types that are explained above.
- `connect_post_to_entity()` : Links two post object of given post types.
- `connection_to_string()` : Converts a connection object into a string. Used for getting a difference between two relations.
- `register_post_type()` : Register a WordPress post type for entity.
- `get_posts_for_entity()` : This fetches list posts connected with a single post object.
- `get_post_type_capabilities()` : Gives you an array of required capabilities for a entity. Used in rtBiz ACL.
- `add_meta()` : Adds a meta value.
- `get_meta()` : Gets a meta value.
- `update_meta()` : Updates a meta value.
- `delete_meta()` : Deletes a meta value.
- `search()` : Search for entity for given term

Hooks available for this class:

- Actions

    - `rt_biz_entity_hooks`
    - `rt_biz_entity_meta_boxes`
    - `rt_biz_before_render_meta_fields`
    - `rt_biz_after_render_meta_fields`
    - `rt_biz_print_metabox_js`
    - `rt_biz_save_entity_meta`
    - `rt_entity_manage_columns`


- Filters

    - `rt_entity_fields_loop_single_field`
    - `rt_entity_columns`
