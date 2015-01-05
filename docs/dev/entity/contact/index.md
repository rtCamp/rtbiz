Contact
=======

### `Class Rt_Contact`

This class is inherited from `Rt_Entity`.

**Attribute**

- `public $email_key = 'contact_email';` : Email meta key in use
- `public $website_url_key = 'contact_website';` : Website meta key in use
- `public $user_id_key = 'contact_user_id';` : WP User ID meta key in use. (This will be deprecated soon, once we map this using Posts2Posts plugin.)
- `static $our_team_mate_key = 'is_our_team_mate';` : Our Team mate check meta key in use. This will also get deprecated once, new person type taxonomy is introduced.

**Methods**

- `entity_meta_boxes()` : Overriden from `Rt_Entity` parent class. Adds new metabox for WP_User mapping. (Will be removed once, P2P connection is added.)
- `render_wp_user_details_meta_box()` : Renders metabox for WP_User mapping. (Will be removed once P2P connection is added.)
- `person_meta_box()` : Our team mate meta box is added in this function. But this will be removed once new taxonomy for Person category is added.
- `setup_meta_fields()` : This method defines all the meta fields needed for `Person` entity.
- `print_metabox_js()` : Overriden from `Rt_Entity` parent class.
- `save_meta_values()` : Overriden from `Rt_Entity` parent class.
- `post_table_columns()` : Adds additional columns for Person list table. ( Phone Number, Country & Organization ). Overriden from `Rt_Entity` parent class.
- `manage_post_table_columns()` : Manages additional columns mentioned above.
- `add_person()` : Adds new person in the database.
- `get_by_email()` : Search person by email. Returns Person post object if found and empty array if not found.
- `get_contact_for_wp_user()` : Search person object for given WP_User ID. Returns person object if found otherwise empty array.
- `get_wp_user_for_person()` : Search for WP_User for given person id. Returns WP_User ID if found otherwise returns `false`.
- `get_employees()` : Returns all the employees from all the persons.
- `get_clients()` : Returns all the employees from all the persons.
- `person_create_for_wp_user()` : Creates Person object for given WP_User.

**Hooks**

- **Actions**

	- `rt_biz_person_meta_box` : Any additional metabox can be added using this action, if needed.

- **Filters**

	- `rt_biz_person_meta_fields` : Additional custom fields can be added using this filter.
