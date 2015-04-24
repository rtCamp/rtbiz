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

		/**
		 * @var $page_slug - Page slug for Mailbox Page
		 */
		static $page_slug = 'mailbox';

		static $page_name = 'Mailbox';

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
		 * @var $parent_page_slug -
		 *
		 * Page slug under which the Mailbox page is to be shown.
		 * If null / empty then an individual Menu Page will be added
		 */
		var $parent_page_slug;

		/**
		 * @var $page_cap
		 *
		 * Capability for Mailbox setup Page;
		 * if not passed, default cap will be 'manage_options'
		 */
		var $page_cap;

		/**
		 * @var $pageflag
		 *
		 * Flag for register page for mailbox or not
		 */
		var $pageflag;

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

		var $module = '';

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
		function __construct( $plugin_path_for_deactivate_cron, $module='', $admin_menu = false, $parent_slug = '', $cap = 'manage_options' ) {
			$this->pageflag = $admin_menu;
			if ( $this->pageflag ) {
				$this->parent_page_slug = $parent_slug;
				$this->page_cap = $cap;
				add_action( 'admin_menu', array( $this, 'register_attribute_menu' ) );
			}
			$this->module = $module;

			$this->default_imap_servers = array(
				array(
					'server_name' => 'Gmail/Google',
					'incoming_imap_server' => 'imap.gmail.com',
					'outgoing_smtp_server' => 'smtp.gmail.com',
				),
				array(
					'server_name' => 'Outlook',
					'incoming_imap_server' => 'imap-mail.outlook.com',
					'outgoing_smtp_server' => 'smtp-mail.outlook.com',
				),
				array(
					'server_name' => 'Yahoo',
					'incoming_imap_server' => 'imap.mail.yahoo.com',
					'outgoing_smtp_server' => 'smtp.mail.yahoo.com',
				),
			);

			$this->auto_loader();
			$this->db_upgrade();
			$this->inti_global();

			$this->init_rt_wp_mail_cron( $plugin_path_for_deactivate_cron );

			add_action( 'init', array( $this, 'rtmailbox_ajax' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ), 999 );
		}

		/**
		 * Register rt-mailbox setup page
		 */
		function register_attribute_menu() {
			if ( ! empty( $this->parent_page_slug ) ) {
				add_submenu_page( $this->parent_page_slug, __( ucfirst( self::$page_name ) ), __( ucfirst( self::$page_name ) ), $this->page_cap, self::$page_slug, array( $this, 'render_mailbox_setting_page' ) );
			} else {
				add_menu_page( __( ucfirst( self::$page_name ) ), __( ucfirst( self::$page_name ) ), $this->page_cap, self::$page_slug, array( $this, 'render_mailbox_setting_page' ) );
			}
		}

		/**
		 * Register AutoLoader for MailBox
		 *
		 * load to all classes
		 */
		function auto_loader() {
			include_once  plugin_dir_path( __FILE__ ) . 'vendor/'  . 'MailLib/zendAutoload.php';
			include_once trailingslashit( dirname( __FILE__ ) ) . 'helper/'.'rt-mailbox-functions.php';
			self::$auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'model/' );
			self::$auto_loader  = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'helper/' );
			self::$auto_loader  = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'settings/' );
			self::$auto_loader  = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'vendor/' );
			self::$auto_loader  = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) );
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
		function inti_global(  ){
			global $rt_mailbox_help;
			$rt_mailbox_help = new Rt_Mailbox_Help();

			global $rt_imap_server_model, $rt_mail_accounts_model, $rt_mail_message_model, $rt_outbound_model;
			$rt_imap_server_model           = new Rt_IMAP_Server_Model();
			$rt_mail_accounts_model         = new Rt_Mail_Accounts_Model();
			$rt_mail_message_model          = new Rt_Mail_Message_Model();
			$rt_outbound_model        = new Rt_Mail_Outbound_Model();

			global $rt_setting_inbound_email, $rt_setting_imap_server, $rt_mail_settings;
			$rt_setting_inbound_email   = new RT_Setting_Inbound_Email( );
			$rt_setting_imap_server     = new RT_Setting_Imap_Server();
			$rt_mail_settings           = new Rt_Mail_Settings();
		}

		/**
		 * Ajx request for rtmailbox
		 */
		function rtmailbox_ajax(){
			add_action( 'wp_ajax_rtmailbox_imap_connect', array( $this, 'rtmailbox_imap_connect_callback' ) );
			add_action( 'wp_ajax_rtmailbox_folder_update', array( $this, 'rtmailbox_imap_folder_save_callback' ) );
		}

		/**
		 * Register css & js for rt-mailbox
		 */
		function enqueue_styles_scripts(){
			wp_enqueue_style( 'mailbox-setting-css',  plugin_dir_url( __FILE__ ).'assets/css/rt-mailbox.css' );
			wp_enqueue_script( 'mailbox-setting-js', plugin_dir_url( __FILE__ ).'assets/js/rt-mailbox.js', array( 'jquery' ) , '', true );
		}

		/**
		 * Setup WordPress cron for mail import
		 *
		 * @param $plugin_path_for_deactivate_cron
		 */
		function init_rt_wp_mail_cron( $plugin_path_for_deactivate_cron ){
			global $rt_mail_crons;
			$rt_mail_crons = new Rt_Mail_Cron( $plugin_path_for_deactivate_cron, $this->module );
		}

		/**
		 * render mailbox setup page
		 */
		function render_mailbox_setting_page( ){

			global $rt_imap_server_model;

			$default_servers = $rt_imap_server_model->get_all_servers();
			$rtmailbox_default_server = wp_list_pluck( $this->default_imap_servers, 'server_name' );
			?>
			<div id="rtmailbox-wrap" class="wrap">
				<form id="rtmailbox-imap-connect-form" method="post">
					<input id="rtmailbox-module" name="rtmailbox[module]" value="<?php echo $this->module; ?>" type="hidden">
					<input id="rtmailbox-incoming_ssl_port" name="rtmailbox[incoming_ssl_port]" value="<?php echo $this->incoming_ssl_port; ?>" type="hidden">
					<input id="rtmailbox-incoming_tls_port" name="rtmailbox[incoming_tls_port]" value="<?php echo $this->incoming_tls_port; ?>" type="hidden">
					<input id="rtmailbox-outgoing_ssl_port" name="rtmailbox[outgoing_ssl_port]" value="<?php echo $this->outgoing_ssl_port; ?>" type="hidden">
					<input id="rtmailbox-outgoing_tls_port" name="rtmailbox[outgoing_tls_port]" value="<?php echo $this->outgoing_tls_port; ?>" type="hidden">

					<?php wp_nonce_field( 'rtmailbox_connect_imap' );
					do_action( 'rt_mailbox_randed_view_before' ); ?>

					<!--<div class="rtmailbox-row">
						<label for=""><?php /*_e( 'Mailbox Reading' ); */?></label>
						<input data-toggle="switch" title="On" id="rtmailbox-enable" name="rtmailbox[enable]" value="1" type="checkbox">
					</div>-->
					<div class="rtmailbox-row">
						<label><?php _e( 'Account Type: IMAP' ); ?></label>
					</div>
					<div class="rtmailbox-row">
						<label for="rtmailbox-email"><?php _e( 'Email' ); ?> </label>
						<input autocomplete="off" id="rtmailbox-email" name="rtmailbox[email]" placeholder="Email" value="" type="text">
					</div>
					<div class="rtmailbox-row">
						<label for="rtmailbox-password"><?php _e( 'Password' ); ?> </label>
						<input autocomplete="off" id="rtmailbox-password" name="rtmailbox[password]" placeholder="Password" value="" type="password">
					</div>
					<div class="rtmailbox-row">
						<label for=""><?php _e( 'Provider' ); ?></label>
						<ul class="rtmailbox_provider_list">
							<?php foreach ( $default_servers as $server ){
								if ( in_array( $server->server_name, $rtmailbox_default_server ) ) {
									$seleted = ( 'Gmail/Google' == $server->server_name )? 'checked' : ''   ?>
									<li>
										<input title="<?php echo $server->server_name; ?>" class="rtmailbox_provider" id="rtmailbox-provider-<?php echo $server->id; ?>" name="rtmailbox[provider]" <?php echo $seleted; ?> value="<?php echo $server->id; ?>" type="radio">
										<label for="rtmailbox-provider-<?php echo $server->id; ?>"><?php echo $server->server_name; ?></label>
									</li>
								<?php }
							}?>
							<li>
								<input title="<?php _e( 'custom' ) ?>" class="rtmailbox_provider" id="rtmailbox-provider-<?php _e( 'custom' ) ?>" name="rtmailbox[provider]" value="<?php _e( 'custom' ) ?>" type="radio">
								<label for="rtmailbox-provider-<?php _e( 'custom' ) ?>"><?php _e( 'custom' ) ?></label>
							</li>
						</ul>
					</div>
					<div id="rtmailbox-imap-server-container">
						<div class="rtmailbox-row">
							<label for="rtmailbox-provider_name"><?php _e( 'Provider Name' ); ?></label>
							<input autocomplete="off" id="rtmailbox-provider_name" name="rtmailbox[provider_name]" placeholder="Provider name" value="" type="text">
						</div>

						<div class="rtmailbox-row">
							<label for="rtmailbox-incoming_server"><?php _e( 'Incoming Server' ); ?></label>
							<input autocomplete="off" id="rtmailbox-incoming_server" name="rtmailbox[incoming_server]" placeholder="imap.example.com" value="" type="text">
						</div>
						<div class="rtmailbox-row">
							<label for="rtmailbox-incoming_port"><?php _e( 'Port' ); ?></label>
							<input autocomplete="off" id="rtmailbox-incoming_port" name="rtmailbox[incoming_port]" value="<?php echo $this->incoming_ssl_port; ?>" type="text">
							<input id="rtmailbox-incoming_ssl" name="rtmailbox[incoming_ssl]" checked="checked" value="enable" type="checkbox">
							<label for="rtmailbox-incoming_ssl"><?php _e( 'Use SSL' ); ?></label>
						</div>

						<div class="rtmailbox-row">
							<label for="rtmailbox-outgoing_server"><?php _e( 'Outgoing Server' ); ?></label>
							<input autocomplete="off" id="rtmailbox-outgoing_server" name="rtmailbox[outgoing_server]" placeholder="smtp.example.com" value="" type="text">
						</div>
						<div class="rtmailbox-row">
							<label for="rtmailbox-outgoing_port"><?php _e( 'Port' ); ?></label>
							<input autocomplete="off" id="rtmailbox-outgoing_port" name="rtmailbox[outgoing_port]" value="<?php echo $this->outgoing_tls_port; ?>" type="text">
							<input id="rtmailbox-outgoing_ssl" name="rtmailbox[outgoing_ssl]" value="enable" type="checkbox">
							<label for="rtmailbox-outgoing_ssl"><?php _e( 'Use SSL' ); ?></label>
						</div>
					</div>
					<div class="rtmailbox-row">
						<label></label>
						<input id="rtmailbox-action" name="rtmailbox[action]" value="rtmailbox_connect_imap" type="hidden">
						<input id="rtmailbox-connect" name="rtmailbox[connect]" class="button button-primary" value="Connect" type="submit">
					</div>

					<?php do_action( 'rt_mailbox_randed_view_after' ); ?>

				</form>
			</div><?php
		}

		/**
		 * Ajax method to create mailbox
		 */
		function rtmailbox_imap_connect_callback(){

			$result = array();
			$result['status'] = false;

			$obj_data = array();
			parse_str( $_POST['data'], $obj_data );

			if ( ! wp_verify_nonce( $obj_data['_wpnonce'], 'rtmailbox_connect_imap' ) ) {
				$result['error'] = 'Security check false';
				die();
			}
			$obj_data = $obj_data['rtmailbox'];
			if ( 'rtmailbox_connect_imap' == $obj_data['action'] ){
				if ( 'custom'  == $obj_data['provider'] ) {
					$response= $this->rtmailbox_create_imap_server( $obj_data );
					if ( is_array( $response ) && true == $response['status'] ){
						$obj_data['provider'] = $response['server_id'];
					}else{
						$result['error'] = $response['error'];
						die();
					}
				}
				$response = $this->rtmailbox_add_mailbox( $obj_data );
				if ( is_array( $response ) && true == $response['status'] ){
					$result['html'] = $response['html_imap_folder'];
					$result['status'] = true;
				}else{
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
		function rtmailbox_create_imap_server( $obj_data ){
			global $rt_imap_server_model;

			$result = array();
			$result['status'] = false;

			$incoming_enc = ( isset( $obj_data['incoming_ssl'] ) && $obj_data['incoming_ssl'] == 'enable' )? 'ssl' :'tls';
			$outgoing_enc = ( isset( $obj_data['outgoing_ssl'] ) && $obj_data['outgoing_ssl'] == 'enable' )? 'ssl' :'tls';

			// if port empty set default
			if ( ! isset( $obj_data['incoming_port'] ) || empty( $obj_data['incoming_port'] ) ){
				$obj_data['incoming_port'] = ( 'ssl' == $incoming_enc ) ? $this->incoming_ssl_port : $this->incoming_tls_port ;
			}
			if ( ! isset( $obj_data['outgoing_port'] ) || empty( $obj_data['outgoing_port'] ) ){
				$obj_data['outgoing_port'] = ( 'ssl' == $outgoing_enc ) ? $this->outgoing_ssl_port : $this->outgoing_tls_port ;
			}

			try {
				$hdZendEmail = new Rt_Zend_Mail();
				$ssl  = ( isset( $incoming_enc ) && ! is_null( $incoming_enc ) ) ? $incoming_enc : false;
				$hdZendEmail->try_imap_connect( $obj_data['incoming_server'], (int)$obj_data['incoming_port'], $ssl );
				$args = array(
					'server_name'          => $obj_data['provider_name'],
					'incoming_imap_server' => $obj_data['incoming_server'],
					'incoming_imap_port'   => $obj_data['incoming_port'],
					'incoming_imap_enc'    => $incoming_enc,
					'outgoing_smtp_server' => $obj_data['outgoing_server'],
					'outgoing_smtp_port'   => $obj_data['outgoing_port'],
					'outgoing_smtp_enc'    => $outgoing_enc,
				);
				$server_id =  $rt_imap_server_model->add_server( $args );
				if ( empty( $server_id ) ){
					$result['server_id'] = $server_id;
					$result['status'] = true;
				}else{
					$result['error'] = "Error: problem occurs while adding server";
				}
			} catch (Exception $e) {
				$result['error'] = 'Caught exception: ' .  $e->getMessage();
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
		function rtmailbox_add_mailbox( $obj_data ){
			global $rt_mail_settings;

			$result = array();
			$result['status'] = false;

			if ( ! empty( $obj_data['email'] ) && ! empty( $obj_data['password'] ) && ! empty( $obj_data['provider'] ) ){
				$email       = $obj_data['email'];
				$password    = $obj_data['password'];
				$email_data  = array(
					'email' => $email,
					'mail_folders' => 'INBOX',
				);
				$email_type = 'imap';
				$module = $obj_data['module'];
				$imap_server = $obj_data['provider'];
				$hdZendEmail = new Rt_Zend_Mail();
				try {
					if ( ! $hdZendEmail->try_imap_login( $email, rtmb_encrypt_decrypt( $password ), $email_type, $imap_server ) ) {
						$result['error'] = "Error: login failed. Please enter correct credential or enable IMAP in your mailbox";
					} else {
						$rt_mail_settings->add_user_google_ac( rtmb_encrypt_decrypt( $password ), $email, maybe_serialize( $email_data ), '', $email_type, $imap_server, $module );
						ob_start();
						$this->rtmailbox_mailbox_folder_ui( $module );
						$result['html_imap_folder'] = ob_get_clean();
						$result['status'] = true;
					}
				} catch (Exception $e) {
					$result['error'] = 'Caught exception: ' .  $e->getMessage();
				}
			}
			return $result;
		}

		function rtmailbox_mailbox_folder_ui( ){
			global $rt_mail_settings;

			$mailboxes = $rt_mail_settings->get_user_google_ac( array( 'module' => $this->module ) );
			if ( isset( $mailboxes ) && ! empty( $mailboxes ) ){
				foreach ( $mailboxes as $mailbox ){
					$mailbox->email_data = unserialize( $mailbox->email_data );
					$email          = filter_var( $mailbox->email_data['email'], FILTER_SANITIZE_EMAIL );
					$email_type     = $mailbox->type;
					$imap_server    = $mailbox->imap_server;
					$mail_folders   = ( isset( $mailbox->email_data['mail_folders'] ) ) ? $mailbox->email_data['mail_folders'] : '';
					$mail_folders   = array_filter( explode( ',', $mail_folders ) );
					$token = $mailbox->outh_token;

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
						$login_successful = false;
						echo '<p class="description">' . esc_html( $e->getMessage() ) . '</p>';
					}

					if ( $login_successful ){
						// user photo
						if ( isset( $mailbox->email_data['picture'] ) ){
							$img          = filter_var( $mailbox->email_data['picture'], FILTER_VALIDATE_URL );
							$personMarkup = "<img src='$img?sz=30'>";
						} else {
							$personMarkup = get_avatar( 'dipesh.kakadiya111@gmail.com', 30 );
						}
						?>
						<form id="rtmailbox-imap-folder-form" method="post">
							<input id="rtmailbox-module" name="rtmailbox[module]" value="<?php echo $this->module; ?>" type="hidden">
							<input id="rtmailbox-email" name="rtmailbox[email]" value="<?php echo $email; ?>" type="hidden">
							<?php wp_nonce_field( 'rtmailbox_folder_update' );
							do_action( 'rt_mailbox_folder_view_before' ); ?>
							<div class="rtmailbox-row">
								<label for=""><?php _e( 'Connected Email' ); ?></label>
								<a href="mailto:<?php echo $email; ?>"><?php echo $personMarkup; ?></a>
							</div>
							<div class="rtmailbox-row">
								<label for=""><?php _e( 'Select Folder' ); ?></label>
								<div class="mailbox-folder-list">
									<?php $hdZendEmail->render_folders_checkbox( $all_folders, $element_name = 'mail_folders[' . esc_attr( $email ) . ']', $values = $mail_folders, $data_str = 'data-email-id=' . $mailbox->id ); ?>
								</div>
							</div>
							<div class="rtmailbox-row">
								<label></label>
								<input id="rtmailbox-action" name="rtmailbox[action]" value="rtmailbox_folder_update" type="hidden">
								<input id="rtmailbox-save" name="rtmailbox[save]" class="button button-primary" value="Save" type="submit">
							</div>
							<?php do_action( 'rt_mailbox_folder_view_after' ); ?>
						</form>
						<?php wp_nonce_field( 'rtmailbox_connect_imap' );
						do_action( 'rt_mailbox_randed_view_before' ); ?>
						<?php
					}
				}
			}
		}

		/**
		 * Ajax method to save mailbox folder
		 */
		function rtmailbox_imap_folder_save_callback(){

			global $rt_mail_settings;

			$result = array();
			$result['status'] = false;

			$obj_data = array();
			parse_str( $_POST['data'], $obj_data );

			if ( ! wp_verify_nonce( $obj_data['_wpnonce'], 'rtmailbox_folder_update' ) ) {
				$result['error'] = 'Security check false';
				die();
			}

			$mail_folders = $obj_data['mail_folders'];
			$obj_data = $obj_data['rtmailbox'];
			$email = $obj_data['email'];
			if ( 'rtmailbox_folder_update' == $obj_data['action'] ){
				if ( ! is_email( $email ) ){
					echo json_encode( 'Email not valid' );
					die();
				}
				$email_ac   = $rt_mail_settings->get_email_acc( $email, $obj_data['module'] );
				$email_data = null;
				if ( isset( $mail_folders ) && ! empty( $mail_folders ) && is_array( $mail_folders ) && ! empty( $email_ac ) ) {
					$email_data = maybe_unserialize( $email_ac->email_data );
					if ( empty ( $mail_folders[ $email ]  ) ) {
						$mail_folders[ $email ] = array();
					}
					$email_data['mail_folders'] = implode( ',', array_filter( $mail_folders[ $email ] ) );
					$rt_mail_settings->update_mail_acl( $email, null, maybe_serialize( $email_data ) );
					$result['message'] = 'Mailbox imap folder successfully updated';
					$result['status'] = true;
				}
			}
			echo json_encode( $_REQUEST );
			die();
		}

		/**
		 * render mailbox setup page
		 */
		function render_mailbox_setting_page_ui(){
			$title_ele = $this->pageflag ? 'h2' : 'h3';?>
			<div class="wrap">
			<?php
			echo '<' . $title_ele . '>' . __( 'Mailbox Setting' ) . '</' . $title_ele . '>';
			if ( ! isset( $_REQUEST['type'] ) ){
				$_REQUEST['type'] = 'mailbox'; // remove when csv is active
			}
			$this->mailbox_tabs( $_REQUEST['tab'] );

			if ( isset( $_REQUEST['tab'] ) && 'imap' == $_REQUEST['tab'] ) {
				echo $this->imap_view();
			} else if ( isset( $_REQUEST['tab'] ) && self::$page_slug == $_REQUEST['tab'] ){
				$module_key = rt_biz_sanitize_module_key( RT_BIZ_TEXT_DOMAIN );
				$this->mailbox_view( $module_key );
			}
			do_action( 'rt_mailbox_randed_view_after' );
			?> </div> <?php

		}

		/**
		 * Render tab for mailbox setting ui
		 *
		 * @param string $active_tab
		 */
		function mailbox_tabs( $active_tab = 'mailbox' ){
			// Declare local variables
			$tabs_html    = '';
			$idle_class   = 'nav-tab';
			$active_class = 'nav-tab nav-tab-active';

			// Setup core admin tabs
			$tabs = array(
				array(
					'href' => 'mailbox',
					'name' => __( ucfirst( self::$page_name ), self::$page_name ),
					'slug' => 'mailbox',
				), array(
					'href' => 'imap',
					'name' => __( 'IMAP', self::$page_name ),
					'slug' => 'imap',
				),
			);
			$filterd_tab = apply_filters( 'rt_mailbox_add_tab', $tabs );

			if ( ! empty( $filterd_tab ) ){
				if ( $this->pageflag ) {

					$idle_class   = 'nav-tab';
					$active_class = 'nav-tab nav-tab-active';

					$tabs_html .= '<div class="nav-tab-wrapper" >';
					// Loop through tabs and build navigation
					foreach ( array_values( $filterd_tab ) as $tab_data ) {
						$is_current = (bool) ( $tab_data['slug'] == $active_tab );
						$tab_class  = $is_current ? $active_class : $idle_class;

						if ( isset( $tab_data['class'] ) && is_array( $tab_data['class'] ) ) {
							$tab_class .= ' ' . implode( ' ', $tab_data['class'] );
						}
						$tabs_html .= '<a href="' . $tab_data['href'] . '" class="' . $tab_class . '">' . $tab_data['name'] . '</a>';
					}
					$tabs_html .= '</div>';
				} else {
					$idle_class   = '';
					$active_class = 'current';
					$tabs_html .= '<div class="sub-nav-tab-wrapper" ><ul class="subsubsub">';
					foreach ( array_values( $filterd_tab ) as $i => $tab_data ) {
						$is_current = (bool) ( $tab_data['slug'] == $active_tab );
						$tab_class  = $is_current ? $active_class : $idle_class;

						if ( isset( $tab_data['class'] ) && is_array( $tab_data['class'] ) ) {
							$tab_class .= ' ' . implode( ' ', $tab_data['class'] );
						}
						$separator = $i != ( count( $filterd_tab ) - 1 ) ? ' | ' : '';
						$tabs_html .= '<li class="' . $tab_data['name'] . '"><a href="' . $tab_data['href'] . '" class="' . $tab_class . '">' . $tab_data['name'] . '</a>'. $separator .'</li>';
					}
					$tabs_html .= '</ul></div>';
				}
			}

			// Output the tabs
			echo $tabs_html;
		}

		/**
		 * @param $module
		 */
		function mailbox_view( $module ){
			global $rt_setting_inbound_email;
			do_action( 'rt_mailbox_randed_mailbox_view_before' ); ?>
			<fieldset class="tab-body-wrapper">
					<?php $rt_setting_inbound_email->rt_reply_by_email_view( null, null, $module ); ?>
			</fieldset> <?php
			do_action( 'rt_mailbox_randed_mailbox_view_after' );
		}

		function imap_view(){
			global $rt_setting_imap_server;
			do_action( 'rt_mailbox_randed_imap_view_before' ); ?>
			<div class="imap_servers">
				<h3><?php echo __( 'Available IMAP Servers:' ); ?></h3>
				<?php $rt_setting_imap_server->rt_imap_servers( null, null ); ?>
				<input class="button button-primary" type="submit" value="Save">
			</div> <?php
			do_action( 'rt_mailbox_randed_imap_view_after' );
		}
	}
}
