<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
if ( ! class_exists( 'Rtbiz_Help' ) ) {

	class Rtbiz_Help {

		var $tabs = array();
		var $help_sidebar_content;

		public function __construct() {
			add_action( 'init', array( $this, 'init_help' ) );
		}

		function init_help() {
			$this->tabs = apply_filters( 'rtbiz_help_tabs', array(
				'post-new.php'  => array(
					array(
						'id'        => 'create_person_overview',
						'title'     => __( 'Overview' ),
						'content'   => '',
						'post_type' => rtbiz_get_contact_post_type(),
					),
					array(
						'id'        => 'create_person_screen_content',
						'title'     => __( 'Screen Content' ),
						'content'   => '',
						'post_type' => rtbiz_get_contact_post_type(),
					),
					array(
						'id'        => 'create_person_role',
						'title'     => __( 'Role' ),
						'content'   => '',
						'post_type' => rtbiz_get_contact_post_type(),
					),
					array(
						'id'        => 'create_organization_overview',
						'title'     => __( 'Overview' ),
						'content'   => '',
						'post_type' => rtbiz_get_company_post_type(),
					),
					array(
						'id'        => 'create_organization_screen_content',
						'title'     => __( 'Screen Content' ),
						'content'   => '',
						'post_type' => rtbiz_get_company_post_type(),
					),
				),
				'post.php'      => array(
					array(
						'id'        => 'edit_person_overview',
						'title'     => __( 'Overview' ),
						'content'   => '',
						'post_type' => rtbiz_get_contact_post_type(),
					),
					array(
						'id'        => 'edit_person_screen_content',
						'title'     => __( 'Screen Content' ),
						'content'   => '',
						'post_type' => rtbiz_get_contact_post_type(),
					),
					array(
						'id'        => 'edit_person_role',
						'title'     => __( 'Role' ),
						'content'   => '',
						'post_type' => rtbiz_get_contact_post_type(),
					),
					array(
						'id'        => 'edit_organization_overview',
						'title'     => __( 'Overview' ),
						'content'   => '',
						'post_type' => rtbiz_get_company_post_type(),
					),
					array(
						'id'        => 'edit_organization_screen_content',
						'title'     => __( 'Screen Content' ),
						'content'   => '',
						'post_type' => rtbiz_get_company_post_type(),
					),
				),
				'edit.php'      => array(
					array(
						'id'        => 'person_list_overview',
						'title'     => __( 'Overview' ),
						'content'   => '',
						'post_type' => rtbiz_get_contact_post_type(),
					),
					array(
						'id'        => 'person_list_screen_content',
						'title'     => __( 'Screen Content' ),
						'content'   => '',
						'post_type' => rtbiz_get_contact_post_type(),
					),
					array(
						'id'        => 'organization_list_overview',
						'title'     => __( 'Overview' ),
						'content'   => '',
						'post_type' => rtbiz_get_company_post_type(),
					),
					array(
						'id'        => 'organization_list_screen_content',
						'title'     => __( 'Screen Content' ),
						'content'   => '',
						'post_type' => rtbiz_get_company_post_type(),
					),
				),
				'admin.php'     => array(
					array(
						'id'      => 'dashboard_overview',
						'title'   => __( 'Overview' ),
						'content' => '',
						'page'    => Rtbiz_Dashboard::$page_slug,
					),
					array(
						'id'      => 'dashboard_screen_content',
						'title'   => __( 'Screen Content' ),
						'content' => '',
						'page'    => Rtbiz_Dashboard::$page_slug,
					),
					array(
						'id'      => 'acl_overview',
						'title'   => __( 'Overview' ),
						'content' => '',
						'page'    => Rtbiz_Access_Control::$page_slug,
					),
					array(
						'id'      => 'acl_roles',
						'title'   => __( 'Roles' ),
						'content' => '',
						'page'    => Rtbiz_Access_Control::$page_slug,
					),
					array(
						'id'      => 'settings_overview',
						'title'   => __( 'Overview' ),
						'content' => '',
						'page'    => Rtbiz_Access_Control::$page_slug,
					),
					array(
						'id'      => 'attributes_overview',
						'title'   => __( 'Overview' ),
						'content' => '',
						'page'    => Rtbiz_Attributes::$page_slug,
					),
					array(
						'id'      => 'attributes_screen_content',
						'title'   => __( 'Screen Content' ),
						'content' => '',
						'page'    => Rtbiz_Attributes::$page_slug,
					),
				),
				'edit-tags.php' => array(
					array(
						'id'       => 'teams_overview',
						'title'    => __( 'Overview' ),
						'content'  => '',
						'taxonomy' => Rtbiz_Teams::$slug,
					),
					array(
						'id'       => 'teams_screen_content',
						'title'    => __( 'Screen Content' ),
						'content'  => '',
						'taxonomy' => Rtbiz_Teams::$slug,
					),
					array(
						'id'       => 'rt-product_overview',
						'title'    => __( 'Overview' ),
						'content'  => '',
						'taxonomy' => Rt_Products::$product_slug,
					),
					array(
						'id'       => 'rt-product_screen_content',
						'title'    => __( 'Screen Content' ),
						'content'  => '',
						'taxonomy' => Rt_Products::$product_slug,
					),
					/*array(
						'id' => 'contact_group_overview',
						'title' => __( 'Overview' ),
						'content' => '',
						'taxonomy' => Rtbiz_Contact::$user_category_taxonomy,
					),
					array(
						'id' => 'contact_group_screen_content',
						'title' => __( 'Screen Content' ),
						'content' => '',
						'taxonomy' => Rtbiz_Contact::$user_category_taxonomy,
					),*/
				),
			) );

			$documentation_link         = apply_filters( 'rtbiz_help_documentation_link', 'http://docs.rtcamp.com/rtbiz/' );
			$support_forum_link         = apply_filters( 'rtbiz_help_support_forum_link', 'https://rtcamp.com/premium-support/' );
			$this->help_sidebar_content = apply_filters( 'rtbiz_help_sidebar_content', '<p><strong>' . __( 'For More Information -  ' ) . '</strong></p><p><a href="' . $documentation_link . '">' . __( 'Documentation' ) . '</a></p><p><a href="' . $support_forum_link . '">' . __( 'Support Forum' ) . '</a></p>' );

			add_action( 'current_screen', array( $this, 'check_tabs' ) );
		}

		function check_tabs() {
			if ( isset( $this->tabs[ $GLOBALS['pagenow'] ] ) ) {
				switch ( $GLOBALS['pagenow'] ) {
					case 'post-new.php':
					case 'edit.php':
						if ( isset( $_GET['post_type'] ) ) {
							foreach ( $this->tabs[ $GLOBALS['pagenow'] ] as $args ) {
								if ( $args['post_type'] == $_GET['post_type'] ) {
									$this->add_tab( $args );
								}
							}
						}
						break;
					case 'post.php':
						if ( isset( $_GET['post'] ) ) {
							$post_type = get_post_type( $_GET['post'] );
							foreach ( $this->tabs[ $GLOBALS['pagenow'] ] as $args ) {
								if ( $args['post_type'] == $post_type ) {
									$this->add_tab( $args );
								}
							}
						}
						break;
					case 'admin.php':
						if ( isset( $_GET['page'] ) ) {
							foreach ( $this->tabs[ $GLOBALS['pagenow'] ] as $args ) {
								if ( $args['page'] == $_GET['page'] ) {
									$this->add_tab( $args );
								}
							}
						}
						break;
					case 'edit-tags.php':
						if ( isset( $_GET['taxonomy'] ) ) {
							foreach ( $this->tabs[ $GLOBALS['pagenow'] ] as $args ) {
								if ( $args['taxonomy'] == $_GET['taxonomy'] ) {
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
			$module_name = 'rtBiz';
			$cpt_name    = 'post';
			if ( is_plugin_active( 'rtbiz-helpdesk/rtbiz-helpdesk.php' ) && ! empty( $_REQUEST['module'] ) && RTBIZ_HD_TEXT_DOMAIN == $_REQUEST['module'] ) {
				$module_name = 'Helpdesk';
				$cpt_name    = 'tickets';
			}

			switch ( $tab['id'] ) {
				case 'create_person_overview':
				case 'edit_person_overview':
					?>
					<p>
						<?php _e( 'Screen to add customer and staff into the ' . $module_name . ' system.' ); ?>
					</p>
					<?php
					break;
				case 'create_person_screen_content':
				case 'edit_person_screen_content':
					?>
					<ul>
						<li>
							<strong><?php _e( 'Adding Staff - ' ); ?></strong><?php _e( "Check 'Staff member' box and select " . $module_name . ' role for this new staff member' ); ?>
						</li>
						<li>
							<strong><?php _e( 'Adding Customer - ' ); ?></strong><?php _e( "Keep the 'Staff member' box unchecked. Customers have no role for " . $module_name . ' backend' ); ?>
						</li>
						<li>
							<strong><?php _e( 'Connected Users - ' ); ?></strong><?php _e( 'WordPress user account linked to this contact' ); ?>
						</li>
						<li>
							<strong><?php _e( 'Teams - ' ); ?></strong><?php _e( 'Applicable for Staff. The team to which a staff member belongs to' ); ?>
						</li>
						<li>
							<strong><?php _e( 'Additional Information - ' ); ?></strong><?php _e( 'To add social, contact and HR information' ); ?>
						</li>
						<?php if ( 'Helpdesk' == $module_name ) { ?>
							<li>
								<strong><?php _e( 'Helpdesk information - ' ); ?></strong><?php _e( 'Profile level setting to disable all mails from Helpdesk' ); ?>
							</li>
						<?php } ?>
					</ul>
					<?php
					break;
				case 'create_person_role':
				case 'edit_person_role':
					?>
					<ul>
						<li>
							<strong><?php _e( 'Admin - ' ); ?></strong><?php _e( 'Can manage all ' . $cpt_name . ' and ' . $module_name . ' settings.' ); ?>
						</li>
						<li>
							<strong><?php _e( 'Editor - ' ); ?></strong><?php _e( 'Can manage all the ' . $cpt_name . '. No access to settings.' ); ?>
						</li>
						<li>
							<strong><?php _e( 'Author - ' ); ?></strong><?php _e( 'Can manage only the ' . $cpt_name . ' assigned to them. No access to settings.' ); ?>
						</li>
						<li>
							<strong><?php _e( 'No Role - ' ); ?></strong><?php _e( 'No access to Helpdesk backend. Access only to front end of ' . $cpt_name . ' to which a contact is assigned to.' ); ?>
						</li>
					</ul>
					<?php
					break;
				case 'create_organization_overview':
				case 'edit_organization_overview':
					?>
					<p>
						<?php _e( 'From this screen you can add new company into the system.' ); ?>
						<?php _e( 'You can fill up optional additional details related to company such as Email, Phone Number, Address etc.' ); ?>
						<?php _e( 'Those can be updated later on from the Edit Company screen as well.' ); ?>
					</p>
					<?php
					break;
				case 'create_organization_screen_content':
				case 'edit_organization_screen_content':
					?>
					<p><?php _e( 'There are a few sections where you can save essential information about a company -  ' ); ?></p>
					<ul>
						<li><?php _e( 'There is a textbox for the title of a company.' ); ?></li>
						<li><?php _e( 'You can also put any description/comments related to the company in to the rich text editor provided.' ); ?></li>
						<li>
							<?php _e( 'There might be other extra attributes metaboxes depending upon how you add an attribute from the attributes page' ); ?>
							<a href="<?php echo add_query_arg( array( 'page' => Rtbiz_Attributes::$page_slug ), admin_url( 'admin.php' ) ); ?>"><?php _e( 'here' ); ?></a>.
						</li>
						<li>
							<?php _e( 'You will see a "Connected X" metaboxes in the side colum.' ); ?>
							<?php _e( 'They are the supportive modules of the system which are connected to the company.' ); ?>
							<?php _e( 'E.g., A company is connected to a person since a person can be a part of a company.' ); ?>
							<?php _e( 'You can select any entity from the metabox to connect it to the company.' ); ?>
						</li>
					</ul>
					<?php
					break;
				case 'person_list_overview':
					?>
					<p><?php
						if ( isset( $_REQUEST['contact_group'] ) && 'staff' == $_REQUEST['contact_group'] ) {
							echo __( 'Staff are part of your organization who are responsible to handle customer tickets.' );
						} else {
							if ( 'Helpdesk' == $module_name ) {
								echo __( 'Customers are the people who have purchased your product and have created a support ticket.' );
							} else {
								echo __( 'Customers are the people who have purchased your product.' );
							}
						} ?>
					</p>
					<?php
					break;
				case 'person_list_screen_content':
					?>
					<ul>
						<li>
							<strong><?php _e( 'User - ' ); ?></strong><?php _e( 'WordPress user account linked to this contact.' ); ?>
						</li>
						<?php if ( isset( $_REQUEST['contact_group'] ) && 'staff' == $_REQUEST['contact_group'] ) { ?>
							<li>
								<strong><?php _e( 'Teams - ' ); ?></strong><?php _e( 'Applicable for Staff. The team to which a staff member belongs to.' ); ?>
							</li>
						<?php } ?>
						<?php if ( 'Helpdesk' == $module_name ) { ?>
							<li>
								<strong><?php _e( 'Ticket - ' ); ?></strong><?php _e( 'The number of tickets created by a customer.' ); ?>
							</li>
						<?php } ?>
					</ul>
					<!--<p><?php /*_e( 'You can customize the display of this screen’s contents in a number of ways - ' ); */
					?></p>
					<ul>
						<li><?php /*_e( 'You can hide/display columns based on your needs and decide how many people to list per screen using the Screen Options tab.' ); */
					?></li>
						<li>
							<?php /*_e( 'You can filter the list of people by status using the text links in the upper left to show All, Published, Draft, or Trashed people.' ); */
					?>
							<?php /*_e( 'The default view is to show all people.' ); */
					?>
						</li>
						<li>
							<?php /*_e( 'You can view people in a simple title list or with an excerpt.' ); */
					?>
							<?php /*_e( 'Choose the view you prefer by clicking on the icons at the top of the list on the right.' ); */
					?>
						</li>
						<li>
							<?php /*_e( 'You can refine the list to show only people in a specific category or from a specific month by using the dropdown menus above the people list.' ); */
					?>
							<?php /*_e( 'Click the Filter button after making your selection.' ); */
					?>
							<?php /*_e( 'You also can refine the list by clicking on the author, organization or tag in the people list.' ); */
					?>
						</li>
						<li><?php /*_e( 'You can also see the entity counts for respective modules, if activated, such as Lead Count, Ticket Count etc.' ) */
					?></li>
					</ul>-->
					<?php
					break;
				case 'organization_list_overview':
					?>
					<p>
						<?php _e( 'This screen provides access to all companies. You can customize the display of this screen to suit your workflow.' ); ?>
					</p>
					<?php
					break;
				case 'organization_list_screen_content':
					?>
					<p><?php _e( 'You can customize the display of this screen’s contents in a number of ways - ' ); ?></p>
					<ul>
						<li><?php _e( 'You can hide/display columns based on your needs and decide how many companies to list per screen using the Screen Options tab.' ); ?></li>
						<li>
							<?php _e( 'You can filter the list of companies by status using the text links in the upper left to show All, Published, Draft, or Trashed companies.' ); ?>
							<?php _e( 'The default view is to show all companies.' ); ?>
						</li>
						<li>
							<?php _e( 'You can view companies in a simple title list or with an excerpt.' ); ?>
							<?php _e( 'Choose the view you prefer by clicking on the icons at the top of the list on the right.' ); ?>
						</li>
						<li>
							<?php _e( 'You can refine the list to show only companies in a specific category or from a specific month by using the dropdown menus above the companies list.' ); ?>
							<?php _e( 'Click the Filter button after making your selection.' ); ?>
							<?php _e( 'You also can refine the list by clicking on the author, country or attribute in the companies list.' ); ?>
						</li>
						<li><?php _e( 'You can also see the entity counts for respective modules, if activated, such as Lead Count, Ticket Count etc.' ) ?></li>
					</ul>
					<?php
					break;
				case 'dashboard_overview':
					?>
					<p>
						<?php echo sprintf( __( 'Welcome to your rtBiz Dashboard!' ) ); ?>
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
				case 'teams_overview':
					?>
					<p>
						<?php _e( 'This screen is useful when you have to introduce teams within your organization.' ); ?>
						<?php _e( 'You can create, edit, delete teams & perfom other CRUD operations from here.' ); ?>
						<?php _e( 'These teams can be later assigned to contacts to further categorize them.' ); ?>
						<?php /*_e( 'They will also be useful in defining Access Control for the system & its other modules.' ); */
						?>
					</p>
					<?php
					break;
				case 'teams_screen_content':
					?>
					<ul>
						<li><?php _e( 'Using the left column form, you can create new team.' ); ?></li>
						<li><?php _e( 'You can assign an group email address to the team as well, if in use.' ); ?></li>
						<!--<li><?php /*_e( 'You can also assign a color code to the team. It will help you identify the team or the user from which team he is just by the color.' ); */
						?></li>-->
						<li><?php _e( 'On the right column, there will be existing teams listed along with basic information related to the team.' ); ?></li>
						<li><?php _e( 'You can edit an individual team on the Edit Team Screen.' ); ?></li>
					</ul>
					<?php
					break;
				case 'contact_group_overview':
					?>
					<p>
						<?php _e( 'This screen is useful when you have to introduce contact groups within your organization.' ); ?>
						<?php _e( 'You can create, edit, delete teams & perfom other CRUD operations from here.' ); ?>
						<?php _e( 'These groups can be later assigned to contacts to further categorize them.' ); ?>
						<?php _e( 'Once a contact is categorized in particular group, there\'ll be additional information of contact displayed on Edit Contact Screen.' ); ?>
					</p>
					<?php
					break;
				case 'contact_group_screen_content':
					?>
					<ul>
						<li><?php _e( 'We\'ve given you the very basic contact groups by default i.e., Customers, Employees, Vendors. These groups will always be there in the system & it will let you categorize your contacts.' ); ?></li>
						<li><?php _e( 'Using the left column form, you can create new contact groups.' ); ?></li>
						<li><?php _e( 'On the right column, there will be existing contact groups listed along with basic information related to the contact group.' ); ?></li>
						<li><?php _e( 'You can edit an individual contact group on the Edit Contact Group Screen.' ); ?></li>
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
				case 'acl_roles':
					?>
					<ul>
						<li>
							<?php echo sprintf( __( 'First column consists of all the module that has been registered with %s.' ), 'rtBiz' ); ?>
							<?php echo sprintf( __( '%s will itself be registered for its ACL use.' ), 'rtBiz' ); ?>
							<?php echo sprintf( __( 'Hence, %s will be there on the first row by default.' ), 'rtBiz' ); ?>
						</li>
						<li><?php _e( 'Each row will represent the ACL for that particular module.' ); ?></li>
						<li>
							<?php _e( 'Users can be assigned to any of the roles from -  Admin, Editor, Author, No Role.' ); ?>
							<ul>
								<li>
									<strong><?php _e( 'No Role - ' ); ?></strong> <?php _e( 'This will revoke all access for that particular module from the user.' ); ?>
								</li>
								<li>
									<strong><?php _e( 'Author - ' ); ?></strong> <?php _e( 'This almost works like the WordPress Author. User with Author Role will be able to see entitis which are only assigned to him and some of the user level settings.' ); ?>
								</li>
								<li>
									<strong><?php _e( 'Editor - ' ); ?></strong> <?php _e( 'Just like WordPress Editor Role, this role will enable users to see his own & others\' entities as well. Also with this, Editor will be able to have some of the editor level settings.' ); ?>
								</li>
								<li>
									<strong><?php _e( 'Admin - ' ); ?></strong> <?php _e( 'Admin will have the full control of the module including all entities & admin level settings.' ); ?>
								</li>
							</ul>
						</li>
						<li>
							<strong><?php _e( 'Note - ' ); ?></strong>
							<?php _e( 'This matrix is for Group Level Access Control.' ); ?>
							<?php _e( 'It means, any individual user can be assigned to any above roles from his/her WordPress Profile Page which will be assumed Profile Level Access Control.' ); ?>
							<strong><?php _e( 'Group Level Access Control will always be overridden by Profile Level Access Control.' ); ?></strong>
							<strong><?php _e( 'WordPress Site Admins will have the Admin role for all the modules by default.' ); ?></strong>
						</li>
					</ul>
					<?php
					break;
				case 'settings_overview':
					?>
					<p>
						<?php echo sprintf( __( 'This screen consists of all the %s settings.' ), 'rtBiz' ); ?>
						<?php _e( 'The settings are divided into different tabs depending upon their functionality.' ); ?>
						<?php _e( 'You can configure & update them according to your choice from here.' ); ?>
						<?php _e( 'There\'s also a buttom named "Reset to Default" which will put all settings to its default values.' ); ?>
					</p>
					<?php
					break;
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
				case 'rt-product_overview':
					?>
					<p>
						<?php _e( 'This screen is useful when you have to add custom products.You can create, edit, delete teams & select default assignee for each product.' ); ?>
					</p>
					<?php
					break;
				case 'rt-product_screen_content':
					?>
					<ul>
						<li><?php _e( 'Using the left column form, you can create a new product and select a default assignee for it.' ); ?></li>
						<li><?php _e( 'On the right column, there are existing products listed.' ); ?></li>
						<li>
							<strong><?php _e( 'Count - ' ); ?></strong><?php _e( 'The number of ' . $cpt_name . ' created for each product.' ); ?>
						</li>
						<li>
							<strong><?php _e( 'Product - ' ); ?></strong><?php _e( 'EDD or WooCommerce product to which an ' . $module_name . ' product is linked to.' ); ?>
						</li>
						<?php if ( 'Helpdesk' == $module_name ) { ?>
							<li>
								<strong><?php _e( 'Helpdesk default assignee - ' ); ?></strong><?php _e( 'Staff member to whom all tickets for that product will be assigned.' ); ?>
							</li>
						<?php } ?>
					</ul>
					<?php
					break;
				default:
					do_action( 'rtbiz_help_tab_content', $screen, $tab );
					break;
			}
		}

	}

}
