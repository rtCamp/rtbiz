<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-rt-biz-help
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Biz_Help' ) ) {

	class Rt_Biz_Help {

		var $tabs = array();
		var $help_sidebar_content;

		public function __construct() {
			add_action( 'init', array( $this, 'init_help' ) );
		}

		function init_help() {
			$this->tabs = apply_filters( 'rt_biz_help_tabs', array(
				'post-new.php' => array(
					array(
						'id' => 'create_person_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'post_type' => rt_biz_get_person_post_type(),
					),
					array(
						'id' => 'create_person_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'post_type' => rt_biz_get_person_post_type(),
					),
					array(
						'id' => 'create_organization_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'post_type' => rt_biz_get_organization_post_type(),
					),
					array(
						'id' => 'create_organization_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'post_type' => rt_biz_get_organization_post_type(),
					),
				),
				'post.php' => array(
					array(
						'id' => 'edit_person_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'post_type' => rt_biz_get_person_post_type(),
					),
					array(
						'id' => 'edit_person_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'post_type' => rt_biz_get_person_post_type(),
					),
					array(
						'id' => 'edit_organization_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'post_type' => rt_biz_get_organization_post_type(),
					),
					array(
						'id' => 'edit_organization_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'post_type' => rt_biz_get_organization_post_type(),
					),
				),
				'edit.php' => array(
					array(
						'id' => 'person_list_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'post_type' => rt_biz_get_person_post_type(),
					),
					array(
						'id' => 'person_list_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'post_type' => rt_biz_get_person_post_type(),
					),
					array(
						'id' => 'organization_list_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'post_type' => rt_biz_get_organization_post_type(),
					),
					array(
						'id' => 'organization_list_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'post_type' => rt_biz_get_organization_post_type(),
					),
				),
				'admin.php' => array(
					array(
						'id' => 'dashboard_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'page' => Rt_Biz::$dashboard_slug,
					),
					array(
						'id' => 'dashboard_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'page' => Rt_Biz::$dashboard_slug,
					),
					array(
						'id' => 'acl_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'page' => Rt_Biz::$access_control_slug,
					),
					array(
						'id' => 'acl_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'page' => Rt_Biz::$access_control_slug,
					),
					array(
						'id' => 'settings_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'page' => Rt_Biz::$settings_slug,
					),
//					array(
//						'id' => 'settings_screen_content',
//						'title' => __( 'Screen Content' ),
//						'content' => '',
//						'page' => Rt_Biz::$settings_slug,
//					),
					array(
						'id' => 'attributes_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'page' => Rt_Biz_Attributes::$attributes_page_slug,
					),
					array(
						'id' => 'attributes_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'page' => Rt_Biz_Attributes::$attributes_page_slug,
					),
				),
				'edit-tags.php' => array(
					array(
						'id' => 'user_group_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'taxonomy' => 'user-group',
					),
					array(
						'id' => 'user_group_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'taxonomy' => 'user-group',
					),
				),
					) );

			$documentation_link = apply_filters( 'rt_biz_help_documentation_link', '#' );
			$support_forum_link = apply_filters( 'rt_biz_help_support_forum_link', '#' );
			$this->help_sidebar_content = apply_filters( 'rt_biz_help_sidebar_content', '<p><strong>' . __( 'For More Information : ' ) . '</strong></p><p><a href="' . $documentation_link . '">' . __( 'Documentation' ) . '</a></p><p><a href="' . $support_forum_link . '">' . __( 'Support Forum' ) . '</a></p>' );

			add_action( 'current_screen', array( $this, 'check_tabs' ) );
		}

		function check_tabs() {
			if ( isset( $this->tabs[ $GLOBALS[ 'pagenow' ] ] ) ) {
				switch ( $GLOBALS[ 'pagenow' ] ) {
					case 'post-new.php':
					case 'edit.php':
						if ( isset( $_GET[ 'post_type' ] ) ) {
							foreach ( $this->tabs[ $GLOBALS[ 'pagenow' ] ] as $args ) {
								if ( $args[ 'post_type' ] == $_GET[ 'post_type' ] ) {
									$this->add_tab( $args );
								}
							}
						}
						break;
					case 'post.php':
						if ( isset( $_GET[ 'post' ] ) ) {
							$post_type = get_post_type( $_GET[ 'post' ] );
							foreach ( $this->tabs[ $GLOBALS[ 'pagenow' ] ] as $args ) {
								if ( $args[ 'post_type' ] == $post_type ) {
									$this->add_tab( $args );
								}
							}
						}
						break;
					case 'admin.php':
						if ( isset( $_GET[ 'page' ] ) ) {
							foreach ( $this->tabs[ $GLOBALS[ 'pagenow' ] ] as $args ) {
								if ( $args[ 'page' ] == $_GET[ 'page' ] ) {
									$this->add_tab( $args );
								}
							}
						}
						break;
					case 'edit-tags.php':
						if ( isset( $_GET[ 'taxonomy' ] ) ) {
							foreach ( $this->tabs[ $GLOBALS[ 'pagenow' ] ] as $args ) {
								if ( $args[ 'taxonomy' ] == $_GET[ 'taxonomy' ] ) {
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
				'id' => $args[ 'id' ],
				'title' => $args[ 'title' ],
				// You can directly set content as well.
//				'content' => $args[ 'content' ],
				// This is for some extra content & logic
				'callback' => array( $this, 'tab_content' ),
			) );
			get_current_screen()->set_help_sidebar( $this->help_sidebar_content );
		}

		function tab_content( $screen, $tab ) {
			// Some Extra content with logic
			switch ( $tab[ 'id' ] ) {
				case 'create_person_overview':
				case 'edit_person_overview':
					?>
					<p>
						<?php _e( 'From this screen you can add new Person into the system.' ); ?>
						<?php _e( 'You can fill up optional additional details related to person such as Email, Phone Number, Address etc.' ); ?>
						<?php _e( 'Those can be updated later on from the Edit Person screen as well.' ); ?>
					</p>
					<?php
					break;
				case 'create_person_screen_content':
				case 'edit_person_screen_content':
					?>
					<p><?php _e( 'There are a few sections where you can save essential information about an Person : ' ); ?></p>
					<ul>
						<li><?php _e( 'There is a textbox for the title of a person.' ); ?></li>
						<li><?php _e( 'You can also put any description/comments related to the person in to the rich text editor provided.' ); ?></li>
						<li>
							<?php _e( 'There\'s a metabox to decide whether the person is a team mate or any other contact.' ); ?>
							<?php _e( 'You can mark the checkbox accordingly for that.' ); ?>
						</li>
						<li>
							<?php _e( 'There might be other extra attributes metaboxes depending upon how you add an attribute from the attributes page' ); ?>
							<a href="<?php echo add_query_arg( array( 'page' => Rt_Biz_Attributes::$attributes_page_slug ), admin_url( 'admin.php' ) ); ?>"><?php _e( 'here' ); ?></a>.
						</li>
						<li>
							<?php _e( 'You will see a numerous "Connected X" metaboxes in the side colum.' ); ?>
							<?php _e( 'They are the supportive modules of the system which are connected to the Person.' ); ?>
							<?php _e( 'E.g., An Organization is connected to a person since a person can be a part of an organization.' ); ?>
							<?php _e( 'You can select any entity from the metabox to connect it to the person.' ); ?>
						</li>
						<li>
							<?php _e( 'There might be metaboxes visible depending upon the plugins you\'ve activated on the site.' ); ?>
							<?php _e( 'E.g., If HRM Module is activated then "Documents" metabox & "Leaves" metabox also will be displayed for those who are team mates.' ); ?>
						</li>
					</ul>
					<?php
					break;
				case 'create_organization_overview':
				case 'edit_organization_overview':
					?>
					<p>
						<?php _e( 'From this screen you can add new Organization into the system.' ); ?>
						<?php _e( 'You can fill up optional additional details related to organization such as Email, Phone Number, Address etc.' ); ?>
						<?php _e( 'Those can be updated later on from the Edit Organization screen as well.' ); ?>
					</p>
					<?php
					break;
				case 'create_organization_screen_content':
				case 'edit_organization_screen_content':
					?>
					<p><?php _e( 'There are a few sections where you can save essential information about an Organization : ' ); ?></p>
					<ul>
						<li><?php _e( 'There is a textbox for the title of a organization.' ); ?></li>
						<li><?php _e( 'You can also put any description/comments related to the organization in to the rich text editor provided.' ); ?></li>
						<li>
							<?php _e( 'There might be other extra attributes metaboxes depending upon how you add an attribute from the attributes page' ); ?>
							<a href="<?php echo add_query_arg( array( 'page' => Rt_Biz_Attributes::$attributes_page_slug ), admin_url( 'admin.php' ) ); ?>"><?php _e( 'here' ); ?></a>.
						</li>
						<li>
							<?php _e( 'You will see a "Connected X" metaboxes in the side colum.' ); ?>
							<?php _e( 'They are the supportive modules of the system which are connected to the Organization.' ); ?>
							<?php _e( 'E.g., An Organization is connected to a person since a person can be a part of an organization.' ); ?>
							<?php _e( 'You can select any entity from the metabox to connect it to the organization.' ); ?>
						</li>
					</ul>
					<?php
					break;
				case 'person_list_overview':
					$title = __( 'Contacts' );
					if ( isset( $_GET[ 'rt-biz-my-team' ] ) && $_GET[ 'rt-biz-my-team' ] ) {
						$title = __( 'Employees' );
					}
					?>
					<p>
						<?php echo __( 'This screen provides access to all' ) . ' ' . $title . __( '. You can customize the display of this screen to suit your workflow.' ); ?>
					</p>
					<?php
					break;
				case 'person_list_screen_content':
					?>
					<p><?php _e( 'You can customize the display of this screen’s contents in a number of ways :' ); ?></p>
					<ul>
						<li><?php _e( 'You can hide/display columns based on your needs and decide how many people to list per screen using the Screen Options tab.' ); ?></li>
						<li>
							<?php _e( 'You can filter the list of people by status using the text links in the upper left to show All, Published, Draft, or Trashed people.' ); ?>
							<?php _e( 'The default view is to show all people.' ); ?>
						</li>
						<li>
							<?php _e( 'You can view people in a simple title list or with an excerpt.' ); ?>
							<?php _e( 'Choose the view you prefer by clicking on the icons at the top of the list on the right.' ); ?>
						</li>
						<li>
							<?php _e( 'You can refine the list to show only people in a specific category or from a specific month by using the dropdown menus above the people list.' ); ?>
							<?php _e( 'Click the Filter button after making your selection.' ); ?>
							<?php _e( 'You also can refine the list by clicking on the author, organization or tag in the people list.' ); ?>
						</li>
						<li><?php _e( 'You can also see the entity counts for respective modules, if activated, such as Lead Count, Ticket Count etc.' ) ?></li>
					</ul>
					<?php
					break;
				case 'organization_list_overview':
					?>
					<p>
						<?php _e( 'This screen provides access to all Organizations. You can customize the display of this screen to suit your workflow.' ); ?>
					</p>
					<?php
					break;
				case 'organization_list_screen_content':
					?>
					<p><?php _e( 'You can customize the display of this screen’s contents in a number of ways :' ); ?></p>
					<ul>
						<li><?php _e( 'You can hide/display columns based on your needs and decide how many organizations to list per screen using the Screen Options tab.' ); ?></li>
						<li>
							<?php _e( 'You can filter the list of organizations by status using the text links in the upper left to show All, Published, Draft, or Trashed organizations.' ); ?>
							<?php _e( 'The default view is to show all organizations.' ); ?>
						</li>
						<li>
							<?php _e( 'You can view organizations in a simple title list or with an excerpt.' ); ?>
							<?php _e( 'Choose the view you prefer by clicking on the icons at the top of the list on the right.' ); ?>
						</li>
						<li>
							<?php _e( 'You can refine the list to show only organizations in a specific category or from a specific month by using the dropdown menus above the organizations list.' ); ?>
							<?php _e( 'Click the Filter button after making your selection.' ); ?>
							<?php _e( 'You also can refine the list by clicking on the author, country or attribute in the organizations list.' ); ?>
						</li>
						<li><?php _e( 'You can also see the entity counts for respective modules, if activated, such as Lead Count, Ticket Count etc.' ) ?></li>
					</ul>
					<?php
					break;
				case 'dashboard_overview':
					$menu_label = Rt_Biz_Settings::$settings[ 'menu_label' ];
					?>
					<p>
						<?php echo sprintf( __( 'Welcome to your %s Dashboard!' ), $menu_label ); ?>
						<?php _e( 'You can get help for any screen by clicking the Help tab in the upper corner.' ); ?>
					</p>
					<?php
					break;
				case 'dashboard_screen_content':
					?>
					<p>
						<?php _e( 'This screen will give you the generic overview of the contacts, colleagues within the system.' ) ?>
						<?php _e( 'It will show the various chart distribution based on the attributes assigned to the contacts & their terms.' ); ?>
					</p>
					<?php
					break;
				case 'user_group_overview':
					?>
					<p>
						<?php _e( 'This screen is useful when you have to introduce departments within your organization.' ); ?>
						<?php _e( 'You can create, edit, delete departments & perfom other CRUD operations from here.' ); ?>
						<?php _e( 'These departments can be later assigned to contacts to further categorize them.' ); ?>
						<?php _e( 'They will also be useful in defining Access Control for the system & its other modules.' ); ?>
					</p>
					<?php
					break;
				case 'user_group_screen_content':
					?>
					<ul>
						<li><?php _e( 'Using the left column form, you can create new departments.' ); ?></li>
						<li><?php _e( 'You can assign an group email address to the department as well, if in use.' ); ?></li>
						<li><?php _e( 'You can also assign a color code to the department. It will help you identify the department or the user from which department he is just by the color.' ); ?></li>
						<li><?php _e( 'On the right column, there will be existing departments listed along with basic information related to the department.' ); ?></li>
						<li><?php _e( 'You can edit an individual department on the Edit Department Screen.' ); ?></li>
					</ul>
					<?php
					break;
				case 'acl_overview':
					?>
					<p>
						<?php _e( 'This screen will define the usability matrix for the users of the system.' ); ?>
						<?php _e( 'It will decide who will get how much access of the system based on the access level a user has got.' ); ?>
					</p>
					<?php
					break;
				case 'acl_screen_content':
					$menu_label = Rt_Biz_Settings::$settings[ 'menu_label' ];
					?>
					<ul>
						<li>
							<?php echo sprintf( __( 'First column consists of all the module that has been registered with %s.' ), $menu_label ); ?>
							<?php echo sprintf( __( '%s will itself be registered for its ACL use.' ), $menu_label ); ?>
							<?php echo sprintf( __( 'Hence, %s will be there on the first row by default.' ), $menu_label ); ?>
						</li>
						<li><?php _e( 'Each row will represent the ACL for that particular module.' ); ?></li>
						<li>
							<?php _e( 'Users can be assigned to any of the roles from : Admin, Editor, Author, No Role.' ); ?>
							<ul>
								<li><strong><?php _e( 'No Role :' ); ?></strong> <?php _e( 'This will revoke all access for that particular module from the user.' ); ?></li>
								<li><strong><?php _e( 'Author :' ); ?></strong> <?php _e( 'This almost works like the WordPress Author. User with Author Role will be able to see entitis which are only assigned to him and some of the user level settings.' ); ?></li>
								<li><strong><?php _e( 'Editor :' ); ?></strong> <?php _e( 'Just like WordPress Editor Role, this role will enable users to see his own & others\' entities as well. Also with this, Editor will be able to have some of the editor level settings.' ); ?></li>
								<li><strong><?php _e( 'Admin :' ); ?></strong> <?php _e( 'Admin will have the full control of the module including all entities & admin level settings.' ); ?></li>
							</ul>
						</li>
						<li>
							<strong><?php _e( 'Note :' ); ?></strong>
							<?php _e( 'This matrix is for Group Level Access Control.' ); ?>
							<?php _e( 'It means, any individual user can be assigned to any above roles from his/her WordPress Profile Page which will be assumed Profile Level Access Control.' ); ?>
							<strong><?php _e( 'Group Level Access Control will always be overridden by Profile Level Access Control.' ); ?></strong>
							<strong><?php _e( 'WordPress Site Admins will have the Admin role for all the modules by default.' ); ?></strong>
						</li>
					</ul>
					<?php
					break;
				case 'settings_overview':
					$menu_label = Rt_Biz_Settings::$settings[ 'menu_label' ];
					?>
					<p>
						<?php echo sprintf ( __( 'This screen consists of all the %s settings.' ), $menu_label ); ?>
						<?php _e( 'The settings are divided into different tabs depending upon their functionality.' ); ?>
						<?php _e( 'You can configure & update them according to your choice from here.' ); ?>
						<?php _e( 'There\'s also a buttom named "Reset to Default" which will put all settings to its default values.' ); ?>
					</p>
					<?php
					break;
//				case 'settings_screen_content':
//					// Put Screen Content Option if required.
//					break;
				case 'attributes_overview':
					?>
					<p>
						<?php _e( 'Using this screen, different attributes can be defined & assinged to post types according to the requirement.' ); ?>
						<?php _e( 'All the CRUD operations are possible from this screen related to Attributes.' ); ?>
					</p>
					<?php
					break;
				case 'attributes_screen_content':
					?>
					<ul>
						<li><?php _e( 'From the left column, you can create new attributes.' ); ?></li>
						<li><?php _e( 'You can assign this attribute to any kind of registered post type by marking the checkbox in front of it.' ); ?></li>
						<li><?php _e( 'These configuration can be changed later on from the Edit Attribute Screen.' ); ?></li>
						<li><?php _e( 'E.g., If an attribute is assgined to "Media" first & later if you want to assign it to "Posts"; then you just need to update the checkboxes & it will be in effect.' ); ?></li>
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
