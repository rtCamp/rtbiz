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
		 * @param       $field
		 * @param       $value
		 * @param array $modules
		 * @param bool  $newflag
		 */
		public function rt_reply_by_email_view( $field, $value, $modules = array(), $newflag = true ) {
			global $rt_mail_settings, $rt_imap_server_model;

			$imap_servers = $rt_imap_server_model->get_all_servers();

			if ( empty( $imap_servers ) ){
				echo '<div id="error_handle" class=""><p>'.__( 'Please set Imap Servers detail on ' ).'<a href="' . esc_url( admin_url( 'admin.php?page='.Rt_Mailbox::$page_name.'&tab=imap' ) ) . '">IMAP </a>  Page </p></div>';
				return;
			} else {
				if ( $newflag ) { ?>

					<form id="frm-replay-by-email" method="post" action="">
						<div class="form-body">
							<label><?php echo __( 'Enable Reply by Email: ' ); ?></label>
							<?php $val = Rt_Mailbox::get_enable_by_reply_email(); ?>
							<input type="radio" name="mailbox_reply_by_email" value="yes" <?php echo 'yes' == $val ? 'checked' :  '' ; ?> ><?php echo __( 'Enable' ); ?>
							<input type="radio" name="mailbox_reply_by_email" value="no" <?php echo 'yes' != $val ? 'checked' :  '' ;; ?>><?php echo __( 'Disable' );?>
						</div>
						<input class="button button-primary" type="submit" value="Save">
					</form>

					<form id="frm-new-mail" method="post" action="">
						<legend><a class="button" id="rtmailbox_add_personal_email" href="#"><?php _e( 'Add Email' ); ?></a></legend>
						<div class="rtmailbox-hide-row" id="rtmailbox_email_acc_type_container">
							<table class="">
								<tr>
									<th><label for="rtmailbox_select_email_acc_type">Select Server Connection Type </label></th>
									<td>
										<select id="rtmailbox_select_email_acc_type">
											<option value=""><?php _e( 'Select Type' ); ?></option>
											<?php if ( ! empty( $imap_servers ) ){?>
												<option value="imap"><?php _e( 'IMAP' ); ?></option>
											<?php } ?>
										</select>
									</td>
								</tr>
							<?php if ( ! empty( $modules ) ) { ?>
								<tr>
									<th><label for="module_to_register">Select Module To Register </label></th>
									<td>
										<select id="module_to_register" name="module_to_register" required>
											<?php foreach ( Rt_Access_Control::$modules as $key => $value ) { ?>
												<option value="<?php echo $key; ?>"><?php echo $key; ?></option>
											<?php } ?>
										</select>
									</td>
								</tr>
							<?php } ?>
							</table>
						<?php if ( $imap_servers ) { ?>
							<div class="rtmailbox-hide-row" id="rtmailbox_add_imap_acc_form" autocomplete="off">
								<input type="hidden" name="rtmailbox_add_imap_email" value="1"/>
								<select  name="rtmailbox_imap_server">
									<option value=""><?php _e( 'Select Mail Server' ); ?></option>
									<?php foreach ( $imap_servers as $server ) { ?>
										<option value="<?php echo esc_attr( $server->id ); ?>"><?php echo esc_html( $server->server_name ); ?></option>
									<?php } ?>
								</select>
								<div id="rtmailbox_add_imap_acc_fields">
								</div>
								<input class="button button-primary" type="submit" value="Save">
							</div>
						<?php } ?>
						</div>
					</form>
					<?php
				}
			}
			?>
			<form id="frm-new-mail" method="post" action="">
				<div class="mail_list" >
					<h2 class="title">Mail List</h2><?php
					$rCount = 0;
					$google_acs = $rt_mail_settings->get_user_google_ac();
					if ( isset( $google_acs ) && ! empty( $google_acs ) ){
						foreach ( $google_acs as $ac ){
							$rCount ++;
							$ac->email_data = unserialize( $ac->email_data );
							$email          = filter_var( $ac->email_data['email'], FILTER_SANITIZE_EMAIL );
							$email_type     = $ac->type;
							$imap_server    = $ac->imap_server;
							$mail_folders   = ( isset( $ac->email_data['mail_folders'] ) ) ? $ac->email_data['mail_folders'] : '';
							$mail_folders   = array_filter( explode( ',', $mail_folders ) );
							$inbox_folder   = ( isset( $ac->email_data['inbox_folder'] ) ) ? $ac->email_data['inbox_folder'] : '';
							$token = $ac->outh_token;

							if ( isset( $ac->email_data['picture'] ) ){
								$img          = filter_var( $ac->email_data['picture'], FILTER_VALIDATE_URL );
								$personMarkup = "<img src='$img?sz=96'>";
							} else {
								$personMarkup = get_avatar( $email, 96 );
							}

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
									echo '<p class="description">' . esc_html( $e->getMessage() ) . '</p>';
							} ?>
							<div>
								<div>
									<input type="hidden" name='mail_ac[]' value="<?php echo esc_attr( $email ); ?>"/>
									<strong><?php if ( isset( $ac->email_data['name'] ) ) { echo $ac->email_data['name']; } ?> <br/><a href='mailto:<?php echo $email ?>'><?php echo $email ?></a></strong>
									<input type="hidden" name="rtmailbox_submit_enable_reply_by_email" value="save"/>
								</div>
								<div>
									<a class='button remove-google-ac right' href='<?php echo esc_url( admin_url( 'admin.php?page='.Rt_Mailbox::$page_name.'&rtmailbox_submit_enable_reply_by_email=save&email=' . $email ) ); ?>'><?php echo __( 'Remove A/C' ); ?></a>
									<?php if ( $login_successful ) { ?>
									<a class="button right rtMailbox-hide-mail-folders mailbox_show_hide" href="#"><?php echo __( 'Show' ); ?></a>
									<?php } ?>
								</div>
								<?php if ( $login_successful ) { ?>
								<table class="rtmailbox-hide-row">
									<tr valign="top" >
										<td class="long">
											<?php if ( ! empty( $ac->module ) ){
												?>
												<b> <?php echo __( 'Mailbox Registered with ' ); ?><?php echo $ac->module ?></b> <br/>
											<?php } ?>
											<br/><label><strong><?php _e( 'Mail Folders to read' ); ?></strong></label><br/>
											<label>
												<?php _e( 'Inbox Folder' ); ?>
												<select data-email-id="<?php echo esc_attr( $ac->id ); ?>" name="inbox_folder[<?php echo esc_attr( $email ); ?>]" data-prev-value="<?php echo esc_attr( $inbox_folder ); ?>">
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
													<?php $hdZendEmail->render_folders_checkbox( $all_folders, $element_name = 'mail_folders[' . esc_attr( $email ) . ']', $values = $mail_folders, $data_str = 'data-email-id=' . $ac->id, $inbox_folder ); ?>
												</div>
											<?php } else { ?>
												<p class="description"><?php _e( 'No Folders found.' ); ?></p>
											<?php } ?>
										</td>
									</tr>
								</table>
								<?php } else {
									echo '<td class="long"><strong>'.__( ' Please remove account and enter correct credential or enable IMAP in your mailbox.' ). '</strong></td>'; }?>
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
							</div>
						<?php
						}
					} ?>
				</div>
				<input class="button button-primary" type="submit" value="Save">
			</form>
			<?php
		}

		public function save_replay_by_email() {
			global $rt_mail_settings;
			$module = '';
			if ( isset( $_POST['module_to_register'] ) && ! empty( $_POST['module_to_register'] ) ){
				$module = $_POST['module_to_register'];
			}
			if ( ( isset( $_REQUEST['rtmailbox_submit_enable_reply_by_email'] ) && 'save' == $_REQUEST['rtmailbox_submit_enable_reply_by_email'] ) || ( isset( $_REQUEST['rtmailbox_add_imap_email'] ) && $_REQUEST['rtmailbox_add_imap_email'] ) ) {
				if ( isset( $_POST['mail_ac'] ) ) {
					foreach( $_POST['mail_ac'] as $mail_ac ){
						if ( ! is_email( $mail_ac ) ){
							continue;
						}
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
						$email_ac   = $rt_mail_settings->get_email_acc( $mail_ac );
						$email_data = null;
						if ( isset( $_POST['mail_folders'] ) && ! empty( $_POST['mail_folders'] ) && is_array( $_POST['mail_folders'] ) && ! empty( $email_ac ) ) {
							$email_data                 = maybe_unserialize( $email_ac->email_data );
							$email_data['mail_folders'] = implode( ',', $_POST['mail_folders'][ $mail_ac ] );
						}
						if ( isset( $_POST['inbox_folder'] ) && ! empty( $_POST['inbox_folder'] ) && ! empty( $email_ac ) ) {
							if ( is_null( $email_data ) ) {
								$email_data = maybe_unserialize( $email_ac->email_data );
							}
							$email_data['inbox_folder'] = $_POST['inbox_folder'][ $mail_ac ];
						}
						$rt_mail_settings->update_mail_acl( $mail_ac, $token, maybe_serialize( $email_data ), $imap_server );
					}
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
				if ( isset( $_REQUEST['rtmailbox_add_imap_email'] ) ) {
					if ( isset( $_POST['rtmailbox_imap_user_email'] ) && ! empty( $_POST['rtmailbox_imap_user_email'] ) && isset( $_POST['rtmailbox_imap_user_pwd'] ) && ! empty( $_POST['rtmailbox_imap_user_pwd'] ) && isset( $_POST['rtmailbox_imap_server'] ) && ! empty( $_POST['rtmailbox_imap_server'] ) ) {
						$password    = $_POST['rtmailbox_imap_user_pwd'];
						$email       = $_POST['rtmailbox_imap_user_email'];
						$email_data  = array(
							'email' => $email,
						);
						$imap_server = $_POST['rtmailbox_imap_server'];
						$rt_mail_settings->add_user_google_ac( rt_encrypt_decrypt( $password ), $email, maybe_serialize( $email_data ), $this->user_id, 'imap', $imap_server, $module );
					}
				}
			}
		}

	}

}
