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
 * Description of class-rt-biz-dashboard
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_Biz_Dashboard' ) ) {

	class Rt_Biz_Dashboard {

		var $screen_id;
		var $charts = array();

		public function __construct() {
			$this->screen_id = '';

			add_action( 'wp_ajax_update_rtbiz_welcome_panel', array( $this, 'update_rtbiz_welcome_panel' ) );

			$this->setup_defaults();
		}

		function setup_defaults() {
			if ( ! metadata_exists( 'user', get_current_user_id(), 'show_rtbiz_welcome_panel' ) ) {
				update_user_meta( get_current_user_id(), 'show_rtbiz_welcome_panel', 1 );
			}
		}

		function setup_dashboard() {
			/* Add callbacks for this screen only */
			add_action( 'load-' . $this->screen_id, array( $this, 'page_actions' ), 9 );
			add_action( 'admin_footer-' . $this->screen_id, array( $this, 'footer_scripts' ) );

			/* Setup Google Charts */
			add_action( 'rtbiz_after_dashboard', array( $this, 'render_google_charts' ) );

			/* Metaboxes for dashboard widgets */
			add_action( 'add_meta_boxes', array( $this, 'add_dashboard_widgets' ) );

			add_action( 'rtbiz_welcome_panel', array( $this, 'rtbiz_welcome_panel' ) );

			add_action( 'rtbiz_after_dashboard', array( $this, 'print_dashboard_js' ) );

			$this->check_welcome_panel();
		}

		function update_rtbiz_welcome_panel() {

			check_ajax_referer( 'rtbiz-welcome-panel-nonce', 'rtbizwelcomepanelnonce' );

			$author_cap = rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'author' );

			if ( ! current_user_can( $author_cap ) ) {
				wp_die( -1 );
			}

			update_user_meta( get_current_user_id(), 'show_rtbiz_welcome_panel', empty( $_POST['visible'] ) ? 0 : 1 );

			wp_die( 1 );
		}

		function print_dashboard_js() {
			if ( isset( $_GET['rtbizwelcome'] ) ) {
				$welcome_checked = empty( $_GET['rtbizwelcome'] ) ? 0 : 1;
				update_user_meta( get_current_user_id(), 'show_rtbiz_welcome_panel', $welcome_checked );
			} else {
				$welcome_checked = get_user_meta( get_current_user_id(), 'show_rtbiz_welcome_panel', true );
				if ( 2 == $welcome_checked && wp_get_current_user()->user_email != get_option( 'admin_email' ) ) {
					$welcome_checked = false;
				}
			}
			?>
			<script>
				jQuery(document).ready( function($) {
					var rtbiz_welcomePanel = $( '#rtbiz-welcome-panel' ),
						rtbiz_welcomePanelHide = '#rtbiz_welcome_panel-hide',
						rtbiz_updateWelcomePanel;

					rtbiz_updateWelcomePanel = function( visible ) {
						$.post( '<?php echo admin_url( 'admin-ajax.php' ); ?>', {
							action: 'update_rtbiz_welcome_panel',
							visible: visible,
							rtbizwelcomepanelnonce: $( '#rtbizwelcomepanelnonce' ).val()
						});
					};

					if ( rtbiz_welcomePanel.hasClass('hidden') && $(rtbiz_welcomePanelHide).prop('checked') ) {
						rtbiz_welcomePanel.removeClass('hidden');
					}

					$('.welcome-panel-close, .welcome-panel-dismiss a', rtbiz_welcomePanel).click( function(e) {
						e.preventDefault();
						rtbiz_welcomePanel.addClass('hidden');
						rtbiz_updateWelcomePanel( 0 );
						$('#wp_welcome_panel-hide').prop('checked', false);
					});

					$(document).on('click', rtbiz_welcomePanelHide, function() {
						rtbiz_welcomePanel.toggleClass('hidden', ! this.checked );
						rtbiz_updateWelcomePanel( this.checked ? 1 : 0 );
					} );

					$('#screen-options-wrap #adv-settings .metabox-prefs' ).append("<label for='rtbiz_welcome_panel-hide'><input type='checkbox' id='rtbiz_welcome_panel-hide' value='rtbiz-welcome-panel' <?php echo checked( (bool) $welcome_checked, true, false ); ?> /><?php _e( 'Welcome', RT_BIZ_TEXT_DOMAIN ); ?></label>");
				} );
			</script>
		<?php }

		function check_welcome_panel() {
			if ( isset( $_GET['rtbizwelcome'] ) ) {
				$welcome_checked = empty( $_GET['rtbizwelcome'] ) ? 0 : 1;
				update_user_meta( get_current_user_id(), 'show_rtbiz_welcome_panel', $welcome_checked );
			}
		}

		function rtbiz_welcome_panel() {
			$admin_cap = rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'admin' );
			$editor_cap = rt_biz_get_access_role_cap( RT_BIZ_TEXT_DOMAIN, 'editor' );
			?>
			<div class="welcome-panel-content">
				<h3><?php _e( 'Welcome to rtBiz!' ); ?></h3>
				<p class="about-description"><?php _e( 'We&#8217;ve assembled some links to get you started:' ); ?></p>
				<div class="welcome-panel-column-container">
					<div class="welcome-panel-column">
						<?php if ( current_user_can( $admin_cap ) ): ?>
							<h4><?php _e( 'Get Started' ); ?></h4>
							<a class="button button-primary button-hero" href="<?php echo admin_url( 'admin.php?page=' . Rt_Biz_Setting::$page_slug ); ?>"><?php _e( 'Customize Your Biz' ); ?></a>
						<?php endif; ?>
						<p><?php printf( __( 'You can also <a href="%s">configure your mailbox</a>' ), admin_url( 'admin.php?page=' . Rt_Mailbox::$page_name ) ); ?></p>
					</div>
					<div class="welcome-panel-column">
						<h4><?php _e( 'Next Steps' ); ?></h4>
						<ul>
							<?php if ( current_user_can( $editor_cap ) ) { ?>
								<li><?php printf( '<a href="%s" class="welcome-icon welcome-admin-users">' . __( 'Add new Contact' ) . '</a>', admin_url( 'post-new.php?post_type=' . rt_biz_get_contact_post_type() ) ); ?></li>
								<li><?php printf( '<a href="%s" class="welcome-icon welcome-groups">' . __( 'Setup Contact Groups' ) . '</a>', admin_url( 'edit-tags.php?taxonomy=' . Rt_Contact::$user_category_taxonomy . '&post_type=' . rt_biz_get_contact_post_type() ) ); ?></li>
								<li><?php printf( '<a href="%s" class="welcome-icon welcome-admin-home">' . __( 'Add new Company' ) . '</a>', admin_url( 'post-new.php?post_type=' . rt_biz_get_company_post_type() ) ); ?></li>
								<li><?php printf( '<a href="%s" class="welcome-icon welcome-networking">' . __( 'Setup Attributes' ) . '</a>', admin_url( 'admin.php?page=' . Rt_Biz_Attributes::$attributes_page_slug ) ); ?></li>
							<?php } else { ?>
								<li><?php printf( '<a href="%s" class="welcome-icon welcome-groups">' . __( 'View your Contacts' ) . '</a>', admin_url( 'edit.php?post_type=' . rt_biz_get_contact_post_type() ) ); ?></li>
								<li><?php printf( '<a href="%s" class="welcome-icon welcome-admin-home">' . __( 'View your Companies' ) . '</a>', admin_url( 'edit.php?post_type=' . rt_biz_get_company_post_type() ) ); ?></li>
							<?php } ?>
						</ul>
					</div>

					<div class="welcome-panel-column welcome-panel-last">
						<h4><?php _e( 'More Actions' ); ?></h4>
						<ul>

							<?php if ( current_user_can( $admin_cap ) ) { ?>
								<li><?php printf( '<a href="%s" class="welcome-icon welcome-lock">' . __( 'Define your Access Control for Biz' ) . '</a>', admin_url( 'admin.php?page=' . Rt_Biz::$access_control_slug ) ); ?></li>
							<?php } ?>

							<?php if ( current_user_can( $editor_cap ) ) { ?>
								<li><?php printf( '<a href="%s" class="welcome-icon welcome-universal-access-alt">' . __( 'Add new Department' ) . '</a>', admin_url( 'edit-tags.php?taxonomy=' . RT_Departments::$slug . '&post_type=' . rt_biz_get_contact_post_type() ) ); ?></li>
							<?php } ?>

							<li><?php printf( '<a href="%s" class="welcome-icon welcome-learn-more">' . __( 'Learn more about getting started' ) . '</a>', 'https://rtcamp.com/rtbiz/docs/' ); ?></li>
						</ul>
					</div>
				</div>
			</div>
		<?php }

		/**
		 *
		 */
		function add_screen_id( $screen_id ) {
			$this->screen_id = $screen_id;
		}

		/**
		 * Prints the jQuery script to initiliase the metaboxes
		 * Called on admin_footer-*
		 */
		function footer_scripts() {
			?>
			<script> postboxes.add_postbox_toggles( pagenow );</script>
			<?php

		}

		/*
		 * Actions to be taken prior to page loading. This is after headers have been set.
		 * call on load-$hook
		 * This calls the add_meta_boxes hooks, adds screen options and enqueues the postbox.js script.
		 */

		function page_actions() {

			if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] === Rt_Biz::$dashboard_slug ) {
				do_action( 'add_meta_boxes_' . $this->screen_id, null );
				do_action( 'add_meta_boxes', $this->screen_id, null );

				/* Enqueue WordPress' script for handling the metaboxes */
				wp_enqueue_script( 'postbox' );
			}
		}

		function render_google_charts() {
			global $rt_biz_reports;
			$rt_biz_reports->render_chart( $this->charts );
		}

		function add_dashboard_widgets() {

			add_meta_box( 'rtbiz-department-by-contacts', __( 'Department wise Contacts', RT_BIZ_TEXT_DOMAIN ), array( $this, 'department_by_contacts' ), $this->screen_id, 'column1' );

			add_meta_box( 'rtbiz-contact-type-by-contacts', __( 'Contact Group wise Contacts', RT_BIZ_TEXT_DOMAIN ), array( $this, 'contact_type_wise_contacts' ), $this->screen_id, 'column2' );

			add_meta_box( 'rtbiz-offering-wise-contacts', __( 'Offering wise Contacts', RT_BIZ_TEXT_DOMAIN ), array( $this, 'offering_wise_contacts' ), $this->screen_id, 'column3' );

			$rt_biz_attributes_model = new RT_Attributes_Model();
			$rt_biz_attributes_relationship_model = new RT_Attributes_Relationship_Model();
			$relations = $rt_biz_attributes_relationship_model->get_relations_by_post_type( rt_biz_get_contact_post_type() );
			foreach ( $relations as $r ) {
				$attr = $rt_biz_attributes_model->get_attribute( $r->attr_id );
				if ( 'taxonomy' == $attr->attribute_store_as ) {
					add_meta_box( 'rtbiz-people-by-' . $attr->attribute_name, $attr->attribute_label . ' ' . __( 'wise Contacts' ), array( $this, 'dashboard_widget_content' ), $this->screen_id, 'column4', 'default', array( 'attribute_id' => $attr->id ) );
				}
			}
		}

		function offering_wise_contacts( $obj, $args ){

			$taxonomy    = Rt_Offerings::$offering_slug;
			$terms       = get_terms( $taxonomy );
			$data_source = array();
			$cols        = array( __( 'Offerings', RT_BIZ_TEXT_DOMAIN ), __( 'Count', RT_BIZ_TEXT_DOMAIN ) );
			$rows        = array();
			$post_type   = rt_biz_get_contact_post_type();
			$total       = 0;

			if ( ! $terms instanceof WP_Error ) {
				foreach ( $terms as $t ) {
					$posts = new WP_Query( array(
						'post_type' => $post_type,
						'post_status' => 'any',
						'nopaging' => true,
						$taxonomy => $t->slug,
					) );

					$rows[] = array(
						$t->name,
						count( $posts->posts ),
					);
					$total += count( $posts->posts );
				}
			}
			$posts = new WP_Query( array(
				'post_type' => $post_type,
				'post_status' => 'any',
				'nopaging' => true,
			) );

			$rows[] = array( __( 'Uncategorized' ), count( $posts->posts ) - $total );

			$data_source['cols'] = $cols;
			$data_source['rows'] = $rows;

			$this->charts[] = array(
				'id' => $args['id'],
				'chart_type' => 'pie',
				'data_source' => $data_source,
				'dom_element' => 'rtbiz_pie_'.$args['id'],
				'options' => array(
					'title' => $args['title'],
				),
			);
			?>
			<div id="<?php echo 'rtbiz_pie_'.$args['id']; ?>"></div>
		<?php }

		function contact_type_wise_contacts( $obj, $args ){
			$taxonomy    = Rt_Contact::$user_category_taxonomy;
			$terms       = get_terms( $taxonomy );
			$data_source = array();
			$cols        = array( __( 'Contacts type', RT_BIZ_TEXT_DOMAIN ), __( 'Count', RT_BIZ_TEXT_DOMAIN ) );
			$rows        = array();
			$post_type   = rt_biz_get_contact_post_type();
			$total       = 0;

			if ( ! $terms instanceof WP_Error ) {
				foreach ( $terms as $t ) {
					$posts = new WP_Query( array(
						'post_type' => $post_type,
						'post_status' => 'any',
						'nopaging' => true,
						$taxonomy => $t->slug,
					) );

					$rows[] = array(
						$t->name,
						count( $posts->posts ),
					);
					$total += count( $posts->posts );
				}
			}
			$posts = new WP_Query( array(
				'post_type' => $post_type,
				'post_status' => 'any',
				'nopaging' => true,
			) );

			$rows[] = array( __( 'Uncategorized' ), count( $posts->posts ) - $total );

			$data_source['cols'] = $cols;
			$data_source['rows'] = $rows;

			$this->charts[] = array(
				'id' => $args['id'],
				'chart_type' => 'pie',
				'data_source' => $data_source,
				'dom_element' => 'rtbiz_pie_'.$args['id'],
				'options' => array(
					'title' => $args['title'],
				),
			);
			?>
			<div id="<?php echo 'rtbiz_pie_'.$args['id']; ?>"></div>
		<?php }

		function department_by_contacts( $obj, $args ){
			$taxonomy    = RT_Departments::$slug;
			$terms       = get_terms( $taxonomy );
			$data_source = array();
			$cols        = array( __( 'Department', RT_BIZ_TEXT_DOMAIN ), __( 'Count', RT_BIZ_TEXT_DOMAIN ) );
			$rows        = array();
			$post_type   = rt_biz_get_contact_post_type();
			$total       = 0;

			if ( ! $terms instanceof WP_Error ) {
				foreach ( $terms as $t ) {
					$posts = new WP_Query( array(
						'post_type' => $post_type,
						'post_status' => 'any',
						'nopaging' => true,
						$taxonomy => $t->slug,
					) );

					$rows[] = array(
						$t->name,
						count( $posts->posts ),
					);
					$total += count( $posts->posts );
				}
			}
			$posts = new WP_Query( array(
				'post_type' => $post_type,
				'post_status' => 'any',
				'nopaging' => true,
			) );

			$rows[] = array( __( 'Uncategorized' ), count( $posts->posts ) - $total );

			$data_source['cols'] = $cols;
			$data_source['rows'] = $rows;

			$this->charts[] = array(
				'id' => $args['id'],
				'chart_type' => 'pie',
				'data_source' => $data_source,
				'dom_element' => 'rtbiz_pie_'.$args['id'],
				'options' => array(
					'title' => $args['title'],
				),
			);
			?>
			<div id="<?php echo 'rtbiz_pie_'.$args['id']; ?>"></div>
		<?php }

		function dashboard_widget_content( $obj, $args ) {
			global $rt_biz_rt_attributes;
			$rt_biz_attributes_model = new RT_Attributes_Model();
			$attribute_id            = $args['args']['attribute_id'];
			$attr                    = $rt_biz_attributes_model->get_attribute( $attribute_id );
			$taxonomy                = $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name );
			$post_type               = rt_biz_get_contact_post_type();
			$terms                   = get_terms( $taxonomy );
			$data_source             = array();
			$cols                    = array( $attr->attribute_label, __( 'People' ) );
			$rows                    = array();
			$total                   = 0;

			if ( ! $terms instanceof WP_Error ) {
				foreach ( $terms as $t ) {
					$posts = new WP_Query( array(
						'post_type' => $post_type,
						'post_status' => 'any',
						'nopaging' => true,
						$taxonomy => $t->slug,
					) );

					$rows[] = array(
						$t->name,
						count( $posts->posts ),
					);
					$total += count( $posts->posts );
				}
			}

			$posts = new WP_Query( array(
				'post_type' => $post_type,
				'post_status' => 'any',
				'nopaging' => true,
			) );

			$rows[] = array( __( 'Others' ), count( $posts->posts ) - $total );

			$data_source['cols'] = $cols;
			$data_source['rows'] = $rows;

			$this->charts[] = array(
				'id' => $args['id'],
				'chart_type' => 'pie',
				'data_source' => $data_source,
				'dom_element' => 'rtbiz_pie_'.$args['id'],
				'options' => array(
					'title' => $args['title'],
				),
			);
		?>
    		<div id="<?php echo 'rtbiz_pie_'.$args['id']; ?>"></div>
		<?php }

	}

}
