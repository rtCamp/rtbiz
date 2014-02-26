<?php
/**
 * Created by PhpStorm.
 * User: faishal
 * Date: 24/02/14
 * Time: 3:50 PM
 */
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ){
	exit;
}
/**
 * Template : Edit Attribute
 *
 * Created by PhpStorm.
 * User: udit
 * Date: 2/20/14
 * Time: 4:04 AM
 */
?>
<div class="wrap">
	<h2><i class="icon-tag"></i> <?php _e( 'Edit Attribute' ) ?></h2>
	<form action="?edit=<?php echo absint( $edit ); ?>" method="post">
		<table class="form-table">
			<tbody>
			<tr class="form-field form-required">
				<th scope="row">
					<label for="attribute_label"><?php _e( 'Name' ); ?></label>
				</th>
				<td>
					<input name="attribute_label" id="attribute_label" type="text" value="<?php echo esc_attr( $att_label ); ?>" />
					<p class="description"><?php _e( 'Name for the attribute (shown on the front-end).' ); ?></p>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row">
					<label for="attribute_name"><?php _e( 'Slug' ); ?></label>
				</th>
				<td>
					<input name="attribute_name" id="attribute_name" type="text" value="<?php echo esc_attr( $att_name ); ?>" maxlength="28" />
					<p class="description"><?php _e( 'Unique slug/reference for the attribute; must be shorter than 28 characters.' ); ?></p>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row">
					<label for="attribute_store_as"><?php _e( 'Store As' ); ?></label>
				</th>
				<td>
					<select name="attribute_store_as" id="attribute_store_as">
						<option value="taxonomy" <?php selected( $att_store_as, 'tax' ); ?>><?php _e( 'Taxonomy' ); ?></option>
						<option value="meta" <?php selected( $att_store_as, 'meta' ); ?>><?php _e( 'Meta Value' ); ?></option>
						<?php do_action( 'rt_wp_attributes_admin_attribute_store_as' ); ?>
					</select>
					<p class="description"><?php _e( 'Determines how you want to store attributes.' ); ?></p>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row">
					<label for="attribute_render_type"><?php _e( 'Render Type' ); ?></label>
				</th>
				<td>
					<select name="attribute_render_type" id="attribute_render_type">
						<optgroup label="Taxonomy">
							<!--<option value="autocomplete" <?php selected( $att_render_type, 'autocomplete' ); ?>><?php _e( 'Autocomplete' ); ?></option>-->
							<option value="dropdown" <?php selected( $att_render_type, 'dropdown' ); ?>><?php _e( 'Dropdown' ); ?></option>
							<option value="checklist" <?php selected( $att_render_type, 'checklist' ); ?>><?php _e( 'Checklist' ); ?></option>
							<!--<option value="radio" <?php selected( $att_render_type, 'radio' ); ?>><?php _e( 'Radio' ); ?></option>-->
							<option value="rating-stars" <?php selected( $att_render_type, 'rating-stars' ); ?>><?php _e( 'Rating Stars' ); ?></option>
						</optgroup>
						<optgroup label="Meta">
							<option value="date" <?php selected( $att_render_type, 'date' ); ?>><?php _e( 'Date' ); ?></option>
							<option value="datetime" <?php selected( $att_render_type, 'datetime' ); ?>><?php _e( 'Date & Time' ); ?></option>
							<option value="currency" <?php selected( $att_render_type, 'currency' ); ?>><?php _e( 'Currency' ); ?></option>
							<option value="text" <?php selected( $att_render_type, 'text' ); ?>><?php _e( 'Text' ); ?></option>
							<!--<option value="richtext" <?php selected( $att_render_type, 'richtext' ); ?>><?php _e( 'Rich Text' ); ?></option>-->
						</optgroup>
						<?php do_action( 'rt_wp_attributes_admin_attribute_render_types' ); ?>
					</select>
					<p class="description"><?php _e( 'Determines how you select attributes.' ); ?></p>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row">
					<label for="attribute_orderby"><?php _e( 'Default sort order' ); ?></label>
				</th>
				<td>
					<select name="attribute_orderby" id="attribute_orderby">
						<option value="menu_order" <?php selected( $att_orderby, 'menu_order' ); ?>><?php _e( 'Custom ordering' ); ?></option>
						<option value="name" <?php selected( $att_orderby, 'name' ); ?>><?php _e( 'Name' ); ?></option>
						<option value="id" <?php selected( $att_orderby, 'id' ); ?>><?php _e( 'Term ID' ); ?></option>
					</select>
					<p class="description"><?php _e( 'Determines the sort order on the frontend for this attribute.' ); ?></p>
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit"><input type="submit" name="save_attribute" id="submit" class="button-primary" value="<?php _e( 'Update' ); ?>"></p>
		<?php //nonce ?>
	</form>
</div>