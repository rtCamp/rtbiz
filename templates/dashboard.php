<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<div class="wrap">

	<?php screen_icon(); ?>

	<h2><?php $settings  = biz_get_redux_settings(); $menu_label = $settings['menu_label']; echo $menu_label . ' ' . __( 'Dashboard' ); ?></h2>

		<div id="poststuff">

			<div id="dashboard-widgets" class="metabox-holder">

				<div id="postbox-container-1" class="postbox-container">
					<?php do_meta_boxes('', 'column1', null); ?>
				</div>

				<div id="postbox-container-2" class="postbox-container">
					<?php do_meta_boxes('', 'column2', null); ?>
				</div>

				<div id="postbox-container-3" class="postbox-container">
					<?php do_meta_boxes('', 'column3', null); ?>
				</div>

				<div id="postbox-container-4" class="postbox-container">
					<?php do_meta_boxes('', 'column4', null); ?>
				</div>

			</div> <!-- #post-body -->
			<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php do_action( 'rtbiz_after_dashboard' ); ?>

		</div> <!-- #poststuff -->

</div><!-- .wrap -->
