<?php

if ( ! isset( $_REQUEST['type'] ) ) {
	$_REQUEST['type'] = 'gravity';
}
?>

<div class="wrap">
	<h2><?php _e( 'Importer Mapping List' ) ?></h2>

<div class="nav-tab-wrapper">
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Rt_Importer_Mapper::$page_slug ) ); ?>" class="nav-tab nav-tab-active">Gravity</a>
</div>

<table class="wp-list-table widefat rtlib-gravity-mapping" cellspacing="0">
	<thead>
	<tr>
		<th scope='col' id='rtlib_form_name' class='manage-column column-rtlib_form_name' style=""><span><?php _e( 'Form' ); ?></span><span
				class="sorting-indicator"></span></th>
		<th scope='col' id='rtlib_post_type' class='manage-column column-rtlib_form_name' style=""><span><?php _e( 'PostType [ Module ]' ); ?></span><span
				class="sorting-indicator"></span></th>
		<th scope='col' id='rtlib_create_date' class='manage-column column-rtlib_create_date' style="">
			<span>Create Date</span></th>
		<th scope='col' id='rtlib_enable' class='manage-column column-rtlib_enable' style=""><span>Enable\Disable</span>
		</th>
		<th scope='col' id='rtlib_delete' class='manage-column column-rtlib_delete' style=""><span>Delete</span></th>
	</tr>
	</thead>

	<tfoot>
	<tr>
		<th scope='col' id='rtlib_form_name' class='manage-column column-rtlib_form_name' style=""><span><?php _e( 'Form' ); ?></span><span
				class="sorting-indicator"></span></th>
		<th scope='col' id='rtlib_post_type' class='manage-column column-rtlib_form_name' style=""><span><?php _e( 'PostType [ Module ]' ); ?></span><span
				class="sorting-indicator"></span></th>
		<th scope='col' id='rtlib_create_date' class='manage-column column-rtlib_create_date' style="">
			<span>Create Date</span></th>
		<th scope='col' id='rtlib_enable' class='manage-column column-rtlib_enable' style=""><span>Enable\Disable</span>
		</th>
		<th scope='col' id='rtlib_delete' class='manage-column column-rtlib_delete' style=""><span>Delete</span></th>
	</tr>
	</tfoot>

	<tbody id="the-list" data-wp-lists='list:ticket'>
	<?php if ( isset( $gravity_fields ) && count( $gravity_fields ) > 0 ) { ?>
		<?php foreach ( $gravity_fields as $gravity_field ) { ?>
			<tr id="mapping_<?php echo esc_attr( $gravity_field->id ); ?>" class="">
				<td class='rtlib_form_name column-rtlib_form_name'><a href="<?php echo admin_url( 'admin.php?page=gf_edit_forms&id=' . $gravity_field->form_id ); ?>"><?php echo esc_html( $gravity_field->form_name ); ?></a></td>
				<td class='rtlib_post_type column-rtlib_post_type'><?php echo esc_html( $gravity_field->post_type . ' [' . $gravity_field->module_id . ']' ); ?></td>
				<td class='rtlib_create_date column-rtlib_create_date'><?php echo esc_html( $gravity_field->create_date ); ?></td>
				<td class='rtlib_enable column-rtlib_enable aligncenter'><input
						id="cb-select-<?php echo esc_attr( $gravity_field->id ); ?>" class="rtlib_enable_mapping"
						type="checkbox"  <?php echo esc_attr( isset( $gravity_field->enable ) && $gravity_field->enable == 'yes' ? 'checked="checked"' : '' ) ?>
						data-mapping-id="<?php echo esc_attr( $gravity_field->id ); ?>" value="yes"/></td>
				<td class='rtlib_delete column-rtlib_delete aligncenter'><a
						id="rtlib-delete--<?php echo esc_attr( $gravity_field->id ); ?>" class="rtlib_delete_mapping"
						style="color: red; cursor: pointer" data-mapping-id="<?php echo esc_attr( $gravity_field->id ); ?>">X</a>
				</td>
			</tr>
		<?php
}
} else {
	?>
	<tr>
		<td colspan='5'><?php echo esc_attr( _e( 'No Mapping Found!' ) ) ?></td>
	</tr>
	<?php } ?>
	</tbody>
</table>
</div>