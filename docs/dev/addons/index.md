rtBiz Add-on Development
========================

There are a few guidelines that a developer needs to follow in order to develop an add-on for rtBiz.

rtBiz has a rich set of APIs, re-usable modules, functions that are available for other developers to use. Using those, developers can achieve many things within rtBiz plugin scope. In fact, in many times developer put an enhancement in rtBiz. It might be possible that an add-on is included within rtBiz Core, if core contributors feel the necessity of it.

Following are the steps a developer should follow to create a simple basic rtBiz addon. Of course, more can be built keeping that as a base.

### Get Started

First step is to write `index.php` of a plugin.

```
/**
 * Plugin Name: rtBiz Sample Addon
 * Plugin URI: http://rtcamp.com/
 * Description: rtBiz Sample Addon
 * Version: 1.0
 * Author: rtCamp
 * Author URI: http://rtcamp.com
 * License: GPL
 * Text Domain: rtbiz_sample_addon
 */

function rtbiz_sample_addon_admin_notice() {
	?>
	<div class="updated ">
		<p><strong>rtBiz Sample Addon:</strong> This is a very basic Sample rtBiz Addon Example.</p>
	</div>
	<?php
}

function rtbiz_sample_addon_init() {
	add_action( 'admin_notices', 'rtbiz_sample_addon_admin_notice' );
}

/**
 * This is the main action where any rtBiz addon will be able to hook and initialize its own files.
 * Basically, this action ensures that rtBiz has been activated and initialized properly.
 * So any other addon can start loading its own files from here onwards.
 */
add_action( 'rt_biz_init', 'rtbiz_sample_addon_init' );
```

Above snippet shows how you can get started with developing a rtBiz addon. You can also explore developer documentation of rtBiz to learn more about rtBiz.
