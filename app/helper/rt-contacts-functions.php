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

function rt_contacts_get_posts_for_person( $post_type, $post_id, $key ) {
	global $rt_person;
	return $rt_person->get_posts_for_entity( $post_type, $post_id, $key );
}

function rt_contacts_get_posts_for_organization( $post_type, $post_id, $key ) {
	global $rt_organization;
	return $rt_organization->get_posts_for_entity( $post_type, $post_id, $key );
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

function rt_contacts_get_entity_meta( $id, $key, $value ) {
	return Rt_Entity::add_meta( $id, $key, $value );
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