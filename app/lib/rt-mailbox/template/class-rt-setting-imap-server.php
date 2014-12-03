<?php
/**
 * Created by PhpStorm.
 * User: spock
 * Date: 15/9/14
 * Time: 11:55 AM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Zend\Mail\Storage\Imap as ImapStorage;

if ( ! class_exists( 'RT_Setting_Imap_Server' ) ) {

	class RT_Setting_Imap_Server {

		function __construct() {
			add_action( 'init', array( $this, 'save_imap_servers' ) );
			$updateDB = new RT_DB_Update( RT_LIB_FILE, trailingslashit( dirname( __FILE__ ) ) . 'schema/' );
			add_action( 'rt_db_update_finished_' . str_replace( '-', '_', sanitize_title( $updateDB->rt_plugin_info->name ) ), array( $this, 'default_imap_servers' ) );
		}

		function default_imap_servers() {
			global $rt_imap_server_model;
			$default_imap_servers = array(
				array(
					'server_name' => 'Google',
					'incoming_imap_server' => 'imap.gmail.com',
					'incoming_imap_port' => '993',
					'incoming_imap_enc' => 'ssl',
					'outgoing_smtp_server' => 'smtp.gmail.com',
					'outgoing_smtp_port' => '587',
					'outgoing_smtp_enc' => 'tls',
				),
				array(
					'server_name' => 'Outlook',
					'incoming_imap_server' => 'imap-mail.outlook.com',
					'incoming_imap_port' => '993',
					'incoming_imap_enc' => 'ssl',
					'outgoing_smtp_server' => 'smtp-mail.outlook.com',
					'outgoing_smtp_port' => '587',
					'outgoing_smtp_enc' => 'tls',
				),
				array(
					'server_name' => 'Yahoo',
					'incoming_imap_server' => 'imap.mail.yahoo.com',
					'incoming_imap_port' => '993',
					'incoming_imap_enc' => 'ssl',
					'outgoing_smtp_server' => 'smtp.mail.yahoo.com',
					'outgoing_smtp_port' => '587',
					'outgoing_smtp_enc' => 'tls',
				),
			);

			foreach ( $default_imap_servers as $server ) {
				$existing_server = $rt_imap_server_model->get_servers( array( 'incoming_imap_server' => $server['incoming_imap_server'] ) );
				if ( empty( $existing_server ) ) {
					$rt_imap_server_model->add_server( $server );
				}
			}
		}

		function rt_imap_servers( $field, $value ) {
			global $rt_imap_server_model;
			$servers = $rt_imap_server_model->get_all_servers();
			?>
			<table>
				<tbody>
				<?php foreach ( $servers as $server ) { ?>
					<tr valign="top">
						<th scope="row"><?php echo esc_html( $server->server_name ); ?></th>
						<td>
							<a href="#" class="rthd-edit-server"
							   data-server-id="<?php echo esc_attr( $server->id ); ?>"><?php _e( 'Edit' ); ?></a> <a href="#"
							                                                                                         class="rthd-remove-server"
							                                                                                         data-server-id="<?php echo esc_attr( $server->id ); ?>"><?php _e( 'Remove' ); ?></a>
						</td>
					</tr>
					<tr valign="top" id="rthd_imap_server_<?php echo esc_attr( $server->id ); ?>" class="rthd-hide-row">
						<td>
							<table>
								<tr valign="top">
									<th scope="row"><?php _e( 'Server Name: ' ); ?></th>
									<td><input type="text" required="required"
									           name="rthd_imap_servers[<?php echo esc_attr( $server->id ); ?>][server_name]"
									           value="<?php echo esc_attr( $server->server_name ); ?>"/></td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'IMAP (Incoming) Server: ' ); ?></th>
									<td><input type="text" required="required"
									           name="rthd_imap_servers[<?php echo esc_attr( $server->id ); ?>][incoming_imap_server]"
									           value="<?php echo esc_attr( $server->incoming_imap_server ); ?>"/></td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'IMAP (Incoming) Port: ' ); ?></th>
									<td><input type="text" required="required"
									           name="rthd_imap_servers[<?php echo esc_attr( $server->id ); ?>][incoming_imap_port]"
									           value="<?php echo esc_attr( $server->incoming_imap_port ); ?>"/></td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'IMAP (Incoming) Encryption: ' ); ?></th>
									<td>
										<select
											name="rthd_imap_servers[<?php echo esc_attr( $server->id ); ?>][incoming_imap_enc]">
											<option value=""><?php _e( 'Select Encryption Method' ); ?></option>
											<option
												value="ssl" <?php echo esc_html( ( $server->incoming_imap_enc == 'ssl' ) ? 'selected="selected"' : '' ); ?>><?php _e( 'SSL' ); ?></option>
											<option
												value="tls" <?php echo esc_html( ( $server->incoming_imap_enc == 'tls' ) ? 'selected="selected"' : '' ); ?>><?php _e( 'TLS' ); ?></option>
										</select>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'SMTP (Outgoing) Server: ' ); ?></th>
									<td><input type="text" required="required"
									           name="rthd_imap_servers[<?php echo esc_attr( $server->id ); ?>][outgoing_smtp_server]"
									           value="<?php echo esc_attr( $server->outgoing_smtp_server ); ?>"/></td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'SMTP (Outgoing) Port: ' ); ?></th>
									<td><input type="text" required="required"
									           name="rthd_imap_servers[<?php echo esc_attr( $server->id ); ?>][outgoing_smtp_port]"
									           value="<?php echo esc_attr( $server->outgoing_smtp_port ); ?>"/></td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'SMTP (Outgoing) Encryption: ' ); ?></th>
									<td>
										<select
											name="rthd_imap_servers[<?php echo esc_attr( $server->id ); ?>][outgoing_smtp_enc]">
											<option value=""><?php _e( 'Select Encryption Method' ); ?></option>
											<option
												value="ssl" <?php echo esc_html( ( $server->outgoing_smtp_enc == 'ssl' ) ? 'selected="selected"' : '' ); ?>><?php _e( 'SSL' ); ?></option>
											<option
												value="tls" <?php echo esc_html( ( $server->outgoing_smtp_enc == 'tls' ) ? 'selected="selected"' : '' ); ?>><?php _e( 'TLS' ); ?></option>
										</select>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				<?php } ?>
				<input type="hidden" name="rthd_imap_servers_changed" value="1"/>
				<tr valign="top">
					<th scope="row"><a href="#" class="button" id="rthd_add_imap_server"><?php _e( 'Add new server' ); ?></a>
					</th>
				</tr>
				<tr valign="top" id="rthd_new_imap_server" class="rthd-hide-row">
					<td>
						<table>
							<tr valign="top">
								<th scope="row"><?php _e( 'Server Name: ' ); ?></th>
								<td><input type="text" name="rthd_imap_servers[new][server_name]"/></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'IMAP (Incoming) Server: ' ); ?></th>
								<td><input type="text" name="rthd_imap_servers[new][incoming_imap_server]"/></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'IMAP (Incoming) Port: ' ); ?></th>
								<td><input type="text" name="rthd_imap_servers[new][incoming_imap_port]"/></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'IMAP (Incoming) Encryption: ' ); ?></th>
								<td>
									<select name="rthd_imap_servers[new][incoming_imap_enc]">
										<option value=""><?php _e( 'Select Encryption Method' ); ?></option>
										<option value="ssl"><?php _e( 'SSL' ); ?></option>
										<option value="tls"><?php _e( 'TLS' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'SMTP (Outgoing) Server: ' ); ?></th>
								<td><input type="text" name="rthd_imap_servers[new][outgoing_smtp_server]"/></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'SMTP (Outgoing) Port: ' ); ?></th>
								<td><input type="text" name="rthd_imap_servers[new][outgoing_smtp_port]"/></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Is SSL required for SMTP (Outgoing Mails): ' ); ?></th>
								<td>
									<select name="rthd_imap_servers[new][outgoing_smtp_enc]">
										<option value=""><?php _e( 'Select Encryption Method' ); ?></option>
										<option value="ssl"><?php _e( 'SSL' ); ?></option>
										<option value="tls"><?php _e( 'TLS' ); ?></option>
									</select>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				</tbody>
			</table>
		<?php
		}

		function save_imap_servers() {

			if ( isset( $_POST['rthd_imap_servers_changed'] ) ) {
				global $rt_imap_server_model;
				$old_servers = $rt_imap_server_model->get_all_servers();

				if ( isset( $_POST['rthd_imap_servers'] ) ) {
					$new_servers = $_POST['rthd_imap_servers'];

					// Handle / Update Existing Servers
					foreach ( $old_servers as $id => $server ) {
						if ( isset( $new_servers[ $server->id ] ) ) {
							if ( empty( $new_servers[ $server->id ]['server_name'] ) || empty( $new_servers[ $server->id ]['incoming_imap_server'] ) || empty( $new_servers[ $server->id ]['incoming_imap_port'] ) || empty( $new_servers[ $server->id ]['outgoing_smtp_server'] ) || empty( $new_servers[ $server->id ]['outgoing_smtp_port'] ) ) {
								continue;
							}
							$args = array(
								'server_name'          => $new_servers[ $server->id ]['server_name'],
								'incoming_imap_server' => $new_servers[ $server->id ]['incoming_imap_server'],
								'incoming_imap_port'   => $new_servers[ $server->id ]['incoming_imap_port'],
								'incoming_imap_enc'    => ( ! empty( $new_servers[ $server->id ]['incoming_imap_enc'] ) ) ? $new_servers[ $server->id ]['incoming_imap_enc'] : '',
								'outgoing_smtp_server' => $new_servers[ $server->id ]['outgoing_smtp_server'],
								'outgoing_smtp_port'   => $new_servers[ $server->id ]['outgoing_smtp_port'],
								'outgoing_smtp_enc'    => ( ! empty( $new_servers[ $server->id ]['outgoing_smtp_enc'] ) ) ? $new_servers[ $server->id ]['outgoing_smtp_enc'] : '',
							);
							$rt_imap_server_model->update_server( $args, $server->id );

						} else {
							$rt_imap_server_model->delete_server( $server->id );
						}
					}

					// New Server in the list
					if ( ! empty( $new_servers['new']['server_name'] ) && ! empty( $new_servers['new']['incoming_imap_server'] ) && ! empty( $new_servers['new']['incoming_imap_port'] ) && ! empty( $new_servers['new']['outgoing_smtp_server'] ) && ! empty( $new_servers['new']['outgoing_smtp_port'] ) ) {

						$args = array(
							'server_name'          => $new_servers['new']['server_name'],
							'incoming_imap_server' => $new_servers['new']['incoming_imap_server'],
							'incoming_imap_port'   => $new_servers['new']['incoming_imap_port'],
							'incoming_imap_enc'    => ( ! empty( $new_servers['new']['incoming_imap_enc'] ) ) ? $new_servers['new']['incoming_imap_enc'] : '',
							'outgoing_smtp_server' => $new_servers['new']['outgoing_smtp_server'],
							'outgoing_smtp_port'   => $new_servers['new']['outgoing_smtp_port'],
							'outgoing_smtp_enc'    => ( ! empty( $new_servers['new']['outgoing_smtp_enc'] ) ) ? $new_servers['new']['outgoing_smtp_enc'] : '',
						);
						$rt_imap_server_model->add_server( $args );

						return true;
					}
				} else {
					foreach ( $old_servers as $server ) {
						$rt_imap_server_model->delete_server( $server->id );
					}
				}
			}
		}
	}
}
