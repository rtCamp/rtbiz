<?php
/**
 * rt-biz Helper Functions
 *
 * Helper functions for rt-biz
 *
 * @author udit
 */

/**
 *
 * Get rtBiz Templates
 *
 * @param $template_name
 * @param array $args
 * @param string $template_path
 * @param string $default_path
 */
function rt_biz_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

	if ( $args && is_array($args) )
		extract( $args );

	$located = rt_biz_locate_template( $template_name, $template_path, $default_path );

	do_action( 'rt_biz_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'rt_biz_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Loads rtBiz Templates
 *
 * @param $template_name
 * @param string $template_path
 * @param string $default_path
 * @return mixed|void
 */
function rt_biz_locate_template( $template_name, $template_path = '', $default_path = '' ) {

	$rt_biz = rtbiz();
	if ( ! $template_path ) $template_path = $rt_biz->templateURL;
	if ( ! $default_path ) $default_path = RT_BIZ_PATH_TEMPLATES;

	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);

	// Get default template
	if ( ! $template )
		$template = $default_path . $template_name;

	// Return what we found
	return apply_filters('rt_biz_locate_template', $template, $template_name, $template_path);
}

/**
 * Sanitize Module Key
 *
 * @param $key
 * @return mixed|string
 */
function rt_biz_sanitize_module_key( $key ) {
	$filtered = strtolower( remove_accents( stripslashes( strip_tags( $key ) ) ) );
	$filtered = preg_replace( '/&.+?;/', '', $filtered ); // Kill entities
	$filtered = str_replace( array( '.', '\'', '"' ), '', $filtered ); // Kill quotes and full stops.
	$filtered = str_replace( array( ' ' ), '-', $filtered ); // Replace spaces.

	return $filtered;
}

/**
 * Register the person conection with a post type
 *
 * @param $post_type
 * @param $label
 */
function rt_biz_register_person_connection( $post_type, $label ) {
	global $rt_person;
	$rt_person->init_connection( $post_type, $label );
}

/**
 * Register a organization connection with a post type
 *
 * @param $post_type
 * @param $label
 */
function rt_biz_register_organization_connection( $post_type, $label ) {
	global $rt_organization;
	$rt_organization->init_connection( $post_type, $label );
}

/**
 * Get Posts for Person conection
 *
 * @param $post_id
 * @param $post_type
 * @param bool $fetch_person
 * @return mixed
 */
function rt_biz_get_post_for_person_connection( $post_id, $post_type, $fetch_person = false ) {
	global $rt_person;
	return $rt_person->get_posts_for_entity( $post_id, $post_type, $fetch_person );
}

/**
 * Get posts for organization connection
 *
 * @param $post_id
 * @param $post_type
 * @param bool $fetch_organization
 * @return mixed
 */
function rt_biz_get_post_for_organization_connection( $post_id, $post_type, $fetch_organization = false ) {
	global $rt_organization;
	return $rt_organization->get_posts_for_entity( $post_id, $post_type, $fetch_organization );
}

function rt_biz_get_person_labels() {
	global $rt_person;
	return $rt_person->labels;
}

function rt_biz_get_organization_labels() {
	global $rt_organization;
	return $rt_organization->labels;
}

/**
 * Returns person post type
 *
 * @return mixed
 */
function rt_biz_get_person_post_type() {
	global $rt_person;
	return $rt_person->post_type;
}

/**
 * Returns organization post type
 *
 * @return mixed
 */
function rt_biz_get_organization_post_type() {
	global $rt_organization;
	return $rt_organization->post_type;
}

/**
 * get person by email
 *
 * @param $email
 * @return mixed
 */
function rt_biz_get_person_by_email( $email ) {
	global $rt_person;
	return $rt_person->get_by_email( $email );
}

/**
 * Add meta field
 *
 * @param $id
 * @param $key
 * @param $value
 */
function rt_biz_add_entity_meta( $id, $key, $value ) {
	Rt_Entity::add_meta( $id, $key, $value );
}

/**
 * get meta field
 *
 * @param $id
 * @param $key
 * @param bool $single
 * @return mixed
 */
function rt_biz_get_entity_meta( $id, $key, $single = false ) {
	return Rt_Entity::get_meta( $id, $key, $single );
}

/**
 * update meta field
 *
 * @param $id
 * @param $key
 * @param $value
 */
function rt_biz_update_entity_meta( $id, $key, $value ) {
	Rt_Entity::update_meta( $id, $key, $value );
}

/**
 * delete meta field
 *
 * @param $id
 * @param $key
 * @param $value
 */
