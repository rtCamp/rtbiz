<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if ( isset( $_POST['rt_biz_logo_url'] ) ) {
	update_site_option( 'rt_biz_logo_url', $_POST['rt_biz_logo_url'] );
}

if(!isset($_REQUEST["type"])){
	$_REQUEST["type"]="other";
}
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div><h2><?php _e( 'rtBiz Settings' ); ?></h2>
	<ul class="subsubsub">
		<li><a href="<?php echo admin_url( 'admin.php?page=' . Rt_Biz::$settings_page_slug . '&type=other' );?>" <?php if ( $_REQUEST['type'] == 'other' ) echo ' class="current"'; ?> ><?php _e( 'Other' ); ?></a></li>
	</ul>

	<form method="post" action="<?php echo admin_url( 'admin.php?page=' . Rt_Biz::$settings_page_slug . '&type=' . $_REQUEST['type']); ?>">
		<table class="form-table crm-option">
			<tbody>
			<?php if( $_REQUEST['type'] == 'other' ) { ?>
				<tr valign="top">
					<th scope="row"><label for="rt_biz_logo_url"><?php _e( 'rtBiz Icon (Logo) URL' ); ?></label></th>
					<td><input type="text" name="rt_biz_logo_url" value="<?php echo get_site_option( 'rt_biz_logo_url', '' ); ?>" /></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
	</form>
</div>
