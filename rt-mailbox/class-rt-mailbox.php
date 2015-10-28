<?php
/**
 * Description of Rt_Mailbox
 * Main class for Attributes.
 * This will be responsible for inbound Mailbox setup
 *
 * @author Dipesh Kakadiya <dipesh.kakadiya111@gmail.com>
 */

use Zend\Mail\Storage\Imap as ImapStorage;

if ( ! class_exists( 'Rt_Mailbox' ) ) {

	class Rt_Mailbox {

		static $rt_mime_types = array(
			'pdf'  => 'application/pdf',
			'exe'  => 'application/octet-stream',
			'zip'  => 'application/zip',
			'docx' => 'application/msword',
			'doc'  => 'application/msword',
			'xls'  => 'application/vnd.ms-excel',
			'ppt'  => 'application/vnd.ms-powerpoint',
			'gif'  => 'image/gif',
			'png'  => 'image/png',
			'jpeg' => 'image/jpg',
			'jpg'  => 'image/jpg',
			'mp3'  => 'audio/mpeg',
			'wav'  => 'audio/x-wav',
			'mpeg' => 'video/mpeg',
			'mpg'  => 'video/mpeg',
			'mpe'  => 'video/mpeg',
			'mov'  => 'video/quicktime',
			'avi'  => 'video/x-msvideo',
			'3gp'  => 'video/3gpp',
			'css'  => 'text/css',
			'jsc'  => 'application/javascript',
			'js'   => 'application/javascript',
			'php'  => 'text/html',
			'htm'  => 'text/html',
			'html' => 'text/html',
		);

		/**
		 * @var $auto_loader
		 *
		 * rt-mailbox auto loader object
		 */
		static $auto_loader;

		/**
		 * @var $default_imap_servers
		 *
		 * Default imap server array
		 */
		var $default_imap_servers;

		/**
		 * @var int $ssl_port
		 */
		var $incoming_ssl_port = 993;

		/**
		 * @var int $tls_port
		 */
		var $incoming_tls_port = 143;

		/**
		 * @var int $ssl_port
		 */
		var $outgoing_ssl_port = 465;

		/**
		 * @var int $tls_port
		 */
		var $outgoing_tls_port = 587;

		/**
		 * Constructor to initialized rt-mailbox
		 *
		 * @param $plugin_path_for_deactivate_cron
		 * Plugin path for which you activated rt-mailbox
		 *
		 * @param $parent_slug
		 * Page slug under which the Mailbox page is to be shown. it's not mandatory
		 * Default: create individual menu
		 *
		 * @param bool $admin_menu
		 * Flag for register page for mailbox or not
		 *
		 * @param string $cap
		 * Capability for Mailbox setup Page;
		 * Default: manage_options
		 */
		function __construct( $plugin_path_for_cron ) {

			$this->default_imap_servers = array(
				array(
					'server_name'          => 'Gmail/Google',
					'incoming_imap_server' => 'imap.gmail.com',
					'outgoing_smtp_server' => 'smtp.gmail.com',
				),
				array(
					'server_name'          => 'Outlook/Hotmail',
					'incoming_imap_server' => 'imap-mail.outlook.com',
					'outgoing_smtp_server' => 'smtp-mail.outlook.com',
				),
				array(
					'server_name'          => 'Yahoo',
					'incoming_imap_server' => 'imap.mail.yahoo.com',
					'outgoing_smtp_server' => 'smtp.mail.yahoo.com',
				),
			);

			$this->auto_loader();
			add_action( 'rt_db_update_finished', array( $this, 'call_default_imap_servers' ) );
			$this->db_upgrade();
			$this->inti_global();
			//			$this->default_imap_servers();

			$this->init_rt_wp_mail_cron( $plugin_path_for_cron );

			add_action( 'init', array( $this, 'rtmailbox_ajax' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ), 999 );
		}

		function call_default_imap_servers() {
			add_action( 'rt_mailbox_global_init_finish', array( $this, 'default_imap_servers' ) );
		}

		/**
		 * Register AutoLoader for MailBox
		 *
		 * load to all classes
		 */
		function auto_loader() {
			include_once plugin_dir_path( __FILE__ ) . 'vendor/' . 'MailLib/zendAutoload.php';
			include_once trailingslashit( dirname( __FILE__ ) ) . 'helper/' . 'rt-mailbox-functions.php';
			self::$auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'model/' );
			self::$auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'helper/' );
			self::$auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'settings/' );
			self::$auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'vendor/' );
			self::$auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) );
		}

		/**
		 * update schema fore rt-mailbox lib
		 *
		 * New version available if yes update rtmailbox schema
		 */
		function db_upgrade() {
			$updateDB = new  RT_DB_Update( RT_LIB_FILE, trailingslashit( dirname( __FILE__ ) ) . 'schema/' );
			$updateDB->db_version_option_name .= '_RT_MAILBOX';
			$updateDB->install_db_version = $updateDB->get_install_db_version();
			$updateDB->do_upgrade();
		}

		/**
		 * init global rt-mailbox class
		 */
		function inti_global() {
			global $rt_imap_server_model, $rt_mail_accounts_model, $rt_mail_message_model, $rt_outbound_model;
			$rt_imap_server_model   = new Rt_IMAP_Server_Model();
			$rt_mail_accounts_model = new Rt_Mail_Accounts_Model();
			$rt_mail_message_model  = new Rt_Mail_Message_Model();
			$rt_outbound_model      = new Rt_Mail_Outbound_Model();

			global $rt_mail_settings;
			$rt_mail_settings = new Rt_Mail_Settings();
			do_action( 'rt_mailbox_global_init_finish' );
		}

		/**
		 * Default Imap server added
		 */
		function default_imap_servers() {

			global $rt_imap_server_model;
			$default_imap_servers = array(
				array(
					'server_name'          => 'Gmail/Google',
					'incoming_imap_server' => 'imap.gmail.com',
					'incoming_imap_port'   => '993',
					'incoming_imap_enc'    => 'ssl',
					'outgoing_smtp_server' => 'smtp.gmail.com',
					'outgoing_smtp_port'   => '587',
					'outgoing_smtp_enc'    => 'tls',
				),
				array(
					'server_name'          => 'Outlook/Hotmail',
					'incoming_imap_server' => 'imap-mail.outlook.com',
					'incoming_imap_port'   => '993',
					'incoming_imap_enc'    => 'ssl',
					'outgoing_smtp_server' => 'smtp-mail.outlook.com',
					'outgoing_smtp_port'   => '587',
					'outgoing_smtp_enc'    => 'tls',
				),
				array(
					'server_name'          => 'Yahoo',
					'incoming_imap_server' => 'imap.mail.yahoo.com',
					'incoming_imap_port'   => '993',
					'incoming_imap_enc'    => 'ssl',
					'outgoing_smtp_server' => 'smtp.mail.yahoo.com',
					'outgoing_smtp_port'   => '587',
					'outgoing_smtp_enc'    => 'tls',
				),
			);
			$existing_server      = $rt_imap_server_model->get_all_servers();
			foreach ( $default_imap_servers as $server ) {
				$exist_in_db = false;
				foreach ( $existing_server as $old_servers ) {
					if ( $server['incoming_imap_server'] == $old_servers->incoming_imap_server ) {
						$exist_in_db = true;
						break;
					}
				}
				if ( empty( $exist_in_db ) ) {
					$rt_imap_server_model->add_server( $server );
				}
			}
		}

		/**
		 * Ajx request for rtmailbox
		 */
		function rtmailbox_ajax() {
			add_action( 'wp_ajax_rtmailbox_imap_connect', array( $this, 'rtmailbox_imap_connect_callback' ) );
			add_action( 'wp_ajax_rtmailbox_folder_update', array( $this, 'rtmailbox_imap_folder_save_callback' ) );
			add_action( 'wp_ajax_rtmailbox_mailbox_update', array( $this, 'rtmailbox_mailbox_update_callback' ) );
			add_action( 'wp_ajax_rtmailbox_mailbox_remove', array( $this, 'rtmailbox_mailbox_remove_callback' ) );
			add_action( 'wp_ajax_rtmailbox_mailbox_add', array( $this, 'rtmailbox_mailbox_add_callback' ) );
		}

		/**
		 * Register css & js for rt-mailbox
		 */
		function enqueue_styles_scripts() {
			wp_enqueue_style( 'mailbox-setting-css', plugin_dir_url( __FILE__ ) . 'assets/css/rt-mailbox.css', array(), time() );
			wp_enqueue_script( 'mailbox-setting-js', plugin_dir_url( __FILE__ ) . 'assets/js/rt-mailbox.js', array( 'jquery' ), time(), true );
			wp_localize_script( 'mailbox-setting-js', 'adminurl', admin_url() );
		}

		/**
		 * Setup WordPress cron for mail import
		 *
		 * @param $plugin_path_for_deactivate_cron
		 */
		function init_rt_wp_mail_cron( $plugin_path_for_cron ) {
			global $rt_mail_crons;
			$rt_mail_crons = new Rt_Mail_Cron( $plugin_path_for_cron );
		}


		/**
		 * render mailbox setting ui
		 *
		 * @param        $module
		 * @param string $reload_page_url
		 * If page requires reload after successfull connection pass url where to reload or redirect.
		 */
		function render_mailbox_setting_page( $module, $reload_page_url = '' ) {
			if ( ! empty( $reload_page_url ) ) {
				?>
				<script>
					var reload_url = '<?php echo ( $reload_page_url ); ?>';
				</script>
				<?php
			}
			?>
			<div id="rtmailbox-page" class="wrap">
				<div id="mailbox-list" class="mailbox-list">
					<?php $this->render_list_mailbox_page( $module ); ?>
				</div>
				<div id="rtmailbox-wrap" class="rtmailbox-wrap">
					<?php global $rt_mail_settings;
					$mailbox = $rt_mail_settings->get_user_google_ac( array( 'module' => $module ) );
					if ( ! empty( $mailbox ) ) { ?>
						<input id="rtmailbox-add" data-module="<?php echo $module; ?>" name="rtmailbox[Cancel]"
						       class="button" value="Add Another Mailbox" type="button"><?php
					} else {
						$this->render_add_mailbox_page( $module );
					} ?>
				</div>
			</div>
			<?php
		}


		/**
		 * render mailbox setup page add mailbox
		 */
		function render_add_mailbox_page( $module, $mailboxid = '' ) {

			global $rt_imap_server_model, $rt_mail_settings;

			$default_servers          = $rt_imap_server_model->get_all_servers();
			$rtmailbox_default_server = wp_list_pluck( $this->default_imap_servers, 'server_name' );
			if ( ! empty( $mailboxid ) ) { ?>
				<h4>Change Mailbox Configuration</h4><?php
				$mailbox = $rt_mail_settings->get_user_google_ac( array( 'module' => $module, 'id' => $mailboxid ) );
				if ( ! empty( $mailbox ) ) {
					$mailbox = $mailbox[0];
					$server  = $rt_imap_server_model->get_servers( array( 'id' => $mailbox->imap_server ) );
					if ( ! empty( $server ) ) {
						$server = $server[0];
					}
				} else {
					echo '<p>Mailbox not found.</p>';
				}
			} else {
				$mailbox = $rt_mail_settings->get_user_google_ac( array( 'module' => $module ) );
				if ( ! empty( $mailbox ) ) { ?>
					<h4>Add Another Mailbox</h4><?php
				} else { ?>
					<h4>Add New Mailbox</h4><?php
				}
			} ?>
			<form id="rtmailbox-imap-connect-form" method="post">
			<input id="rtmailbox-module" name="rtmailbox[module]" value="<?php echo $module; ?>" type="hidden">
			<input id="rtmailbox-mailbox-id" name="rtmailbox[mailboxid]"
			       value="<?php echo ! empty( $mailbox->id ) ? $mailbox->id : ''; ?>" type="hidden">
			<input id="rtmailbox-incoming_ssl_port" name="rtmailbox[incoming_ssl_port]"
			       value="<?php echo $this->incoming_ssl_port; ?>" type="hidden">
			<input id="rtmailbox-incoming_tls_port" name="rtmailbox[incoming_tls_port]"
			       value="<?php echo $this->incoming_tls_port; ?>" type="hidden">
			<input id="rtmailbox-outgoing_ssl_port" name="rtmailbox[outgoing_ssl_port]"
			       value="<?php echo $this->outgoing_ssl_port; ?>" type="hidden">
			<input id="rtmailbox-outgoing_tls_port" name="rtmailbox[outgoing_tls_port]"
			       value="<?php echo $this->outgoing_tls_port; ?>" type="hidden">

			<?php wp_nonce_field( 'rtmailbox_connect_imap' );
			do_action( 'rt_mailbox_randed_view_before' ); ?>

			<div class="rtmailbox-row">
				<label><?php _e( 'Account Type:' ); ?></label>
				<?php _e( 'IMAP' ); ?>
			</div>
			<div class="rtmailbox-row">
				<label for="rtmailbox-email"><?php _e( 'Email' ); ?><sup>*</sup></label>
				<?php ?>
				<input autocomplete="off" id="rtmailbox-email" name="rtmailbox[email]" placeholder="Email"
				       value="<?php echo ! empty( $mailbox->email ) ? $mailbox->email : ''; ?>" type="text">
			</div>
			<div class="rtmailbox-row">
				<label for="rtmailbox-password"><?php _e( 'Password' ); ?><sup>*</sup></label>
				<input autocomplete="off" id="rtmailbox-password" name="rtmailbox[password]" placeholder="Password"
				       value="" type="password">
			</div>
			<div class="rtmailbox-row">
				<label><?php _e( 'Provider' ); ?></label>
				<ul class="rtmailbox_provider_list">
					<?php $provider = ! empty( $server->server_name ) ? ( in_array( $server->server_name, $rtmailbox_default_server ) ) ? $server->server_name : 'custom' : 'Gmail/Google';
					foreach ( $default_servers as $ser ) {
						if ( in_array( $ser->server_name, $rtmailbox_default_server ) ) {
							$seleted = ( $provider == $ser->server_name ) ? 'checked' : ''; ?>
							<li>
								<input title="<?php echo $ser->server_name; ?>" class="rtmailbox_provider"
								       id="rtmailbox-provider-<?php echo $ser->id; ?>"
								       name="rtmailbox[provider]" <?php echo $seleted; ?>
								       value="<?php echo $ser->id; ?>" type="radio">
								<label
									for="rtmailbox-provider-<?php echo $ser->id; ?>"><?php echo $ser->server_name; ?></label>
							</li>
						<?php }
					} ?>
					<li>
						<?php $seleted = ( 'custom' == $provider ) ? 'checked' : ''; ?>
						<input title="<?php _e( 'custom' ) ?>" class="rtmailbox_provider"
						       id="rtmailbox-provider-<?php _e( 'custom' ) ?>"
						       name="rtmailbox[provider]" <?php echo $seleted; ?> value="<?php _e( 'custom' ) ?>"
						       type="radio">
						<label for="rtmailbox-provider-<?php _e( 'custom' ) ?>"><?php _e( 'Custom' ) ?></label>
					</li>
				</ul>
			</div>
			<div id="rtmailbox-imap-server-container">
				<input id="rtmailbox-server-id" name="rtmailbox[serverid]"
				       value="<?php echo ( ! empty( $server->id ) && 'custom' == $provider ) ? $server->id : ''; ?>"
				       type="hidden">

				<div class="rtmailbox-row">
					<label for="rtmailbox-provider_name"><?php _e( 'Provider Name' ); ?></label>
					<input autocomplete="off" id="rtmailbox-provider_name" name="rtmailbox[provider_name]"
					       placeholder="Provider name"
					       value="<?php echo ( ! empty( $server->server_name ) && 'custom' == $provider ) ? $server->server_name : ''; ?>"
					       type="text">
				</div>

				<div class="rtmailbox-row">
					<label for="rtmailbox-incoming_server"><?php _e( 'Incoming Server (*)' ); ?></label>
					<input autocomplete="off" id="rtmailbox-incoming_server" name="rtmailbox[incoming_server]"
					       placeholder="imap.example.com"
					       value="<?php echo ( ! empty( $server->incoming_imap_server ) && 'custom' == $provider ) ? $server->incoming_imap_server : ''; ?>"
					       type="text">
				</div>
				<div class="rtmailbox-row">
					<label for="rtmailbox-incoming_port"><?php _e( 'Port' ); ?></label>
					<input autocomplete="off" id="rtmailbox-incoming_port" name="rtmailbox[incoming_port]"
					       value="<?php echo ! empty( $server->incoming_imap_port ) && 'custom' == $provider ? $server->incoming_imap_port : $this->incoming_ssl_port; ?>"
					       type="text">
					<input id="rtmailbox-incoming_ssl"
					       name="rtmailbox[incoming_ssl]" <?php echo ! empty( $server->incoming_imap_enc ) && $server->incoming_imap_enc == 'tls' ? '' : 'checked'; ?>
					       value="enable" type="checkbox">
					<label for="rtmailbox-incoming_ssl"><?php _e( 'Use SSL' ); ?></label>
				</div>

				<div class="rtmailbox-row">
					<label for="rtmailbox-outgoing_server"><?php _e( 'Outgoing Server (*)' ); ?></label>
					<input autocomplete="off" id="rtmailbox-outgoing_server" name="rtmailbox[outgoing_server]"
					       placeholder="smtp.example.com"
					       value="<?php echo ! empty( $server->outgoing_smtp_server ) && 'custom' == $provider ? $server->outgoing_smtp_server : ''; ?>"
					       type="text">
				</div>
				<div class="rtmailbox-row">
					<label for="rtmailbox-outgoing_port"><?php _e( 'Port' ); ?></label>
					<input autocomplete="off" id="rtmailbox-outgoing_port" name="rtmailbox[outgoing_port]"
					       value="<?php echo ! empty( $server->outgoing_smtp_port ) && 'custom' == $provider ? $server->outgoing_smtp_port : $this->outgoing_tls_port; ?>"
					       type="text">
					<input
						id="rtmailbox-outgoing_ssl" <?php echo ! empty( $server->outgoing_smtp_enc ) && $server->outgoing_smtp_enc == 'ssl' ? 'checked' : ''; ?>
						name="rtmailbox[outgoing_ssl]" value="enable" type="checkbox">
					<label for="rtmailbox-outgoing_ssl"><?php _e( 'Use SSL' ); ?></label>
				</div>
			</div>
			<div class="rtmailbox-row">
				<label></label>
				<input id="rtmailbox-action" name="rtmailbox[action]" value="rtmailbox_connect_imap" type="hidden">
				<input id="rtmailbox-connect" name="rtmailbox[connect]" class="button" value="Test Connection"
				       type="button">
			</div>

			<?php do_action( 'rt_mailbox_randed_view_after' ); ?>

			</form><?php
		}

		/**
		 * mailbox list page
		 *
		 * @param string $module
		 * @param string $mailboxid
		 */
		function render_list_mailbox_page( $module = '', $mailboxid = '' ) {
			global $rt_mail_settings;
			$args = array();
			if ( ! empty( $module ) ) {
				$args['module'] = $module;
			}
			if ( ! empty( $mailboxid ) ) {
				$args['id'] = $mailboxid;
			}
			$mailboxes = $rt_mail_settings->get_user_google_ac( $args );
			if ( isset( $mailboxes ) && ! empty( $mailboxes ) ) {
				foreach ( $mailboxes as $mailbox ) {
					$mailbox->email_data = unserialize( $mailbox->email_data );
					$email               = filter_var( $mailbox->email_data['email'], FILTER_SANITIZE_EMAIL );
					$email_type          = $mailbox->type;
					$imap_server         = $mailbox->imap_server;
					$mail_folders        = ( isset( $mailbox->email_data['mail_folders'] ) ) ? $mailbox->email_data['mail_folders'] : '';
					$mail_folders        = array_filter( explode( ',', $mail_folders ) );
					$token               = $mailbox->outh_token;

					$all_folders      = null;
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
						$login_successful = false;
						echo '<p class="description">' . esc_html( $e->getMessage() ) . '</p>';
					}

					// user photo
					if ( isset( $mailbox->email_data['picture'] ) ) {
						$img          = filter_var( $mailbox->email_data['picture'], FILTER_VALIDATE_URL );
						$personMarkup = "<img src='$img?sz=30'>";
					} else {
						$personMarkup = get_avatar( $email, 30 );
					} ?>
					<div id="mailbox-<?php echo $mailbox->id; ?>" class="rtmailbox-row">
						<input type="hidden" name='mail_ac[]' value="<?php echo esc_attr( $email ); ?>"/>

						<div class='rtmailbox-avtar'>
							<a href="mailto:<?php echo $email; ?>"><?php echo $personMarkup . '<span>' . $email . '</span>'; ?></a><?php
							if ( isset( $mailbox->email_data['name'] ) ) {
								echo $mailbox->email_data['name'] . '<br/>';
							} ?>
						</div>

						<div class="rtmailbox-maillist-action">
							<?php if ( $login_successful ) { ?>
								<a id="rtmailbox-update-mailbox" class="button" data-action="rtmailbox_update"
								   data-mailboxid="<?php echo $mailbox->id; ?>" data-email="<?php echo $email; ?>"
								   data-module="<?php echo $mailbox->module; ?>"
								   href="javascript:;"><?php echo __( 'Select Folder' ); ?></a>
							<?php } else { ?>
								<a id="rtmailbox-update-mailbox" class="button" data-action="rtmailbox_reconfigured"
								   data-mailboxid="<?php echo $mailbox->id; ?>" data-email="<?php echo $email; ?>"
								   data-module="<?php echo $mailbox->module; ?>"
								   href="javascript:;"><?php echo __( 'Reconfigured' ); ?></a>
							<?php } ?>
							<a class='button remove-mailbox'
							   data-mailboxid="<?php echo $mailbox->id; ?>" data-email="<?php echo $email; ?>"
							   data-module="<?php echo $mailbox->module; ?>"
							   href="javascript:;"><?php echo __( 'Remove' ); ?></a>
						</div>
						<div id="mailbox-folder-<?php echo $mailbox->id; ?>" class="rtmailbox-mail-folder">
							<?php $this->rtmailbox_mailbox_folder_ui( $mailbox->module, $mailbox->id ); ?>
						</div>
					</div>
					<?php
				}
			}
		}

		/**
		 * Ajax method to create mailbox
		 */
		function rtmailbox_imap_connect_callback() {

			$result           = array();
			$result['status'] = false;

			$obj_data = array();
			parse_str( $_POST['data'], $obj_data );

			if ( ! wp_verify_nonce( $obj_data['_wpnonce'], 'rtmailbox_connect_imap' ) ) {
				$result['error'] = 'Security check false';
				echo json_encode( $result );
				die();
			}
			$obj_data = $obj_data['rtmailbox'];

			if ( empty( $obj_data['email'] ) || empty( $obj_data['password'] ) || empty( $obj_data['provider'] ) ) {
				$result['error'] = 'Error: Required mailbox field missing';
				echo json_encode( $result );
				die();
			}

			if ( 'rtmailbox_connect_imap' == $obj_data['action'] ) {
				if ( 'custom' == $obj_data['provider'] ) {
					$response = $this->rtmailbox_create_imap_server( $obj_data );
					if ( is_array( $response ) && true == $response['status'] ) {
						$obj_data['provider'] = $response['server_id'];
					} else {
						$result['error'] = $response['error'];
						echo json_encode( $result );
						die();
					}
				}
				$response = $this->rtmailbox_add_mailbox( $obj_data );
				if ( is_array( $response ) && true == $response['status'] ) {
					ob_start();
					echo '<input id="rtmailbox-add" data-module="' . $obj_data['module'] . '" name="rtmailbox[Cancel]" class="button" value="Add Another Mailbox" type="button">';
					$result['html']      = ob_get_clean();
					$result['html_list'] = $response['html_list'];
					$result['moduleid']  = $response['moduleid'];
					$result['status']    = true;
				} else {
					$result['error'] = $response['error'];
				}
			}
			echo json_encode( $result );
			die();
		}

		/**
		 * Create new IMAP server
		 *
		 * @param $obj_data
		 *
		 * @return mixed
		 */
		function rtmailbox_create_imap_server( $obj_data ) {
			global $rt_imap_server_model;

			$result           = array();
			$result['status'] = false;

			if ( empty( $obj_data['provider_name'] ) || empty( $obj_data['incoming_server'] ) || empty( $obj_data['outgoing_server'] ) ) {
				$result['error'] = 'Error: Required server field missing.';

				return $result;
			}

			$incoming_enc = ( isset( $obj_data['incoming_ssl'] ) && 'enable' == $obj_data['incoming_ssl'] ) ? 'ssl' : 'tls';
			$outgoing_enc = ( isset( $obj_data['outgoing_ssl'] ) && 'enable' == $obj_data['outgoing_ssl'] ) ? 'ssl' : 'tls';

			// if port empty set default
			if ( ! isset( $obj_data['incoming_port'] ) || empty( $obj_data['incoming_port'] ) ) {
				$obj_data['incoming_port'] = ( 'ssl' == $incoming_enc ) ? $this->incoming_ssl_port : $this->incoming_tls_port;
			}
			if ( ! isset( $obj_data['outgoing_port'] ) || empty( $obj_data['outgoing_port'] ) ) {
				$obj_data['outgoing_port'] = ( 'ssl' == $outgoing_enc ) ? $this->outgoing_ssl_port : $this->outgoing_tls_port;
			}

			try {
				$hdZendEmail = new Rt_Zend_Mail();
				$ssl         = ( isset( $incoming_enc ) && ! is_null( $incoming_enc ) ) ? $incoming_enc : false;
				$hdZendEmail->try_imap_connect( $obj_data['incoming_server'], (int) $obj_data['incoming_port'], $ssl );
				$args = array(
					'server_name'          => $obj_data['provider_name'],
					'incoming_imap_server' => $obj_data['incoming_server'],
					'incoming_imap_port'   => $obj_data['incoming_port'],
					'incoming_imap_enc'    => $incoming_enc,
					'outgoing_smtp_server' => $obj_data['outgoing_server'],
					'outgoing_smtp_port'   => $obj_data['outgoing_port'],
					'outgoing_smtp_enc'    => $outgoing_enc,
				);
				if ( ! empty( $obj_data['serverid'] ) ) {
					$where     = array( 'id' => $obj_data['serverid'] );
					$server_id = $rt_imap_server_model->update_server( $args, $where );
					if ( $server_id ) {
						$server_id = $obj_data['serverid'];
					}
				} else {
					$server_id = $rt_imap_server_model->add_server( $args );
				}
				if ( ! empty( $server_id ) ) {
					$result['server_id'] = $server_id;
					$result['status']    = true;
				} else {
					$result['error'] = 'Error: problem occurs while adding/updating server';
				}
			} catch ( Exception $e ) {
				$result['error'] = 'Caught exception: ' . $e->getMessage();
			}

			return $result;
		}

		/**
		 * create new mailbox
		 *
		 * @param $obj_data
		 *
		 * @return bool
		 */
		function rtmailbox_add_mailbox( $obj_data ) {
			global $rt_mail_settings;

			$result           = array();
			$result['status'] = false;

			if ( ! empty( $obj_data['email'] ) && ! empty( $obj_data['password'] ) && ! empty( $obj_data['provider'] ) ) {

				$email = trim( $obj_data['email'] );

				$available_email = rtmb_get_module_mailbox_email( $email, $obj_data['module'] );

				if ( empty ( $available_email ) ) {

					$password = $obj_data['password'];

					$Imap_folder = 'INBOX';
					if ( $obj_data['provider'] == 2 || $obj_data['provider'] == 3 ) {
						$Imap_folder = 'Inbox';
					}

					$email_data = array(
						'email'        => $email,
						'mail_folders' => $Imap_folder,
					);

					$email_type  = 'imap';
					$module      = $obj_data['module'];
					$imap_server = $obj_data['provider'];
					$hdZendEmail = new Rt_Zend_Mail();

					try {
						if ( ! $hdZendEmail->try_imap_login( $email, rtmb_encrypt_decrypt( $password ), $email_type, $imap_server ) ) {
							$result['error'] = 'Error: login failed. Please enter correct credential or enable IMAP in your mailbox';
						} else {
							if ( ! empty( $obj_data['mailboxid'] ) ) {
								$mailboxid = $rt_mail_settings->update_user_google_ac( rtmb_encrypt_decrypt( $password ), $email, '', '', $email_type, $imap_server, $module, $obj_data['mailboxid'] );
								if ( $mailboxid ) {
									$mailboxid = $obj_data['mailboxid'];
								}
							} else {
								$mailboxid = $rt_mail_settings->add_user_google_ac( rtmb_encrypt_decrypt( $password ), $email, maybe_serialize( $email_data ), '', $email_type, $imap_server, $module );
								do_action( 'rt_mailbox_add_mailbox', $email, $module );
							}
							if ( ! empty( $mailboxid ) ) {
								ob_start();
								$this->render_list_mailbox_page( $module, $mailboxid );
								$result['html_list'] = ob_get_clean();
								$result['moduleid']  = $mailboxid;
								$result['status']    = true;
							} else {
								if ( ! empty( $obj_data['mailboxid'] ) ) {
									$result['error'] = 'Error: Mailbox configured not updated';
								} else {
									$result['error'] = 'Error: Mailbox not configured';
								}
							}
						}
					} catch ( Exception $e ) {
						$result['error'] = 'Caught exception: ' . $e->getMessage();
					}
				} else {
					$result['error'] = 'Error: This Mail is already added in Mailbox';
				}
			} else {
				$result['error'] = 'Error: Required mailbox field missing.';
			}

			return $result;
		}

		/**
		 * mailbox folder ui
		 *
		 * @param $module
		 * @param string $mailboxid
		 */
		function rtmailbox_mailbox_folder_ui( $module, $mailboxid = '' ) {
			global $rt_mail_settings;

			$args = array( 'module' => $module );
			if ( ! empty( $mailboxid ) ) {
				$args['id'] = $mailboxid;
			}
			$mailboxes = $rt_mail_settings->get_user_google_ac( $args );
			?> <h4>Select Mailbox Folders</h4>
			<?php
			if ( isset( $mailboxes ) && ! empty( $mailboxes ) ) {
				foreach ( $mailboxes as $mailbox ) {
					$mailbox->email_data = unserialize( $mailbox->email_data );
					$email               = filter_var( $mailbox->email_data['email'], FILTER_SANITIZE_EMAIL );
					$email_type          = $mailbox->type;
					$imap_server         = $mailbox->imap_server;
					$mail_folders        = ( isset( $mailbox->email_data['mail_folders'] ) ) ? $mailbox->email_data['mail_folders'] : '';
					$mail_folders        = array_filter( explode( ',', $mail_folders ) );
					$token               = $mailbox->outh_token;

					$all_folders      = null;
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
						$login_successful = false;
						echo '<p class="description">' . esc_html( $e->getMessage() ) . '</p>';
					}

					if ( $login_successful ) {
						?>
						<form id="rtmailbox-imap-folder-form" method="post">
							<input id="rtmailbox-module" name="rtmailbox[module]" value="<?php echo $module; ?>"
							       type="hidden">
							<input id="rtmailbox-email" name="rtmailbox[email]" value="<?php echo $email; ?>"
							       type="hidden">
							<?php wp_nonce_field( 'rtmailbox_folder_update' );
							do_action( 'rt_mailbox_folder_view_before' ); ?>
							<div class="rtmailbox-row">
								<label for=""><?php _e( 'Select Folder' ); ?></label>

								<div class="mailbox-folder-list">
									<?php $hdZendEmail->render_folders_checkbox( $all_folders, $element_name = 'mail_folders[' . esc_attr( $email ) . ']', $values = $mail_folders, $data_str = 'data-email-id=' . $mailbox->id ); ?>
								</div>
							</div>
							<?php do_action( 'rt_mailbox_assignee_ui', $mailbox->email_data, $module ); ?>
							<?php do_action( 'rt_mailbox_custom_ui', $mailbox->email_data, $module ); ?>
							<div class="clear"></div>
							<div class="rtmailbox-row">
								<label></label>
								<input id="rtmailbox-action" name="rtmailbox[action]" value="rtmailbox_folder_update"
								       type="hidden">
								<input id="rtmailbox-save" data-mailboxid="<?php echo $mailboxid; ?>"
								       name="rtmailbox[save]" class="button" value="Save" type="button">
							</div>
							<?php do_action( 'rt_mailbox_folder_view_after' ); ?>
						</form>
						<?php
					}
				}
			} else {
				echo '<p>Mailbox not found.</p>';
			}
		}

		/**
		 * Ajax method to save mailbox folder
		 */
		function rtmailbox_imap_folder_save_callback() {

			global $rt_mail_settings;

			$result           = array();
			$result['status'] = false;

			$obj_data = array();
			parse_str( $_POST['data'], $obj_data );

			if ( ! wp_verify_nonce( $obj_data['_wpnonce'], 'rtmailbox_folder_update' ) ) {
				$result['error'] = 'Security check false';
				die();
			}

			$mail_folders = $obj_data['mail_folders'];
			$user_id = ( $obj_data['allow_user'] ) ? $obj_data['allow_user'] : null;
			$obj_data     = $obj_data['rtmailbox'];
			$email        = $obj_data['email'];
			if ( 'rtmailbox_folder_update' == $obj_data['action'] ) {
				if ( ! is_email( $email ) ) {
					$result['error'] = 'Error: Email not valid';
					echo json_encode( $result );
					die();
				}
				$email_ac = $rt_mail_settings->get_email_acc( $email, $obj_data['module'] );
				if ( empty( $email_ac ) ) {
					$result['error'] = 'Error: Mailbox not found';
					echo json_encode( $result );
					die();
				}

				$email_data = null;
				if ( isset( $mail_folders ) && ! empty( $mail_folders ) && is_array( $mail_folders ) && ! empty( $email_ac ) ) {
					$email_data = maybe_unserialize( $email_ac->email_data );
					if ( empty( $mail_folders[ $email ] ) ) {
						$mail_folders[ $email ] = array();
					}
					$email_data                 = apply_filters( 'rt_mailbox_assignee_save', $email_data, $obj_data );
					$email_data['mail_folders'] = implode( ',', array_filter( $mail_folders[ $email ] ) );
					$rt_mail_settings->update_mail_acl( $obj_data['module'], $email, null, maybe_serialize( $email_data ), null, $user_id );
					$result['status'] = true;
				}
			}
			echo json_encode( $result );
			die();
		}

		/**
		 * mailbox update ui
		 */
		function rtmailbox_mailbox_update_callback() {
			$result           = array();
			$result['status'] = false;
			$dataobj          = $_POST;
			if ( ! empty( $dataobj['email'] ) && is_email( $dataobj['email'] ) && ! empty( $dataobj['module'] ) && ! empty( $dataobj['mailboxid'] ) ) {
				if ( 'rtmailbox_reconfigured' == $dataobj['subaction'] ) {
					ob_start();
					$this->render_add_mailbox_page( $_POST['module'], $_POST['mailboxid'] );
					$result['html']   = ob_get_clean();
					$result['status'] = true;
				} elseif ( 'rtmailbox_update' == $dataobj['subaction'] ) {
					ob_start();
					$this->rtmailbox_mailbox_folder_ui( $_POST['module'], $_POST['mailboxid'] );
					$result['html']   = ob_get_clean();
					$result['status'] = true;
				}
			} else {
				$result['error'] = 'Error: Required field missing.';
			}

			echo json_encode( $result );
			die();
		}

		/**
		 * remove mailbox
		 */
		function rtmailbox_mailbox_remove_callback() {
			$result           = array();
			$result['status'] = false;
			$dataobj          = $_POST;
			if ( ! empty( $dataobj['email'] ) && is_email( $dataobj['email'] ) && ! empty( $dataobj['module'] ) && ! empty( $dataobj['mailboxid'] ) ) {
				global $rt_mail_crons, $rt_mail_settings;
				$status = $rt_mail_settings->delete_user_google_ac( $dataobj['email'], $dataobj['module'], $dataobj['mailboxid'] );
				if ( $status ) {
					do_action( 'rt_mailbox_remove_mailbox', $dataobj['email'], $dataobj['module'] );
					$result['status']   = true;
					$result['moduleid'] = $dataobj['mailboxid'];
				} else {
					$result['error'] = 'Error: Mailbox not deleted.';
				}
			} else {
				$result['error'] = 'Error: Required field missing.';
			}
			echo json_encode( $result );
			die();
		}

		/**
		 * mailbox add event
		 */
		function rtmailbox_mailbox_add_callback() {
			$result           = array();
			$result['status'] = false;
			ob_start();
			$this->render_add_mailbox_page( $_POST['module'] );
			$result['html']   = ob_get_clean();
			$result['status'] = true;
			echo json_encode( $result );
			die();
		}


		/**
		 * Get mailbox list.
		 */
		public function rtmailbox_list_all( $rtbiz_modules ) {
			global $rt_mail_settings;
			$mailbox_list = $rt_mail_settings->get_all_mailbox();

			if ( isset( $mailbox_list ) && ! empty( $mailbox_list ) ) {
				?>
				<table id="rtmailbox-container" class="form-table">
					<tbody>
					<tr>
						<th>Mail Account</th>
						<th>Module</th>
						<th>Setting Page</th>
					</tr>
					<?php
					foreach ( $mailbox_list as $mailbox ) {
						$mailbox->email_data = unserialize( $mailbox->email_data );
						$email               = filter_var( $mailbox->email_data['email'], FILTER_SANITIZE_EMAIL );
						?>
						<tr>
							<td>
								<strong>
									<?php if ( isset( $mailbox->email_data['name'] ) ) {
										echo $mailbox->email_data['name'] . '<br />';
									} ?>
									<a href='mailto:<?php echo $email ?>'>
										<?php echo $email ?>
									</a>
								</strong>
							</td>
							<td>
								<?php if ( isset( $rtbiz_modules[ $mailbox->module ]['label'] ) ) {
									echo $rtbiz_modules[ $mailbox->module ]['label'];
								} else {
									echo $mailbox->module;
								} ?>
							</td>
							<td>
								<?php if ( isset( $rtbiz_modules[ $mailbox->module ]['setting_page_url'] ) ) { ?>
									<a class='button show-mailbox-settings'
									   href="<?php echo $rtbiz_modules[ $mailbox->module ]['setting_page_url']; ?>"><?php echo __( 'Settings' ); ?></a>
								<?php } else { ?>
									<span>Not found.</span>
								<?php } ?>
							</td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
				<?php
			} else { ?>
				<div>No Mailbox Found!</div>
			<?php }
		}

	}
}
