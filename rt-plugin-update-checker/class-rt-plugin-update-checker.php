<?php

/**
 * Description of RT_Plugin_Update_Checker
 * A custom plugin update checker.
 *
 * @author faishal
 */
if ( ! class_exists( 'RT_Plugin_Update_Checker' ) ){
	/**
	 * Class RT_Plugin_Update_Checker
	 */
	class RT_Plugin_Update_Checker
	{

		/**
		 * @var string The URL of the plugin's metadata file.
		 */
		public $metadataUrl = '';
		/**
		 * @var string Plugin filename relative to the plugins directory.
		 */
		public $pluginFile = '';
		/**
		 * @var string Plugin slug.
		 */
		public $slug	= '';
		/**
		 * @var int How often to check for updates (in hours).
		 */
		public $checkPeriod = 12; //
		/**
		 * @var string Where to store the update info.
		 */
		public $optionName = '';
		/**
		 * @var bool Set to TRUE to enable error reporting. Errors are raised using trigger_error() and should be logged to the standard PHP error log.
		 */
		public $debugMode = true;
		/**
		 * @var null
		 */
		private $cronHook = null;
		/**
		 * @var null
		 */
		private $debugBarPlugin = null;

		/**
		 * Class constructor.
		 *
		 * @param string  $metadataUrl The URL of the plugin's metadata file.
		 * @param string  $pluginFile  Fully qualified path to the main plugin file.
		 * @param string  $slug        The plugin's 'slug'. If not specified, the filename part of $pluginFile sans '.php' will be used as the slug.
		 * @param integer $checkPeriod How often to check for updates (in hours). Defaults to checking every 12 hours. Set to 0 to disable automatic update checks.
		 * @param string  $optionName  Where to store book-keeping info about update checks. Defaults to 'external_updates-$slug'.
		 */

		public function __construct( $metadataUrl, $pluginFile, $slug = '', $checkPeriod = 12, $optionName = '' )
		{
			if ( strpos( $metadataUrl, 'https:' ) !== false ){
				$metadataUrl = str_replace( 'https:', 'http:', $metadataUrl );
			}
			$this->metadataUrl = $metadataUrl;
			$this->pluginFile  = plugin_basename( $pluginFile );
			$this->checkPeriod = $checkPeriod;
			$this->slug        = $slug;
			$this->optionName  = $optionName;
			$this->debugMode   = defined( 'WP_DEBUG' ) && WP_DEBUG;

			//If no slug is specified, use the name of the main plugin file as the slug.
			//For example, 'my-cool-plugin/cool-plugin.php' becomes 'cool-plugin'.
			if ( empty( $this->slug ) ){
				$this->slug = basename( $this->pluginFile, '.php' );
			}

			if ( empty( $this->optionName ) ){
				$this->optionName = 'external_updates-' . $this->slug;
			}

			$this->install_hooks();
		}

		/**
		 * Install the hooks required to run periodic update checks and inject update info
		 * into WP data structures.
		 *
		 * @return void
		 */
		protected function install_hooks()
		{
			//Override requests for plugin information
			add_filter( 'plugins_api', array( $this, 'injectInfo' ), 20, 3 );

			//Insert our update info into the update array maintained by WP
			add_filter( 'site_transient_update_plugins', array( $this, 'injectUpdate' ) ); //WP 3.0+
			add_filter( 'transient_update_plugins', array( $this, 'injectUpdate' ) ); //WP 2.8+

			add_filter( 'plugin_row_meta', array( $this, 'addCheckForUpdatesLink' ), 10, 4 );
			add_action( 'admin_init', array( $this, 'handleManualCheck' ) );
			add_action( 'all_admin_notices', array( $this, 'displayManualCheckResult' ) );

			//Set up the periodic update checks
			$this->cronHook = 'check_plugin_updates-' . $this->slug;
			if ( $this->checkPeriod > 0 ){

				//Trigger the check via Cron
				add_filter( 'cron_schedules', array( $this, '_addCustomSchedule' ) );
				if ( ! wp_next_scheduled( $this->cronHook ) && ! defined( 'WP_INSTALLING' ) ){
					$scheduleName = 'every' . $this->checkPeriod . 'hours';
					wp_schedule_event( time(), $scheduleName, $this->cronHook );
				}
				add_action( $this->cronHook, array( $this, 'checkForUpdates' ) );

				register_deactivation_hook( $this->pluginFile, array( $this, '_remove_updater_cron' ) );

				//In case Cron is disabled or unreliable, we also manually trigger
				//the periodic checks while the user is browsing the Dashboard.
				add_action( 'admin_init', array( $this, 'maybeCheckForUpdates' ) );
			} else {
				//Periodic checks are disabled.
				wp_clear_scheduled_hook( $this->cronHook );
			}

			add_action( 'plugins_loaded', array( $this, 'initDebugBarPanel' ) );
		}

		/**
		 * Add our custom schedule to the array of Cron schedules used by WP.
		 *
		 * @param array $schedules
		 *
		 * @return array
		 */

		public function _add_custom_schedule( $schedules )
		{
			if ( $this->checkPeriod && ( $this->checkPeriod > 0 ) ){
				$scheduleName               = 'every' . $this->checkPeriod . 'hours';
				$schedules[ $scheduleName ] = array( 'interval' => $this->checkPeriod * 3600, 'display' => sprintf( __( 'Every %d hours', 'rtmedia' ), $this->checkPeriod ), );
			}

			return $schedules;
		}

		/**
		 * Remove the scheduled cron event that the library uses to check for updates.
		 *
		 * @return void
		 */
		public function _remove_updater_cron()
		{
			wp_clear_scheduled_hook( $this->cronHook );
		}

		/**
		 * Get the name of the update checker's WP-cron hook. Mostly useful for debugging.
		 *
		 * @return string
		 */

		public function get_cron_hook_name()
		{
			return $this->cronHook;
		}

		/**
		 * Retrieve plugin info from the configured API endpoint.
		 *
		 * @uses wp_remote_get()
		 *
		 * @param array $queryArgs Additional query arguments to append to the request. Optional.
		 *
		 * @return RT_Plugin_Update_Info
		 */

		public function request_info( $queryArgs = array() )
		{
			//Query args to append to the URL. Plugins can add their own by using a filter callback (see addQueryArgFilter()).
			$installedVersion                 = $this->get_installed_version();
			$queryArgs[ 'installed_version' ] = ( $installedVersion !== null ) ? $installedVersion : '';
			$queryArgs[ 'admin_email' ]       = get_option( 'admin_email' );
			$queryArgs[ 'slug' ]              = $this->slug;
			$queryArgs                        = apply_filters( 'puc_request_info_query_args-' . $this->slug, $queryArgs );
			//Various options for the wp_remote_get() call. Plugins can filter these, too.
			$options = array(
				'timeout' => 10, //seconds
				'headers' => array( 'Accept' => 'application/json' ), );
			$options = apply_filters( 'puc_request_info_options-' . $this->slug, $options );

			//The plugin info should be at 'http://your-api.com/url/here/$slug/info.json'
			$url = $this->metadataUrl;
			if ( ! empty( $queryArgs ) ){
				$url = add_query_arg( $queryArgs, $url );
			}

			$result = wp_remote_get( $url, $options );
			//Try to parse the response
			$pluginInfo = null;
			if ( ! is_wp_error( $result ) && isset( $result[ 'response' ][ 'code' ] ) && ( $result[ 'response' ][ 'code' ] == 200 ) && ! empty( $result[ 'body' ] ) ){
				$pluginInfo = RT_Plugin_Update_Info::from_json( $result[ 'body' ], $this->debugMode );
			} else {
				if ( $this->debugMode ){
					$message = sprintf( __( 'The URL %s does not point to a valid plugin metadata file.', 'rtmedia' ), $url );
					if ( is_wp_error( $result ) ){
						$message .= sprintf( __( 'WP HTTP error: %s', 'rtmedia' ), $result->get_error_message() );
					} else {
						if ( isset( $result[ 'response' ][ 'code' ] ) ){
							$message .= sprintf( __( 'HTTP response code is %s (expected: 200)', 'rtmedia' ), $result[ 'response' ][ 'code' ] );
						} else {
							$message .= __( 'wp_remote_get() returned an unexpected result.', 'rtmedia' );
						}
					}
					trigger_error( $message, E_USER_WARNING );
				}
			}

			$pluginInfo = apply_filters( 'puc_request_info_result-' . $this->slug, $pluginInfo, $result );

			return $pluginInfo;
		}

		/**
		 * Retrieve the latest update (if any) from the configured API endpoint.
		 *
		 * @uses RT_Plugin_Update_Checker::requestInfo()
		 *
		 * @return RT_Plugin_Update_Info An instance of PluginUpdate, or NULL when no updates are available.
		 */

		public function request_update()
		{
			//For the sake of simplicity, this function just calls requestInfo()
			//and transforms the result accordingly.
			$pluginInfo = $this->request_info( array( 'checking_for_updates' => '1' ) );
			if ( $pluginInfo == null ){
				return null;
			}

			return RT_Plugin_Update::from_plugin_info( $pluginInfo );
		}

		/**
		 * Get the currently installed version of the plugin.
		 *
		 * @return string Version number.
		 */

		public function get_installed_version()
		{

			if ( ! function_exists( 'get_plugins' ) ){
				if ( is_multisite() ){
					require_once( ABSPATH . '/wp-admin/network/includes/plugin.php' );
				} else {
					require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
				}
			}
			$allPlugins = get_plugins();
			if ( array_key_exists( $this->pluginFile, $allPlugins ) && array_key_exists( 'Version', $allPlugins[ $this->pluginFile ] ) ){
				return $allPlugins[ $this->pluginFile ][ 'Version' ];
			} else {
				//This can happen if the filename is wrong or the plugin is installed in mu-plugins.
				if ( $this->debugMode ){
					trigger_error( sprintf( __( "Can't to read the Version header for %s. The filename may be incorrect, or the file is not present in /wp-content/plugins.", 'rtmedia' ), $this->pluginFile ), E_USER_WARNING );
				}

				return null;
			}
		}

		/**
		 * Check for plugin updates.
		 * The results are stored in the DB option specified in $optionName.
		 *
		 * @return RT_Plugin_Update|null
		 */

		public function check_for_updates()
		{
			$installedVersion = $this->get_installed_version();
			//Fail silently if we can't find the plugin or read its header.
			if ( $installedVersion === null ){
				if ( $this->debugMode ){
					trigger_error( sprintf( __( 'Skipping update check for %s - installed version unknown.', 'rtmedia' ), $this->pluginFile ), E_USER_WARNING );
				}

				return null;
			}

			$state = $this->get_update_state();
			if ( empty( $state ) ){
				$state                 = new StdClass;
				$state->lastCheck      = 0;
				$state->checkedVersion = '';
				$state->update         = null;
			}

			$state->lastCheck      = time();
			$state->checkedVersion = $installedVersion;
			$this->set_update_state( $state ); //Save before checking in case something goes wrong

			$state->update = $this->request_update();
			$this->set_update_state( $state );

			return $this->get_update();
		}

		/**
		 * Check for updates only if the configured check interval has already elapsed.
		 *
		 * @return void
		 */

		public function maybe_check_for_updates()
		{
			if ( empty( $this->checkPeriod ) ){
				return;
			}
			$state = $this->get_update_state();

			$shouldCheck = empty( $state ) || ! isset( $state->lastCheck ) || ( ( time() - $state->lastCheck ) >= $this->checkPeriod * 3600 );

			if ( $shouldCheck ){
				$this->check_for_updates();
			}
		}

		/**
		 * Load the update checker state from the DB.
		 *
		 * @return StdClass|null
		 */

		public function get_update_state()
		{
			$state = get_site_option( $this->optionName, null );
			if ( empty( $state ) || ! is_object( $state ) ){
				$state = null;
			}

			if ( ! empty( $state ) && isset( $state->update ) && is_object( $state->update ) ){
				$state->update = RT_Plugin_Update::from_object( $state->update );
			}

			return $state;
		}

		/**
		 * Persist the update checker state to the DB.
		 *
		 * @param StdClass $state
		 *
		 * @return void
		 */

		private function set_update_state( $state )
		{
			if ( isset( $state->update ) && is_object( $state->update ) && method_exists( $state->update, 'toStdClass' ) ){
				$update = $state->update;
				/** @var PluginUpdate $update */
				$state->update = $update->to_std_class();
			}
			update_site_option( $this->optionName, $state );
		}

		/**
		 * Reset update checker state - i.e. last check time, cached update data and so on.
		 *
		 * Call this when your plugin is being uninstalled, or if you want to
		 * clear the update cache.
		 */
		public function reset_update_state()
		{
			delete_site_option( $this->optionName );
		}

		/**
		 * Intercept plugins_api() calls that request information about our plugin and
		 * use the configured API endpoint to satisfy them.
		 *
		 * @see plugins_api()
		 *
		 * @param mixed        $result
		 * @param string       $action
		 * @param array|object $args
		 *
		 * @return mixed
		 */

		public function inject_info( $result, $action = null, $args = null )
		{
			$relevant = ( $action == 'plugin_information' ) && isset( $args->slug ) && ( $args->slug == $this->slug );
			if ( ! $relevant ){
				return $result;
			}

			$pluginInfo = $this->request_info();
			$pluginInfo = apply_filters( 'puc_pre_inject_info-' . $this->slug, $pluginInfo );
			if ( $pluginInfo ){
				return $pluginInfo->toWpFormat();
			}

			return $result;
		}

		/**
		 * Insert the latest update (if any) into the update list maintained by WP.
		 *
		 * @param StdClass $updates Update list.
		 *
		 * @return StdClass Modified update list.
		 */

		public function inject_update( $updates )
		{
			//Is there an update to insert?
			$update = $this->get_update();
			if ( ! empty( $update ) ){
				//Let plugins filter the update info before it's passed on to WordPress.
				$update = apply_filters( 'puc_pre_inject_update-' . $this->slug, $update );
				if ( ! is_object( $updates ) ){
					$updates           = new StdClass();
					$updates->response = array();
				}
				$updates->response[ $this->pluginFile ] = $update->toWpFormat();
			} else {
				if ( isset( $updates, $updates->response ) ){
					unset( $updates->response[ $this->pluginFile ] );
				}
			}

			return $updates;
		}

		/**
		 * Get the details of the currently available update, if any.
		 *
		 * If no updates are available, or if the last known update version is below or equal
		 * to the currently installed version, this method will return NULL.
		 *
		 * Uses cached update data. To retrieve update information straight from
		 * the metadata URL, call requestUpdate() instead.
		 *
		 * @return RT_Plugin_Update|null
		 */

		public function get_update()
		{
			$state = $this->get_update_state();
			/** @var StdClass $state */
			//Is there an update available insert?
			if ( ! empty( $state ) && isset( $state->update ) && ! empty( $state->update ) ){
				$update = $state->update;
				//Check if the update is actually newer than the currently installed version.
				$installedVersion = $this->get_installed_version();
				if ( ( $installedVersion !== null ) && version_compare( $update->version, $installedVersion, '>' ) ){
					return $update;
				}
			}

			return null;
		}

		/**
		 * Add a "Check for updates" link to the plugin row in the "Plugins" page. By default,
		 * the new link will appear after the "Visit plugin site" link.
		 *
		 * You can change the link text by using the "puc_manual_check_link-$slug" filter.
		 * Returning an empty string from the filter will disable the link.
		 *
		 * @param array       $pluginMeta Array of meta links.
		 * @param string      $pluginFile
		 * @param array|null  $pluginData Currently ignored.
		 * @param string|null $status     Currently ignored.
		 *
		 * @return array
		 */

		public function add_check_for_updates_link( $pluginMeta, $pluginFile, $pluginData = null, $status = null )
		{
			if ( $pluginFile == $this->pluginFile && current_user_can( 'update_plugins' ) ){
				$linkUrl = wp_nonce_url( add_query_arg( array( 'puc_check_for_updates' => 1, 'puc_slug' => $this->slug, ), is_network_admin() ? network_admin_url( 'plugins.php' ) : admin_url( 'plugins.php' ) ), 'puc_check_for_updates' );

				$linkText = apply_filters( 'puc_manual_check_link-' . $this->slug, __( 'Check for updates', 'rtmedia' ) );
				if ( ! empty( $linkText ) ){
					$pluginMeta[ ] = sprintf( '<a href="%s">%s</a>', esc_attr( $linkUrl ), $linkText );
				}
			}

			return $pluginMeta;
		}

		/**
		 * Check for updates when the user clicks the "Check for updates" link.
		 *
		 * @see self::addCheckForUpdatesLink()
		 *
		 * @return void
		 */
		public function handle_manual_check()
		{
			$shouldCheck = isset( $_GET[ 'puc_check_for_updates' ], $_GET[ 'puc_slug' ] ) && $_GET[ 'puc_slug' ] == $this->slug && current_user_can( 'update_plugins' ) && check_admin_referer( 'puc_check_for_updates' );

			if ( $shouldCheck ){
				$update = $this->check_for_updates();
				$status = ( $update === null ) ? 'no_update' : 'update_available';
				wp_redirect( add_query_arg( array( 'puc_update_check_result' => $status, 'puc_slug' => $this->slug, ), is_network_admin() ? network_admin_url( 'plugins.php' ) : admin_url( 'plugins.php' ) ) );
			}
		}

		/**
		 * Display the results of a manual update check.
		 *
		 * @see self::handleManualCheck()
		 *
		 * You can change the result message by using the "puc_manual_check_message-$slug" filter.
		 */
		public function display_manual_check_result()
		{
			if ( isset( $_GET[ 'puc_update_check_result' ], $_GET[ 'puc_slug' ] ) && ( $_GET[ 'puc_slug' ] == $this->slug ) ){
				$status = strval( $_GET[ 'puc_update_check_result' ] );
				if ( $status == 'no_update' ){
					$message = __( 'This plugin is up to date.', 'rtmedia' );
				} else {
					if ( $status == 'update_available' ){
						$message = __( 'A new version of this plugin is available.', 'rtmedia' );
					} else {
						$message = sprintf( __( 'Unknown update checker status "%s"', 'rtmedia' ), htmlentities( $status ) );
					}
				}
				printf( '<div class="updated"><p>%s</p></div>', apply_filters( 'puc_manual_check_message-' . $this->slug, $message, $status ) );
			}
		}

		/**
		 * Register a callback for filtering query arguments.
		 *
		 * The callback function should take one argument - an associative array of query arguments.
		 * It should return a modified array of query arguments.
		 *
		 * @uses add_filter() This method is a convenience wrapper for add_filter().
		 *
		 * @param callable $callback
		 *
		 * @return void
		 */

		public function add_query_arg_filter( $callback )
		{
			add_filter( 'puc_request_info_query_args-' . $this->slug, $callback );
		}

		/**
		 * Register a callback for filtering arguments passed to wp_remote_get().
		 *
		 * The callback function should take one argument - an associative array of arguments -
		 * and return a modified array or arguments. See the WP documentation on wp_remote_get()
		 * for details on what arguments are available and how they work.
		 *
		 * @uses add_filter() This method is a convenience wrapper for add_filter().
		 *
		 * @param callable $callback
		 *
		 * @return void
		 */

		public function add_http_request_arg_filter( $callback )
		{
			add_filter( 'puc_request_info_options-' . $this->slug, $callback );
		}

		/**
		 * Register a callback for filtering the plugin info retrieved from the external API.
		 *
		 * The callback function should take two arguments. If the plugin info was retrieved
		 * successfully, the first argument passed will be an instance of  PluginInfo. Otherwise,
		 * it will be NULL. The second argument will be the corresponding return value of
		 * wp_remote_get (see WP docs for details).
		 *
		 * The callback function should return a new or modified instance of PluginInfo or NULL.
		 *
		 * @uses add_filter() This method is a convenience wrapper for add_filter().
		 *
		 * @param callable $callback
		 *
		 * @return void
		 */

		public function add_result_filter( $callback )
		{
			add_filter( 'puc_request_info_result-' . $this->slug, $callback, 10, 2 );
		}

		/**
		 * Register a callback for one of the update checker filters.
		 *
		 * Identical to add_filter(), except it automatically adds the "puc_" prefix
		 * and the "-$plugin_slug" suffix to the filter name. For example, "request_info_result"
		 * becomes "puc_request_info_result-your_plugin_slug".
		 *
		 * @param string   $tag
		 * @param callable $callback
		 * @param int      $priority
		 * @param int      $acceptedArgs
		 */

		public function add_filter( $tag, $callback, $priority = 10, $acceptedArgs = 1 )
		{
			add_filter( 'puc_' . $tag . '-' . $this->slug, $callback, $priority, $acceptedArgs );
		}

		/**
		 * Initialize the update checker Debug Bar plugin/add-on thingy.
		 */
		public function init_debug_bar_panel()
		{
			if ( class_exists( 'Debug_Bar' ) ){
				require_once dirname( __FILE__ ) . '/debug-bar-plugin.php';
				$this->debugBarPlugin = new PucDebugBarPlugin( $this );
			}
		}

	}
}