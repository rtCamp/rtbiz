<?php
/**
 * User: udit, utkarsh
 * Date: 12/22/14
 * Time: 1:17 PM
 */

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rt_Mailbox_Help' ) ) {

	class Rt_Mailbox_Help {

		var $tabs = array();
		var $help_sidebar_content;

		public function __construct() {
			add_action( 'init', array( $this, 'init_help' ) );
		}

		function init_help() {

			$this->tabs = apply_filters( 'rt_biz_help_tabs', array(
				'admin.php' => array(
					array(
						'id'      => 'mailbox_overview',
						'title'   => __( 'Overview' ),
						'content' => '',
						'page'    => Rt_Mailbox::$page_name,
					),
					array(
						'id'      => 'mailbox_screen_content',
						'title'   => __( 'Screen Content' ),
						'content' => '',
						'page'    => Rt_Mailbox::$page_name,
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
				case 'mailbox_overview':
					?>
					<p>
						<?php _e( 'Mailbox module helps you configure your emails with plugin that uses this library.' ); ?>
						<?php _e( 'Once configured properly, this module is useful to rtBiz and its addons in many ways.' ); ?>
					</p>
					<p>
						<?php _e( 'Consider this to be a generic utility which lets you connect your mailbox with any plugin.' ); ?>
						<?php _e( 'Once it is connected, this module starts parsing emails from your mailbox and delivers them to you / your addon.' ); ?>
						<?php _e( 'With those emails received from mailbox module, you can do alot many things that you can imagine.' ); ?>
					</p>
					<p>
						<?php echo sprintf( __( 'For example, if a new email comes to %s then mailbox module parses the email and delivers it to you.' ), '<code>support@example.com</code>' ); ?>
						<?php echo sprintf( __( 'You could setup a beautiful canned reply message as a response to every email that comes to %s.' ), '<code>support@example.com</code>' ); ?>
						<?php _e( 'You can develop such functionality within one of your rtBiz addon & it will work like a charm.' ); ?>
					</p>
					<p>
						<?php _e( 'This was just one use case. There are many more things that you can achieve using this module. Please feel free to contact us in case you have a wonderful idea that we can help you with.' ); ?>
					</p>
					<?php
					break;
				case 'mailbox_screen_content':
					?>
					<ul>
						<li><?php _e( 'Mailbox library will give you a admin page where you can setup this module for your plugin.' ); ?></li>
						<li><?php _e( 'This screen is divided into two tabs:' ); ?></li>
						<li>
							<strong><?php _e( 'Mailbox:' ) ?></strong>
							<ul>
								<li><?php _e( 'New mailboxes can be added from this tab.' ); ?></li>
								<li><?php _e( 'You will need to select mail server type, and module for which you need the mailbox to be setup.' ); ?></li>
								<li><?php _e( 'Select a mail servers from the existing ones ( Checkout IMAP tab ) and fill in mailbox credentials. And this is it! Your mailbox is added.' ); ?></li>
								<li><?php echo sprintf( __( 'Once a mailbox is added, it will be listed in the mail list. You will need to choose the default %s folder. So that mailbox module will know from where it has to read & parse emails.' ), '<code>INBOX</code>' ); ?></li>
								<li><?php _e( 'You can also add other mail folders from where you want your emails parsed.' ); ?></li>
							</ul>
						</li>
						<li>
							<strong><?php _e( 'IMAP:' ) ?></strong>
							<ul>
								<li><?php _e( 'Here is a list of existing mail servers that can be used in rtBiz Mailbox modules while adding new mailboxes.' ); ?></li>
								<li><?php _e( 'We have put a few popular mail servers for you by default. These will stay in the list always even if you remove them once.' ); ?></li>
								<li><?php _e( 'If you have any private mail server and you want to configure it, then it is possible as well. Just fill in required configurations for the mail server and save them. Once saved, it will appear in the list while adding new mailbox.' ); ?></li>
							</ul>
						</li>
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
