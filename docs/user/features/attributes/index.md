Attributes
==========

Attributes library is useful to assign dynamic taxonomy / meta attributes to WordPress post types. These attributes can be used to further categorize the posts conveniently.

It has got basic CRUD operations of attributes.

User can :

- add new attributes
- update existing ones
- remove them if not required.

All these attributes can be assigned to one or more post types. So this library will dynamically register all the linked attributes with post type; if it is defined as a taxonomy otherwise it can be used as a post meta.

For example, Consider a scenario of Wiki Page for documentation of software products.

Now each wiki page can have multiple attributes such as :

- it's related to which product
- does it require any pre-requisites
- does it have any external dependency

etc and so on.

Now These entities such as *Product*, *Pre-requisite* or *External Dependency* are attributes of that page. These attributes can be linked with the Wiki Page post type and then these attributes will hold different values for different pages which are eventually either term values or meta values for respective attributes.
