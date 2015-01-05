## Settings

There're a bunch of settings that rtBiz offers on its settings page that enables you to run your site well with rtBiz.

To access the settings, click `rtBiz > Settings` from the admin dashboard of your site.

### General

![General Settings](https://cloud.githubusercontent.com/assets/2236554/5557851/5bd4921c-8d35-11e4-8c19-42a610ee8c86.png)

The **General** tab allows you to configure very generic customization of rtBiz.

1. **rtBiz Menu Label:** This is a menu identity for rtBiz. This label will be used for the Menu Item label for rtBiz. It will also be used wherever `rtBiz` keyword placeholder is used in plugin.

2. **Logo:** This logo is used for rtBiz branding. It will be used as icon in WordPress admin menu. You can set your own icon for branding. You will need to upload `.png` or `.jpeg` file, preferably of size 16x16.

3. **Offering Sync Option:** This option is related to your e-commerce store. Be it [WooCommerce](https://wordpress.org/plugins/woocommerce/) or [Easy Digital Downloads](https://wordpress.org/plugins/easy-digital-downloads/); we support both ! What it does is, keeps the products from your store synced with a custom taxonomy called *Offerings*. This taxonomy stays available globally within rtBiz environment. So you can link up your customers with your offerings. And there are many things you will want to map with your products that's possible

### Import/Export

![Import/Export Settings](https://cloud.githubusercontent.com/assets/2236554/5557855/70dade28-8d35-11e4-9026-e4ba3dcad9bd.png)

This is the section from where you can take backup of your rtBiz settings for future reference. You can also import previously saved settings.

1. **Import:** You can import rtBiz settings. You need to have a json string from the backup. Either you can paste the string in textarea of `Import from file` option or you can directly give url that returns json string of settings from `Import From URL` option.

2. **Export:** There are three ways to export rtBiz settings.
	- Copy the whole JSON string of settings
	- Download the JSON file containing settings
	- Copy the URL that returns JSON string of settings
