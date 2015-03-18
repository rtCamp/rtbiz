rtBiz Dashboard
===============

### `Class Rt_Biz_Dashboard`

This class provides a dedicated dashboard for rtBiz plugin. It includes of lots of customized dashboard widgets which gives user a top overview of rtBiz environment. It also includes a welcome widget with useful navigation links which gives a head-start to user about getting along with rtBiz.

#### Attributes

##### `var $screen_id;`

WordPress Screen's ID for dashboard admin page.

##### `var $charts = array();`

This array would hold the data for all the Google charts that will be displayed in the widgets. It will be passed on to Chart library.

#### Methods

##### `__construct()`

Constructor method for the class. It initializes dashboard class & sets default values for required keys in database.

``` php
@uses add_action() - Wordpress Core. Adds ajax callback action for welcome widget dismissal.
@uses Rt_Biz_Dashboard::setup_defaults() - rtBiz Core. Setup default values for welcome widget dismissal key.
```

##### `setup_defaults()`

This method setup default values for welcome widget dismissal key in usermeta.

``` php
@uses metadata_exists() - WordPress core. Checks if usermeta key exists or not.
@uses get_current_user_id() - WordPress core. Gets current logged in user's ID.
@uses update_user_meta() - WordPress Core. updates usermeta value for dismissal key if it doesn't exists.
```

##### `setup_dashboard()`

This method setup the dashboard to be rendered on the page.

``` php
@uses add_action() - WordPress core. Defines a few action methods for dashboard setup.
@uses Rt_Biz_Dashboard::check_welcome_panel() - rtBiz Core. Check whether to display welcome widget or not.
```

##### `update_rtbiz_welcome_panel()`

AJAX action callback to update show/hide welcome widget action.

``` php
@uses check_ajax_referer() - WordPress core. Verify the correct action.
@uses rt_biz_get_access_role_cap() - rtBiz core. Get capability string to check for given role.
@uses current_user_can() - WordPress core. Checks for user's capability.
@uses update_user_meta() - WordPress core. Updates usermeta
@uses get_current_user_id() - WordPress core. Gets current logged in user's ID.
```

##### `print_dashboard_js()`

Adds required JS code into dashboard page. It also checks for Welcome Widget visibility.

``` php
@uses update_user_meta() - WordPress core. Updates usermeta
@uses get_user_meta() - WordPress core. Gets usermeta
@uses get_current_user_id() - WordPress core. Gets current logged in user's ID.
@uses wp_get_current_user() - Gets current WP User object.
```

##### `check_welcome_panel()`

This method checks for welcome widget query string and accordingly updates user meta.

``` php
@uses update_user_meta() - WordPress core. Updates usermeta
@uses get_current_user_id() - WordPress core. Gets current logged in user's ID.
```

##### `rtbiz_welcome_panel()`

This method renders UI for rtBiz Welcome Widget.

``` php
@uses rt_biz_get_access_role_cap() - rtBiz core. Get capability string to check for given role.
@uses rt_biz_get_redux_settings() - rtBiz core. Get redux settings for label.
@uses _e() - WordPress core. Internationalization method.
@uses current_user_can() - WordPress core. Checks for user's capability.
@uses do_action() - WordPress core. Defines new actin hook.
@uses admin_url() - WordPress Core. Gets admin url.
@uses __() - WordPress core. Internationalization method.
@uses rt_biz_get_contact_post_type() - rtBiz core. Gets rtBiz contact post type
@uses rt_biz_get_company_post_type() - rtBiz core. Gets rtBiz company post type

@defined rtbiz_welcome_panel_addon_link - New custom action hook defined for other plugins to add their quick links in rtBiz Welcome widget.
```

*Example*

``` php
add_action( 'rtbiz_welcome_panel_addon_link', 'my_custom_plugin_quick_link' );

function my_custom_plugin_quick_link() { ?>
	<a id="rtbiz-customize-my-plugin" class="button button-primary button-hero" href="<?php echo admin_url( 'admin.php?page=rt-my-plugin-dashboard' ); ?>"><?php _e( 'My Plugin' ); ?></a>
<?php }
```

##### `add_screen_id( $screen_id )`

This methods set screen_id variable for the class.

``` php
@param $screen_id string - Screen ID for WordPress admin page.
```

##### `footer_scripts()`

This method adds widget JS from the WordPress core on the dashboard page.

##### `page_actions()`

Actions to be taken prior to page loading. This is after headers have been set.

Call on `load-$hook`

This calls the `add_meta_boxes` hooks, adds screen options and enqueues the `postbox.js` script.

``` php
@uses do_action() - WordPress core. calls add_meta_boxes hook.
@uses wp_enqueue_script() - Wordpress core. enqueues the `postbox.js` script.
```

##### `render_google_charts()`

This method calls for chart library which would render all the charts in the widget.

``` php
@uses Rt_Reports::render_chart() - rtBiz core. Renders the chart on a page.
```

##### `add_dashboard_widgets()`

Add all the dashboard widget here as a metaboz on the dashboard page.

``` php
@uses rt_biz_get_redux_settings() - rtBiz core. Get redux settings for label.
@uses add_meta_box() - WordPress core. Add metaboxes for dashboard widgets.
@uses rt_biz_get_contact_post_type() - rtBiz core. Gets rtBiz contact post type
@uses RT_Attributes_Model() - Attributes model class for DB operations.
@uses RT_Attributes_Relationship_Model() - Attributes Relationship model class for DB operations.
@uses RT_Attributes_Relationship_Model::get_relations_by_post_type() - Get relations based on post type.
@uses RT_Attributes_Model::get_attribute() - get attribute detaild for given attribute ID.
```

##### `rtbiz_dashboard_recent_posts( $args, $post_type )`

This method renders latest posts in the Recent Activity widget added based on the post type passed.

``` php
@param $args array - Array of arguments for WP_Query
@param $post_type string - Post type slug for which posts to be fetched.

@return bool - Returns true if posts found and rendered otherwise returns false.

@uses WP_Query() - WordPress Post Query
@uses WP_Query::have_posts() - Checks if post exists or not.
```

##### `rtbiz_dashboard_recent_comments_row( &$comment, $show_date )`

This method displays a single comment in a row within Recent Comments widget on rtbiz dashboard.

##### `rtbiz_dashboard_site_activity()`

This is a combo widget displaying recent contacts, recent companies & recent comments.

##### `get_post_count_excluding_tax( $taxonomy, $post_type )`

This method gives post count of all the posts which does not have any terms linked from given taxonomy.

##### `offering_wise_contacts( $obj, $args )`

This method renders the widget UI where contacts are dispalyed in a chart categorized by offerings.

##### `contact_type_wise_contacts( $obj, $args )`

This method renders the widget UI where contacts are dispalyed in a chart categorized by contact type.

##### `department_by_contacts( $obj, $args )`

This method renders the widget UI where contacts are dispalyed in a chart categorized by departments.

##### `dashboard_widget_content( $obj, $args )`

This method renders all the attributes widget that are defined from attributes library.

#### Hooks

##### Actions

###### `rtbiz_welcome_panel_addon_link` - Action for other addons to add quick links for their plugins.

##### Filters
