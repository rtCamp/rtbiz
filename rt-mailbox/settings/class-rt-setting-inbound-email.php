<?php
/**
 * Created by PhpStorm.
 * User: spock
 * Date: 12/9/14
 * Time: 5:10 PM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Zend\Mail\Storage\Imap as ImapStorage;

if ( ! class_exists( 'RT_Setting_Inbound_Email' ) ) {

	/**
	 * Class RT_Setting_Inbound_Email
	 */
	class RT_Setting_Inbound_Email {

		/**
		 * @var string
		 */
		var $user_id = '';
		/**
		 * @var null
		 */
		var $oauth2 = null;
		/**
		 * @var null
		 */
		var $client = null;

		function __construct() {
			add_action( 'init', array( $this, 'save_replay_by_email' ) );
		}


		/**
		 * @param $field
		 * @param $value
		 */
		public function rt_reply_by_email_view( $field, $value, $modules = array(), $newflag = true ) {
			global $rt_mail_settings, $rt_imap_server_model;
			$responce = false;
			$google_acs = $rt_mail_settings->get_user_google_ac();

			$imap_servers = $rt_imap_server_model->get_all_servers();
			if ( false == $responce &&( ! empty( $google_acs ) ) ) {
				foreach ( $google_acs as $acs ){
					if ( 'goauth' == $acs->type  ) {
						$rt_mail_settings->delete_user_google_ac( $acs->email );
					}
				}
				$google_acs = $rt_mail_settings->get_user_google_ac();
			}

			if ( false == $responce && ( empty( $google_acs ) && empty( $imap_servers )  ) ){
				echo '<div id="error_handle" class=""><p>'.__( 'Please set Imap Servers detail on ' ).'<a href="' . esc_url( admin_url( 'admin.php?page='.Rt_Mailbox::$page_name.'&tab=imap' ) ) . '">IMAP </a>  Page </p></div>';
				return;
			}

			$authUrl = '';
			if ( false != $responce ){
				$authUrl    = $this->client->createAuthUrl();
			}

			//			$results           = Rt_HD_Utils::get_hd_rtcamp_user();
			//			$arrSubscriberUser = array();
			//			foreach ( $results as $author ) {
			//				$arrSubscriberUser[] = array(
			//					'id'      => $author->ID,
			//					'label'   => $author->display_name,
			//					'imghtml' => get_avatar( $author->user_email, 25 ),
			//				);
			//			}

			//			echo '<script> var arr_rtcamper=' . json_encode( $arrSubscriberUser ) . '; </script>';
			$rCount = 0;

			if ( isset( $google_acs ) && ! empty( $google_acs ) ) {
				foreach ( $google_acs as $ac ) {
					$rCount ++;
					$ac->email_data = unserialize( $ac->email_data );
					$email          = filter_var( $ac->email_data['email'], FILTER_SANITIZE_EMAIL );
					$email_type     = $ac->type;
					$imap_server    = $ac->imap_server;
					$mail_folders   = ( isset( $ac->email_data['mail_folders'] ) ) ? $ac->email_data['mail_folders'] : '';
					$mail_folders   = array_filter( explode( ',', $mail_folders ) );
					$inbox_folder   = ( isset( $ac->email_data['inbox_folder'] ) ) ? $ac->email_data['inbox_folder'] : '';

					if ( 'goauth' == $ac->type ) {
						$token = json_decode( $ac->outh_token );
						$this->client->setAccessToken( $ac->outh_token );
						if ( $this->client->isAccessTokenExpired() ) {
							$this->client->refreshToken( $token->refresh_token );
							$user  = $this->oauth2->userinfo_v2_me->get();
							$email = filter_var( $user['email'], FILTER_SANITIZE_EMAIL );
							if ( isset( $ac->email_data['inbox_folder'] ) ) {
								$user['inbox_folder'] = $ac->email_data['inbox_folder'];
							}
							if ( isset( $ac->email_data['mail_folders'] ) ) {
								$user['mail_folders'] = $ac->email_data['mail_folders'];
							}
							$rt_mail_settings->update_user_google_ac( $this->client->getAccessToken(), $email, serialize( $user ) );
							$ac->email_data = $user;
							$token          = json_decode( $this->client->getAccessToken() );
						}
						$token = $token->access_token;
					} else {
						$token = $ac->outh_token;
					}
					if ( isset( $ac->email_data['picture'] ) ) {
						$img          = filter_var( $ac->email_data['picture'], FILTER_VALIDATE_URL );
						$personMarkup = "<img src='$img?sz=96'>";
					} else {
						$personMarkup = get_avatar( $email, 96 );
					} ?>
					<table class="form-table hd-option">
						<tbody>
						<tr>
							<td>
								<input type="hidden" name='mail_ac' value="<?php echo esc_attr( $email ); ?>"/>
								<table class='hd-google-profile-table'>
					<?php
					$all_folders = null;
					$login_successful = true;
					try {
						$hdZendEmail = new Rt_Zend_Mail();
						if ( $hdZendEmail->try_imap_login( $email, $token, $email_type, $imap_server ) ) {
							$storage     = new ImapStorage( $hdZendEmail->imap );
							$all_folders = $storage->getFolders();
						} else {
							$login_successful = false;
						}
					} catch ( Exception $e ) {
						echo '<tr valign="top"><td></td><td></td><td><p class="description">' . esc_html( $e->getMessage() ) . '</p></td></tr>';
					}
					?>
									<tr valign="top">
										<td>
					<!--											<label>--><?php //echo balanceTags( $personMarkup ); ?><!--</label>-->
											<strong><?php if ( isset( $ac->email_data['name'] ) ) { echo $ac->email_data['name']; } ?> <br/><a href='mailto:<?php echo $email ?>'><?php echo $email ?></a></strong>
											<input type="hidden" name="rthd_submit_enable_reply_by_email" value="save"/>
											<a
												class='button remove-google-ac right'
												href='<?php echo esc_url( admin_url( 'admin.php?page='.Rt_Mailbox::$page_name.'&rthd_submit_enable_reply_by_email=save&email=' . $email ) ); ?>'><?php echo __( 'Remove' ); ?>
												A/C</a>
											<a class="button right rtMailbox-hide-mail-folders mailbox_show_hide" href="#"><?php echo __( 'Show' ); ?></a>
					<!--											--><?php //if ( 'goauth' == $ac->type ) { ?>
					<!--												<a class='button button-primary right'-->
					<!--												   href='--><?php //echo esc_url( $authUrl ); ?><!--'>ReConnect Google Now</a>-->
					<!--											--><?php //} ?>
										</td>
									</tr>
									<tr valign="top" class="rthd-hide-row">
										<?php if ( $login_successful ) { ?>
										<td class="long">
											<?php if ( ! empty( $ac->module ) ){
												?>
												<b> <?php echo __( 'Mailbox Registered with ' ); ?><?php echo $ac->module ?></b> <br/>
											<?php } ?>
											<br/><label><strong><?php _e( 'Mail Folders to read' ); ?></strong></label><br/>
											<label>
												<?php _e( 'Inbox Folder' ); ?>
												<select data-email-id="<?php echo esc_attr( $ac->id ); ?>" name="inbox_folder" data-prev-value="<?php echo esc_attr( $inbox_folder ); ?>">
													<option value=""><?php _e( 'Choose Inbox Folder' ); ?></option>
													<?php if ( ! is_null( $all_folders ) ) { ?>
													<?php $hdZendEmail->render_folders_dropdown( $all_folders, $value = $inbox_folder ); ?>
													<?php } ?>
												</select>
											</label>
											<p class="description"><?php _e( 'Choosing an Inbox Folder is mandatory in order to parse the emails from Mailbox.' ) ?></p>
											<?php if ( in_array( $email, rt_get_all_system_emails() ) ) { ?>
												<p class="description"><?php _e( 'This is linked as a system mail. Hence it will only read the Inbox Folder; no matter what folder you choose over here. These will be ignored.' ); ?></p>
											<?php } ?>
											<?php if ( ! is_null( $all_folders ) ) { ?>
												<div id="mail_folder_container">
													<?php $hdZendEmail->render_folders_checkbox( $all_folders, $element_name = 'mail_folders', $values = $mail_folders, $data_str = 'data-email-id=' . $ac->id, $inbox_folder ); ?>
												</div>
											<?php } else { ?>
												<p class="description"><?php _e( 'No Folders found.' ); ?></p>
											<?php } ?>
										</td>
										<?php } else {
											echo '<td class="long"><strong>'.__( ' Please remove account and enter correct credential or enable IMAP in your mailbox.' ). '</strong></td>'; }?>
									</tr>
								</table>
							</td>
						</tr>
						</tbody>
					</table>
					<script>
						jQuery(document).ready(function ($) {
							$(document).on('change', 'select[name=inbox_folder]', function (e) {
								e.preventDefault();
								inbox = $(this).val();
								prev_value = $(this).data('prev-value');
								$(this).data('prev-value', inbox);
								var email_id = $(this).data('email-id');
								$('input[data-email-id="' + email_id + '"][value="' + inbox + '"]').attr('disabled', 'disabled');
								$('input[data-email-id="' + email_id + '"][value="' + inbox + '"]').attr('checked', false);
								$('input[data-email-id="' + email_id + '"][value="' + inbox + '"]').prop('checked', false);
								$('input[data-email-id="' + email_id + '"][value="' + prev_value + '"]').removeAttr('disabled');
							});
						});
					</script>
				<?php
				}
			}

			if ( $newflag ) { ?>
				<p class="submit"><a class="button" id="rthd_add_personal_email" href="#"><?php _e( 'Add Email' ); ?></a></p>
				<p class="submit rthd-hide-row" id="rthd_email_acc_type_container">
				<?php if ( ! empty( $modules ) ) { ?>
					<label for="module_to_register">Select module to register: </label>
					<select id="module_to_register" name="module_to_register" required>
					<?php foreach ( Rt_Access_Control::$modules as $key => $value ) { ?>
						<option value="<?php echo $key; ?>"><?php echo $key; ?></option>
					<?php } ?>
					</select>
					<br />
				<?php } ?>

					<select id="rthd_select_email_acc_type">
						<option value=""><?php _e( 'Select Type' ); ?></option>
					<?php if ( false != $responce ){ ?>
						<option value="goauth"><?php _e( 'Google OAuth App' ); ?></option>
					<?php } if ( ! empty( $imap_servers ) ){?>
						<option value="imap"><?php _e( 'IMAP' ); ?></option>
					<?php } ?>
					</select>
				</p>
				<?php if ( false != $responce ) { ?>
				<p class="submit rthd-hide-row" id="rthd_goauth_container">
					<a class='button button-primary' href='<?php echo esc_url( $authUrl ); ?>'><?php _e( 'Connect New Google A/C' ); ?></a>
				</p>
				<?php } ?>
				<?php if ( $imap_servers ) { ?>
				<p class="submit rthd-hide-row" id="rthd_add_imap_acc_form" autocomplete="off">
					<input type="hidden" name="rthd_add_imap_email" value="1"/>
					<select  name="rthd_imap_server">
						<option value=""><?php _e( 'Select Mail Server' ); ?></option>
						<?php foreach ( $imap_servers as $server ) { ?>
						<option value="<?php echo esc_attr( $server->id ); ?>"><?php echo esc_html( $server->server_name ); ?></option>
						<?php } ?>
					</select>
				</p>
				<?php }
			}
		}

		public function save_replay_by_email() {
			global $rt_mail_settings;
			$module = '';
			if ( isset( $_POST['module_to_register'] ) && ! empty( $_POST['module_to_register'] ) ){
				$module = $_POST['module_to_register'];
			}
			if ( ( isset( $_REQUEST['rthd_submit_enable_reply_by_email'] ) && 'save' == $_REQUEST['rthd_submit_enable_reply_by_email'] ) || ( isset( $_REQUEST['rthd_add_imap_email'] ) && $_REQUEST['rthd_add_imap_email'] ) ) {
				if ( isset( $_POST['mail_ac'] ) && is_email( $_POST['mail_ac'] ) ) {
					if ( isset( $_POST['imap_password'] ) ) {
						$token = rt_encrypt_decrypt( $_POST['imap_password'] );
					} else {
						$token = null;
					}
					if ( isset( $_POST['imap_server'] ) ) {
						$imap_server = $_POST['imap_server'];
					} else {
						$imap_server = null;
					}
					$email_ac   = $rt_mail_settings->get_email_acc( $_POST['mail_ac'] );
					$email_data = null;
					if ( isset( $_POST['mail_folders'] ) && ! empty( $_POST['mail_folders'] ) && is_array( $_POST['mail_folders'] ) && ! empty( $email_ac ) ) {
						$email_data                 = maybe_unserialize( $email_ac->email_data );
						$email_data['mail_folders'] = implode( ',', $_POST['mail_folders'] );
					}
					if ( isset( $_POST['inbox_folder'] ) && ! empty( $_POST['inbox_folder'] ) && ! empty( $email_ac ) ) {
						if ( is_null( $email_data ) ) {
							$email_data = maybe_unserialize( $email_ac->email_data );
						}
						$email_data['inbox_folder'] = $_POST['inbox_folder'];
					}
					$rt_mail_settings->update_mail_acl( $_POST['mail_ac'], $token, maybe_serialize( $email_data ), $imap_server );
				}
				if ( isset( $_REQUEST['email'] ) && is_email( $_REQUEST['email'] ) ) {
					$rt_mail_settings->delete_user_google_ac( $_REQUEST['email'] );
					echo '<script>';
					//					window.location="' . esc_url_raw( add_query_arg(
					//							array(
					//								'post_type' => Rt_HD_Module::$post_type,
					//								'page'      => 'rthd-settings',
					//							), admin_url( 'admin.php' ) ) ) . '";</script>';
						echo 'window.location="'. admin_url( 'admin.php' ) .'?page='.Rt_Mailbox::$page_name.'"; </script>';
					die();
				}
				if ( isset( $_REQUEST['rthd_add_imap_email'] ) ) {
					if ( isset( $_POST['rthd_imap_user_email'] ) && ! empty( $_POST['rthd_imap_user_email'] ) && isset( $_POST['rthd_imap_user_pwd'] ) && ! empty( $_POST['rthd_imap_user_pwd'] ) && isset( $_POST['rthd_imap_server'] ) && ! empty( $_POST['rthd_imap_server'] ) ) {
						$password    = $_POST['rthd_imap_user_pwd'];
						$email       = $_POST['rthd_imap_user_email'];
						$email_data  = array(
							'email' => $email,
						);
						$imap_server = $_POST['rthd_imap_server'];
						$rt_mail_settings->add_user_google_ac( rt_encrypt_decrypt( $password ), $email, maybe_serialize( $email_data ), $this->user_id, 'imap', $imap_server, $module );
					}
				}
			}
		}

	}

}