function rt_biz_delete_entity_meta( $id, $key, $value ) {
	Rt_Entity::delete_meta( $id, $key, $value );
}

/**
 * adds an organization
 *
 * @param $name
 * @param string $note
 * @param string $address
 * @param string $country
 * @param array $meta
 * @return mixed
 */
function rt_biz_add_organization( $name, $note = '', $address = '', $country = '', $meta = array() ) {
	global $rt_organization;
	return $rt_organization->add_organization( $name, $note, $address, $country, $meta );
}

/**
 * add a person
 *
 * @param $name
 * @param string $description
 * @return mixed
 */
function rt_biz_add_person( $name, $description = '' ) {
	global $rt_person;
	return $rt_person->add_person( $name, $description );
}

function rt_biz_clear_post_connections_to_person( $post_type, $from ) {
	global $rt_person;
	$rt_person->clear_post_connections_to_entity( $post_type, $from );
}

function rt_biz_clear_post_connections_to_organization( $post_type, $from ) {
	global $rt_organization;
	$rt_organization->clear_post_connections_to_entity( $post_type, $from );
}

/**
 *
 * @param $post_type
 * @param string $from
 * @param string $to
 */
function rt_biz_connect_post_to_person( $post_type, $from = '', $to = '' ) {
	global $rt_person;
	$rt_person->connect_post_to_entity( $post_type, $from, $to );
}

/**
 * @param $post_type
 * @param string $from
 * @param string $to
 * @param bool $clear_old
 */
function rt_biz_connect_post_to_organization( $post_type, $from = '', $to = '', $clear_old = false ) {
	global $rt_organization;
	$rt_organization->connect_post_to_entity( $post_type, $from, $to, $clear_old );
}

/**
 * @param string $from
 * @param string $to
 */
function rt_biz_connect_organization_to_person( $from = '', $to = '' ) {
	$rt_biz = rtbiz();
	$rt_biz->connect_organization_to_person( $from, $to );
}

/**
 * @param $post_id
 * @param string $term_seperator
 * @return string
 */
function rt_biz_person_connection_to_string( $post_id, $term_seperator = ' , ' ) {
	global $rt_person;
	return Rt_Entity::connection_to_string( $post_id, $rt_person->post_type, $term_seperator );
}

/**
 * @param $post_id
 * @param string $term_seperator
 * @return string
 */
function rt_biz_organization_connection_to_string( $post_id, $term_seperator = ' , ' ) {
	global $rt_organization;
	return Rt_Entity::connection_to_string( $post_id, $rt_organization->post_type, $term_seperator );
}

/**
 * @param $connected_items
 * @return mixed
 */
function rt_biz_get_organization_to_person_connection( $connected_items ) {
	$rt_biz = rtbiz();
	return $rt_biz->get_organization_to_person_connection( $connected_items );
}

/**
 * @return mixed
 */
function rt_biz_get_person_capabilities() {
	global $rt_person;
	return $rt_person->get_post_type_capabilities();
}

/**
 * @return mixed
 */
function rt_biz_get_organization_capabilities() {
	global $rt_organization;
	return $rt_organization->get_post_type_capabilities();
}

/**
 * Search person
 *
 * @param $query
 * @return mixed
 */
function rt_biz_search_person( $query, $args = array() ) {
	global $rt_person;
	return $rt_person->search( $query, $args );
}

/**
 *
 * @param $query
 * @return mixed
 */
function rt_biz_search_organization( $query, $args = array() ) {
	global $rt_organization;
	return $rt_organization->search( $query, $args );
}

/**
 * @return mixed
 */
function rt_biz_get_person_meta_fields() {
	global $rt_person;
	return $rt_person->meta_fields;
}

/**
 * @return mixed
 */
function rt_biz_get_organization_meta_fields() {
	global $rt_organization;
	return $rt_organization->meta_fields;
}

/**
 * @return array|WP_Error
 */
function rt_biz_get_user_groups() {
	$user_groups = get_terms( 'user-group', array( 'hide_empty' => false ) );
	return $user_groups;
}

function rt_biz_get_group_users( $group_term_id ) {
	$user_ids = RT_User_Groups::get_user_by_group_id( $group_term_id );
	if ( ! $user_ids instanceof WP_Error ) {
		return $user_ids;
	}
	return array();
}

/**
 * @return mixed
 */
function rt_biz_get_acl_permissions() {
	return Rt_Access_Control::$permissions;
}

/**
 * @return mixed
 */
function rt_biz_get_modules() {
	return Rt_Access_Control::$modules;
}

/**
 * @param $module_key
 * @param string $role
 * @return string
 */
