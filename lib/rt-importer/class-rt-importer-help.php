<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rt_Importer_Help' ) ) {

	class Rt_Importer_Help {

		var $tabs = array();
		var $help_sidebar_content;

		public function __construct() {
			add_action( 'init', array( $this, 'init_help' ) );
		}

		function init_help() {

			$this->tabs = apply_filters( 'rt_biz_help_tabs', array(
				'admin.php' => array(
					array(
						'id'      => 'importer_overview',
						'title'   => __( 'Overview' ),
						'content' => '',
						'page'    => Rt_Importer::$page_slug,
					),
					array(
						'id'      => 'importer_screen_content',
						'title'   => __( 'Screen Content' ),
						'content' => '',
						'page'    => Rt_Importer::$page_slug,
					),
					array(
						'id'      => 'importer_mapper_overview',
						'title'   => __( 'Overview' ),
						'content' => '',
						'page'    => Rt_Importer_Mapper::$page_slug,
					),
					array(
						'id'      => 'importer_mapper_screen_content',
						'title'   => __( 'Screen Content' ),
						'content' => '',
						'page'    => Rt_Importer_Mapper::$page_slug,
					),
				),
			) );

			$documentation_link         = apply_filters( 'rt_biz_help_documentation_link', 'https://github.com/rtCamp/rt-lib/' );
			$this->help_sidebar_content = apply_filters( 'rt_biz_help_sidebar_content', '<p><strong>' . __( 'For More Information : ' ) . '</strong></p><p><a href="' . $documentation_link . '">' . __( 'Documentation' ) . '</a></p>' );

			add_action( 'current_screen', array( $this, 'check_tabs' ) );
		}

		function check_tabs() {

			if ( isset( $this->tabs[ $GLOBALS['pagenow'] ] ) ) {
				switch ( $GLOBALS['pagenow'] ) {
					case 'admin.php':
						if ( isset( $_GET['page'] ) ) {
							foreach ( $this->tabs[ $GLOBALS['pagenow'] ] as $args ) {
								if ( $args['page'] == $_GET['page'] ) {
									$this->add_tab( $args );
								}
							}
						}
						break;
				}
			}

		}

		function add_tab( $args ) {
			get_current_screen()->add_help_tab( array(
				'id'       => $args['id'],
				'title'    => $args['title'],
				'callback' => array( $this, 'tab_content' ),
			) );
			get_current_screen()->set_help_sidebar( $this->help_sidebar_content );
		}

		function tab_content( $screen, $tab ) {
			// Some Extra content with logic
			switch ( $tab['id'] ) {
				case 'importer_overview':
					?>
					<p>
						<?php _e( 'Importer module helps you configure your gravity form with plugin that uses this library.' ); ?>
						<?php _e( 'Once configured properly, this module is useful to rtBiz and its addons in many ways.' ); ?>
					</p>
					<p>
						<?php _e( 'Consider this to be a generic utility which lets you connect your gravity form with any plugin.' ); ?>
						<?php _e( 'Once it is connected, this module starts parsing entries from your gravity form and delivers them to you / your addon.' ); ?>
						<?php _e( 'With those entries received from gravity form, you can do alot many things that you can imagine.' ); ?>
					</p>
					<p>
						<?php echo sprintf( __( 'For example, if a new entry comes to gravity form then importer module parses the entry and delivers it to you.' ), '<code>support@example.com</code>' ); ?>
						<?php _e( 'You can develop such functionality within one of your rtBiz addon & it will work like a charm.' ); ?>
					</p>
					<p>
						<?php _e( 'This was just one use case. There are many more things that you can achieve using this module. Please feel free to contact us in case you have a wonderful idea that we can help you with.' ); ?>
					</p>
					<?php
					break;
				case 'importer_screen_content':
					?>
					<ul>
						<li><?php _e( 'Importer library will give you a admin page where you can setup this module for your plugin.' ); ?></li>
						<li><?php _e( 'New  importer can be added from this tab.' ); ?></li>
						<li><?php _e( 'By default, Gravity tabs is active on importer page. In future CSV importer will be added here.' ); ?></li>
						<li><?php _e( 'You will need to select CPT( rtBiz module post type ) and gravity form for which you need to setup importer.  Click on Next button.' ); ?></li>
						<li><?php _e( 'New panel becomes visible to map gravity form field with your module field.' ); ?></li>
						<li><?php _e( 'After choosing correct options for mapping, click on Import button and the importer will start importing all existing records into your module' ); ?></li>
						<li><?php _e( 'Once importing is done, it will ask to import the future entries as well or not. ' ); ?></li>
					</ul>
					<?php
					break;
				case 'importer_mapper_overview':
					?>
					<p>
						<?php _e( 'Importer library will give you a admin page where you perform different action on importer mapper.' ); ?>
					</p>
					<?php
					break;
				case 'importer_mapper_screen_content':
					?>
					<ul>
						<li><?php _e( 'This screen provides access to enable/Disable and remove importer mapping  permanently.' ); ?></li>
					</ul>
					<?php
					break;
				default:
					do_action( 'rt_biz_help_tab_content', $screen, $tab );
					break;
			}
		}

	}

}
