<?php
/**
 * User: Utkarsh
 * Date: 26/11/14
 * Time: 6:52 PM
 */
if ( ! class_exists( 'Rt_Mailbox' ) ) {

	class Rt_Mailbox {

		static $page_name = 'mailbox';

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

		static $auto_loader;
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
		public $modules = array();

		function __construct( $cap, $module = array(), $setting_page_parent_slug = '', $plugin_path_for_deactivate_cron ) {
			$this->add_mailbox_page( self::$page_name, $setting_page_parent_slug, $cap );
			$this->auto_loader();
			$this->modules = $module;
			$this->init_rt_mail_models();
			$this->init_mail_functions();
			$this->init_rt_wp_mail_cron( $plugin_path_for_deactivate_cron );
			$this->init_mailbox_help();
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );
			$this->db_upgrade();
		}
		function init_mailbox_help(){
			global $rt_mailbox_help;
			$rt_mailbox_help = new Rt_Mailbox_Help();
		}

		function enqueue_styles_scripts(){
			wp_enqueue_style( 'mailbox-setting-css',  plugin_dir_url( __FILE__ ).'/assets/css/rt-mailbox.css' );
			wp_enqueue_script( 'mailbox-setting-js', plugin_dir_url( __FILE__ ).'assets/js/rt-mailbox.js', '', false, true );
		}

		function init_mail_functions(){
			global $rt_setting_inbound_email, $rt_setting_imap_server, $rt_mail_settings;
			$rt_setting_inbound_email   = new RT_Setting_Inbound_Email();
			$rt_setting_imap_server     = new RT_Setting_Imap_Server();
			$rt_mail_settings           = new Rt_Mail_Settings();
		}

		function init_rt_mail_models() {
			global $rt_imap_server_model, $rt_mail_accounts_model, $rt_mail_message_model, $rt_outbound_model;
			$rt_imap_server_model           = new Rt_IMAP_Server_Model();
			$rt_mail_accounts_model         = new Rt_Mail_Accounts_Model();
			$rt_mail_message_model          = new Rt_Mail_Message_Model();
			$rt_outbound_model        = new Rt_Mail_Outbound_Model();
		}

		function init_rt_wp_mail_cron( $plugin_path_for_deactivate_cron ){
			global $rt_mail_crons;
			$rt_mail_crons = new Rt_Mail_Cron( $plugin_path_for_deactivate_cron );
		}

		/**
		 * Register AutoLoader for MailBox
		 */
		function auto_loader() {
			include_once  plugin_dir_path( __FILE__ ) . 'vendor/'  . 'MailLib/zendAutoload.php';
			self::$auto_loader = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'model/' );
			self::$auto_loader  = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'helper/' );
			self::$auto_loader  = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'settings/' );
			self::$auto_loader  = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) . 'vendor/' );
			self::$auto_loader  = new RT_WP_Autoload( trailingslashit( dirname( __FILE__ ) ) );
			include_once trailingslashit( dirname( __FILE__ ) ) . 'helper/'.'rt-mailbox-functions.php';
		}

		function register_attribute_menu() {
			if ( ! empty( $this->parent_page_slug ) ) {
				add_submenu_page( $this->parent_page_slug, __( ucfirst( self::$page_name ) ), __( ucfirst( self::$page_name ) ), $this->page_cap, $this->page_slug, array( $this, 'render_mailbox_setting_page' ) );
			} else {
				add_menu_page( __( ucfirst( self::$page_name ) ), __( ucfirst( self::$page_name ) ), $this->page_cap, $this->page_slug, array( $this, 'render_mailbox_setting_page' ) );
			}
		}

		function render_mailbox_setting_page(){
			do_action( 'rt_mailbox_randed_view' );
			?>
			<div class="wrap">
			<h2> <?php echo __( 'Mailbox Setting' ); ?></h2>
			<?php
			$this->mailbox_tabs();
			if ( isset( $_REQUEST['tab'] ) && 'imap' == $_REQUEST['tab'] ) {
				echo $this->imap_view();
			} else if ( isset( $_REQUEST['page'] ) && self::$page_name == $_REQUEST['page'] ){
				$this->mailbox_view();
			}
			?> </div> <?php
		}

		function add_mailbox_page( $page_slug, $parent_page_slug = '', $page_cap = 'manage_options' ) {
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
					'href' => get_admin_url( null, add_query_arg( array( 'page' => self::$page_name ), 'admin.php' ) ),
					'name' => __( ucfirst( self::$page_name ), self::$page_name ),
					'slug' => self::$page_name,
				), array(
					'href' => get_admin_url( null, add_query_arg( array( 'page' => self::$page_name.'&tab=imap' ), 'admin.php' ) ),
					'name' => __( 'IMAP', self::$page_name ),
					'slug' => self::$page_name.'&tab=imap',
				),
			);
			$filterd_tab = apply_filters( 'rt_mailbox_add_tab', $tabs );

			if ( ! empty( $filterd_tab ) ){
				$tabs_html .= '<div class="nav-tab-wrapper" >';
				// Loop through tabs and build navigation
				foreach ( array_values( $filterd_tab ) as $tab_data ) {
					$is_current = (bool) ( $tab_data['slug'] == $this->get_current_tab() );
					$tab_class  = $is_current ? $active_class : $idle_class;

					if ( isset( $tab_data['class'] ) && is_array( $tab_data['class'] ) ){
						$tab_class .= ' ' . implode( ' ', $tab_data['class'] );
					}

					$tabs_html .= '<a href="' . $tab_data['href'] . '" class="' . $tab_class . '">' . $tab_data['name'] . '</a>';
				}
				$tabs_html .= '</div>';
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
			return isset( $_REQUEST['page'] ) ? ( isset( $_REQUEST['tab'] )? $_REQUEST['page'] .'&tab='.$_REQUEST['tab']: $_REQUEST['page'] ) : 'mailbox';
		}

		function mailbox_view(){
			global $rt_setting_inbound_email;
			if ( isset( $_POST ) && ! empty( $_POST ) ){
				if ( ! empty( $_POST['mailbox_reply_by_email'] ) ){
					update_option( 'mailbox_reply_by_email', $_POST['mailbox_reply_by_email'] );
				}

				//$rt_setting_inbound_email->save_replay_by_email( );
			}
			?>
			<div class="tab-body-wrapper">
					<?php $rt_setting_inbound_email->rt_reply_by_email_view( null, null, $this->modules ); ?>
			</div> <?php
		}

		function imap_view(){
			global $rt_setting_imap_server;
			?>
			<div class="imap_servers">
			<h3><?php echo __( 'Available IMAP Servers:' ); ?></h3>
			<form method="post" action="">
			<?php
			$rt_setting_imap_server->rt_imap_servers( null, null );

			?>
				<input class="button button-primary" type="submit" value="Save">
			</form>
			</div>
				<?php
		}
		public static function get_enable_by_reply_email(){
			$google_auth = get_option( 'mailbox_reply_by_email' );
			if ( ! empty( $google_auth ) ) {
				return $google_auth;
			}
			return null;
		}

	}
}
