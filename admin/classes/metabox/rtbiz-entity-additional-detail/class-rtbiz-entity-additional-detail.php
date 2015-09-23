<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rtbiz_Entity_Additional_Detail' ) ) {


	class Rtbiz_Entity_Additional_Detail extends Rtbiz_Metabox {

		public static function ui( $post ) {

			if ( rtbiz_get_contact_post_type() == $post->post_type ) {
				$meta_fields = rtbiz_get_contact_meta_fields();
			} elseif ( rtbiz_get_company_post_type() == $post->post_type ) {
				$meta_fields = rtbiz_get_company_meta_fields();
			}
			do_action( 'rtbiz_before_render_meta_fields', $meta_fields );

			if ( empty( $meta_fields ) ) {
				return false;
			} ?>
			<div id="rtbiz-additional-detail-meta-box"> <?php
				$category = array_unique( wp_list_pluck( $meta_fields, 'category' ) );
				$cathtml  = array();

				foreach ( $category as $key => $value ) {
					$cathtml[ $value ]['title'] = '<div><h3 class="rtbiz-category-title">' . __( $value ) . __( ' information:' ) . ' </h3> </div>';
				}

				$cathtml['other']['title'] = '<div><h3 class="rtbiz-category-title">' . __( 'Other information:' ) . '</h3></div>';
				$other_flag                = false;

				$is_our_team_mate = false;
				$postid           = $post;
				if ( is_object( $post ) ) {
					$postid = $post->ID;
				}

				$wp_user = rtbiz_get_wp_user_for_contact( $postid ); //get wp user
				$cap     = rtbiz_get_access_role_cap( RTBIZ_TEXT_DOMAIN, 'author' );
				if ( ! empty( $wp_user[0] ) ) {
					$is_our_team_mate = user_can( $wp_user[0], $cap );
				}
				foreach ( $meta_fields as $field ) {
					ob_start();
					$field = apply_filters( 'rtbiz_entity_fields_loop_single_field', $field );

					if ( ! $is_our_team_mate ) {
						if ( isset( $field['hide_for_client'] ) && $field['hide_for_client'] ) {
							continue;
						}
					}

					if ( isset( $field['is_datepicker'] ) && $field['is_datepicker'] ) {
						$values = Rtbiz_Entity::get_meta( $post->ID, $field['key'], true ); ?>
						<script>
							jQuery(document).ready(function ($) {
								$(document).on('focus', ".datepicker", function () {
									$(this).datepicker({
										'dateFormat': 'dd/mm/yy',
										changeMonth: true,
										changeYear: true
									});
								});
							});
						</script>
						<p class="rtbiz-form-group"><?php
							if ( isset( $field['label'] ) ) { ?>
							<label for="<?php echo ( isset( $field['id'] ) ) ? '' . $field['id'] . '' : '' ?>">
								<?php echo $field['label']; ?>
								</label><?php
							} ?>
							<input type="text" <?php
							echo ( isset( $field['name'] ) ) ? 'name="' . $field['name'] . '"' : '';
							echo ( isset( $field['id'] ) ) ? 'id="' . $field['id'] . '"' : ''; ?>
							       value='<?php echo $values; ?>' <?php
							echo ( isset( $field['class'] ) ) ? 'class="datepicker ' . $field['class'] . '"' : 'class="datepicker"'; ?> >
							<br/><span></span>
						</p> <?php
					} else if ( isset( $field['is_multiple'] ) && $field['is_multiple'] ) {
					$values = Rtbiz_Entity::get_meta( $post->ID, $field['key'] ); ?>
						<p class="rtbiz-form-group"><?php
							if ( isset( $field['label'] ) ) { ?>
							<label for="<?php echo ( isset( $field['id'] ) ) ? '' . $field['id'] . '' : '' ?>">
								<?php echo $field['label']; ?>
								</label><?php
							} ?>
							<input <?php echo ( isset( $field['type'] ) ) ? 'type="' . $field['type'] . '"' : ''; ?>
								<?php echo ( isset( $field['name'] ) ) ? 'name="' . $field['name'] . '"' : ''; ?>
								<?php echo ( isset( $field['class'] ) ) ? 'class="' . $field['class'] . '"' : ''; ?> >

							<button data-type='<?php echo( $field['type'] ); ?>' type='button'
							        class='button button-primary add-multiple'> +
							</button>

							<br/><span></span><?php
							foreach ( $values as $value ) { ?>
								<input <?php echo ( isset( $field['type'] ) ) ? 'type="' . $field['type'] . '"' : '';
								echo ( isset( $field['name'] ) ) ? 'name="' . $field['name'] . '"' : ''; ?>
									value='<?php echo $value; ?>' <?php
								echo ( isset( $field['class'] ) ) ? 'class="second-multiple-input ' . $field['class'] . '"' : 'class="second-multiple-input"'; ?> >
								<button type='button' class='button delete-multiple'> -</button><?php
							} ?>
						</p> <?php
					} else if ( isset( $field['type'] ) && 'textarea' == $field['type'] ) {
						$values = Rtbiz_Entity::get_meta( $post->ID, $field['key'], true ); ?>
						<p class="rtbiz-form-group"><?php
							if ( isset( $field['label'] ) ) { ?>
								<label
									for="<?php echo ( isset( $field['id'] ) ) ? '' . $field['id'] . '' : ''; ?>"><?php
									echo $field['label']; ?>
								</label> <?php
							} ?>
							<textarea <?php echo ( isset( $field['name'] ) ) ? 'name="' . $field['name'] . '"' : '';
							echo ( isset( $field['id'] ) ) ? 'id="' . $field['id'] . '"' : '';
							echo ( isset( $field['class'] ) ) ? 'class="' . $field['class'] . '"' : ''; ?> > <?php echo $values; ?> </textarea>
							<br/><span></span>
						</p> <?php
					} else if ( isset( $field['type'] ) && 'user_group' == $field['type'] ) {
						$user_id = Rtbiz_Entity::get_meta( $post->ID, $field['key'], true );
						if ( empty( $user_id ) ) {
							continue;
						} ?>
					<p class="rtbiz-form-group"><?php
						call_user_func( $field['data_source'], new WP_User( $user_id ) ); ?>
					</p><?php
					} else if ( isset( $field['type'] ) && 'checkbox' == $field['type'] ) {
						$values = Rtbiz_Entity::get_meta( $post->ID, $field['key'], true ); ?>
						<p class="rtbiz-form-group rtbiz-form-checkbox">
							<label for="<?php echo ( isset( $field['id'] ) ) ? '' . $field['id'] . '' : '' ?>">
								<input value='yes' <?php echo ( 'yes' == $values ) ? 'checked' : ''; ?>
								       type='checkbox' <?php echo ( isset( $field['name'] ) ) ? 'name="' . $field['name'] . '"' : '';
								echo ( isset( $field['id'] ) ) ? 'id="' . $field['id'] . '"' : '';
								echo ( isset( $field['class'] ) ) ? 'class="' . $field['class'] . '"' : ''; ?> /><?php
								echo $field['text']; ?></label>
							<br/><span></span>
						</p> <?php
					} else {
						$values = Rtbiz_Entity::get_meta( $post->ID, $field['key'], true ); ?>
						<p class="rtbiz-form-group"><?php
							if ( isset( $field['label'] ) ) { ?>
								<label for="<?php echo ( isset( $field['id'] ) ) ? '' . $field['id'] . '' : '' ?>"><?php
								echo $field['label']; ?>
								</label><?php
							} ?>

							<input <?php echo ( isset( $field['type'] ) ) ? 'type="' . $field['type'] . '"' : '';
							echo ( isset( $field['name'] ) ) ? 'name="' . $field['name'] . '"' : '';
							echo ( isset( $field['id'] ) ) ? 'id="' . $field['id'] . '"' : ''; ?>
								value='<?php echo $values; ?>' <?php echo ( isset( $field['class'] ) ) ? 'class="' . $field['class'] . '"' : ''; ?> >
							<br/><span></span>
						</p> <?php
					}
					$tmphtml = ob_get_clean();
					if ( ! isset( $cathtml[ $field['category'] ]['fields'] ) ) {
						$cathtml[ $field['category'] ]['fields'] = '';
					}
					if ( isset( $field['category'] ) ) {
						$cathtml[ $field['category'] ]['fields'] .= $tmphtml;
					} else {
						$cathtml['other']['fields'] .= $tmphtml;
						$other_flag = true;
					}
				}

				$printimpload = array();
				if ( isset( $cathtml['Contact'] ) ) {
					$printimpload[] = $cathtml['Contact'];
					unset( $cathtml['Contact'] );
				}
				if ( isset( $cathtml['Social'] ) ) {
					$printimpload[] = $cathtml['Social'];
					unset( $cathtml['Social'] );
				}
				if ( isset( $cathtml['HR'] ) ) {
					if ( $is_our_team_mate ) {
						$printimpload[] = $cathtml['HR'];
					}
					unset( $cathtml['HR'] );
				}

				foreach ( $cathtml as $key => $value ) {
					if ( 'other' == $key ) {
						if ( true == $other_flag ) {
							$printimpload[] = $value;
						}
					} else {
						$printimpload[] = $value;
					}
				}
				$filter       = function ( $category ) {
					return $category['title'] . '<div class="rtbiz-category-group">' . $category['fields'] . '</div>';
				};
				$printimpload = array_map( $filter, $printimpload );

				echo implode( '<div class="add-gap-div"><hr></div>', $printimpload ); ?>

			</div> <?php

			do_action( 'rtbiz_after_render_meta_fields', $post, $post->post_type );

			wp_nonce_field( 'rtbiz_additional_details_metabox', 'rtbiz_additional_details_metabox_nonce' );

			if ( rtbiz_get_contact_post_type() == $post->post_type ) {
				/*  @var $rtbiz_contact Rtbiz_Contact */
				global $rtbiz_contact;
				$rtbiz_contact->print_metabox_js( $post );
			} elseif ( rtbiz_get_company_post_type() == $post->post_type ) {
				/*  @var $rtbiz_company Rtbiz_Company */
				global $rtbiz_company;
				$rtbiz_company->print_metabox_js( $post );
			}

			do_action( 'print_metabox_js', $post, $post->post_type );

		}

		public static function save( $post_id, $post ) {

		}

	}

}
