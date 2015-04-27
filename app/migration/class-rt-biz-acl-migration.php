<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

if ( ! class_exists( 'Rt_Biz_ACL_Migration' ) ) {

	/*
	 * Migration to store User permission in custom table
	 */
	class Rt_Biz_ACL_Migration {

		/*
		 * construct
		 */
		public function __construct() {
			if ( strcmp( RT_BIZ_VERSION , '1.2.13' ) >= 0 ) {
				add_action( 'admin_init', array( $this, 'migrate' ), 20 );
			}
		}

		public function migrate(){
			$isMigrated = get_option( 'rtbiz_acl_migration_1.2.13' );
			if ( empty( $isMigrated ) ||  'false' === $isMigrated ){
				global $rt_biz_acl_model;
				$modules            = Rt_Access_Control::$modules;
				$module_permissions = get_site_option( 'rt_biz_module_permissions' );
				$args               = array(
					'post_type'      => rt_biz_get_contact_post_type(),
					'post_status'    => 'any',
					'posts_per_page' => - 1,
					'nopaging'       => true,
				);
				$contacts           = get_posts( $args );

				foreach ( $contacts as $contact ) {
					$profile_permissions = get_post_meta( $contact->ID, 'rt_biz_profile_permissions', true );
					$departments         = wp_get_post_terms( $contact->ID, RT_Departments::$slug );
					if ( ! empty( $profile_permissions ) || ! empty( $departments ) ) {
						$user = rt_biz_get_wp_user_for_contact( $contact->ID );
						foreach ( $modules as $module_Key => $module ) {
							if ( 0 != strlen( $profile_permissions[ $module_Key ] ) ) {
								switch ( $profile_permissions[ $module_Key ] ) {
									case 10:
									case 20:
									case 30:
										$data = array(
											'userid'     => $user[0]->ID,
											'module'     => $module_Key,
											'groupid'    => 0,
											'permission' => $profile_permissions[ $module_Key ],
										);
										$rt_biz_acl_model->add_acl( $data );
										break;
								}
							} elseif ( ! empty( $departments ) ) {
								$profile_permissions[ $module_Key ] = '';
								$module_permission = ( ! empty( $module_permissions ) ) ? $module_permissions[ $module_Key ] : array();
								foreach ( $departments as $department ) {
									$data = array(
										'userid'     => $user[0]->ID,
										'module'     => $module_Key,
										'groupid'    => $department->term_id,
										'permission' => $module_permission[ $department->term_id ],
									);
									$rt_biz_acl_model->add_acl( $data );
								}
							}
						}
					}
					update_post_meta( $contact->ID, 'rt_biz_profile_permissions', $profile_permissions );
				}
				update_option( 'rtbiz_acl_migration_1.2.13', 'true' );
			}

		}

	}
}