function rt_biz_get_access_role_cap( $module_key, $role = 'no_access' ) {
	return Rt_Access_Control::get_capability_from_access_role( $module_key, $role );
}

function rt_biz_get_employees() {
	global $rt_person;
	return $rt_person->get_employees();
}

function rt_biz_get_clients() {
    global $rt_person;
    return $rt_person->get_clients();
}

function rt_biz_get_organizations() {
    global $rt_organization;
    return $rt_organization->get_organizations();
}

function rt_biz_search_employees( $query ) {
	$args = array(
//		'meta_key' => Rt_Person::$meta_key_prefix.Rt_Person::$our_team_mate_key,
//		'meta_value' => '1',
		'tax_query' => array(
			'taxonomy' => Rt_Person::$user_category_taxonomy,
			'field'    => 'slug',
			'terms'    => Rt_Person::$employees_category_slug,
		),what
	);
	return rt_biz_search_person($query, $args);
}

function rt_biz_get_module_users( $module_key ) {
	global $rt_access_control;
	return $rt_access_control->get_module_users( $module_key );
}

function rt_biz_get_person_for_wp_user( $user_id ) {
	global $rt_person;
	return $rt_person->get_contact_for_wp_user( $user_id );
}

function rt_biz_get_wp_user_for_person( $person_id ) {
	global $rt_person;
	return $rt_person->get_wp_user_for_person( $person_id );
}

function rt_biz_get_user_department( $user_ID ) {
	return RT_User_Groups::get_user_groups( $user_ID );
}

function rt_biz_get_user_department_section( $user ) {
	global $rtbiz_user_groups;
	return $rtbiz_user_groups->edit_user_user_group_section( $user );
}

function rt_biz_save_user_user_group( $user_id  ) {
	global $rtbiz_user_groups;
	return $rtbiz_user_groups->save_user_user_group( $user_id );
}

function rt_biz_get_settings ( $key = null) {
	if ( isset( $key ) ) {
		return Rt_Biz_Settings::$settings[ $key ];
	}
	return Rt_Biz_Settings::$settings;
}

/**
 * wp1_text_diff
 *
 * @param      $left_string
 * @param      $right_string
 * @param null $args
 *
 * @return string
 * @since rt-BIZ
 */
function rtbiz_text_diff( $left_string, $right_string, $args = null ) {
	$defaults = array( 'title' => '', 'title_left' => '', 'title_right' => '' );
	$args     = wp_parse_args( $args, $defaults );

	$left_string  = normalize_whitespace( $left_string );
	$right_string = normalize_whitespace( $right_string );
	$left_lines   = explode( "\n", $left_string );
	$right_lines  = explode( "\n", $right_string );

	$renderer  = new Rt_Biz_Text_Diff();
	$text_diff = new Text_Diff( $left_lines, $right_lines );
	$diff      = $renderer->render( $text_diff );

	if ( ! $diff ) {
		return '';
	}

	$r = "<table class='diff' style='width: 100%;background: white;margin-bottom: 1.25em;border: solid 1px #dddddd;border-radius: 3px;margin: 0 0 18px;'>\n";
	$r .= "<col class='ltype' /><col class='content' /><col class='ltype' /><col class='content' />";

	if ( $args['title'] || $args['title_left'] || $args['title_right'] ) {
		$r .= '<thead>';
	}
	if ( $args['title'] ) {
		$r .= "<tr class='diff-title'><th colspan='4'>" . $args['title'] . '</th></tr>\n';
	}
	if ( $args['title_left'] || $args['title_right'] ) {
		$r .= "<tr class='diff-sub-title'>\n";
		$r .= "\t<td></td><th>{$args['title_left']}</th>\n";
		$r .= "\t<td></td><th>{$args['title_right']}</th>\n";
		$r .= "</tr>\n";
	}
	if ( $args['title'] || $args['title_left'] || $args['title_right'] ) {
		$r .= "</thead>\n";
	}
	$r .= "<tbody>\n$diff\n</tbody>\n";
	$r .= '</table>';

	return $r;
}

// Setting ApI
function biz_get_redux_settings() {
	if ( ! isset( $GLOBALS[Rt_Biz_Setting::$biz_opt] ) ) {
		$GLOBALS[Rt_Biz_Setting::$biz_opt] = get_option( Rt_Biz_Setting::$biz_opt, array() );
	}

	error_log(var_export($GLOBALS[Rt_Biz_Setting::$biz_opt],true). ": -> asddddd ", 3, "/var/www/dummytest.com/logs/my-errors.log");

	return $GLOBALS[Rt_Biz_Setting::$biz_opt];
}