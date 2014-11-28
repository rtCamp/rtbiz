<?php
/**
 * User: Utkarsh
 * Date: 26/11/14
 * Time: 6:52 PM
 */
if ( ! class_exists( 'Rt_Mailbox' ) ) {

	class Rt_Mailbox {

		var $auto_loader;
		/**
		 * @var $module_id - unique module id for which this class is called. This can be either plugin slug or plugin name or any unique identifier that will be used to know which plugin or module is calling the library class. And accordingly that can be mapped to attributes.
		 */
		var $module_name;

		/**
		 * @var $parent_page_slug - Page slug under which the attributes page is to be shown. If null / empty then an individual Menu Page will be added
		 */
		var $parent_page_slug;

		/**
		 * @var $page_slug - Page slug for Attributes Page
		 */
		var $page_slug;

		/**
		 * @var $post_type - If any post type passed, only attributes for those post type will be listed on the page.
		 */
		var $post_type;

		/**
		 * @var $page_cap - Capability for Attributes Admin Page; if not passed, default cap will be 'manage_options'
		 */
		var $page_cap;
		public $modules= array();

		function __construct($module = array()) {
			$this->auto_loader();
			$this->db_upgrade();
			$this->modules = $module;
		}

		/**
		 * Register AutoLoader for MailBox
		 */
		function auto_loader() {
			include_once  plugin_dir_path( __FILE__ ) . 'vendor/'  . 'MailLib/zendAutoload.php';
			$this->auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'model/' );
			$this->auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'helper/' );
			$this->auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'template/' );
			$this->auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'vendor/' );
			$this->auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) );
		}

		function register_attribute_menu() {
			if ( ! empty( $this->parent_page_slug ) ) {
				add_submenu_page( $this->parent_page_slug, __( 'MailBox' ), __( 'MailBox' ), $this->page_cap, $this->page_slug, array( $this, 'render_mailbox_setting_page' ) );
			} else {
				add_menu_page( __( 'MailBox' ), __( 'MailBox' ), $this->page_cap, $this->page_slug, array( $this, 'render_mailbox_setting_page' ) );
			}
		}

		function render_mailbox_setting_page(){
			?>
			<h1> Mailbox Setting </h1>
		<?php
			wp_enqueue_style( 'mailbox-setting-css',  plugin_dir_url( __FILE__ ).'/assets/css/rt-mailbox.css');

			$this->mailbox_tabs();
			if ( isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'auth'){
				?> <h1> Google-Auth Settings </h1><?php
				$this->google_auth_view();

			}
			else if  ( isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'imap') {
				?> <h1> IMAP Settings  </h1><?php echo $this->imap_view();

			} else if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'Rt-MailBox'){
				?>			<h1> Mailbox Settings </h1>
			<?php
				$this->mailbox_view();
			}
			wp_enqueue_script( 'mailbox-setting-js', plugin_dir_url( __FILE__ ).'assets/js/rt-mailbox.js', '', false, true );

		}

		function add_mailbox_page( $page_slug, $parent_page_slug = '', $page_cap = 'manage_options') {

			$this->page_slug             = $page_slug;
			$this->parent_page_slug      = $parent_page_slug;
			$this->page_cap              = $page_cap;

			add_action( 'admin_menu', array( $this, 'register_attribute_menu' ) );
		}

		function mailbox_tabs( $active_tab = '' ){
			// Declare local variables
			$tabs_html    = '';
			$idle_class   = 'nav-tab';
			$active_class = 'nav-tab nav-tab-active';

			// Setup core admin tabs
			$tabs = array(
				array(
					'href' => get_admin_url( null, add_query_arg( array( 'page' => 'Rt-MailBox' ), 'admin.php' ) ),
					'name' => __( 'Mailbox', 'Rt-MailBox' ),
					'slug' => 'Rt-MailBox',
				), array(
					'href' => get_admin_url( null, add_query_arg( array( 'page' => 'Rt-MailBox&tab=auth' ), 'admin.php' ) ),
					'name' => __( 'Google Auth', 'mailbox' ),
					'slug' => 'Rt-MailBox&tab=auth',
				), array(
					'href' => get_admin_url( null, add_query_arg( array( 'page' => 'Rt-MailBox&tab=imap' ), 'admin.php' ) ),
					'name' => __( 'IMAP', 'mailbox' ),
					'slug' => 'Rt-MailBox&tab=imap',
				)
			);

			// Loop through tabs and build navigation
			foreach ( array_values( $tabs ) as $tab_data ) {
				$is_current = (bool) ( $tab_data['slug'] == $this->get_current_tab() );
				$tab_class  = $is_current ? $active_class : $idle_class;

				if ( isset( $tab_data['class'] ) && is_array( $tab_data['class'] ) ){
					$tab_class .= ' ' . implode( ' ', $tab_data['class'] );
				}

				$tabs_html .= '<a href="' . $tab_data['href'] . '" class="' . $tab_class . '">' . $tab_data['name'] . '</a>';
			}

			// Output the tabs
			echo $tabs_html;
		}

		function db_upgrade() {
			$updateDB = new  RT_DB_Update( RT_LIB_FILE, trailingslashit( dirname( __FILE__ ) ) . 'schema/' );
			$updateDB->db_version_option_name .= '_RT_MAILBOX';
			$updateDB->install_db_version = $updateDB->get_install_db_version();
			$updateDB->do_upgrade();
		}

		static function get_current_tab(){
			return isset( $_REQUEST['page'] ) ? (isset($_REQUEST['tab'])? $_REQUEST['page'] .'&tab='.$_REQUEST['tab']: $_REQUEST['page']) : 'mailbox';
		}

		function mailbox_view(){
			global $rt_setting_inbound_email;
			if (isset($_POST) && !empty($_POST)){
				$rt_setting_inbound_email->save_replay_by_email( );
			}
			?>
			<form method="post" action="">
			<?php
			$rt_setting_inbound_email->rthd_reply_by_email_view( null, null, $this->modules );
			?>				<input class="button button-primary" type="submit" value="Save">
			</form> <?php
		}

		function imap_view(){
			global $rt_setting_imap_server;
			if (isset($_POST) && !empty($_POST)){
				$rt_setting_imap_server->save_imap_servers();
			}
			?>
			<form method="post" action="">
			<?php
			$rt_setting_imap_server->rthd_imap_servers( null, null );

			?>
				<input class="button button-primary" type="submit" value="Save">
			</form>
				<?php
		}

		function google_auth_view(){

			$redirect_url = get_option( 'googleapi_redirecturl' );
			if ( ! $redirect_url ) {
				$redirect_url = admin_url( 'admin.php?page=Rt-MailBox' );
				update_option( 'googleapi_redirecturl', $redirect_url );
			}
			if (isset($_POST) && !empty($_POST)){
				self::save_google_auth();
			}

			$auth = self::get_Google_auth();
			$auth_key="" ;
			$auth_secret="";
			if (!empty($auth)){
				$auth_key=$auth['googleapi_clientid'] ;
				$auth_secret=$auth['googleapi_clientsecret'];
			}
			?>
			<form method="post" action="">
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row">
						<div class="redux_field_th">Google API Client ID</div>
					</th>
					<td>
						<fieldset id="mailbox_settings-googleapi_clientid" class="redux-field-container redux-field redux-field-init redux-container-text " data-id="googleapi_clientid" data-type="text">
							<input type="text" id="googleapi_clientid-text" name="mailbox_settings[googleapi_clientid]" value="<?php echo $auth_key; ?>" class="regular-text ">
							<div class="description field-desc">
								<p class="description"><?php echo sprintf( '<p class="description">%s <a href="https://console.developers.google.com">%s</a>, %s <b>%s</b></p>', __( 'Create an app on' ), __( 'Google API Console' ), __( 'set authorized redirect urls to' ), $redirect_url ) ?></p>
							</div>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<div class="redux_field_th">Google API Client Secret</div>
					</th>
					<td>
						<fieldset id="mailbox_settings-googleapi_clientsecret" class="redux-field-container redux-field redux-field-init redux-container-text " data-id="googleapi_clientsecret" data-type="text"><input type="text" id="googleapi_clientsecret-text" name="mailbox_settings[googleapi_clientsecret]" value="<?php echo $auth_secret; ?>" class="regular-text "></fieldset>
					</td>
				</tr>
				</tbody>
			</table>
			<input type="submit" class="button button-primary" value="Save Changes">
			</form>
			<?php
		}

		public static function save_google_auth(){
			update_option('mailbox-google-auth', $_POST['mailbox_settings']);
		}

		public static function get_Google_auth() {
			$google_auth = get_option( 'mailbox-google-auth' );
			if ( !empty($google_auth) ) {
				return $google_auth;
			}
			return null;
		}

	}
}

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
 * returns all system emails
 * @return array
 */
function rt_get_all_system_emails() {
	global $rt_mail_settings;

	$emails   = array();
	$google_acs = $rt_mail_settings->get_user_google_ac();

	foreach ( $google_acs as $ac ) {
		$ac->email_data = unserialize( $ac->email_data );
		$ac_email          = filter_var( $ac->email_data['email'], FILTER_SANITIZE_EMAIL );
		$emails[] = $ac_email;
	}

	return $emails;
}