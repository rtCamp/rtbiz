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

		function __construct( ) {
			add_action( 'init', array( $this, 'save_replay_by_email' ) );
			add_action( 'wp_ajax_rtmailbox_remove_account', array( $this, 'rtmailbox_remove_account_callback' ) );
		}


		/**
		 * @param       $field
		 * @param       $value
		 * @param array $modules
		 * @param bool  $newflag
		 */
		public function rt_reply_by_email_view( $field, $value, $modules, $newflag = true ) {
			global $rt_mail_settings, $rt_imap_server_model;

			$imap_servers = $rt_imap_server_model->get_all_servers();

			$server_types = array();
			if ( ! empty( $imap_servers ) ){
				$server_types['imap'] = 'IMAP';
			}
			$server_types = apply_filters( 'rt_mailbox_server_type', $server_types );
			?>
			<div class="mail_list" >
				<?php
				$rCount = 0;
				$is_empty_mailbox_check = true;
				$google_acs = $rt_mail_settings->get_user_google_ac( array( 'module' => $modules ) );
				if ( isset( $google_acs ) && ! empty( $google_acs ) ){
					foreach ( $google_acs as $ac ){
						$rCount ++;
						$ac->email_data = unserialize( $ac->email_data );
						$email          = filter_var( $ac->email_data['email'], FILTER_SANITIZE_EMAIL );
						$email_type     = $ac->type;
						$imap_server    = $ac->imap_server;
						$mail_folders   = ( isset( $ac->email_data['mail_folders'] ) ) ? $ac->email_data['mail_folders'] : '';
						$mail_folders   = array_filter( explode( ',', $mail_folders ) );
						$token = $ac->outh_token;
						$is_empty_mailbox_check = false;
						$validation_script = '';
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
					<div id="rtmailbox-container<?php echo $rCount; ?>">
						<div>
							<input type="hidden" name='mail_ac[]' value="<?php echo esc_attr( $email ); ?>"/>
							<strong><?php if ( isset( $ac->email_data['name'] ) ) { echo $ac->email_data['name'].'<br/>'; } ?><a href='mailto:<?php echo $email ?>'><?php echo $email ?></a></strong>
							<br>
							<p class="description field-desc rtMailbox-folder-info">
								Please click on `Show` button and select at least one folder.
							</p>
							<div class="rtmailbox-maillist-action">
								<?php if ( $login_successful ) { ?>
									<a class="button rtMailbox-hide-mail-folders mailbox_show_hide" href="#"><?php echo __( 'Show' ); ?></a>
								<?php } ?>
								<a class='button remove-google-ac remove-mailbox' data-mailboxid="<?php echo $rCount; ?>" data-email="<?php echo $email; ?>" data-module="<?php echo $modules; ?>" href="javascript:;"><?php echo __( 'Remove A/C' ); ?></a>
								<img id="remove-mailbox-spinner<?php echo $rCount; ?>" class="rtmailbox-spinner" src="<?php echo admin_url() . 'images/spinner.gif'; ?>" />
							</div>
							<p id="rtMailbox-folder-info<?php echo $rCount; ?>" class="description field-desc rtMailbox-folder-error">
							</p>
						</div>
						<?php if ( $login_successful ) { ?>
							<table class="rtmailbox-hide-row">
								<tr valign="top" >
									<td class="long">
										<label><strong><?php _e( 'Mail Folders to read' ); ?></strong></label><br/>
										<?php if ( ! is_null( $all_folders ) ) { ?>
											<div id="mail_folder_container">
												<?php $hdZendEmail->render_folders_checkbox( $all_folders, $element_name = 'mail_folders[' . esc_attr( $email ) . ']', $values = $mail_folders, $data_str = 'data-email-id=' . $ac->id ); ?>
												<?php
												$validation_script .= "
															jQuery('#rtmailbox-container".$rCount."').find('input[type=\"checkbox\"]').each(function() {
																if( jQuery(this).is(':checked') ) {
																	count++;
																}
															});
															if( count <= 0 ) {
																jQuery('#rtMailbox-folder-info".$rCount."').html('Please select at least one mail folder for this account.');
		 														jQuery('#rtMailbox-folder-info".$rCount."').show();
																jQuery(window).scrollTop(jQuery('#rtmailbox-container".$rCount."').offset().top);
																event.preventDefault();
																return false;
															}
															else {
																jQuery('#rtMailbox-folder-info".$rCount."').hide();
															}
															count = 0;
												";
													?>
											</div>
										<?php } else { ?>
											<p class="description"><?php _e( 'No Folders found.' ); ?></p>
										<?php } ?>
									</td>
								</tr>
							</table>
						<?php } else {
							echo '<p class="long"><strong>'.__( ' Please remove account and enter correct credential or enable IMAP in your mailbox.' ). '</strong></p>'; }?>
					</div>
						<hr class="rt-mailbox-hr">
				<?php } ?>
				<?php if ( $validation_script != '' ) { ?>
					<script type="text/javascript">
						jQuery(document).ready(function() {

							jQuery('.rtmailbox_submit_enable_reply_by_email, #redux_save').click(function(event) {
								var count = 0;
								<?php echo $validation_script; ?>
								return true;
							});
						});
					</script>
                                <?php } ?>
				<?php } ?>
				<?php if ( $is_empty_mailbox_check ){
					?>
					<p>You have no mailbox setup please setup one.</p>
				<?php } ?>
			</div>
			<input class="button button-primary rtmailbox_submit_enable_reply_by_email" name="rtmailbox_submit_enable_reply_by_email" type="submit" value="Save">
			<?php
			if ( empty( $imap_servers ) ){
				echo '<div id="error_handle" class=""><p>'.__( 'Please set Imap Servers detail on ' ).'<a href="' . esc_url( admin_url( 'admin.php?page='.Rt_Mailbox::$page_name.'&tab=imap' ) ) . '">IMAP </a>  Page </p></div>';
				return;
			} else {
				if ( $newflag ) { ?>
					<a class="button" id="rtmailbox_add_personal_email" href="#"><?php _e( 'Add Email' ); ?></a>
					<?php
					$error_login = get_option( 'rt_login_fail_email' );
					if ( ! empty( $error_login ) ){
						?>
						<div class="rt_mailbox_error"> <?php echo $error_login; ?> login failed. Please enter correct credential or enable IMAP in your mailbox.</div>
						<?php
						delete_option( 'rt_login_fail_email' );
					}

					?>
					<div class="rtmailbox-hide-row" id="rtmailbox_email_acc_type_container">
						<div class="rtmailbox-severtype-container" >
							<input type="hidden" name="module_to_register" name="module_to_register" value="<?php echo $modules; ?>" />
							<select id="rtmailbox_select_email_acc_type" >
								<option value="" ><?php _e( 'Select Server Connection Type' ); ?></option>
								<?php foreach ( $server_types as $key => $value ) {?>
									<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
								<?php } ?>
							</select>
						</div>
						<?php if ( $imap_servers ) { ?>
							<div class="rtmailbox-hide-row" id="rtmailbox_add_imap_acc_form" autocomplete="off">
								<select  name="rtmailbox_imap_server" id="rtmailbox_imap_server">
									<option value=""><?php _e( 'Select Mail Server' ); ?></option>
									<?php foreach ( $imap_servers as $server ) { ?>
										<option value="<?php echo esc_attr( $server->id ); ?>"><?php echo esc_html( $server->server_name ); ?></option>
									<?php } ?>
								</select>
								<div id="rtmailbox_add_imap_acc_fields">
								</div>
								<input id="rtmailbox_add_imap" name="rtmailbox_add_imap_email" class="button button-primary" type="submit" value="Connect">
								<div class="rt_mailbox_error"></div>
							</div>
						<?php } ?>
					</div>
				<?php
				}
			}

			?>
			<?php do_action( 'rt_mailbox_reply_by_email_view' );
		}

		public function save_replay_by_email() {
			global $rt_mail_settings;
			$module = '';
			if ( isset( $_POST['module_to_register'] ) && ! empty( $_POST['module_to_register'] ) ){
				$module = $_POST['module_to_register'];
			}
			if ( ( isset( $_REQUEST['rtmailbox_submit_enable_reply_by_email'] ) && 'Save' == $_REQUEST['rtmailbox_submit_enable_reply_by_email'] ) || ( isset( $_REQUEST['redux_save'] ) && 'Save Changes' == $_REQUEST['redux_save'] ) ) {
				if ( isset( $_POST['mail_ac'] ) ) {
					foreach ( $_POST['mail_ac'] as $mail_ac ) {
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
						$email_ac   = $rt_mail_settings->get_email_acc( $mail_ac, $module );
						$email_data = null;
						if ( isset( $_POST['mail_folders'] ) && ! empty( $_POST['mail_folders'] ) && is_array( $_POST['mail_folders'] ) && ! empty( $email_ac ) ) {
							$email_data                 = maybe_unserialize( $email_ac->email_data );
							$email_data['mail_folders'] = implode( ',', $_POST['mail_folders'][ $mail_ac ] );
						}
						$rt_mail_settings->update_mail_acl( $mail_ac, $token, maybe_serialize( $email_data ), $imap_server );
					}
				}
			}
			if ( isset( $_REQUEST['rtmailbox_add_imap_email'] ) ) {
				if ( isset( $_POST['rtmailbox_imap_user_email'] ) && ! empty( $_POST['rtmailbox_imap_user_email'] ) && isset( $_POST['rtmailbox_imap_user_pwd'] ) && ! empty( $_POST['rtmailbox_imap_user_pwd'] ) && isset( $_POST['rtmailbox_imap_server'] ) && ! empty( $_POST['rtmailbox_imap_server'] ) ) {

					$password    = $_POST['rtmailbox_imap_user_pwd'];
					$email       = $_POST['rtmailbox_imap_user_email'];
					$email_data  = array(
						'email' => $email,
					);
					$imap_server = $_POST['rtmailbox_imap_server'];
					$hdZendEmail = new Rt_Zend_Mail();
					$email_type = 'imap';
					if ( ! $hdZendEmail->try_imap_login( $email, rt_encrypt_decrypt( $password ), $email_type, $imap_server ) ) {
						update_option( 'rt_login_fail_email', $email );
					} else {
						$rt_mail_settings->add_user_google_ac( rt_encrypt_decrypt( $password ), $email, maybe_serialize( $email_data ), $this->user_id, 'imap', $imap_server, $module );
					}
				}
			}
		}

		/**
		 * Remove Mailbox account using ajax request.
		 */
		public function rtmailbox_remove_account_callback() {

			$response = array();
			$response['status'] = false;

			if ( isset( $_POST['rtmailbox_submit_action'] ) && 'delete' == $_POST['rtmailbox_submit_action'] ) {

				if ( isset( $_POST['email'] ) && is_email( $_POST['email'] ) ) {

					global $rt_mail_crons, $rt_mail_settings;
					$status = $rt_mail_settings->delete_user_google_ac( $_POST['email'], $_POST['module_to_register'] );
					$rt_mail_crons->deregister_cron_for_module( $_POST['module_to_register'] );

					if ( $status ) {
						$response['status'] = true;
					}
				}
			}

			echo json_encode( $response );
			die();
		}

		/**
		 * Get mailbox list.
		 */
		public function rtmailbox_list_all() {
			global $rt_mail_settings;

			$rtbiz_modules = rt_biz_get_modules();

			$mailbox_list = $rt_mail_settings->get_all_mailbox();

			if ( isset( $mailbox_list ) && ! empty( $mailbox_list ) ) {
				?>
				<table id="rtmailbox-container" class="form-table">
					<tbody>
					<tr>
						<th>Mail Account</th>
						<th>Module</th>
						<th></th>
					</tr>
					<?php
					foreach ( $mailbox_list as $mailbox ) {
						$mailbox->email_data = unserialize( $mailbox->email_data );
						$email = filter_var( $mailbox->email_data['email'], FILTER_SANITIZE_EMAIL );
						?>
						<tr>
							<td>
								<strong>
									<?php if ( isset( $mailbox->email_data['name'] ) ) { echo $mailbox->email_data['name'].'<br />'; } ?>
									<a href='mailto:<?php echo $email ?>'>
										<?php echo $email ?>
									</a>
								</strong>
							</td>
							<td>
								<?php if ( isset( $rtbiz_modules[ $mailbox->module ]['label'] ) ){ echo $rtbiz_modules[ $mailbox->module ]['label'];} else { echo $mailbox->module; } ?>
							</td>
							<td class="rtmailbox-maillist-action">
								<a class='button show-mailbox-settings' href="<?php if ( isset( $rtbiz_modules[ $mailbox->module ]['setting_page_url'] ) ){ echo $rtbiz_modules[ $mailbox->module ]['setting_page_url']; } else { echo 'javascript:;'; } ?>"><?php echo __( 'Settings' ); ?></a>
							</td>
						</tr>
					<?php
					}
					?>
					</tbody>
				</table>
			<?php
			}
		}
	}
}
