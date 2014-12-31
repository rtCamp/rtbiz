Company
=======

### `Class Rt_Company`

Just like `Rt_Contact`, `Rt_Company` is also inherited from `Rt_Entity`.

**Attributes**

**Methods**

- `setup_meta_fields()` : This method defines all the meta fields needed for `Organization` entity.
- `print_metabox_js()` : Overriden from `Rt_Entity` parent class.
- `save_meta_values()` : Overriden from `Rt_Entity` parent class.
- `post_table_columns()` : Adds additional columns for Person list table. ( Country ). Overriden from `Rt_Entity` parent class.
- `manage_post_table_columns()` : Manages additional columns mentioned above.
- `add_organization()` : Adds new organization in the database.
- `get_organizations()` : Returns Organizations post objects if found according to arguments passed and empty array if not found.

**Hooks**

- **Actions**

- **Filters**

	- `rt_biz_organization_meta_fields` : This filter can be used to add additional custom meta fields.
