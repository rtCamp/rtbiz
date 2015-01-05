Access Control
==============

rtBiz Access Control plays very important role in the workflow of this plugin.

rtBiz ACL will define the usability matrix for the users of the system.

It will decide who will get how much access of the system based on the access level a user has got.

- ACL is used to define the access level for a department and hence all the contacts of that department.
- The access levels are defined for the department at the group level.  Any (departmentâs) contact can be assigned to any role (listed below in 4.2) from its profile page, which will be assumed Contactâs Profile Level Access Control.
- Group Level Access Control will always be overridden by Contact Profile Level Access Control. WordPress Site Admins will have the Admin role for all the modules by default.

### ACL Screen Content/ Structure

The ACL is present as a Matrix and has the layout, which maps rtBiz-modules with the departments. Every entry represents access control of a departmentâs contacts to an rtBiz module.

Imagine a matrix as follows and try to understand the following explanation:

 Modules			| Accounting	| Developers	| Designers
--------------------|---------------|---------------|----------
rtBiz				| No Role		| Editor		| Author
rtBiz Addon 1		| Editor		| Admin			| Author
rtBiz Addon 2		| Admin			| Admin			| Admin
rtBiz Addon 3		| Author		| No Role		| No Role
rtBiz Addon 4		| No Role		| Author		| No Role
rtBiz Addon 5		| No Role		| Author		| No Role


- *Columns:* First column lists of all the rtBiz modules that have been registered with rtBiz. Since rtBiz itself is registered for its ACL use,  therefore rtBiz is there as the first item of the column.
- *Rows:* The first row represents the departments. The subsequent rows represents the access level of that departmentâs contacts to a module.

### Roles and Access Levels

- There are four roles that a contact can be assigned: Admin, Editor, Author, No Role.

	- **No Role :** This role revokes all access of a module from the contact.

	- **Author :** This role almost works like the WordPress Author. Contact from a department with Author Role will be able to see modules which are only assigned to him and some of the **author** level settings.

	- **Editor :** Just like WordPress Editor Role, this role will enable contacts to see his own & others' entities as well. Also with this, Editor will be able to have some of the editor level settings.

	- **Admin :** Admin will have the full control of the module including all entities & admin level settings.

- **Note :** This matrix is for Group Level Access Control. It means, any individual user can be assigned to any above roles from his/her WordPress Profile Page which will be assumed Profile Level Access Control. **Group Level Access Control will always be overridden by Profile Level Access Control. WordPress Site Admins will have the Admin role for all the modules by default.**
