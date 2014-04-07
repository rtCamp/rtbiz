<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$user_groups = rt_biz_get_user_groups();
$modules_registered = '';
$permissions = array();
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div><h2><?php _e( 'rtBiz Access Control' ); ?></h2>
	<div class="rt-biz-container">
		<form>
			<table class="wp-list-table widefat" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" class="manage-column">&nbsp;</th>
						<?php foreach ( $user_groups as $ug ) { ?>
						<th scope="col" class="manage-column"><?php echo $ug->name; ?></th>
						<?php } ?>
					</tr>
				</thead>
				<tbody id="the-list">
					<tr>
						<td>Test</td>
						<td>Test</td>
						<td>Test</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<th scope="col" class="manage-column">&nbsp;</th>
						<?php foreach ( $user_groups as $ug ) { ?>
						<th scope="col" class="manage-column"><?php echo $ug->name; ?></th>
						<?php } ?>
					</tr>
				</tfoot>
			</table>
			<input type="submit" value="Save Settings" />
		</form>
	</div>
</div>
