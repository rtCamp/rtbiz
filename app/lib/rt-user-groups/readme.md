RT User Group
==============

This class gives classifications between user groups or rather departments.

Basically it is a Custom Taxonomy 'user-group' which we are assigning it to WP_User giving the functionality to put WP_Users into groups.

We have also given usable filters to extend this User Group Taxonomy to other post types as well.
So that same taxonomy can be used to classify two different entities in WordPress environment nullifying the headache of maintaining the same set of terms under two different taxonomies.
The terms automatically get shared between different post types / WP_User.

For the WP_User, we have handled it with a special solution.

##Problem :
If we assign same taxonomy to WP_User & a post type, let's say CPT_1, There arises a conflict when a user and the post of post type 'CPT_1' shares the same ID in the database.
WordPress Taxonomy/Terms mechanism doesn't really know which entity to consider in this case because WordPress plays on Object_ID & does not take object_type into consideration while fetching the terms of a specific object.

##Solution :
We saw that there arises a conflict between the relationship of Term-Post & Term-User. So we separated the relationships of Term-User into our custom table.
And we maintain those relationships separately far from Term-Post relationships which goes into WordPress DB Tables by default.

We put a check while performing operations on Terms that whether the queried object is a post or a user.
If it's a post we give the execution to WP Environment i.e., we perform operations using WordPress Core functions only as they suffice the situation to return correct results.
In case of users where it causes enough ambiguity; we decided to take control in our hands and we wrote custom functions that will update our custom tables to save user relationships with terms.
Also those functions will give correct user relationships when fetched.

##DB Schema

**rt_user_groups_relationships**

 - user_id
 - term_taxonomy_id
 - term_order


## How to Use

Create Object of RT_User_Groups Class with argument taxonomy slug and its label

    $obj = new RT_User_Groups( Slug, Label_Array, Caps, Select_multiple );

	Slug : Group slug

	Label_Array : Group lable array

	Caps : Capability for manage group

		$terms_cap = array(
			'manage_terms' => 'manage_custom_terms',
			'edit_terms' => 'edit_custom_terms',
			'delete_terms' => 'delete_custom_terms',
			'assign_terms' => 'assign_custom_terms',
		);

	Select_multiple : Allow to select multiple group or single
					  by default allows to select multiple group

    Example :-

    $RT_User_Groups = new RT_User_Groups('user-group', array(
            'name' => __( 'Departments' ),
            'singular_name' => __( 'Departmet' ),
            'menu_name' => __( 'Departments' ),
            'search_items' => __( 'Search Departments' ),
            'popular_items' => __( 'Popular Departments' ),
            'all_items' => __( 'All User Departments' ),
            'edit_item' => __( 'Edit Department' ),
            'update_item' => __( 'Update Department' ),
            'add_new_item' => __( 'Add New Department' ),
            'new_item_name' => __( 'New Department Name' ),
            'separate_items_with_commas' => __( 'Separate departments with commas' ),
            'add_or_remove_items' => __( 'Add or remove departments' ),
            'choose_from_most_used' => __( 'Choose from the most popular departments' ),
        )
    );

Assign group-User Relationship

    set_user_group( User_ID, term_Slug  )

Remove group-User Relationship

    remove_user_group( User_ID, term_Slug  )

Remove all user groups for given user

	remove_all_user_groups( User_ID )

Check group-User Relationship exist or not

    is_user_has_group( User_ID, term_Slug  )

Get list of groups for given user

    get_user_groups( User_ID )

Get list of user for given group slug

    get_user_by_group_slug( term_Slug )

Get list of user for given group term id

    get_user_by_group_slug( Taxonomy_Slug )
