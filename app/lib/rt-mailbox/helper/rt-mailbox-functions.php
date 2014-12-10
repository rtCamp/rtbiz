<?php

function rt_encrypt_decrypt( $string ) {

	$string_length    = strlen( $string );
	$encrypted_string = '';

	/**
	 * For each character of the given string generate the code
	 */
	for ( $position = 0; $position < $string_length; $position ++ ) {
		$key                      = ( ( $string_length + $position ) + 1 );
		$key                      = ( 255 + $key ) % 255;
		$get_char_to_be_encrypted = substr( $string, $position, 1 );
		$ascii_char               = ord( $get_char_to_be_encrypted );
		$xored_char               = $ascii_char ^ $key; //xor operation
		$encrypted_char           = chr( $xored_char );
		$encrypted_string .= $encrypted_char;
	}

	/**
	 * Return the encrypted/decrypted string
	 */

	return $encrypted_string;
}


/**
 * Check duplicate message from message ID
 *
 * @param $messageid
 *
 * @return bool
 *
 * @since rt-Helpdesk 0.1
 */
function rt_check_duplicate_from_message_id( $messageid ) {
	global $wpdb;
	if ( $messageid && trim( $messageid ) == '' ) {
		return false;
	}

	$sql    = $wpdb->prepare( "select meta_value from $wpdb->commentmeta where $wpdb->commentmeta.meta_key = '_messageid' and $wpdb->commentmeta.meta_value = %s", $messageid );
	$result = $wpdb->get_results( $sql );
	if ( empty( $result ) ) {

		$sql    = $wpdb->prepare( "select meta_value from $wpdb->postmeta where $wpdb->postmeta.meta_key = '_messageid' and $wpdb->postmeta.meta_value = %s", $messageid );
		$result = $wpdb->get_results( $sql );

		return ! empty( $result );
	} else {
		return ! empty( $result );
	}
}

/**
 * check if given email is system email or not
 *
 * @param $email
 *
 * @return bool
 */
function rt_is_system_email( $email ) {
	global $rt_mail_settings;
	$google_acs = $rt_mail_settings->get_user_google_ac();

	foreach ( $google_acs as $ac ) {
		$ac->email_data = unserialize( $ac->email_data );
		$ac_email          = filter_var( $ac->email_data['email'], FILTER_SANITIZE_EMAIL );
		if ( $ac_email == $email ) {
			return true;
		}
	}
	return false;
}

function rt_force_utf_8( $string ) {
	//			return preg_replace('/[^(\x20-\x7F)]*/','', $string);
	//			$string = preg_replace( '/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]' .
	//									'|(?<=^|[\x00-\x7F])[\x80-\xBF]+' .
	//									'|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*' .
	//									'|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})' .
	//									'|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/' ,
	//			                      '?', $string );

	//			$string = preg_replace( '/\xE0[\x80-\x9F][\x80-\xBF]' . '|\xED[\xA0-\xBF][\x80-\xBF]/S','?', $string );

	return $string;
}


/**
 * Logging errors
 *
 * @param        $msg
 * @param string $filename
 *
 * @since rt-Helpdesk 0.1
 */
function rt_log( $msg, $filename = 'error_log.txt' ) {
	$log_file = '/tmp/mailbox' . $filename;
	if ( $fp = fopen( $log_file, 'a+' ) ) {
		fwrite( $fp, "\n" . '[' . date( DATE_RSS ) . '] ' . $msg . "\n" );
		fclose( $fp );
	}
}




/**
 * Get extension of file
 *
 * @param $file
 *
 * @return int|string
 *
 * @since rt-Helpdesk 0.1
 */
function rt_get_extention( $file ) {

	foreach ( Rt_Mailbox::$rt_mime_types as $key => $mime ) {
		if ( $mime == $file ) {
			return $key;
		}
	}

	return 'tmp';
}


/**
 * Get mime type of file
 *
 * @param $file
 *
 * @return string
 *
 * @since rt-Helpdesk 0.1
 */
function rt_get_mime_type( $file ) {

	// our list of mime types

	$extension = strtolower( end( explode( '.', $file ) ) );
	if ( isset( Rt_Mailbox::$rt_mime_types[ $extension ] ) ) {
		return Rt_Mailbox::$rt_mime_types[ $extension ];
	} else {
		return 'application/octet-stream';
	}
}

?>