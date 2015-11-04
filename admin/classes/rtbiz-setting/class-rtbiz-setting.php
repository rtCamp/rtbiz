<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 *
 * ReduxFramework Sample Config File
 * For full documentation, please visit: https://docs.reduxframework.com
 * @author utkarsh
 *
 * */
if ( ! class_exists( 'Rtbiz_Setting' ) ) {

	class Rtbiz_Setting {

		public $args = array();
		public $sections = array();
		public $ReduxFramework;
		static $page_slug = 'rt-biz-settings';
		static $biz_opt = 'redux_biz_settings';


		public function __construct() {
			if ( ! class_exists( 'ReduxFramework' ) ) {
				return;
			}
			// init because can not get Biz ACL before that.
			Rtbiz::$loader->add_action( 'p2p_init', $this, 'rtbiz_init_settings', 30 );

			//after redux setting saved
			Rtbiz::$loader->add_action( 'redux/options/' . self::$biz_opt . '/saved', $this, 'rtbiz_on_redux_save', 10, 2 );

			Rtbiz::$loader->add_action( 'redux/loaded', $this, 'rtbiz_remove_demo' );

			// Function to test the compiler hook and demo CSS output.
			// Above 10 is a priority, but 2 in necessary to include the dynamically generated CSS to be sent to the function.
			// add_filter('redux/options/'.$this->args['opt_name'].'/compiler', array( $this, 'rtbiz_compiler_action' ), 10, 3);
			// Change the arguments after they've been declared, but before the panel is created
			// add_filter('redux/options/'.$this->args['opt_name'].'/args', array( $this, 'rtbiz_change_arguments' ) );
			// Change the default value of a field after it's been set, but before it's been useds
			// add_filter('redux/options/'.$this->args['opt_name'].'/defaults', array( $this,'rtbiz_change_defaults' ) );
			// Dynamically add a section. Can be also used to modify sections/fields
			// add_filter('redux/options/' . $this->args['opt_name'] . '/sections', array($this, 'rtbiz_dynamic_section'));
			//add_action("redux/options/{$this->args[ 'opt_name' ]}/register", array( $this, 'test') );
		}

		public function rtbiz_on_redux_save( $setting, $old_setting ) {
			//removed product sync option
			$diff = array();
			if ( ! empty( $setting['product_plugin'] ) && ! empty( $old_setting['product_plugin'] ) ) {
				$arr_diff = array_diff_assoc( $setting['product_plugin'], $old_setting['product_plugin'] );
				while ( $val = current( $arr_diff ) ) {
					if ( '1' == $val ) {
						$diff[] = key( $arr_diff );
					}
					next( $arr_diff );
				}
				$diff = array_unique( $diff );
			}
			if ( ! empty( $diff ) ) {
				update_option( 'rt_product_plugin_sync', 'true' );
			} else {
				update_option( 'rt_product_plugin_sync', 'false' );
			}
		}


		public function rtbiz_init_settings() {

			// Set the default arguments
			$this->rtbiz_set_arguments();

			// Set a few help tabs so you can see how it's done
			//			$this->set_helptabs();

			// Create the sections and fields
			$this->rtbiz_set_sections();

			if ( ! isset( $this->args['opt_name'] ) ) { // No errors please
				return;
			}

			$this->ReduxFramework = new ReduxFramework( $this->sections, $this->args );

			return true;
		}

		/**
		 *
		 * Custom function for filtering the sections array. Good for child themes to override or add to the sections.
		 * Simply include this function in the child themes functions.php file.
		 *
		 * NOTE: the defined constants for URLs, and directories will NOT be available at this point in a child theme,
		 * so you must use get_template_directory_uri() if you want to use any of the built in icons
		 * */
		function rtbiz_dynamic_section( $sections ) {
			//$sections = array();
			$sections[] = array(
				'title'  => __( 'Section via hook', 'redux-framework-demo' ),
				'desc'   => __( '<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', 'redux-framework-demo' ),
				'icon'   => 'el-icon-paper-clip',
				// Leave this as a blank section, no options just some intro text set above.
				'fields' => array(),
			);

			return $sections;
		}

		/**
		 *
		 * Filter hook for filtering the args. Good for child themes to override or add to the args array. Can also be used in other functions.
		 * */
		function rtbiz_change_arguments( $args ) {
			//$args['dev_mode'] = true;

			return $args;
		}

		/**
		 *
		 * Filter hook for filtering the default value of any given field. Very useful in development mode.
		 * */
		function rtbiz_change_defaults( $defaults ) {
			$defaults['str_replace'] = 'Testing filter hook!';

			return $defaults;
		}

		// Remove the demo link and the notice of integrated demo from the redux-framework plugin
		function rtbiz_remove_demo() {

			// Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
			if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
				remove_filter( 'plugin_row_meta', array(
					ReduxFrameworkPlugin::instance(),
					'plugin_metalinks'
				), null, 2 );

				// Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
				remove_action( 'admin_notices', array( ReduxFrameworkPlugin::instance(), 'admin_notices' ) );
			}

		}

		/**
		 * @return bool
		 */
		public function rtbiz_set_sections() {
			$admin_cap  = rtbiz_get_access_role_cap( RTBIZ_TEXT_DOMAIN, 'admin' );
			$editor_cap = rtbiz_get_access_role_cap( RTBIZ_TEXT_DOMAIN, 'editor' );

			// ACTUAL DECLARATION OF SECTIONS
			$general_fields = array(
				array(
					'id'       => 'product_plugin',
					'title'    => __( 'Product Sync Option' ),
					'subtitle' => __( 'Select the plugin you want to use for product sync.' ),
					'desc'     => __( 'The option you choose here will define which existing products needs to be taken from either WooCommerce or Easy Digital Downloads and synchronize them with the terms of this special attribute taxonomy Products. So that rtBiz / any other plugin can assign these products to any custom post types that are registered with this taxonomy.' ),
					'type'     => 'checkbox',
					'options'  => array(
						//'none'         => __( 'None' ),
						'woocommerce' => __( 'WooCommerce' ),
						'edd'         => __( 'Easy Digital Download' ),
					),
					'default'  => 'none',
				),
			);

			$this->sections[] = array(
				'icon'        => 'el-icon-cogs',
				'title'       => __( 'General' ),
				'permissions' => $admin_cap,
				'fields'      => $general_fields,
			);

			$contact_labels = rtbiz_get_contact_labels();

			$contact_importer_subtitle = __( '<div class="redux_field_th">Import WordPress Users to ' . $contact_labels['name'] . '</div>' );
			$contact_importer_subtitle .= __( 'Use this tool to import all current users to ' . $contact_labels['name'] . '. You can also import selected users from ' );
			$contact_importer_subtitle .= '<a href="' . admin_url( 'users.php' ) . '">WP users</a> page.';
			$contact_importer_subtitle .= __( '<br/>All new users will automatically get exported as ' . $contact_labels['name'] . '.<br/> <p class="redux-container-multi_text rtbiz-import-contact-warning"><span class="redux-multi-text-remove">Importing ' . $contact_labels['name'] . ' is a heavy process. So please be patient.</span></p><br/>' );
			$contact_importer_subtitle .= rtbiz_export_wp_users_to_contacts();
			$this->sections[] = array(
				'title'       => $contact_labels['singular_name'] . ' ' . __( 'Importer' ),
				'icon'        => 'el-icon-list-alt',
				'permissions' => $editor_cap,
				//'subsection'  => true,
				'fields'      => array(
					array(
						'id'      => 'rtbiz_import_users_to_contacts',
						'type'    => 'raw',
						'content' => $contact_importer_subtitle,
					),
				),
			);

			$this->sections[] = array(
				'icon'        => 'el-icon-envelope',
				'title'       => __( 'Mailbox List' ),
				'permissions' => $admin_cap,
				'fields'      => array(
					array(
						'id'       => 'rtbiz_Mailbox_list',
						'type'     => 'callback',
						'title'    => 'Mailbox List',
						'subtitle' => __( 'All Configured Mailbox(s)' ),
						//'desc'    => 'Following mailboxes have been configured for various modules. Emails from these mailboxes will be parsed and Helpdesk will use them to create new ticket / add new followup accordingly. You can configure these mailboxes from <a href="'.add_query_arg( 'page', RT_BIZ_Configuration::$page_slug, admin_url( 'admin.php' ) ).'"rtBiz</a>',
						'callback' => 'rtbiz_mailbox_list_view',
					),
				),
			);
			// If email template addon is active then add new tab called addon in redux setting and also add individual addon on/off setting
			$addons = array();
			$addons = apply_filters( 'rtbiz_add_addon_settings', $addons );
			if ( ! empty( $addons ) ) {
				$this->sections[] = array(
					'icon'        => 'el-icon-adjust-alt',
					'title'       => __( 'Add-ons' ),
					'permissions' => $admin_cap,
				);
				$this->sections   = array_merge( $this->sections, $addons );
			}

			return true;
		}

		/**
		 *
		 * All the possible arguments for Redux.
		 * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
		 * */
		public function rtbiz_set_arguments() {

			//$theme = wp_get_theme(); // For use with some settings. Not necessary.
			$editor_cap = rtbiz_get_access_role_cap( RTBIZ_TEXT_DOMAIN, 'editor' );
			$this->args = array(
				// TYPICAL -> Change these values as you need/desire
				'opt_name'           => self::$biz_opt,
				// This is where your data is stored in the database and also becomes your global variable name.
				'display_name'       => __( 'Settings' ),
				// Name that appears at the top of your panel
				'display_version'    => RTBIZ_VERSION,
				// Version that appears at the top of your panel
				'menu_type'          => 'submenu',
				//Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
				'allow_sub_menu'     => false,
				// Show the sections below the admin menu item or not
				'menu_title'         => __( 'Settings' ),
				'page_title'         => __( 'Settings' ),
				// You will need to generate a Google API key to use this feature.
				// Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
				'google_api_key'     => '',
				// Must be defined to add google fonts to the typography module
				'async_typography'   => true,
				// Use a asynchronous font on the front end or font string
				//'disable_google_fonts_link' => true,                    // Disable this in case you want to create your own google fonts loader
				'admin_bar'          => false,
				// Show the panel pages on the admin bar
				'global_variable'    => '',
				// Set a different name for your global variable other than the opt_name
				'dev_mode'           => false,
				// Show the time the page took to load, etc
				'customizer'         => false,
				// Enable basic customizer support
				//'open_expanded'     => true,                    // Allow you to start the panel in an expanded way initially.
				//'disable_save_warn' => true,                    // Disable the save warning when a user changes a field
				// OPTIONAL -> Give you extra features
				'page_priority'      => null,
				// Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
				'page_parent'        => Rtbiz_Dashboard::$page_slug,
				// For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
				'page_permissions'   => $editor_cap,
				// Permissions needed to access the options panel.
				//'menu_icon' => '', // Specify a custom URL to an icon
				//'last_tab' => '', // Force your panel to always open to a specific tab (by id)
				//'page_icon' => 'icon-themes', // Icon displayed in the admin panel next to your menu_title
				'page_slug'          => self::$page_slug,
				// Page slug used to denote the panel
				'save_defaults'      => true,
				// On load save the defaults to DB before user clicks save or not
				'default_show'       => true,
				// If true, shows the default value next to each field that is not the default value.
				'default_mark'       => '',
				// What to print by the field's title if the value shown is default. Suggested: *
				'show_import_export' => true,
				// Shows the Import/Export panel when not used as a field.
				// CAREFUL -> These options are for advanced use only
				'transient_time'     => 60 * MINUTE_IN_SECONDS,
				'output'             => true,
				// Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
				'output_tag'         => true,
				// Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
				// 'footer_credit'     => '',                   // Disable the footer credit of Redux. Please leave if you can help it.
				// FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
				'database'           => '',
				// possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
				'system_info'        => false,
				// REMOVE
				// HINTS
				'hints'              => array(
					'icon'          => 'icon-question-sign',
					'icon_position' => 'right',
					'icon_color'    => 'lightgray',
					'icon_size'     => 'normal',
					'tip_style'     => array(
						'color'   => 'light',
						'shadow'  => true,
						'rounded' => false,
						'style'   => '',
					),
					'tip_position'  => array(
						'my' => 'top left',
						'at' => 'bottom right',
					),
					'tip_effect'    => array(
						'show' => array(
							'effect'   => 'slide',
							'duration' => '500',
							'event'    => 'mouseover',
						),
						'hide' => array(
							'effect'   => 'slide',
							'duration' => '500',
							'event'    => 'click mouseleave',
						),
					),
				),
			);

			return true;
		}

	}

}
