<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$user_groups = rt_biz_get_user_groups();
$modules     = rt_biz_get_modules();
$permissions = rt_biz_get_acl_permissions();
$module_permissions = get_site_option( 'rt_biz_module_permissions' );
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div><h2><?php _e( 'rtBiz Access Control' ); ?></h2>
	<div class="rt-biz-container">
		<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<input type="hidden" name="rt_biz_acl_permissions" value="1" />
			<table class="wp-list-table widefat" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" class="manage-column">&nbsp;</th>
						<?php foreach ( $user_groups as $ug ) { ?>
						<th scope="col" class="manage-column"><strong><?php echo $ug->name; ?></strong></th>
						<?php } ?>
					</tr>
				</thead>
				<tbody id="the-list">
					<?php foreach ( $modules as $mkey => $m ) { ?>
					<tr>
						<td><strong><?php echo $m['label']; ?></strong></td>
						<?php foreach ( $user_groups as $ug ) { ?>
							<td>
								<select name="rt_biz_module_permissions[<?php echo $mkey ?>][<?php echo $ug->term_id; ?>]">
								<?php foreach ( $permissions as $pkey => $p ) { ?>
								<option value="<?php echo $p['value']; ?>" <?php echo ( isset( $module_permissions[$mkey][$ug->term_id] ) && $module_permissions[$mkey][$ug->term_id] == $p['value'] ) ? 'selected="selected"' : ''; ?>><?php echo $p['name']; ?></option>
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
						<?php foreach ( $user_groups as $ug ) { ?>
						<th scope="col" class="manage-column"><strong><?php echo $ug->name; ?></strong></th>
						<?php } ?>
					</tr>
				</tfoot>
			</table>
			<br />
			<input type="submit" class="button-primary" value="Save Settings" />
		</form>
	</div>
</div>
