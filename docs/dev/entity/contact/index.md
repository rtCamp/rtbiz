Contact
=======

### `Class Rt_Contact`

This class is inherited from `Rt_Entity`.

#### Attribute

##### `public $email_key = 'contact_email';`

This meta key is used to store contact email in meta table.

##### `public $primary_email_key = 'contact_primary_email';`

This meta key is for primary email of a contact.

##### `public $website_url_key = 'contact_website';`

A meta key in use to store contact website in meta table.

##### `static $user_category_taxonomy = 'rt-contact-group';`

A string constant for slug of Contact Category taxonomy.

##### `static $employees_category_slug = 'employees';`

A string constant for slug of *Employees* term.

##### `static $customer_category_slug = 'customers';`

A string constant for slug of *Customers* term.

##### `static $vendor_category_slug = 'vendors';`

A string constant for slug of *Vendors* term.

#### Methods

##### `__construct()`

Constructor method for the class. It is initializing required tasks for Contact entity such as *WP User to Contact* sync, taxonomy registration, adding default taxonomy terms, P2P connections, metaboxes and other miscellaneous tasks.

``` php
@uses Rt_Entity::__construct()			- rtBiz Core. Calling out parent constructor.
@uses Rt_Contact::setup_meta_fields()	- rtBiz Core. Define meta fields to save for Contact.
@uses add_action()						- WordPress Core. Adding init action to initialize contact entity and other tasks.
@uses add_filter()						- WordPress Core. Useful for some of the miscellaneous tasks.
```

##### `exported_admin_notice()`

Admin notice method to display the user message when WordPress users are exported to rtBiz contacts.

``` php
@uses _n()					- WordPress Core. Internationalization function
@uses number_format_i18n()	- WordPress Core. Internationalization function
```

##### `callback_rtbiz_bulk_action()`

Call back method for bulk export action from users.php to export rtbiz contacts

``` php
@uses check_admin_referer()				- WordPress core. To verify bulk action.
@uses Rt_Contact::export_biz_contacts()	- rtBiz core. Exports WP user to rtBiz contact.
@uses add_query_arg()					- WordPress core. Build redirection URL.
@uses wp_redirect()						- WordPress core. To redirect user back to Users page.
```

##### `add_export_user_bulk_action()`

This method adds bulk action option to export WordPress users to rtBiz Contacts via JS hack.

##### `rtbiz_export_contact()`

AJAX callback for single user export from user.php

``` php
@uses check_ajax_referer()				- WordPress core. To verify export action for user.
@uses Rt_contact::export_biz_contact()	- rtBiz Core. Exports WP user to rtBiz Contact.
@uses get_post()						- WordPress core. To check for existing contact with same email.
@uses get_edit_post_link()				- WordPress core. To get edit post link for contact.
```

##### `rtbiz_export_all_contacts()`

AJAX callback to export all WordPress users to rtBiz Contacts.

``` php
@uses check_ajax_referer() - WordPress Core. To verify export action.
@uses Rt_Contact::export_biz_contacts() - rtBiz Core. Exports WordPress Users to rtBiz Contacts for given array of IDs.
```

##### `export_biz_contacts( $ids )`

This method exports WP users to rtBiz contacts in bulk.

``` php
@param $ids array - Array of WordPress User IDs

@return int - Count of exported users.

@uses get_users() - WordPress Core. To fetch the users.
@uses wp_list_pluck() - WordPress Core. To extract User ID from WordPress User object.
@uses rt_biz_get_contact_for_wp_user() - rtBiz Core. Get mapped rtBiz Contact for give WP User ID.
@uses Rt_Contact::export_biz_contact() - rtBiz Core. Export given User to rtBiz Contact.
```

##### `export_biz_contact()`

This method exports single WP user to rtbiz contact. It will check if contact exists then it will map or else create new contact and will map with p2p.

``` php
@param $id int - WordPress User ID

@return mixed - rtBiz Contact ID if a contact is imported successfully otherwise null.

@uses get_user_by() - WordPress core. Get user object from ID.
@uses get_posts() - WordPress core. Fetches contacts posts to verify if contact exists or not.
@uses biz_is_primary_email_unique() - rtBiz Core. To verify unique email.
@uses rt_biz_add_contact() - rtBiz core. Adds new contact.
@uses Rt_Contact::connect_contact_to_user() - connects WP User to rtBiz Contact.
```

##### `manage_export_user_columns( $value, $column_name, $id )`

Call back method to manage user columns for rtBiz Contact Export.

``` php
@param $value string - Value to display in the column.
@param $column_name string - Column name to identify
@param $id int - User ID

@return string - Markup string to display in the column.

@uses rt_biz_get_contact_for_wp_user() - rtBiz core. Get rtBiz contact for given WP User ID
@uses wp_create_nonce() - WordPrecc Core. Creates nonce for export action.
```

##### `check_primary_email_for_admin_notice()`

This method checks for Unique Primary Email & Empty Primary Email for rtBiz Contact and adds appropriate admin notices on the screen.

``` php
@uses rt_biz_get_contact_post_type() - rtBiz core. To check for needed post type.
@uses get_user_meta() - WordPress core. Fetches email id from user meta table.
@uses get_current_user_id() - WordPress core. Fetches User ID of currently logged in user.
@uses add_action() - Adds action method to display admin notice.
@uses delete_user_meta() - deletes user meta value after displaying the admin notice.
```

##### `setup_meta_fields()`

This method defines all the meta fields needed for `Person` entity.

##### `print_metabox_js()`

Overriden from `Rt_Entity` parent class.

##### `save_meta_values()`

Overriden from `Rt_Entity` parent class.

##### `post_table_columns()`

Adds additional columns for Person list table. ( Phone Number, Country & Organization ). Overriden from `Rt_Entity` parent class.

##### `manage_post_table_columns()`

Manages additional columns mentioned above.

##### `add_person()`

Adds new person in the database.

##### `get_by_email()`

Search person by email. Returns Person post object if found and empty array if not found.

##### `get_contact_for_wp_user()`

Search person object for given WP_User ID. Returns person object if found otherwise empty array.

##### `get_wp_user_for_person()`

Search for WP_User for given person id. Returns WP_User ID if found otherwise returns `false`.

##### `get_employees()`

Returns all the employees from all the persons.

##### `get_clients()`

Returns all the employees from all the persons.

##### `person_create_for_wp_user()`

Creates Person object for given WP_User.

#### Hooks

##### Actions

###### `rt_biz_person_meta_box`

Any additional metabox can be added using this action, if needed.

##### Filters

###### `rt_biz_person_meta_fields`

Additional custom fields can be added using this filter.
