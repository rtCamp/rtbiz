<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$settings   = rtbiz_get_redux_settings();
$menu_label = __( 'rtBiz' );
$author_cap = rtbiz_get_access_role_cap( RTBIZ_TEXT_DOMAIN, 'author' );
?>
<div class="wrap">

	<h2><?php echo $menu_label . ' ' . __( 'Dashboard' ); ?></h2>

	<?php
	if ( current_user_can( $author_cap ) ) {
		$classes = 'welcome-panel';

		$option = get_user_meta( get_current_user_id(), 'rtbiz_show_welcome_panel', true );
		// 0 = hide, 1 = toggled to show or single site creator, 2 = multisite site owner
		$hide = 0 == $option || ( 2 == $option && wp_get_current_user()->user_email != get_option( 'admin_email' ) );
		if ( $hide ) {
			$classes .= ' hidden';
		}
		?>

		<div id="rtbiz-welcome-panel" class="<?php echo esc_attr( $classes ); ?>">
			<?php wp_nonce_field( 'rtbiz-welcome-panel-nonce', 'rtbizwelcomepanelnonce', false ); ?>
			<a class="welcome-panel-close"
			   href="<?php echo esc_url( admin_url( 'admin.php?page=' . Rtbiz_Dashboard::$page_slug . '&rtbizwelcome=0' ) ); ?>"><?php _e( 'Dismiss' ); ?></a>
			<?php
			/**
			 * Add content to the welcome panel on the admin dashboard.
			 *
			 * To remove the default welcome panel, use remove_action():
			 * <code>remove_action( 'rtbiz_welcome_panel', 'wp_welcome_panel' );</code>
			 *
			 * @since 3.5.0
			 */
			do_action( 'rtbiz_welcome_panel' );
			?>
		</div>
	<?php } ?>

	<div id="poststuff">

		<div id="dashboard-widgets" class="metabox-holder">

			<div id="postbox-container-1" class="postbox-container">
				<?php do_meta_boxes( '', 'column1', null ); ?>
			</div>

			<div id="postbox-container-2" class="postbox-container">
				<?php do_meta_boxes( '', 'column2', null ); ?>
			</div>

			<div id="postbox-container-3" class="postbox-container">
				<?php do_meta_boxes( '', 'column3', null ); ?>
			</div>

			<div id="postbox-container-4" class="postbox-container">
				<?php do_meta_boxes( '', 'column4', null ); ?>
			</div>

			<div id="postbox-container-5" class="postbox-container">
				<?php do_meta_boxes( '', 'column5', null ); ?>
			</div>

		</div>
		<!-- #post-body -->
		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php do_action( 'rtbiz_after_dashboard' ); ?>

	</div>
	<!-- #poststuff -->

</div><!-- .wrap -->
