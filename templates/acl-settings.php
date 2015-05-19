<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$department = rt_biz_get_department();
$modules     = rt_biz_get_modules();
$permissions = rt_biz_get_acl_permissions();
$module_permissions = get_site_option( 'rt_biz_module_permissions' );
$settings  = rt_biz_get_redux_settings();
$menu_label = $settings['menu_label'];
if ( is_plugin_active( 'rtbiz-helpdesk/rtbiz-helpdesk.php' ) && isset( $_GET['post_type'] ) && 'rtbiz_hd_ticket' == $_GET['post_type']  ) {
	$menu_label = 'Helpdesk';
}
?>
<div class="wrap">

	<div id="icon-options-general" class="icon32"><br></div><h2><?php echo $menu_label . __( ' Access Control' ); ?></h2>
	<?php if ( empty( $department ) ) { ?>
		<div id="message" class="error"><p><?php echo 'No Team found, please add a department first to manage ACL'; ?></p></div>
	<?php } ?>
	<div class="rt-biz-container">
		<ul class="rt_biz_acl_other_option subsubsub">
			<strong>Department:</strong>
			<?php foreach ( $department as $ug ) { ?>
				<li><a href="<?php echo admin_url( 'edit-tags.php?action=edit&taxonomy=' . RT_Departments::$slug . '&tag_ID=' . $ug->term_id . '&post_type=' . rt_biz_get_contact_post_type() ) ?>" class=""><?php echo $ug->name; ?></a></li> |
			<?php } ?>
			<li><a href="<?php echo admin_url( 'edit-tags.php?taxonomy=' . RT_Departments::$slug . '&post_type=' . rt_biz_get_contact_post_type() ); ?>">Add New</a></li>
		</ul>
		<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<input type="hidden" name="rt_biz_acl_permissions" value="1" />
			<table class="wp-list-table widefat" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" class="manage-column">&nbsp;</th>
						<?php foreach ( $department as $ug ) { ?>
						<th scope="col" class="manage-column"><strong><?php echo $ug->name.' Team'; ?></strong></th>
						<?php } ?>
					</tr>
				</thead>
				<tbody id="the-list">
					<?php foreach ( $modules as $mkey => $m ) {
						if ( RT_BIZ_TEXT_DOMAIN == $mkey && is_plugin_active( 'rtbiz-helpdesk/rtbiz-helpdesk.php' ) && isset( $_GET['post_type'] ) && 'rtbiz_hd_ticket' == $_GET['post_type'] ) {
							$m['label'] = 'People';
						}
						?>
					<tr>
						<td><strong><?php echo $m['label']; ?></strong></td>
						<?php foreach ( $department as $ug ) { ?>
							<td>
								<select name="rt_biz_module_permissions[<?php echo $mkey ?>][<?php echo $ug->term_id; ?>]">
									<?php foreach ( $permissions as $pkey => $p ) { ?>
									<option title="<?php echo $p['tooltip']; ?>" value="<?php echo $p['value']; ?>" <?php echo ( isset( $module_permissions[ $mkey ][ $ug->term_id ] ) && intval( $module_permissions[ $mkey ][ $ug->term_id ] ) == $p['value'] ) ? 'selected="selected"' : ''; ?>><?php echo $p['name']; ?></option>;
									<?php } ?>
								</select>
							</td>
						<?php } ?>
					</tr>
					<?php } ?>
				</tbody>
				<tfoot>
					<tr>
						<th scope="col" class="manage-column">&nbsp;</th>
						<?php foreach ( $department as $ug ) { ?>
						<th scope="col" class="manage-column"><strong><?php echo $ug->name.' Team';; ?></strong></th>
						<?php } ?>
					</tr>
				</tfoot>
			</table>
			<br />
			<input type="submit" class="button-primary" value="Save Settings" />
	<div style="margin-top: 25px">
			<p class="description"> Access to individual contacts can be updated from their contact profile page. Go to <a target="_blank" href="<?php echo admin_url( 'edit.php?post_type='.rt_biz_get_contact_post_type() ); ?>">People</a> section to find a contact.</p>
	</div>
		</form>
	</div>
</div>
