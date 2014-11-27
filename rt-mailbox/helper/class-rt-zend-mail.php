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

		//put your code here
		function __construct() {
			// set_include_path(get_include_path() . PATH_SEPARATOR . RT_HD_PATH_LIB);
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
		 * @param $inbox_folder
		 *
		 * @since rt-Helpdesk 0.1
		 *
		 */
		function render_folders_checkbox( $folders, $element_name, $values, $data_str, $inbox_folder ) {
			while ( $folders->getChildren() ) {
				echo '<ul>';
				$folder = $folders->current();
				if ( $folder->getChildren() ) {
					echo '<li class="folder_title"><strong>' .esc_html( $folder->getGlobalName() ) . '</strong></li>';
					echo '<li  class="has_children">';
					$this->render_folders_checkbox( $folder, $element_name, $values, $data_str, $inbox_folder );
					echo '</li>';
				} else {
					echo '<li class="folder_item">&nbsp;&nbsp;&nbsp;<label><input type="checkbox" ' . ( ( $folder->getGlobalName() == $inbox_folder ) ? 'disabled="disabled"' : '' ) . esc_attr( $data_str ) . ' name="' . esc_attr( $element_name ) . '[]" value="' . esc_attr( $folder->getGlobalName() ) . '" ' . esc_attr( ( in_array( $folder->getGlobalName(), $values ) ) ? 'checked="checked"' : '' ) . ' />';
					echo esc_html( $folder->getGlobalName() ). '</label></li>';
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
					error_log( "got an extra server challenge: $response" );
					// Send empty client response.
					$imap->sendRequest( '' );
				} else {
					if ( preg_match( '/^NO /i', $response ) || preg_match( '/^BAD /i', $response )
					) {
						error_log( "got failure response: $response" );

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

					return $this->imap->login( $email, rt_encrypt_decrypt( $accessToken ) );
				default:
					return false;
			}
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
		public function sendemail( $fromname, $fromemail, $accessToken, $email_type, $imap_server, $subject, $body, $toEmail, $ccEmail, $bccEmail, $attachemnts, $mailtype = 'notification' ) {
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
					$smtp_args['connection_config'] = array( 'xoauth2_request' => $this->authString, 'ssl' => 'ssl', );
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
						'password' => rt_encrypt_decrypt( $accessToken ),
						'ssl'      => $server->outgoing_smtp_enc,
					);
					break;
				default:
					break;
			}

			$options = new SmtpOptions( $smtp_args );
			$transport->setOptions( $options );

			$message = new Message();
			$message->addFrom( $fromemail, $fromname );

			$message->addCustomeHeader( 'X-Helpdesk', $mailtype );

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

					$attachment->type        = Rt_HD_Utils::get_mime_type( $attach );
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
			if ( $charset == 'iso-8859-1' ) {
				$txtBody = utf8_decode( $txtBody ); //convert to utf8
			}

			return $txtBody;
		}

		/**
		 * get import thread request
		 *
		 * @param $email
		 *
		 * @return mixed
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function get_import_thread_request( $email ) {
			global $rt_hd_mail_thread_importer_model;
			$where = array( 'email' => $email, 'status' => 'r', );

			return $rt_hd_mail_thread_importer_model->get_thread( $where );
		}

		/**
		 * update thread import status
		 *
		 * @param $id
		 *
		 * @return bool
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function update_thread_import_status( $id ) {
			global $rt_hd_mail_thread_importer_model;
			$rows_affected = $rt_hd_mail_thread_importer_model->update_thread( array( 'status' => 'c' ), array( 'id' => $id ) );

			return ( ! empty( $rows_affected ) );
		}

		/**
		 * Read Email
		 *
		 * @param        $email
		 * @param        $accessToken
		 * @param        $email_type
		 * @param        $imap_server
		 * @param        $lastDate
		 * @param        $user_id
		 * @param bool   $isSystemEmail
		 * @param string $signature
		 * @param bool   $isThreadImporter
		 *
		 * @return bool
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function reademail( $email, $accessToken, $email_type, $imap_server, $lastDate, $user_id, $isSystemEmail = false, $signature = '', $isThreadImporter = false ) {
			set_time_limit( 0 );
			global $signature, $rt_hd_settings;
			if ( ! $this->try_imap_login( $email, $accessToken, $email_type, $imap_server ) ) {
				$rt_hd_settings->update_sync_status( $email, false );
				error_log( 'login fail' );

				return false;
			}
			$storage = new ImapStorage( $this->imap );

			$rtCampUser = Rt_HD_Utils::get_hd_rtcamp_user();
			$hdUser     = array();
			foreach ( $rtCampUser as $rUser ) {
				$hdUser[ $rUser->user_email ] = $rUser->ID;
			}

			$email_acc = $rt_hd_settings->get_email_acc( $email );
			if ( empty( $email_acc ) ) {
				$rt_hd_settings->update_sync_meta_time( $email, current_time( 'mysql' ) );
				$rt_hd_settings->update_sync_status( $email, false );
				error_log( 'email fail' );

				return false;
			}

			$email_data = maybe_unserialize( $email_acc->email_data );

			if ( empty( $email_data['inbox_folder'] ) ) {
				$rt_hd_settings->update_sync_meta_time( $email, current_time( 'mysql' ) );
				$rt_hd_settings->update_sync_status( $email, false );
				error_log( 'inbox folder fail' );

				return false;
			}

			$mail_folders = explode( ',', ( isset( $email_data['mail_folders'] ) ) ? $email_data['mail_folders'] : '' );
			$inbox_folder = $email_data['inbox_folder'];
			array_unshift( $mail_folders, $inbox_folder );
			if ( $isThreadImporter ) {

				if ( $isSystemEmail ) {
					$mail_folders = array( $inbox_folder );
				}
				foreach ( $mail_folders as $folder ) {
					$storage->selectFolder( $folder );
					$result = $this->get_import_thread_request( $email );
					if ( ! $result ) {
						return;
					}
					if ( empty( $result ) ) {
						return;
					}
					foreach ( $result as $rs ) {
						$threadId    = $rs->threadid;
						$decThreadId = $this->bchexdec( $threadId );
						$allMail     = $storage->protocol->requestAndResponse( 'UID SEARCH X-GM-THRID', array( $storage->protocol->escapeString( $decThreadId ) ) );

						$allMailArray = array();
						foreach ( $allMail as $ids ) {
							if ( $ids[0] == 'SEARCH' ) {
								array_shift( $ids );
								$allMailArray = $ids;
							}
						}
						if ( ! empty( $allMailArray ) ) {
							global $threadPostId;
							$threadPostId = $rs->post_id;
							$this->rt_parse_email( $email, $storage, $allMailArray, $hdUser, $user_id, $isSystemEmail );
							global $rt_hd_import_operation;

							$title = rthd_create_new_ticket_title( 'rthd_new_followup_email_title', $threadId );
							$body  = 'New ' . count( $allMailArray ) . ' Follwup Imported From Gmail threads';
							$body .= '<br/><b>Email Ac : </b>' . $email;
							$body .= '<br/><b>Thread ID: </b>' . $threadId;
							$body .= '<br/> ';
							$rt_hd_import_operation->notify_subscriber_via_email( $threadPostId, $title, $body, 0 );

							$this->update_thread_import_status( $rs->id );
						}
					}
				}
			} else {
				global $sync_inbox_type;
				global $rt_mail_uid;
				if ( $isSystemEmail ) {
					$mail_folders = array( $inbox_folder );
				}
				foreach ( $mail_folders as $folder ) {
					$storage->selectFolder( $folder );
					error_log( sanitize_email( $email ) . " : Reading - " . esc_attr( $folder ) . "\r\n" );
					$sync_inbox_type = $folder;
					if ( ! isset( $rt_mail_uid[ $sync_inbox_type ] ) ) {
						$rt_mail_uid[ $sync_inbox_type ] = 0;
					}

					global $rt_mail_uid;
					if ( $rt_mail_uid[ $sync_inbox_type ] > 0 ) {
						$allMail = $storage->protocol->requestAndResponse( "UID FETCH {$rt_mail_uid[$sync_inbox_type]}:* (UID)", array() );
						foreach ( $allMail as $tempEmail ) {
							$arrayMailIds[] = array( 'uid' => $tempEmail[2][1], 'msgid' => $tempEmail[0] );
						}
					} else {
						$arrayMailIds = $storage->protocol->search( array( 'SINCE ' . $lastDate ) );
					}
					error_log( sanitize_email( $email ) . " : Found " . esc_attr( count( $arrayMailIds ) ) . " Mails \r\n" );
					$this->rt_parse_email( $email, $storage, $arrayMailIds, $hdUser, $user_id, $isSystemEmail );
				}
				$rt_hd_settings->update_sync_meta_time( $email, current_time( 'mysql' ) );
				$rt_hd_settings->update_sync_status( $email, false );
			}
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
			global $rt_hd_mail_message_model;

			$result = $rt_hd_mail_message_model->get_message( array( 'messageid' => $messageid ) );
			if ( empty( $result ) ) {
				return $rt_hd_mail_message_model->add_message( array( 'messageid' => $messageid ) );
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
			global $threadPostId;
			if ( $threadPostId ) {
				return true;
			}
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
		 * @param $email
		 * @param $storage
		 * @param $arrayMailIds
		 * @param $hdUser
		 * @param $user_id
		 * @param $isSystemEmail
		 *
		 * @since rt-Helpdesk 0.1
		 */
		public function rt_parse_email( $email, &$storage, &$arrayMailIds, &$hdUser, $user_id, $isSystemEmail ) {
			$lastMessageId = '-1';
			global $rt_hd_import_operation;
			$lastFlags = false;
			$lastFlag  = array();
			$message   = null;

			$systemEmails = rt_get_all_system_emails();
			global $threadPostId;

			foreach ( $arrayMailIds as $UmailId ) {
				try {
					if ( is_array( $UmailId ) ) {
						$tempUIDArray = $UmailId;
						$UmailId      = $tempUIDArray['uid'];
					}
					$mailId    = $this->get_number_by_unique_id( $UmailId, $storage );
					if ( empty( $mailId ) ) {
						continue;
					}
					$message   = $storage->getMessage( $mailId ); //1474);
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
					if ( $lastMessageId && $rt_hd_import_operation->check_duplicate_from_message_id( $lastMessageId ) ) {
						$dt = new DateTime( $message->date );
						$this->update_last_mail_uid( $email, $UmailId );
						continue;
					}

					if ( $lastMessageId && ! isset( $threadPostId ) ) {
						if ( ! $this->insert_mail_message_id( $lastMessageId ) ) {
							$this->update_last_mail_uid( $email, $UmailId );
							continue;
						}
					}
					if ( ! isset( $message->subject ) ) {
						$message->subject = ' ';
					}
					error_log( sanitize_email( $email ) . " Parsing Mail " . esc_attr( $message->subject ) . "\r\n" );
					$subscriber = array();
					$from       = array();
					$allEmails  = array();
					global $rthd_all_emails;
					$rthd_all_emails = array();
					if ( isset( $message->from ) ) { // or $message->headerExists('cc');
						$arrFrom = $message->getHeader( 'from' )->getAddressList();
						foreach ( $arrFrom as $tFrom ) {
							$from['address']   = $tFrom->getEmail();
							$from['name']      = $tFrom->getName();
							$rthd_all_emails[] = array(
								'address' => $tFrom->getEmail(),
								'name'    => $tFrom->getName(),
								'key'     => 'from',
							);
							if ( ! array_key_exists( $tFrom->getEmail(), $hdUser ) ) {
								if ( ! in_array( $tFrom->getEmail(), $systemEmails ) ) {
									$allEmails[] = array(
										'address' => $tFrom->getEmail(),
										'name'    => $tFrom->getName()
									);
								}
							} else {
								$subscriber[] = $hdUser[ $tFrom->getEmail() ];
							}
						}
					}
					if ( isset( $message->to ) ) { // or $message->headerExists('cc');
						$arrTo = $message->getHeader( 'to' )->getAddressList();
						foreach ( $arrTo as $tTo ) {
							if ( ! is_email( $tTo->getEmail() ) ) {
								continue;
							}
							$rthd_all_emails[] = array(
								'address' => $tTo->getEmail(),
								'name'    => $tTo->getName(),
								'key'     => 'to',
							);
							if ( ! array_key_exists( $tTo->getEmail(), $hdUser ) ) {
								if ( ! in_array( $tTo->getEmail(), $systemEmails ) ) {
									$allEmails[] = array( 'address' => $tTo->getEmail(), 'name' => $tTo->getName() );
								}
							} else {
								$subscriber[] = $hdUser[ $tTo->getEmail() ];
							}
						}
					}
					if ( isset( $message->cc ) ) { // or $message->headerExists('cc');
						$arrCC = $message->getHeader( 'cc' )->getAddressList();
						foreach ( $arrCC as $tCc ) {
							if ( ! is_email( $tCc->getEmail() ) ) {
								continue;
							}
							$rthd_all_emails[] = array(
								'address' => $tCc->getEmail(),
								'name'    => $tCc->getName(),
								'key'     => 'cc',
							);
							if ( ! array_key_exists( $tCc->getEmail(), $hdUser ) ) {
								if ( ! in_array( $tCc->getEmail(), $systemEmails ) ) {
									$allEmails[] = array( 'address' => $tCc->getEmail(), 'name' => $tCc->getName() );
								}
							} else {
								$subscriber[] = $hdUser[ $tCc->getEmail() ];
							}
						}
					}
					if ( isset( $message->bcc ) ) { // or $message->headerExists('cc');
						$arrBCC = $message->getHeader( 'bcc' )->getAddressList();
						foreach ( $arrBCC as $tBCc ) {
							if ( ! is_email( $tBCc->getEmail() ) ) {
								continue;
							}
							$rthd_all_emails[] = array(
								'address' => $tBCc->getEmail(),
								'name'    => $tBCc->getName(),
								'key'     => 'bcc',
							);
							if ( ! array_key_exists( $tBCc->getEmail(), $hdUser ) ) {
								if ( ! in_array( $tBCc->getEmail(), $systemEmails ) ) {
									$allEmails[] = array( 'address' => $tBCc->getEmail(), 'name' => $tBCc->getName() );
								}
							} else {
								$subscriber[] = $hdUser[ $tBCc->getEmail() ];
							}
						}
					}
					$htmlBody     = '';
					$txtBody      = '';
					$attachements = array();
					if ( $message->isMultiPart() ) {
						foreach ( $message as $part ) {
							$ContentType = strtok( $part->contentType, ';' );
							if ( ! ( strpos( $ContentType, 'multipart/alternative' ) === false ) ) {
								$totParts = $part->countParts();
								for ( $rCount = 1; $rCount <= $totParts; $rCount ++ ) {
									$tPart        = $part->getPart( $rCount );
									$tContentType = strtok( $tPart->contentType, ';' );
									if ( $tContentType == 'text/plain' ) {
										$txtBody = $this->get_decoded_message( $tPart );
									} else {
										if ( $tContentType == 'text/html' ) {
											$htmlBody = $this->get_decoded_message( $tPart );
										}
									}
								}
							} else if ( $ContentType == 'text/plain' ) {
								$txtBody = $this->get_decoded_message( $part );
							} else {
								if ( $ContentType == 'text/html' ) {
									$htmlBody = $this->get_decoded_message( $part );
								} else {
									try {
										$filename = $part->getHeader( 'content-disposition' )->getFieldValue( 'filename' );
										if ( preg_match( '*filename=\"([^;]+)\"*', $filename, $matches ) ) {
											if ( isset( $matches[1] ) ) {
												$filename = trim( $matches[1] );
											} else {
												$filename = time() . '.' . Rt_HD_Utils::get_extention( $ContentType );
											}
										} else {
											$filename = time() . '.' . Rt_HD_Utils::get_extention( $ContentType );
										}
									} catch ( Exception $e ) {
										$e->getTrace();
										$filename = time() . '.' . Rt_HD_Utils::get_extention( $ContentType );
									}

									if ( trim( $filename ) == '' ) {
										$filename = time() . '.' . Rt_HD_Utils::get_extention( $ContentType );
									}
									$filedata   = $this->get_decoded_message( $part );
									$upload_dir = wp_upload_dir( null );
									$filename   = sanitize_file_name( $filename );
									if ( ! file_exists( $upload_dir ['path'] . "/$filename" ) ) {
										$uploaded = wp_upload_bits( $filename, null, $filedata );
									} else {
										$uploaded['error'] = false;
										$uploaded['file']  = $upload_dir ['path'] . "/$filename";
										$uploaded['url']   = $upload_dir ['url'] . "/$filename";
									}
									if ( $uploaded['error'] == false ) {
										Rt_HD_Utils::log( "[Attachement Created] File:{$uploaded['file']} ; URL: {$uploaded['url']}", 'mail-attachement.txt' );
										$file                  = array();
										$extn_array            = explode( '.', $filename );
										$extn                  = $extn_array[ count( $extn_array ) - 1 ];
										$file['file']          = $uploaded['file'];
										$file['url']           = $uploaded['url'];
										$file['filename']      = $filename;
										$file['extn']          = $extn;
										$file['type']          = $ContentType;
										if ( $part->hasHeader( 'xattachmentid' ) ) {
											$file['xattachmentid'] = $part->getHeader('xattachmentid')->getFieldValue();
										}
										$attachements[]        = $file;
									} else {
										error_log( 'Attachment Failed ... ' . esc_attr( $filename ) . '\r\n' );
										ob_start();
										error_log( var_export( $uploaded, true ) );
										$data = ob_get_clean();
										Rt_HD_Utils::log( "[Attachement Failed] Email: {$email};Message-Id: {$message->messageid}; Data : $data ", 'error-mail-attachement.txt' );
									}
								}
							}
						}
					} else {
						if ( isset( $message->contentType ) ) {
							if ( $message->contentType == 'text/plain' ) {
								$txtBody  = $this->get_decoded_message( $message );
								$htmlBody = $txtBody;
							} else if ( $message->contentType == 'text/html' ) {
								$htmlBody = $this->get_decoded_message( $message );
								$txtBody  = strip_tags( $htmlBody );
							} else {
								$htmlBody = $message->getContent();
								$txtBody  = strip_tags( $htmlBody );
							}
						} else {
							$htmlBody = nl2br( $message->getContent() );
							$txtBody  = strip_tags( $htmlBody );
						}
					}
					if ( $lastFlags !== false ) {
						$lastFlag = true;
						foreach ( $lastFlags as $fl ) {
							if ( $fl == Zend\Mail\Storage::FLAG_SEEN ) {
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

					foreach( $attachements as $a ) {
						if ( ! empty( $a['xattachmentid'] ) ) {
							$htmlBody = str_replace( 'src="cid:' . $a['xattachmentid'] . '"', 'src="' . $a['url'] . '"', $htmlBody );
						}
					}

					$subject      = $message->subject;
					$htmlBody     = Rt_HD_Utils::force_utf_8( $htmlBody );
					$subject      = Rt_HD_Utils::force_utf_8( $subject );
					$txtBody      = Rt_HD_Utils::force_utf_8( $txtBody );
					$success_flag = $rt_hd_import_operation->process_email_to_ticket( $subject, $htmlBody, $from, $message->date, $allEmails, $attachements, $txtBody, true, $user_id, $messageid, $inreplyto, $references, $isSystemEmail, $subscriber );

					error_log( "Mail Parse Status : " . var_export( $success_flag, true ) . "\n\r" );

					if ( ! $success_flag ) {
						foreach ( $attachements as $attachement ) {
							unlink( $attachement['file'] );
						}
					}

					global $threadPostId;
					if ( ! isset( $threadPostId ) ) {
						$this->update_last_mail_uid( $email, $UmailId );
						try {
							$dt = new DateTime( $message->date );
							$this->update_sync_meta( $email, $dt->format( 'Y-m-d H:i:s' ) );
						} catch ( Exception $e ) {
							$this->update_sync_meta( $email, $dt->format( 'Y-m-d H:i:s' ) );
						}
					}
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
					Rt_HD_Utils::log( "[Mail Sync Failed]Subject:{$message->subject}; Email: {$email}; MailNo: {$mailId};Message-Id: {$lastMessageId} ", 'error-mail-sync.txt' );
					Rt_HD_Utils::log( "[Mail Sync Failed]Subject:{$message->subject}; Email: {$email}; MailNo: {$mailId};Message-Id: {$lastMessageId} ", $email . 'error-mail-sync.txt' );
					wp_mail( 'udit.desai@rtcamp.com', 'Error in Mail Sync ' . $email . ' ' . $message->subject, $data . '<br/><hr>' . $e->getMessage() . '<hr>' . $e->getTraceAsString() );
				}
			}
		}

	}

}
