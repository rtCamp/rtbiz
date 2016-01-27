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
 * Description of Rt_Zend_Mail
 * This class deals with imap email related functions
 *
 * @author udit
 *
 * @since  rt-Helpdesk 0.1
 */
use Zend\Mail\Message;
use Zend\Mail\Storage\Imap as ImapStorage;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;

if ( ! class_exists( 'Rt_Zend_Mail' ) ) {

	/**
	 * Class Rt_Zend_Mail
	 */
	class Rt_Zend_Mail {

		/**
		 * @var
		 */
		public $imap;
		/**
		 * @var authentication string.
		 */
		public $authString;

		function __construct() {
		}

		/**
		 * UI for folders dropdown
		 *
		 * @param $folders
		 * @param $value
		 *
		 * @since rt-Helpdesk 0.1
		 *
		 */
		function render_folders_dropdown( $folders, $value ) {
			while ( $folders->getChildren() ) {
				$folder = $folders->current();
				if ( $folder->getChildren() ) {
					?>
				<optgroup label="<?php echo esc_attr( $folder->getGlobalName() ); ?>">
					<?php $this->render_folders_dropdown( $folder, $value ); ?>
					</optgroup><?php
				} else {
					?>
					<option
					value="<?php echo esc_attr( $folder->getGlobalName() ); ?>" <?php echo ( $folder->getGlobalName() == $value ) ? 'selected="selected"' : '' ?>><?php echo esc_html( $folder->getGlobalName() ); ?></option><?php
				}
				$folders->next();
			}
			$folders->rewind();
		}

		/**
		 * Render UI for folder checkbox
		 *
		 * @param $folders
		 * @param $element_name
		 * @param $values
		 * @param $data_str
		 */
		function render_folders_checkbox( $folders, $element_name, $values, $data_str ) {
			while ( $folders->getChildren() ) {
				echo '<ul>';
				$folder = $folders->current();
				if ( $folder->getChildren() ) {
					echo '<li class="folder_title"><strong>' . esc_html( $folder->getGlobalName() ) . '</strong></li>';
					echo '<li  class="has_children">';
					$this->render_folders_checkbox( $folder, $element_name, $values, $data_str );
					echo '</li>';
				} else {
					echo '<li class="folder_item">&nbsp;&nbsp;&nbsp;<label><input type="checkbox" ' . esc_attr( $data_str ) . ' name="' . esc_attr( $element_name ) . '[]" value="' . esc_attr( $folder->getGlobalName() ) . '" ' . esc_attr( ( in_array( $folder->getGlobalName(), $values ) ) ? 'checked="checked"' : '' ) . ' />';
					echo esc_html( $folder->getGlobalName() ) . '</label></li>';
				}
				$folders->next();
				echo '</ul>';
			}
			$folders->rewind();
		}

		/**
		 * concate string for auth
		 *
		 * @param $email
		 * @param $accessToken
		 *
		 * @return string
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function construct_auth_string( $email, $accessToken ) {
			return base64_encode( "user=$email\1auth=Bearer $accessToken\1\1" );
		}

		/**
		 *  authentication imap email
		 *
		 * @param $imap
		 * @param $email
		 * @param $accessToken
		 *
		 * @return bool
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function oauth2_authenticate( $imap, $email, $accessToken ) {
			$this->authString   = $this->construct_auth_string( $email, $accessToken );
			$authenticateParams = array( 'XOAUTH2', $this->authString );
			//        echo $this->authString;
			//        var_dump($authenticateParams);
			$imap->sendRequest( 'AUTHENTICATE', $authenticateParams );
			while ( true ) {
				$response = '';
				$is_plus  = $imap->readLine( $response, '+', true );
				if ( $is_plus ) {
					rt_log( "got an extra server challenge: $response" );
					// Send empty client response.
					$imap->sendRequest( '' );
				} else {
					if ( preg_match( '/^NO /i', $response ) || preg_match( '/^BAD /i', $response )
					) {
						rt_log( "got failure response: $response" );

						return false;
					} else {
						if ( preg_match( '/^OK /i', $response ) ) {
							return true;
						} else {
							// Some untagged response, such as CAPABILITY
						}
					}
				}
			}
		}

		/**
		 * @param $email
		 * @param $accessToken
		 * @param $email_type
		 * @param $imap_server
		 *
		 * @return bool
		 * try imap login if return true else false
		 *
		 * @since rt-Helpdesk 0.1
		 *
		 */
		function try_imap_login( $email, $accessToken, $email_type, $imap_server ) {

			$this->imap = new Zend\Mail\Protocol\Imap();

			switch ( $email_type ) {
				case 'goauth':
					$this->imap->connect( 'ssl://imap.gmail.com', '993', true );

					return $this->oauth2_authenticate( $this->imap, $email, $accessToken );
				case 'imap':
					global $rt_imap_server_model;
					$server = $rt_imap_server_model->get_server_by_id( $imap_server );
					if ( empty( $server ) ) {
						echo 'Mail Server Not Found. Invalid Server id.';

						return false;
					}
					$host = $server->incoming_imap_server;
					$port = $server->incoming_imap_port;
					$ssl  = ( isset( $server->incoming_imap_enc ) && ! is_null( $server->incoming_imap_enc ) ) ? $server->incoming_imap_enc : false;
					$this->imap->connect( $host, $port, $ssl );

					return $this->imap->login( $email, rtmb_encrypt_decrypt( $accessToken ) );
				default:
					return false;
			}
		}

		/**
		 * try imap server connect
		 *
		 * @param $host
		 * @param $port
		 * @param $ssl
		 *
		 * @return string
		 */
		function try_imap_connect( $host, $port, $ssl ) {
			$this->imap = new Zend\Mail\Protocol\Imap();

			return $this->imap->connect( $host, $port, $ssl );
		}

		/**
		 * send email
		 *
		 * @param        $fromemail
		 * @param        $accessToken
		 * @param        $email_type
		 * @param        $imap_server
		 * @param        $subject
		 * @param        $body
		 * @param        $toEmail
		 * @param        $ccEmail
		 * @param        $bccEmail
		 * @param        $attachemnts
		 * @param string $mailtype
		 *
		 * @return bool|void
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function sendemail( $fromname, $fromemail, $accessToken, $email_type, $imap_server, $subject, $body, $toEmail, $ccEmail, $bccEmail, $attachemnts, $email = null, $mailtype = 'notification' ) {
			set_time_limit( 0 );
			if ( ! $this->try_imap_login( $fromemail, $accessToken, $email_type, $imap_server ) ) {
				return false;
			}

			$transport = new SmtpTransport();

			$smtp_args = array();
			switch ( $email_type ) {
				case 'goauth':
					$smtp_args['name']              = 'gmail-smtp';
					$smtp_args['host']              = 'smtp.gmail.com';
					$smtp_args['port']              = 465;
					$smtp_args['connection_class']  = 'oauth2';
					$smtp_args['connection_config'] = array( 'xoauth2_request' => $this->authString, 'ssl' => 'ssl' );
					break;
				case 'imap':
					global $rt_imap_server_model;
					$server = $rt_imap_server_model->get_server_by_id( $imap_server );
					if ( empty( $server ) ) {
						echo 'Mail Server Not Found. Invalid Server id.';

						return false;
					}
					$smtp_args['name']              = $server->outgoing_smtp_server;
					$smtp_args['host']              = $server->outgoing_smtp_server;
					$smtp_args['port']              = $server->outgoing_smtp_port;
					$smtp_args['connection_class']  = 'login';
					$smtp_args['connection_config'] = array(
						'username' => $fromemail,
						'password' => rtmb_encrypt_decrypt( $accessToken ),
						'ssl'      => $server->outgoing_smtp_enc,
					);
					break;
				default:
					break;
			}

			$options = new SmtpOptions( $smtp_args );
			$transport->setOptions( $options );

			$message = new Message();
			if ( ! empty( $email ) ) {
				$message_id = $reference_id = $in_reply_to = '';
				if ( 'comment' == $email->refrence_type ) {
					$message_id   = get_comment_meta( $email->refrence_id, '_rtlib_messageid', true );
					$reference_id = get_comment_meta( $email->refrence_id, '_rtlib_references', true );
					if ( empty( $message_id ) ) {
						$comment = get_comment( $email->refrence_id );
						$post_id = $comment->comment_post_ID;
					}
				} else if ( 'post' == $email->refrence_type ) {
					$post_id = $email->refrence_id;
				}

				if ( isset( $post_id ) ) {
					$reference_id = get_post_meta( $post_id, '_rtlib_references', true );
					$message_id   = rtmb_get_reply_to_from_ref_id( $reference_id );

					/**
					 * Filter the reply_to email address.
					 *
					 * @param string $fromemail Actual from email address.
					 * @param int $post_id Ticket ID.
					 */
					$reply_to = apply_filters( 'rtlib_reply_to_header', $fromemail, $post_id );

					if ( ! empty( $reply_to ) ) {
						$message->addCustomeHeader( 'Reply-To', trim( $reply_to ) );
					}
				} else {
					$post_id = 0;
				}
				/**
				 * Filter the from email address.
				 *
				 * @param string $fromemail Actual from email address.
				 * @param int $post_id Ticket ID.
				 */
				$new_fromemail = apply_filters( 'rtlib_from_header', $fromemail, $post_id );

				$message->addFrom( $new_fromemail, $fromname );

				//Get reply to header
				if ( ! empty( $message_id ) ) {
					$message->addCustomeHeader( 'In-Reply-To', trim( $message_id ) );
				}

				//Get References header
				if ( ! empty( $message_id ) ) {
					$reference_id = rtmb_add_message_id_in_ref_id( $message_id, $reference_id );
				}
				if ( ! empty( $reference_id ) ) {
					$reference_ids = rtmb_get_reference_id_array( $reference_id );
					$_reference_id = implode( ' ', $reference_ids );
					$message->addCustomeHeader( 'References', $_reference_id );
				}

				// Add x-mailer
				if ( ! empty( $email->refrence_id ) ) {
					$message->addCustomeHeader( 'X-Mailer', 'rtCamp-mail-lib' );
					if ( 'comment' == $email->refrence_type ) {
						$comment = get_comment( $email->refrence_id );
						$post_id = $comment->comment_post_ID;
					} else {
						$post_id = $email->refrence_id;
					}
					$new_message_id = rtmb_generate_message_id( $post_id, $email->id );
					rtmb_add_message_id_in_ref_id( $new_message_id, $reference_id, $post_id );
					$message->addCustomeHeader( 'Message-ID', $new_message_id );
				}
			} else {
				$message->addFrom( $fromemail, $fromname );
			}

			//$mail->setFrom($fromemail);

			$message->setSubject( stripslashes_deep( html_entity_decode( $subject, ENT_QUOTES, 'UTF-8' ) ) );
			//$mail->setSubject($subject);
			if ( ! empty( $toEmail ) ) {
				foreach ( $toEmail as $temail ) {
					//$mail->addTo($temail["email"], isset($temail["name"]) ? $temail["name"] : '');
					$message->addTo( $temail['email'], isset( $temail['name'] ) ? $temail['name'] : '' );
				}
			}
			if ( ! empty( $ccEmail ) ) {
				foreach ( $ccEmail as $temail ) {
					//$mail->addCc($temail["email"], isset($temail["name"]) ? $temail["name"] : '');
					$message->addCc( $temail['email'], isset( $temail['name'] ) ? $temail['name'] : '' );
				}
			}
			if ( ! empty( $bccEmail ) ) {
				foreach ( $bccEmail as $temail ) {
					if ( isset( $temail['email'] ) ) {
						$message->addBcc( $temail['email'], isset( $temail['name'] ) ? $temail['name'] : '' );
					}
				}
			}
			// create a MimeMessage object that will hold the mail body and any attachments
			$bodyPart = new MimeMessage;

			$bodyMessage           = new MimePart( $body );
			$bodyMessage->type     = 'text/html';
			$bodyMessage->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

			$bodyPart->addPart( $bodyMessage );

			if ( ! empty( $attachemnts ) && is_array( $attachemnts ) ) {
				foreach ( $attachemnts as $attach ) {
					$file_array = explode( '/', $attach );
					$fileName   = $file_array[ count( $file_array ) - 1 ];
					$attachment = new MimePart( file_get_contents( $attach ) );

					$attachment->type        = rtmb_get_mime_type( $attach );
					$attachment->filename    = $fileName;
					$attachment->encoding    = Zend\Mime\Mime::ENCODING_BASE64;
					$attachment->disposition = Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
					$bodyPart->addPart( $attachment );
				}
			}
			$message->setBody( $bodyPart );

			$transport->send( $message );

			return true;
		}

		/**
		 * decode message
		 *
		 * @param $part
		 *
		 * @return string
		 *
		 * @since rt-Helpdesk 0.1
		 *
		 */
		function get_decoded_message( $part ) {
			$txtBody = $part->getContent();
			if ( isset( $part->contentTransferEncoding ) ) {
				switch ( $part->contentTransferEncoding ) {
					case 'base64':
						$txtBody = base64_decode( $txtBody );
						break;
					case 'quoted-printable':
						$txtBody = quoted_printable_decode( $txtBody );
						break;
				}
			}
			preg_match( '/charset="(.+)"$/', $part->contentType, $matches );
			$charset = isset( $matches[1] ) ? $matches[1] : '';
			if ( 'iso-8859-1' == $charset ) {
				$txtBody = utf8_decode( $txtBody ); //convert to utf8
			}

			return $txtBody;
		}

		/**
		 * Read Email
		 *
		 * @param        $mailbox_email
		 * @param        $email
		 * @param        $accessToken
		 * @param        $email_type
		 * @param        $imap_server
		 * @param        $lastDate
		 * @param        $user_id
		 * @param        $module
		 * @param string $signature
		 *
		 * @return bool
		 * @internal param $module
		 * @since rt-Helpdesk 0.1
		 */
		public function reademail( $mailbox_email, $email, $accessToken, $email_type, $imap_server, $lastDate, $user_id, $module, $signature = '' ) {

			set_time_limit( 0 );
			global $signature, $rt_mail_settings;
			if ( ! $this->try_imap_login( $email, $accessToken, $email_type, $imap_server ) ) {
				$rt_mail_settings->update_sync_status( $email, false );
				rt_log( 'login fail' );

				return false;
			}

			$storage   = new ImapStorage( $this->imap );
			$email_acc = $rt_mail_settings->get_email_acc( $email, $module );

			if ( empty( $email_acc ) ) {
				$rt_mail_settings->update_sync_meta_time( $email, current_time( 'mysql' ) );
				$rt_mail_settings->update_sync_status( $email, false );
				rt_log( 'email fail' );

				return false;
			}

			$email_data = maybe_unserialize( $email_acc->email_data );

			if ( empty( $email_data['mail_folders'] ) ) {
				$rt_mail_settings->update_sync_meta_time( $email, current_time( 'mysql' ) );
				$rt_mail_settings->update_sync_status( $email, false );
				rt_log( 'inbox folder fail' );

				return false;
			}

			$mail_folders = explode( ',', ( isset( $email_data['mail_folders'] ) ) ? $email_data['mail_folders'] : '' );
			global $sync_inbox_type;
			global $rt_mail_uid;

			foreach ( $mail_folders as $folder ) {
				$storage->selectFolder( $folder );
				rt_log( ' Reading folder - ' . esc_attr( $folder ) . "\r\n" );
				$sync_inbox_type = $folder;
				if ( ! isset( $rt_mail_uid[ $sync_inbox_type ] ) ) {
					$rt_mail_uid[ $sync_inbox_type ] = 0;
				}

				global $rt_mail_uid;
				$arrayMailIds = array();
				if ( $rt_mail_uid[ $sync_inbox_type ] > 0 ) {
					$allMail = $storage->protocol->requestAndResponse( "UID FETCH {$rt_mail_uid[$sync_inbox_type]}:* (UID)", array() );
					if ( is_array( $allMail ) ) {
						foreach ( $allMail as $tempEmail ) {
							$arrayMailIds[] = array( 'uid' => $tempEmail[2][1], 'msgid' => $tempEmail[0] );
						}
					}
				} else {
					$arrayMailIds = $storage->protocol->search( array( 'SINCE ' . $lastDate ) );
				}
				$this->rt_parse_email( $mailbox_email, $email, $storage, $arrayMailIds, $user_id, $module );
				rt_log( ' Reading finish - ' . esc_attr( $folder ) . "\r\n" );
			}
			$rt_mail_settings->update_sync_meta_time( $email, current_time( 'mysql' ) );
			$rt_mail_settings->update_sync_status( $email, false );
		}

		/**
		 * @param $hex
		 *
		 * @return string
		 * todo:what this function does ?
		 */
		function bchexdec( $hex ) {
			$len = strlen( $hex );
			$dec = '';
			for ( $i = 1; $i <= $len; $i ++ ) {
				$dec = bcadd( $dec, bcmul( strval( hexdec( $hex[ $i - 1 ] ) ), bcpow( '16', strval( $len - $i ) ) ) );
			}

			return $dec;
		}

		/**
		 * Get number by unique ID
		 *
		 * @param $UmailId
		 * @param $storage
		 *
		 * @return int
		 *
		 * @throws Exception
		 *
		 * @since rt-Helpdesk 0.1
		 */
		function get_number_by_unique_id( $UmailId, &$storage ) {
			$cMail = $storage->protocol->requestAndResponse( "UID FETCH {$UmailId}:* (UID)", array() );
			if ( is_array( $cMail ) ) {
				foreach ( $cMail as $tempEmail ) {
					return intval( $tempEmail[0] );
				}
			} else {
				echo 'here --> $UmailId ';

				//var_dump( $cMail );

				return $cMail;
			}
			throw new Exception( 'No Unique id found' );
		}

		/**
		 * Insert email message ID
		 *
		 * @param $messageid
		 *
		 * @return bool
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function insert_mail_message_id( $messageid ) {
			global $rt_mail_message_model;

			$result = $rt_mail_message_model->get_message( array( 'messageid' => $messageid ) );
			if ( empty( $result ) ) {
				return $rt_mail_message_model->add_message( array( 'messageid' => $messageid ) );
			}

			return false;
		}

		/**
		 * Update sync
		 *
		 * @param $email
		 * @param $replytime
		 *
		 * @return bool
		 *
		 * @since rt-Helpdesk 0.1
		 *
		 */
		public function update_sync_meta( $email, $replytime ) {
			global $rt_mail_accounts_model;
			$rows_affected = $rt_mail_accounts_model->update_mail_account( array( 'last_mail_time' => $replytime ), array( 'email' => $email ) );

			return ( ! empty( $rows_affected ) );
		}

		/**
		 * Update email UID
		 *
		 * @param $email
		 * @param $uid
		 *
		 * @return bool
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function update_last_mail_uid( $email, $uid ) {
			//			global $threadPostId;
			//			if ( $threadPostId ) {
			//				return true;
			//			}
			global $rt_mail_uid;
			global $sync_inbox_type;
			global $rt_mail_accounts_model;
			$rt_mail_uid[ $sync_inbox_type ] = $uid;
			$rows_affected                   = $rt_mail_accounts_model->update_mail_account( array( 'last_mail_uid' => serialize( $rt_mail_uid ) ), array( 'email' => $email ) );

			return ( ! empty( $rows_affected ) );
		}

		/**
		 * parse email message
		 *
		 * @param $mailbox_email
		 * @param $email
		 * @param $storage
		 * @param $arrayMailIds
		 * @param $user_id
		 *
		 * @param $module
		 *
		 * @internal param $module
		 * @internal param $hdUser
		 * @since rt-Helpdesk 0.1
		 */
		public function rt_parse_email( $mailbox_email, $email, &$storage, &$arrayMailIds, $user_id, $module ) {

			$lastMessageId = '-1';
			//			global $rt_hd_import_operation;
			$lastFlags = false;
			$lastFlag  = array();
			$message   = null;
			//			global $threadPostId;

			foreach ( $arrayMailIds as $UmailId ) {
				try {
					if ( is_array( $UmailId ) ) {
						$tempUIDArray = $UmailId;
						$UmailId      = $tempUIDArray['uid'];
					}
					$mailId = $this->get_number_by_unique_id( $UmailId, $storage );
					if ( empty( $mailId ) ) {
						continue;
					}
					$message = $storage->getMessage( $mailId ); //1474);

					$lastFlags = $message->getFlags();
					try {
						$lastMessageId = $message->messageid;
					} catch ( Exception $e ) {
						$lastMessageId = false;
					}
					//$dateString = strstr($dateString," (",true);

					if ( isset( $message->xhelpdesk ) ) {

						$dt = new DateTime( $message->date );
						$this->update_last_mail_uid( $email, $UmailId );
						continue;
					}

					/*if (isset($message->xcrm)) {
						$dt = new DateTime($message->date);
						$this->update_last_mail_uid($email, $UmailId);
						continue;
					}*/

					if ( $lastMessageId && rtmb_check_duplicate_from_message_id( $lastMessageId ) ) {

						$dt = new DateTime( $message->date );
						$this->update_last_mail_uid( $email, $UmailId );
						continue;
					}

					if ( $lastMessageId ) {
						if ( ! $this->insert_mail_message_id( $lastMessageId ) ) {
							$this->update_last_mail_uid( $email, $UmailId );
							continue;
						}
					}
					if ( ! isset( $message->subject ) ) {
						$message->subject = ' ';
					}
					rt_log( sanitize_email( $email ) . ' Parsing Mail ' . esc_attr( $message->subject ) . "\r\n" );

					$from      = array();
					$allEmails = array();
					if ( isset( $message->from ) ) { // or $message->headerExists('cc');
						$arrFrom = $message->getHeader( 'from' )->getAddressList();
						foreach ( $arrFrom as $tFrom ) {
							$from['address'] = $tFrom->getEmail();
							$from['name']    = $tFrom->getName();
							$allEmails[]     = array(
								'address' => $tFrom->getEmail(),
								'name'    => $tFrom->getName(),
								'key'     => 'from',
							);
						}
					}
					if ( isset( $message->to ) ) { // or $message->headerExists('cc');
						$arrTo = $message->getHeader( 'to' )->getAddressList();
						foreach ( $arrTo as $tTo ) {
							if ( ! is_email( $tTo->getEmail() ) ) {
								continue;
							}
							$allEmails[] = array(
								'address' => $tTo->getEmail(),
								'name'    => $tTo->getName(),
								'key'     => 'to'
							);
						}
					}
					if ( isset( $message->cc ) ) { // or $message->headerExists('cc');
						$arrCC = $message->getHeader( 'cc' )->getAddressList();
						foreach ( $arrCC as $tCc ) {
							if ( ! is_email( $tCc->getEmail() ) ) {
								continue;
							}
							$allEmails[] = array(
								'address' => $tCc->getEmail(),
								'name'    => $tCc->getName(),
								'key'     => 'cc'
							);
						}
					}
					if ( isset( $message->bcc ) ) { // or $message->headerExists('cc');
						$arrBCC = $message->getHeader( 'bcc' )->getAddressList();
						foreach ( $arrBCC as $tBCc ) {
							if ( ! is_email( $tBCc->getEmail() ) ) {
								continue;
							}
							$allEmails[] = array(
								'address' => $tBCc->getEmail(),
								'name'    => $tBCc->getName(),
								'key'     => 'bcc'
							);
						}
					}
					$htmlBody     = '';
					$txtBody      = '';
					$attachements = array();
					if ( $message->isMultiPart() ) {
						foreach ( $message as $part ) {
							$response = $this->parse_message( $part, $email, $message );
							if ( isset( $response['txtBody'] ) && ! empty( $response['txtBody'] ) ) {
								$txtBody = $response['txtBody'];
							}
							if ( isset( $response['htmlBody'] ) && ! empty( $response['htmlBody'] ) ) {
								$htmlBody = $response['htmlBody'];
							}
							if ( isset( $response['attachements'] ) && ! empty( $response['attachements'] ) ) {
								$attachements = array_merge( $attachements, $response['attachements'] );
							}
						}
						if ( isset( $txtBody ) && empty( $htmlBody ) ) {
							$htmlBody = $txtBody;
						}
						if ( isset( $htmlBody ) && empty( $txtBody ) ) {
							$txtBody = strip_tags( $htmlBody, '<br><br/>' );
						}
					} else {
						if ( isset( $message->contentType ) ) {
							if ( 'text/plain' == $message->contentType ) {
								$txtBody  = $this->get_decoded_message( $message );
								$htmlBody = $txtBody;
							} else if ( 'text/html' == $message->contentType ) {
								$htmlBody = $this->get_decoded_message( $message );
								$htmlBody = balanceTags( $htmlBody );
								$txtBody  = strip_tags( $htmlBody, '<br><br/>' );
							} else {
								$htmlBody = $message->getContent();
								$htmlBody = balanceTags( $htmlBody );
								$txtBody  = strip_tags( $htmlBody, '<br><br/>' );
							}
						} else {
							$htmlBody = nl2br( $message->getContent() );
							$htmlBody = balanceTags( $htmlBody );
							$txtBody  = strip_tags( $htmlBody, '<br><br/>' );
						}
					}
					if ( false !== $lastFlags ) {
						$lastFlag = true;
						foreach ( $lastFlags as $fl ) {
							if ( Zend\Mail\Storage::FLAG_SEEN == $fl ) {
								$lastFlag = false;
							}
						}
						if ( $lastFlag ) {
							$storage->protocol->store( array( Zend\Mail\Storage::FLAG_SEEN ), $mailId, null, '-', true );
						}
					}

					$messageid = '';
					if ( isset( $message->messageid ) ) {
						$messageid = $message->messageid;
					}

					$inreplyto = '';
					if ( isset( $message->inreplyto ) ) {
						$inreplyto = $message->inreplyto;
					}

					$references = '';
					if ( isset( $message->references ) ) {
						$references = $message->references;
					}

					foreach ( $attachements as $a ) {
						if ( ! empty( $a['Content-ID'] ) ) {
							$htmlBody = str_replace( 'src="cid:' . $a['Content-ID'] . '"', 'src="' . $a['url'] . '"', $htmlBody );
						}
					}

					$subject  = $message->subject;
					$htmlBody = rtmb_force_utf_8( $htmlBody );
					$subject  = rtmb_force_utf_8( $subject );
					$txtBody  = rtmb_force_utf_8( $txtBody );

					$htmlBody = balanceTags( $htmlBody, true );

					preg_match_all( '/<body\s[^>]*>(.*?)<\/body>/s', $htmlBody, $output_array );
					if ( count( $output_array ) > 0 && ! empty( $output_array[1] ) ) {
						$htmlBody = $output_array[1][0];
					}

					$offset = strpos( $htmlBody, ':: Reply Above This Line ::' );
					if ( empty( $offset ) ) {
						$offset = strpos( $htmlBody, '::Reply Above This Line::' );
					}
					$visibleText = substr( $htmlBody, 0, ( false === $offset ) ? strlen( $htmlBody ) : $offset );

					$offset = strpos( $txtBody, ':: Reply Above This Line ::' );
					if ( empty( $offset ) ) {
						$offset = strpos( $txtBody, '::Reply Above This Line::' );
					}
					$txtBody = substr( $txtBody, 0, ( false === $offset ) ? strlen( $txtBody ) : $offset );

					$visibleText  = balanceTags( $visibleText, true );
					$originalBody = '';
					$tmp          = $message->getHeaders();
					foreach ( $tmp as $header ) {
						$originalBody .= htmlentities( $header->toString() ) . "\n";
					}
					$txtBody = rtrim( $txtBody, '\r\n' );
					$originalBody .= "Body:\r\n" . $txtBody;
					$originalBody .= "\r\n -- End of Body -- ";

					global $rt_mail_settings;
					$ac = $rt_mail_settings->get_email_acc( $email, $module );
					do_action( 'read_rt_mailbox_email_' . $ac->module, $subject, $visibleText, $from, $message->date, $allEmails, $attachements, $txtBody, true, $user_id, $messageid, $inreplyto, $references, $mailbox_email, $originalBody );

					//					global $threadPostId;
					//					if ( ! isset( $threadPostId ) ) {
					$this->update_last_mail_uid( $email, $UmailId );
					try {
						$dt = new DateTime( $message->date );
						$this->update_sync_meta( $email, $dt->format( 'Y-m-d H:i:s' ) );
					} catch ( Exception $e ) {
						$this->update_sync_meta( $email, $dt->format( 'Y-m-d H:i:s' ) );
					}
					//					}
				} catch ( Exception $e ) {
					ob_start();
					echo esc_attr( $e->getMessage() );
					//var_dump( $e->getTrace() );
					echo 'Error : ';
					//var_dump( $message );
					$data = ob_get_clean();

					if ( ! isset( $message->subject ) ) {
						$message->subject = '';
					}
					rtmb_log( "[Mail Sync Failed]Subject:{$message->subject}; Email: {$email}; MailNo: {$mailId};Message-Id: {$lastMessageId} ", 'error-mail-sync.txt' );
					rtmb_log( "[Mail Sync Failed]Subject:{$message->subject}; Email: {$email}; MailNo: {$mailId};Message-Id: {$lastMessageId} ", $email . 'error-mail-sync.txt' );
				}
			}
		}

		/**
		 * Function to divided multipart mail into different part
		 *
		 * @param $part
		 * @param $email
		 * @param $message
		 * @param array $response
		 * @param int $part_index
		 *
		 * @return array
		 */
		function parse_message( $part, $email, $message, $response = array(), $part_index = 0 ) {
			$part_index  = $part_index + 1;
			$ContentType = strtok( $part->contentType, ';' );
			if ( ! ( false === strpos( $ContentType, 'multipart/related' ) ) ) {
				$totParts = $part->countParts();
				for ( $rCount = 1; $rCount <= $totParts; $rCount ++ ) {
					$tPart    = $part->getPart( $rCount );
					$response = $this->parse_message( $tPart, $email, $message, $response, $part_index );
				}
			} else if ( ! ( false === strpos( $ContentType, 'multipart/alternative' ) ) ) {
				$totParts = $part->countParts();
				for ( $rCount = 1; $rCount <= $totParts; $rCount ++ ) {
					$tPart    = $part->getPart( $rCount );
					$response = $this->parse_message( $tPart, $email, $message, $response, $part_index );
				}
			} else {
				$filename = '';
				try {
					$filenameval = $part->getHeader( 'content-disposition' )->getFieldValue( 'filename' );
					if ( preg_match( '*filename=\"([^;]+)\"*', $filenameval, $matches ) ) {
						if ( isset( $matches[1] ) ) {
							$filename = trim( $matches[1] );
						}
					}
				} catch ( Exception $e ) {
				}

				if ( 'text/plain' == $ContentType && empty( $filename ) ) {
					$response['txtBody'] = $this->get_decoded_message( $part );
				} else if ( 'text/html' == $ContentType && empty( $filename ) ) {
					$response['htmlBody'] = $this->get_decoded_message( $part );
					$response['htmlBody'] = balanceTags( $response['htmlBody'] );
				} else {
					if ( trim( $filename ) == '' ) {
						$filename = rtmb_get_extention( $ContentType );
					}
					$filedata   = $this->get_decoded_message( $part );
					$upload_dir = wp_upload_dir( null );

					/* append current time and part index with file name
					   Fixed inline images parse with same name images.png*/
					$filename = time() . $part_index . '-' . sanitize_file_name( $filename );

					if ( ! file_exists( $upload_dir ['path'] . "/$filename" ) ) {
						$uploaded = wp_upload_bits( $filename, null, $filedata );
					} else {
						$uploaded['error'] = false;
						$uploaded['file']  = $upload_dir ['path'] . "/$filename";
						$uploaded['url']   = $upload_dir ['url'] . "/$filename";
					}
					if ( false == $uploaded['error'] ) {
						rtmb_log( "[Attachement Created] File:{$uploaded['file']} ; URL: {$uploaded['url']}", 'mail-attachement.txt' );
						$file             = array();
						$extn_array       = explode( '.', $filename );
						$extn             = $extn_array[ count( $extn_array ) - 1 ];
						$file['file']     = $uploaded['file'];
						$file['url']      = $uploaded['url'];
						$file['filename'] = $filename;
						$file['extn']     = $extn;
						$file['type']     = $ContentType;

						if ( $part->__isset( 'Content-ID' ) ) {
							$tmpval             = $part->getHeader( 'Content-ID' );
							$file['Content-ID'] = trim( $tmpval->getFieldValue(), '<>' );
						}
						$response['attachements'][] = $file;
					} else {
						rt_log( 'Attachment Failed ... ' . esc_attr( $filename ) . "\r\n" );
						ob_start();
						rt_log( var_export( $uploaded, true ) );
						$data = ob_get_clean();
						rtmb_log( "[Attachement Failed] Email: {$email};Message-Id: {$message->messageid}; Data : $data ", 'error-mail-attachement.txt' );
					}
				}
			}

			return $response;
		}

	}

}
