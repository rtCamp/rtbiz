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
		}

		function setup_dashboard() {
			/* Add callbacks for this screen only */
			add_action( 'load-' . $this->screen_id, array( $this, 'page_actions' ), 9 );
			add_action( 'admin_footer-' . $this->screen_id, array( $this, 'footer_scripts' ) );

			/* Setup Google Charts */
			add_action( 'rtbiz_after_dashboard', array( $this, 'render_google_charts' ) );

			/* Metaboxes for dashboard widgets */
			add_action( 'add_meta_boxes', array( $this, 'add_dashboard_widgets' ) );
		}

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

			if ( isset( $_REQUEST[ 'page' ] ) && $_REQUEST[ 'page' ] === Rt_Biz::$dashboard_slug ) {
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
			global $rt_biz_dashboard;
			/* Pie Chart - Progress Indicator (Post status based) */
			add_meta_box( 'rtbiz-department-by-contacts', __( 'Department wise Contacts', RT_BIZ_TEXT_DOMAIN ), array(
				$this,
				'department_by_contacts',
			), $rt_biz_dashboard->screen_id, 'column1' );

			add_meta_box( 'rtbiz-contact-type-by-contacts', __( 'Contacts Type wise Contacts', RT_BIZ_TEXT_DOMAIN ), array(
				$this,
				'contact_type_wise_contacts',
			), $rt_biz_dashboard->screen_id, 'column2' );

			add_meta_box( 'rtbiz-offering-wise-contacts', __( 'Offering wise Contacts', RT_BIZ_TEXT_DOMAIN ), array(
				$this,
				'offering_wise_contacts',
			), $rt_biz_dashboard->screen_id, 'column3' );


			$rt_biz_attributes_model = new RT_Attributes_Model();
			$rt_biz_attributes_relationship_model = new RT_Attributes_Relationship_Model();
			$relations = $rt_biz_attributes_relationship_model->get_relations_by_post_type( rt_biz_get_contact_post_type() );
			foreach ( $relations as $r ) {
				$attr = $rt_biz_attributes_model->get_attribute( $r->attr_id );
				if ( $attr->attribute_store_as == 'taxonomy' ) {
					add_meta_box( 'rtbiz-people-by-' . $attr->attribute_name, $attr->attribute_label . ' ' . __( 'Wise Contacts' ), array( $this, 'dashboard_widget_content' ), $this->screen_id, 'column4', 'default', array( 'attribute_id' => $attr->id ) );
				}
			}
		}

		function offering_wise_contacts( $obj, $args ){
			global $rtbiz_offerings;
			$taxonomy = Rt_Offerings::$offering_slug;
			$terms =  get_terms( $taxonomy );
			$data_source = array();
			$cols        = array( __( 'Offerings', RT_BIZ_TEXT_DOMAIN), __( 'Count', RT_BIZ_TEXT_DOMAIN ) );
			$rows        = array();
			$post_type = rt_biz_get_contact_post_type();
			$total = 0;


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
		<?php

		}


		function contact_type_wise_contacts( $obj, $args ){
			$taxonomy = Rt_Contact::$user_category_taxonomy;
			$terms =  get_terms( $taxonomy);
			$data_source = array();
			$cols        = array( __( 'Contacts type', RT_BIZ_TEXT_DOMAIN), __( 'Count', RT_BIZ_TEXT_DOMAIN ) );
			$rows        = array();
			$post_type = rt_biz_get_contact_post_type();
			$total = 0;


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
		<?php

		}


		function department_by_contacts( $obj, $args ){
			$taxonomy = RT_Departments::$slug;
			$terms =  get_terms( $taxonomy);
			$data_source = array();
			$cols        = array( __( 'Department', RT_BIZ_TEXT_DOMAIN), __( 'Count', RT_BIZ_TEXT_DOMAIN ) );
			$rows        = array();
			$post_type = rt_biz_get_contact_post_type();
			$total = 0;


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
		<?php



		}

		function dashboard_widget_content( $obj, $args ) {
			global $rt_biz_rt_attributes;
			$rt_biz_attributes_model = new RT_Attributes_Model();
			$attribute_id = $args[ 'args' ][ 'attribute_id' ];
			$attr = $rt_biz_attributes_model->get_attribute( $attribute_id );
			$taxonomy = $rt_biz_rt_attributes->get_taxonomy_name( $attr->attribute_name );
			$post_type = rt_biz_get_contact_post_type();
			$terms = get_terms( $taxonomy );

			$data_source = array();
			$cols = array( $attr->attribute_label, __( 'People' ) );
			$rows = array();
			$total = 0;

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
		<?php
		}

	}

}
