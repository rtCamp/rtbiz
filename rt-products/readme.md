RT Offerings
============

### `class Rt_Products`

This class gives a special attribute to link it with other Custom Post Types.

Many a times, we want to link our Custom Post Types with the products in our E-commerce Store such as WooCommerce or EasyDigitalDownload.

This class takes all the existing products from either a WC or EDD store and synchronize them with the terms of this special attribute taxonomy *Offerings*.

So that we can assign these products to any custom post types that are registered with this taxonomy.

#### Attributes

##### `static $product_slug = 'rt-offering'`

Offering taxonomy Slug

##### `static $term_meta_key = '_offering_id'`

Meta key to store Offering ID in product meta

##### `var $labels = array();`

Array of labels for Offerings taxonomy

##### `var $post_types;`

Array of post types which are to be linked with Offerings taxonomy.

##### `var $isSync;`

Offering taxonomy Sync enable

##### `var $caps = array();`

Capability for taxonomy

##### `var $pluginName;`

E-commerce plugin from which products need to be synced.

#### Methods

##### `__construct( $plugin_name, $cap, $post_types )`

Constructor method for the class.

``` php
@param array    $cap - Capability array for the taxonomy
@param string   $plugin_name - Plugin name from which products need to be synced.
@param array    $post_types - Array of post types with which Offerings taxonomy needs to be mapped.

@uses Rt_Products::is_woocommerce_active() - checks if WooCommerce is active or not.
@uses Rt_Products::is_edd_active() - checks if EDD is active or not.
@uses Rt_Products::auto_loader()
@uses Rt_Products::get_label()
@uses Rt_Lib_Taxonomy_Metadata\Taxonomy_Metadata()
@uses Rt_Lib_Taxonomy_Metadata\Taxonomy_Metadata::activate()
@uses Rt_Products::hooks()
```

##### `is_woocommerce_active()`

Check id WooCommerce is installed & active or not.

##### `is_edd_active()`

Check id Easy Digital Downloads is installed & active or not.

##### `get_post_type()`

Get the post type of product according to which plugin is active in WordPress.

``` php
@uses Rt_Products::is_woocommerce_active() - checks if WooCommerce is active or not.
@uses Rt_Products::is_edd_active() - checks if EDD is active or not.
```

##### `auto_loader()`

Auto loader for model classes

##### `get_label()`

Get Labels of Offerings taxonomy

``` php
@return array
```

##### `register_product_taxonomy()`

Register Product taxonomy if it doesn't exist

``` php
@defined rtlib_products_support
```

##### `update_post_term_count( $terms, $taxonomy )`

This method gets called whenever a term is added/edited/deleted from WordPress environment. It will update the post counts for that particular term of a taxonomy.

``` php
@uses do_action() - WordPress Core. Defines custom action hooks for modifications.

@defined edit_term_taxonomy - This is WordPress core action. We are just re-using it for our own custom workflow.
@defined edited_term_taxonomy - This is WordPress core action. We are just re-using it for our own custom workflow.
```

##### `hooks()`

hooks function.

Call all hooks :smiley:

``` php
@access public
@return void
```

##### `cleanup_meta_after_term_deletion( $term, $tt_id, $taxonomy, $deleted_term )`

This method gets called upon a term deletion. It deletes term meta stored for that term.

``` php
@param $term
@param $tt_id
@param $taxonomy
@param $deleted_term

@uses Rt_Lib_Taxonomy_Metadata\delete_term_meta() - Deletes the term meta.
```

##### `existing_offerings_sync()`

old_offerings_synchronization_enabled function.

``` php
@access public
@return void

@uses Rt_Products::is_woocommerce_active() - checks if WooCommerce is active or not.
@uses Rt_Products::is_edd_active() - checks if EDD is active or not.
@uses Rt_Products::bulk_insert_products() - Bult insert of offerings.
```

##### `get_taxonomy( $post_id )`

Get term taxonomy from a post ID ( offering id )

``` php
access public
@param $post_id
@return void
```

##### `insert_products(  $post_id  )`

insert an offering for a given post.

``` php
@param $post_id

@access public
@return void

@uses Rt_Products::is_woocommerce_active() - checks if WooCommerce is active or not.
@uses Rt_Products::is_edd_active() - checks if EDD is active or not.
@uses Rt_Products::get_taxonomy()
@uses Rt_Products::check_postid_term_exist()
@uses Rt_Lib_Taxonomy_Metadata\add_term_meta()
```

##### `check_postid_term_exist( $post_id )`

This method checks for any term that exists for given post id or not.

``` php
@global WPDB $wpdb

@param $post_id
```

##### `bulk_insert_products()`

This method inserts offernigs in bulk.

``` php
@uses Rt_Products::get_taxonomy()
@uses wp_insert_term()
@uses Rt_Lib_Taxonomy_Metadata\add_term_meta()
```

#### `delete_products_meta( $term_id )`

Deletes offerings meta field for given term ID

``` php
@param $term_id
```

#### Hooks

##### Actions

###### `edit_term_taxonomy`

###### `edited_term_taxonomy`

##### Filters

###### `rtlib_products_support`

### How to use

``` php
$terms_caps = array(
    'manage_terms' => true,//$editor_cap,
    'edit_terms'   => true,//$editor_cap,
    'delete_terms' => true,//$editor_cap,
    'assign_terms' => true,//$editor_cap,
);

/* For WooCommerce Product Sync or $product_plugin = 'edd' For EasyDigitalDownloads */
$product_plugin = 'woocommerce';

/* Post Types to provide Product Sync Support to */
$post_types = array( 'ticket', 'lead' );

$rtbiz_offerings = new Rt_Products( $product_plugin, $terms_caps, $post_types );
```
