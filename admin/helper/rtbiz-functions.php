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
function rtbiz_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

	if ( $args && is_array( $args ) ) {
		extract( $args );
	}

	$located = rtbiz_locate_template( $template_name, $template_path, $default_path );

	do_action( 'rtbiz_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'rtbiz_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Loads rtBiz Templates
 *
 * @param $template_name
 * @param string $template_path
 * @param string $default_path
 *
 * @return mixed|void
 */
function rtbiz_locate_template( $template_name, $template_path = '', $default_path = '' ) {

	if ( ! $template_path ) {
		$template_path = Rtbiz::$templateURL;
	}

	if ( ! $default_path ) {
		$default_path = RTBIZ_PATH_TEMPLATES;
	}

	// Look within passed path within the theme - this is priority
	$template = locate_template( array(
		trailingslashit( $template_path ) . $template_name,
		$template_name,
	) );

	// Get default template
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found
	return apply_filters( 'rtbiz_locate_template', $template, $template_name, $template_path );
}

/**
 * Sanitize Module Key
 *
 * @param $key
 *
 * @return mixed|string
 */
function rtbiz_sanitize_module_key( $key ) {
	$filtered = strtolower( remove_accents( stripslashes( strip_tags( $key ) ) ) );
	$filtered = preg_replace( '/&.+?;/', '', $filtered ); // Kill entities
	$filtered = str_replace( array( '.', '\'', '"' ), '', $filtered ); // Kill quotes and full stops.
	$filtered = str_replace( array( ' ' ), '-', $filtered ); // Replace spaces.

	return $filtered;
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

	$renderer  = new Rtbiz_Text_Diff();
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

function rtbiz_get_tex_diff( $post_id, $texonomy ) {
	$post_terms = wp_get_post_terms( $post_id, $texonomy );
	$postterms  = array_filter( $_POST['tax_input'][ $texonomy ] );
	$termids    = wp_list_pluck( $post_terms, 'term_id' );
	$diff       = array_diff( $postterms, $termids );
	$diff2      = array_diff( $termids, $postterms );
	$diff_tax1  = array();
	$diff_tax2  = array();
	foreach ( $diff as $tax_id ) {
		$tmp         = get_term_by( 'id', $tax_id, $texonomy );
		$diff_tax1[] = $tmp->name;
	}

	foreach ( $diff2 as $tax_id ) {
		$tmp         = get_term_by( 'id', $tax_id, $texonomy );
		$diff_tax2[] = $tmp->name;
	}

	$difftxt = rtbiz_text_diff( implode( ' ', $diff_tax2 ), implode( ' ', $diff_tax1 ) );

	if ( ! empty( $difftxt ) || '' != $difftxt ) {
		$tax   = get_taxonomy( $texonomy );
		$lable = get_taxonomy_labels( $tax );
		$body  = '<strong>' . __( $lable->name ) . '</strong> : ' . $difftxt;

		return $body;
	}

	return '';
}


/**
 * ******** Entity Meta function *********
 */

/**
 * Add meta field
 *
 * @param $id
 * @param $key
 * @param $value
 */
function rtbiz_add_entity_meta( $id, $key, $value ) {
	Rtbiz_Entity::add_meta( $id, $key, $value );
}

/**
 * get meta field
 *
 * @param $id
 * @param $key
 * @param bool $single
 *
 * @return mixed
 */
function rtbiz_get_entity_meta( $id, $key, $single = false ) {
	return Rtbiz_Entity::get_meta( $id, $key, $single );
}

/**
 * update meta field
 *
 * @param $id
 * @param $key
 * @param $value
 */
function rtbiz_update_entity_meta( $id, $key, $value ) {
	Rtbiz_Entity::update_meta( $id, $key, $value );
}

/**
 * delete meta field
 *
 * @param $id
 * @param $key
 * @param $value
 */
function rtbiz_delete_entity_meta( $id, $key, $value ) {
	Rtbiz_Entity::delete_meta( $id, $key, $value );
}


/**
 * ******** contact function *********
 */

/**
 * Returns contact post type
 *
 * @return mixed
 */
function rtbiz_get_contact_post_type() {
	global $rtbiz_contact;

	return $rtbiz_contact->post_type;
}

function rtbiz_get_contact_labels() {
	global $rtbiz_contact;

	return $rtbiz_contact->labels;
}

/**
 * @return mixed
 */
function rtbiz_get_contact_meta_fields() {
	global $rtbiz_contact;

	return $rtbiz_contact->get_meta_fields();
}

function rtbiz_is_primary_email_unique( $email, $postid = null ) {
	global $rtbiz_contact;
	$meta_query_args = array(
		array(
			'key'   => Rtbiz_Entity::$meta_key_prefix . Rtbiz_Contact::$primary_email_key,
			'value' => $email,
		),
	);
	$args            = array( 'post_type' => rtbiz_get_contact_post_type(), 'meta_query' => $meta_query_args );
	if ( ! empty( $postid ) ) {
		$args['post__not_in'] = array( $postid );
	}
	$posts = get_posts( $args );
	$count = count( $posts );
	if ( 0 == $count ) {
		return true;
	}

	return false;
}

/**
 * add a contact
 *
 * @param        $name
 * @param string $description
 * @param string $email
 *
 * @return mixed
 */
function rtbiz_add_contact( $name, $description = '', $email = '' ) {
	global $rtbiz_contact;

	return $rtbiz_contact->add_contact( $name, $description, $email );
}

function rtbiz_get_contact_edit_link( $email ) {
	$post = rtbiz_get_contact_by_email( $email );
	if ( ! empty( $post ) ) {
		return get_edit_post_link( $post[0]->ID );
	} else {
		return '#';
	}
}

function rtbiz_export_contact( $user_id ) {
	global $rtbiz_contact;

	return $rtbiz_contact->export_biz_contact( $user_id );
}

/**
 * get contact by email
 *
 * @param $email
 *
 * @return mixed
 */
function rtbiz_get_contact_by_email( $email ) {
	global $rtbiz_contact;

	return $rtbiz_contact->get_by_email( $email );
}

/**
 * Search contact
 *
 * @param $query
 *
 * @return mixed
 */
function rtbiz_search_contact( $query, $args = array() ) {
	global $rtbiz_contact;

	return $rtbiz_contact->search_entity( $query, $args );
}

function rtbiz_get_contact_for_wp_user( $user_id ) {
	global $rtbiz_contact;

	return $rtbiz_contact->get_contact_for_wp_user( $user_id );
}

function rtbiz_get_wp_user_for_contact( $contact_id ) {
	global $rtbiz_contact;

	return $rtbiz_contact->get_wp_user_for_contact( $contact_id );
}

/**
 * Count total contacts for team.
 *
 * @param int $team_id
 *
 * @return int $total
 */
function rtbiz_get_team_contacts( $team_id ) {
	global $rtbiz_contact;
	$team     = get_term_by( 'id', $team_id, Rtbiz_Teams::$slug );
	$contacts = get_posts( array(
		Rtbiz_Teams::$slug => $team->slug,
		'post_type'        => $rtbiz_contact->post_type,
		'post_status'      => 'any',
		'nopaging'         => true,
	) );

	return $contacts;
}


/**
 * ******** Company function *********
 */

/**
 * Returns company post type
 *
 * @return mixed
 */
function rtbiz_get_company_post_type() {
	/* @var $rtbiz_company Rtbiz_Company */

	global $rtbiz_company;

	return $rtbiz_company->post_type;
}

function rtbiz_get_company_labels() {
	/* @var $rtbiz_company Rtbiz_Company */
	global $rtbiz_company;

	return $rtbiz_company->labels;
}

/**
 * @return mixed
 */
function rtbiz_get_company_meta_fields() {

	/* @var $rtbiz_company Rtbiz_Company */
	global $rtbiz_company;

	return $rtbiz_company->meta_fields;
}

function rtbiz_is_primary_email_unique_company( $email ) {
	$meta_query_args = array(
		array(
			'key'   => Rtbiz_Entity::$meta_key_prefix . Rtbiz_Company::$primary_email,
			'value' => $email,
		),
	);
	$posts           = get_posts( array( 'post_type' => 'rt_company', 'meta_query' => $meta_query_args ) );
	$count           = count( $posts );
	if ( 0 == $count ) {
		return true;
	}

	return false;
}

/**
 * adds an company
 *
 * @param $name
 * @param string $note
 * @param string $address
 * @param string $country
 * @param array $meta
 *
 * @return mixed
 */
function rtbiz_add_company( $name, $note = '', $address = '', $country = '', $meta = array() ) {
	/* @var $rtbiz_company Rtbiz_Company */

	global $rtbiz_company;

	return $rtbiz_company->add_company( $name, $note, $address, $country, $meta );
}

/**
 *
 * @param $query
 *
 * @return mixed
 */
function rtbiz_search_company( $query, $args = array() ) {
	/* @var $rtbiz_company Rtbiz_Company */

	global $rtbiz_company;

	return $rtbiz_company->search_entity( $query, $args );
}


function rtbiz_get_companies() {
	/* @var $rtbiz_company Rtbiz_Company */

	global $rtbiz_company;

	return $rtbiz_company->get_company();
}


/**
 * ******** Acl function *********
 */

/**
 * @return mixed
 */
function rtbiz_get_acl_permissions() {
	return Rtbiz_Access_Control::$permissions;
}

/**
 * @return mixed
 */
function rtbiz_get_modules() {
	return Rtbiz_Access_Control::$modules;
}

/**
 * @return array|WP_Error
 */
function rtbiz_get_team() {
	$Team = get_terms( Rtbiz_Teams::$slug, array( 'hide_empty' => false ) );

	return $Team;
}


function rtbiz_get_user_team( $user_ID ) {
	$user_contacts = rtbiz_get_contact_for_wp_user( $user_ID );
	$ug_terms      = array();
	if ( ! empty( $user_contacts ) ) {
		foreach ( $user_contacts as $contact ) {
			$temp_terms = wp_get_post_terms( $contact->ID, Rtbiz_Teams::$slug );
			$ug_terms   = array_merge( $ug_terms, $temp_terms );
		}
	}

	return $ug_terms;

}

/**
 * @param $module_key
 * @param string $role
 *
 * @return string
 */
function rtbiz_get_access_role_cap( $module_key, $role = 'no_access' ) {
	return Rtbiz_Access_Control::get_capability_from_access_role( $module_key, $role );
}

function rtbiz_is_our_employee( $value, $module ) {
	global $rtbiz_contact;
	if ( is_numeric( $value ) ) {
		$value = get_user_by( 'id', $value );
	} elseif ( is_string( $value ) ) {
		$value = get_user_by( 'email', $value );
	} elseif ( ! is_object( $value ) ) {
		return false;
	}

	$isEmployee = p2p_connection_exists( $rtbiz_contact->post_type . '_to_user', array( 'to' => $value->ID ) );

	return ( $isEmployee && ! empty( $value ) && user_can( $value, rtbiz_get_access_role_cap( $module, 'author' ) ) ) ? true : false;
}

function rtbiz_get_module_employee( $module_key ) {
	global $rtbiz_access_control;

	return $rtbiz_access_control->get_module_users( $module_key );
}

function rtbiz_get_team_users( $team_id ) {
	global $rtbiz_contact;
	$team        = get_term_by( 'id', $team_id, Rtbiz_Teams::$slug );
	$contacts    = get_posts( array(
		Rtbiz_Teams::$slug => $team->slug,
		'post_type'        => $rtbiz_contact->post_type,
		'post_status'      => 'any',
		'nopaging'         => true,
	) );
	$contact_ids = array();
	foreach ( $contacts as $contact ) {
		$contact_ids[] = $contact->ID;
	}

	if ( ! empty( $contact_ids ) ) {
		return rtbiz_get_wp_user_for_contact( $contact_ids );
	}

	return array();
}

function rtbiz_get_module_team_users( $team_id, $category_slug = '', $module_key = '' ) {
	global $rtbiz_contact;

	$team = get_term_by( 'id', $team_id, Rtbiz_Teams::$slug );

	$args = array(
		Rtbiz_Teams::$slug => $team->slug,
		'post_type'        => $rtbiz_contact->post_type,
		'post_status'      => 'any',
		'nopaging'         => true,
	);

	/*if ( ! empty( $category_slug ) ) {
		$args = array_merge( $args, array( Rtbiz_Contact::$user_category_taxonomy => $category_slug ) );
	}*/

	$contacts = get_posts( $args );

	$contact_ids = array();
	foreach ( $contacts as $contact ) {
		// module filter
		if ( ! empty( $module_key ) ) {
			$pp = get_post_meta( $contact->ID, 'rtbiz_profile_permissions', true );
			if ( isset( $pp[ $module_key ] ) && 0 == intval( $pp[ $module_key ] ) ) {
				continue;
			}
		}
		$contact_ids[] = $contact->ID;
	}

	if ( ! empty( $contact_ids ) ) {
		return rtbiz_get_wp_user_for_contact( $contact_ids );
	}

	return array();
}

/**
 * ******** P2p function *********
 */
function rtbiz_register_p2p_connection( $from_post_type, $to_post_type, $args = array() ) {
	global $rtbiz_p2p;
	$rtbiz_p2p->init_connection( $from_post_type, $to_post_type, $args );
}


/**
 * Register the contact connection with a post type
 *
 * @param $post_type
 * @param $label
 */
function rtbiz_register_contact_connection( $post_type, $label = array() ) {
	global $rtbiz_contact;
	rtbiz_register_p2p_connection( $post_type, $rtbiz_contact->post_type, $label );
}

/**
 * Register a company connection with a post type
 *
 * @param $post_type
 * @param $label
 */
function rtbiz_register_company_connection( $post_type, $label = array() ) {
	global $rtbiz_company;
	rtbiz_register_p2p_connection( $post_type, $rtbiz_company->post_type, $label );
}

/**
 *
 * @param $post_type
 * @param string $from
 * @param string $to
 */
function rtbiz_connect_post_to_contact( $post_type, $from = '', $to = '' ) {
	global $rtbiz_contact, $rtbiz_p2p;
	$rtbiz_p2p->connect_post_to_entity( $post_type, $rtbiz_contact->post_type, $from, $to );
}

/**
 * @param $post_type
 * @param string $from
 * @param string $to
 * @param bool $clear_old
 */
function rtbiz_connect_post_to_company( $post_type, $from = '', $to = '' ) {
	global $rtbiz_company, $rtbiz_p2p;
	$rtbiz_p2p->connect_post_to_entity( $post_type, $rtbiz_company->post_type, $from, $to );
}


function rtbiz_connect_contact_to_user( $from = '', $to = '' ) {
	global $rtbiz_contact, $rtbiz_p2p;
	$rtbiz_p2p->connect_post_to_entity( $rtbiz_contact->post_type, 'user', $from, $to );
}


/**
 * Get Posts for Person conection
 *
 * @param $post_id
 * @param $post_type
 * @param bool $fetch_contact
 *
 * @return mixed
 */
function rtbiz_get_post_for_contact_connection( $post_id, $post_type, $fetch_contact = false ) {
	global $rtbiz_contact, $rtbiz_p2p;

	return $rtbiz_p2p->get_posts_for_entity( $post_id, $post_type, $rtbiz_contact->post_type, $fetch_contact );
}

/**
 * Get posts for company connection
 *
 * @param $post_id
 * @param $post_type
 * @param bool $fetch_company
 *
 * @return mixed
 */
function rtbiz_get_post_for_company_connection( $post_id, $post_type, $fetch_company = false ) {
	global $rtbiz_company, $rtbiz_p2p;

	return $rtbiz_p2p->get_posts_for_entity( $post_id, $post_type, $rtbiz_company->post_type, $fetch_company );
}


function rtbiz_clear_post_connections_to_contact( $post_type, $ids ) {
	global $rtbiz_contact, $rtbiz_p2p;
	$rtbiz_p2p->clear_post_connections_to_entity( $post_type, $rtbiz_contact->post_type, 'from', $ids );
}

function rtbiz_clear_post_connections_to_company( $post_type, $ids ) {
	global $rtbiz_company, $rtbiz_p2p;
	$rtbiz_p2p->clear_post_connections_to_entity( $post_type, $rtbiz_company->post_type, 'from', $ids );
}

/**
 * @param $post_id
 * @param string $term_seperator
 *
 * @return string
 */
function rtbiz_contact_connection_to_string( $post_id, $term_seperator = ' , ' ) {
	global $rtbiz_contact, $rtbiz_company;

	return Rtbiz_P2p::connection_to_string( $post_id, $rtbiz_company->post_type, $rtbiz_contact->post_type, $term_seperator );
}

/**
 * @param $post_id
 * @param string $term_seperator
 *
 * @return string
 */
function rtbiz_company_connection_to_string( $post_id, $term_seperator = ' , ' ) {
	global $rtbiz_company, $rtbiz_contact;

	return Rtbiz_P2p::connection_to_string( $post_id, $rtbiz_contact->post_type, $rtbiz_company->post_type, $term_seperator );
}

/**
 * Remove single connection from registered post type to Rtbiz_Entity
 *
 * @param string $post_type
 * @param mixed $from
 * @param mixed $to
 */
function rtbiz_clear_post_connection_to_contact( $post_type, $from, $to ) {
	global $rtbiz_contact, $rtbiz_p2p;

	return $rtbiz_p2p->clear_post_connection_to_entity( $post_type, $rtbiz_contact->post_type, $from, $to );
}

function rtbiz_remove_contact_to_user( $from = '', $to = '' ) {
	global $rtbiz_contact, $rtbiz_p2p;

	return $rtbiz_p2p->clear_post_connection_to_entity( $rtbiz_contact->post_type, 'user', $from, $to );
}


/***************************   setting function   ***************************/

function rtbiz_get_redux_settings() {
	if ( ! isset( $GLOBALS[ Rtbiz_Setting::$biz_opt ] ) ) {
		$GLOBALS[ Rtbiz_Setting::$biz_opt ] = get_option( Rtbiz_Setting::$biz_opt, array() );
	}

	return $GLOBALS[ Rtbiz_Setting::$biz_opt ];
}

function rtbiz_set_redux_setting( $key, $val ) {
	global $rtbiz_settings;
	$rtbiz_settings->ReduxFramework->set( $key, $val );
	$GLOBALS[ Rtbiz_Setting::$biz_opt ] = get_option( Rtbiz_Setting::$biz_opt, array() );
}

function rtbiz_get_product_selection_setting() {
	$return                  = array();
	$redux                   = rtbiz_get_redux_settings();
	$redux['product_plugin'] = apply_filters( 'rtbiz_product_setting', ( ! empty( $redux['product_plugin'] ) ) ? $redux['product_plugin'] : '' );
	if ( ! empty( $redux['product_plugin'] ) && is_array( $redux['product_plugin'] ) ) {
		foreach ( $redux['product_plugin'] as $key => $val ) {
			if ( ! empty( $val ) ) {
				$return[] = $key;
			}
		}
	}

	return $return;
}

/**
 * Display all congigured mailbox(s).
 */
function rtbiz_mailbox_list_view() {
	global $rtbiz_mailBox;
	$rtbiz_modules = rtbiz_get_modules();
	$rtbiz_mailBox->rtmailbox_list_all( $rtbiz_modules );
}


function rtbiz_gravity_importer_view( $module ) {
	global $rtbiz_importer;
	ob_start();
	$rtbiz_importer->importer_ui( $module );
	$gravity_importer_view = ob_get_clean();

	return $gravity_importer_view;
}

function rtbiz_gravity_importer_mapper_view() {
	global $rtlib_importer_mapper;
	ob_start();
	$rtlib_importer_mapper->ui();
	$gravity_import_mapper_content = ob_get_clean();

	return $gravity_import_mapper_content;
}

/***************************   Dashboard function   ***************************/

function rtbiz_export_wp_users_to_contacts() {
	ob_start();
	rtbiz_export_wp_users_to_contacts_dashboard( '' );

	return ob_get_clean();
}

function rtbiz_export_wp_users_to_contacts_dashboard( $btnhtml = null ) {
	$nonce          = wp_create_nonce( 'rt-biz-export-all' );
	$users          = new WP_User_Query( array( 'fields' => 'ID', 'number' => 1 ) );
	$contact_labels = rtbiz_get_contact_labels();
	?>
	<div class="rtbiz-exporter-container">
		<?php if ( empty( $btnhtml ) ) { ?>
			<button type="button"
			        class="rtbiz-export-button button button-primary"><?php _e( 'Import all' ); ?></button>
		<?php } else {
			echo $btnhtml;
		} ?>
		<img id="rtbiz-import-spinner" style="display: none;" src="<?php echo admin_url() . 'images/spinner.gif'; ?>"/>
		<input id="rtbiz-contact-import-nonce" type="hidden" value="<?php echo $nonce ?>"/>
		<span id="rtbiz-import-message" class="rtbiz-exporter-message"></span>

		<div class="contact-update" style="display: none;">
			<p> <?php echo __( 'Syncing' ) . ' ' . $contact_labels['name'] . ' :'; ?> <span
					id='rtbiz-contact-count-proceed'>0</span></p>
		</div>
		<div class="contact-synced" style="display: none;">
			<p> <?php _e( 'All ' . $contact_labels['name'] . ' synced!' ); ?> </p>
		</div>
		<div id="rtbiz-contact-importer-bar"></div>


		<input id="rtbiz-contact-count" type="hidden" value="<?php echo $users->get_total(); ?>"/>
	</div>
	<?php
}

/*
 * To check attachment type support by google viewer or not
 * @param $post_mime_type
 * @param string $extation
 *
 * @return bool
 */
function rtbiz_is_google_doc_supported_type( $post_mime_type, $extation = '' ) {
	$mime_types = array(
		// ext		=>	mime_type
		'ai'    => 'application/postscript',
		'doc'   => 'application/msword',
		'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml',
		'dxf'   => 'application/dxf',
		'eps'   => 'application/postscript',
		'otf'   => 'font/opentype',
		'pages' => 'application/x-iwork-pages-sffpages',
		'pdf'   => 'application/pdf',
		'pps'   => 'application/vnd.ms-powerpoint',
		'ppt'   => 'application/vnd.ms-powerpoint',
		'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml',
		'ps'    => 'application/postscript',
		'psd'   => 'image/photoshop',
		//		'rar'		=> 'application/rar',
		'svg'   => 'image/svg+xml',
		'tif'   => 'image/tiff',
		'tiff'  => 'image/tiff',
		'ttf'   => 'application/x-font-ttf',
		'xls'   => 'application/vnd.ms-excel',
		'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml',
		'xps'   => 'application/vnd.ms-xpsdocument',
		// zip can not be previewed and most user wants to download it anyways so let's send them direct download link rather then sending preview link.
		//		'zip'		=> 'application/zip',
		'txt'   => 'text/plain',
	);
	if ( ! empty( $extation ) ) {
		return ( array_key_exists( $extation, $mime_types ) || in_array( $post_mime_type, $mime_types ) );
	}

	return in_array( $post_mime_type, $mime_types );

}

/*
 * Get googel doc viewer link
 */
function rtbiz_google_doc_viewer_url( $attachment_url ) {
	if ( is_ssl() ) {
		$protocol_type = 'https';
	} else {
		$protocol_type = 'http';
	}
	$attachment_url = $protocol_type . '://docs.google.com/viewer?url=' . urlencode( $attachment_url ) . '&embedded=true';

	return $attachment_url;
}

/*
 * Function to extract extension from url
 */
function rtbiz_get_attchment_extension( $guid ) {
	$extn_array = explode( '.', $guid );

	return $extn_array[ count( $extn_array ) - 1 ];
}

/**
 *
 */
function rtbiz_get_avatar( $id_or_email, $size ) {
	if ( is_numeric( $id_or_email ) ) {
		$id   = (int) $id_or_email->user_id;
		$user = get_userdata( $id );
		if ( $user ) {
			$id_or_email = $user->user_email;
		}
	} elseif ( is_object( $id_or_email ) ) {
		if ( ! empty( $id_or_email->user_id ) ) {
			$id   = (int) $id_or_email->user_id;
			$user = get_userdata( $id );
			if ( $user ) {
				$id_or_email = $user->user_email;
			} elseif ( empty( $id_or_email->comment_author_email ) ) {
				$id_or_email = $id_or_email->comment_author_email;
			}
		}
	}
	$default = RTBIZ_URL . 'assets/icon-128x128.png';

	return get_avatar( $id_or_email, $size, $default );
}
