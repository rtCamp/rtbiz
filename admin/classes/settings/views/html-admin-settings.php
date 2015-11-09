<?php
/**
 * Created by PhpStorm.
 * User: spock
 * Date: 5/11/15
 * Time: 10:23 AM
 */

/**
 * Admin View: Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="wrap rtbiz">
	<form method="<?php echo esc_attr( apply_filters( 'rtbiz_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
		<div class="icon32 icon32-rtbiz-settings" id="icon-rtbiz"><br /></div><h2 class="nav-tab-wrapper rtbiz-nav-tab-wrapper">
			<?php
			foreach ( $tabs as $name => $label ) {
				if (!empty($setting_page_url)){
					$href = $setting_page_url. '&tab='.$name;
				} else {
					$href = admin_url( 'edit.php?post_type='.$slug.'&page=rtbiz-'.$slug.'-settings&tab=' . $name );
				}
				echo '<a href="' . $href . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
			}

			do_action( 'rtbiz_settings_tabs' );
			?>
		</h2>

		<?php
		do_action( 'rtbiz_sections_' . $current_tab );
		do_action( 'rtbiz_settings_' . $current_tab );
		?>

		<p class="submit">
			<?php if ( ! isset( $GLOBALS['hide_save_button'] ) ) : ?>
				<input name="save" class="button-primary" type="submit" value="<?php esc_attr_e( 'Save changes', 'rtbiz' ); ?>" />
			<?php endif; ?>
			<input type="hidden" name="subtab" id="last_tab" />
			<?php wp_nonce_field( 'rtbiz-settings' ); ?>
		</p>
	</form>
</div>
