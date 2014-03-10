<?php


/**
 * rt-contacts Helper Functions
 *
 * Helper functions for rt-contacts
 *
 * @author udit
 */

function rt_contacts_register_person_connection( $post_type, $label ) {
	global $rt_person;
	$rt_person->init_connection( $post_type, $label );
}

function rt_contacts_register_organization_connection( $post_type, $label ) {
	global $rt_organization;
	$rt_organization->init_connection( $post_type, $label );
}

function rt_contacts_get_post_to_person_connection( $post_id, $post_type, $fetch_person = false ) {
	global $rt_person;
	return $rt_person->get_posts_for_entity( $post_id, $post_type, $fetch_person );
}

function rt_contacts_get_post_for_organization_connection( $post_id, $post_type, $fetch_organization = false ) {
	global $rt_organization;
	return $rt_organization->get_posts_for_entity( $post_id, $post_type, $fetch_organization );
}

function rt_contacts_get_person_post_type() {
	global $rt_person;
	return $rt_person->post_type;
}

function rt_contacts_get_organization_post_type() {
	global $rt_organization;
	return $rt_organization->post_type;
}

function rt_contacts_get_person_by_email( $email ) {
	global $rt_person;
	return $rt_person->get_by_email( $email );
}

function rt_contacts_add_entity_meta( $id, $key, $value ) {
	Rt_Entity::add_meta( $id, $key, $value );
}

function rt_contacts_get_entity_meta( $id, $key, $single = false ) {
	return Rt_Entity::get_meta( $id, $key, $single );
}

function rt_contacts_update_entity_meta( $id, $key, $value ) {
	Rt_Entity::update_meta( $id, $key, $value );
}

function rt_contacts_delete_entity_meta( $id, $key, $value ) {
	Rt_Entity::delete_meta( $id, $key, $value );
}

function rt_contacts_add_organization( $name, $note = '', $address = '', $country = '', $meta = array() ) {
	global $rt_organization;
	return $rt_organization->add_organization( $name, $note, $address, $country, $meta );
}

function rt_contacts_add_person( $name, $description = '' ) {
	global $rt_person;
	return $rt_person->add_person( $name, $description );
}

function rt_contacts_connect_post_to_person( $post_type, $from = '', $to = '', $clear_old = false ) {
	global $rt_person;
	$rt_person->connect_post_to_entity( $post_type, $from, $to, $clear_old );
}

function rt_contacts_connect_post_to_organization( $post_type, $from = '', $to = '', $clear_old = false ) {
	global $rt_organization;
	$rt_organization->connect_post_to_entity( $post_type, $from, $to, $clear_old );
}

function rt_contacts_connect_organization_to_person( $from = '', $to = '' ) {
	global $rt_contacts;
	$rt_contacts->connect_organization_to_person( $from, $to );
}

function rt_contacts_person_connection_to_string( $post_id, $term_seperator = ' , ' ) {
	global $rt_person;
	return Rt_Entity::connection_to_string( $post_id, $rt_person->post_type, $term_seperator );
}

function rt_contacts_organization_connection_to_string( $post_id, $term_seperator = ' , ' ) {
	global $rt_organization;
	return Rt_Entity::connection_to_string( $post_id, $rt_organization->post_type, $term_seperator );
}

function rt_contacts_get_organization_to_person_connection( $connected_items ) {
	global $rt_contacts;
	return $rt_contacts->get_organization_to_person_connection( $connected_items );
}

function rt_contacts_get_person_capabilities() {
	global $rt_person;
	return $rt_person->get_post_type_capabilities();
}

function rt_contacts_get_organization_capabilities() {
	global $rt_organization;
	return $rt_organization->get_post_type_capabilities();
}

function rt_contacts_search_person( $query ) {
	global $rt_person;
	return $rt_person->search( $query );
}

function rt_contacts_search_organization( $query ) {
	global $rt_organization;
	return $rt_organization->search( $query );
}

function rt_contacts_get_person_meta_fields() {
	global $rt_person;
	return $rt_person->meta_fields;
}

function rt_contacts_get_organization_meta_fields() {
	global $rt_organization;
	return $rt_organization->meta_fields;
}
