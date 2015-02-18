Contextual Help
===============

### `class Rt_Biz_Help`

#### Attributes

##### `var $tabs = array();`

An array of all tabs that are going to be dispalyed on some or the other page of rtBiz plugin.

##### `var $help_sidebar_content;`

Markup string for the sidebar content in the contextual help tab.

#### Methods

##### `__construct()`

Constructor method for the class. It is doing nothing but calling out the init method for the class.

``` php
@uses add_action() - Calling out init method.
```

##### `init_help()`

Initialize all the tabs and its content along with the pages on which the tabs needs to be displayed.

``` php
@uses apply_filters() - Defines a few filter hooks that can be used to modify help tab content and extra tabs.
@uses rt_biz_get_contact_post_type() - Gets rtBiz contact post type slug
@uses rt_biz_get_company_post_type() - Gets rtBiz company post type slug


@defined rt_biz_help_tabs - A filter hook to modify the array of all the tabs.
@defined rt_biz_help_documentation_link - A filter hook to modify documentation link
@defined rt_biz_help_support_forum_link - A filter hook to modify support link
@defined rt_biz_help_sidebar_content - A filter hook to modify sidebar content
```

*Example*

``` php
add_filter( 'rt_biz_help_tabs', 'my_custom_help_tab' );

function my_custom_help_tab( $tabs ) {
	$tabs['admin.php'][] = array(
		'id' => 'my_custom_page_help_tab',
		'title' => __( 'My Custom Page Help Tab' ),
		'content' => '',
		'page' => 'my-custom-page-slug',
	);
	return $tabs;
}

add_filter( 'rt_biz_help_documentation_link', 'my_custom_documentation_link' );

function my_custom_documentation_link( $link ) {
	$link = '<a href="https://google.com">My Custom Doc</a>';
	return $link;
}

add_filter( 'rt_biz_help_support_forum_link', 'my_custom_support_link' );

function my_custom_support_link( $link ) {
	$link = '<a href="https://google.com">My Custom Support</a>';
	return $link;
}

add_filter( 'rt_biz_help_sidebar_content', 'my_custom_sidebar_content' );

function my_custom_sidebar_content( $content ) {
	$content .= '<br /><p>My Custom Content in the sidebar.</p>';
	return $content;
}
```

##### `check_tabs()`

This method checks if the current page is valid for defined tabs for pages. If they are valid then it adds a help tab for the current page.

``` php
@uses Rt_Biz_Help::add_tab() - rtBiz core. Adds a tab in the contextual tab view for a page.
```

##### `add_tab( $args )`

Fetches the current screen for the admin page and adds a tab in the contextual tab view for a page.

``` php
@uses get_current_screen() - Fetches the current screen object.
@uses WP_Screen::add_help_tab() - WordPress core. Adds help tab on the page.
@uses WP_Screen::set_help_sidebar() - WordPress core. Adds sidebar in the help tab.
```

##### `tab_content( $screen, $tab )`

Displays the actual content in the help tab.

``` php
@param $screen object - Current screen object.
@param $tab array - Current tab information.

@uses do_action() - Define custom action filter for default tab.

@defined rt_biz_help_tab_content - Custom action hook for default custom tab which is not defined in rtBiz.
```

*Example*

``` php
add_action( 'rt_biz_help_tab_content', 'my_custom_tab_content' );

function my_custom_tab_content( $screen, $tab ) {
	if ( ! empty( $tab['id'] ) && $tab['id'] == 'my_custom_page_help_tab' ) {
		?>
		<p>
			<?php _e( 'My Custom Tab Content.' ); ?>
		</p>
		<?php
	}
}
```

#### Hooks

##### Actions

###### `rt_biz_help_tab_content`

##### Filters

###### `rt_biz_help_tabs`

###### `rt_biz_help_documentation_link`

###### `rt_biz_help_support_forum_link`

###### `rt_biz_help_sidebar_content`
