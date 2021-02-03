<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      3.0.0
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/admin
 */
class Smaily_For_WP_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since  3.0.0
	 * @access private
	 * @var    string  $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  3.0.0
	 * @access private
	 * @var    string  $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 3.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 3.0.0
	 */
	public function enqueue_styles() {
		wp_register_style( $this->plugin_name, SMLY4WP_PLUGIN_URL . '/admin/css/smaily-for-wp-admin.css', array(), $this->version, 'all' );
		// Only enqueue in module page.
		$screen = get_current_screen();
		if ( isset( $screen->base ) && $screen->base === 'toplevel_page_sendsmaily-wordpress-plugin' ) {
			wp_enqueue_style( $this->plugin_name );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 3.0.0
	 */
	public function enqueue_scripts() {
		wp_register_script( $this->plugin_name, SMLY4WP_PLUGIN_URL . '/admin/js/smaily-for-wp-admin.js', array( 'jquery' ), $this->version, false );
		// Only enqueue in module page.
		$screen = get_current_screen();
		if ( isset( $screen->base ) && $screen->base === 'toplevel_page_sendsmaily-wordpress-plugin' ) {
			wp_enqueue_script( $this->plugin_name );
			wp_localize_script( $this->plugin_name, $this->plugin_name, array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}
	}

	/**
	 * Verify plugin was updated via transient and trigger DB upgrades.
	 *
	 * @since 3.0.0
	 */
	public function listen_for_upgrade_transient() {
		if ( ! get_transient( 'smailyforwp_plugin_updated' ) ) {
			return;
		}

		$plugin_version = SMLY4WP_PLUGIN_VERSION;
		if ( $plugin_version === get_option( 'smailyforwp_db_version' ) ) {
			return;
		}

		if ( version_compare( $plugin_version, '3.0.0', '=' ) ) {
			$this->upgrade_3_0_0();
		}
		delete_transient( 'smailyforwp_plugin_updated' );
	}

	/**
	 * Check if plugin was updated, make a transient option if so.
	 * This alows us to trigger a DB upgrade script if necessary.
	 *
	 * @since 3.0.0
	 * @param Plugin_Upgrader $upgrader_object Instance of WP_Upgrader.
	 * @param array           $options         Array of bulk item update data.
	 */
	public function check_for_update( $upgrader_object, $options ) {
		$smaily_basename = SMLY4WP_PLUGIN_BASENAME;

		$plugin_was_updated = $options['action'] === 'update' && $options['type'] === 'plugin';
		if ( ! isset( $options['plugins'] ) || ! $plugin_was_updated ) {
			return;
		}

		// If updating a single plugin, $options['plugins'] is string of the updated plugin's basename.
		if ( is_string( $options['plugins'] ) && $options['plugins'] === $plugin_basename ) {
			set_transient( 'smailyforwp_plugin_updated', 1 );
			return;
		}

		// If updating multiple plugins, $options['plugins'] is an array of updated plugins' basenames.
		foreach ( $options['plugins'] as $plugin_basename ) {
			if ( $smaily_basename === $plugin_basename ) {
				set_transient( 'smailyforwp_plugin_updated', 1 );
				return;
			}
		}
	}

	/**
	 * Upgrade database structure to 3.0.0 version.
	 *
	 * @since 3.0.0
	 */
	private function upgrade_3_0_0() {
		global $wpdb;
		// Get saved autoresponder ID.
		$table_name       = esc_sql( $wpdb->prefix . 'smaily_config' );
		$autoresponder_id = $wpdb->get_col( "SELECT autoresponder FROM `$table_name` LIMIT 1" )[0];
		// Get widgets' options.
		$widget_options = get_option( 'widget_smaily_subscription_widget' );

		foreach ( $widget_options as &$widget ) {
			// Widgets created before 3.0.0 do not have autoresponder value, adding it here.
			if ( is_array( $widget ) && ! isset( $widget['autoresponder'] ) ) {
				$widget['autoresponder'] = $autoresponder_id;
			}
		}
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smaily_autoresponders" );
		update_option( 'widget_smaily_subscription_widget', $widget_options );
		update_option( 'smailyforwp_db_version', '3.0.0' );
	}

	/**
	 * Render admin page.
	 *
	 * @since 3.0.0
	 */
	public function smaily_admin_render() {
		global $wpdb;

		// Create admin template.
		$template = new Smaily_For_WP_Template( 'admin/partials/smaily-for-wp-admin-page.php' );

		// Load configuration data.
		$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
		$data       = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
		$template->assign( (array) $data );

		// Add menu elements.
		add_menu_page( 'smaily', 'Smaily', 'manage_options', SMLY4WP_PLUGIN_PATH, '', SMLY4WP_PLUGIN_URL . '/gfx/icon.png' );
		add_submenu_page( 'smaily', 'Newsletter subscription form', 'Form', 'manage_options', SMLY4WP_PLUGIN_PATH, array( $template, 'dispatch' ) );
	}

	/**
	 * Load subscribe widget.
	 *
	 * @since 3.0.0
	 */
	public function smaily_subscription_widget_init() {
		register_widget( 'Smaily_For_WP_Widget' );
	}

	/**
	 * Function is run when user performs action which is handled Ajax.
	 *
	 * @since 3.0.0
	 */
	public function smaily_admin_save() {
		// Allow only posted data.
		if ( empty( $_POST ) ) { die( 'Must be post method.' ); }

		// Parse form data out of the serialization.
		$form_data = array();
		parse_str( $_POST['form_data'], $form_data );

		// Validate posted operation.
		if ( ! isset( $form_data['op'] ) ) { die( 'No action or API key set.' ); }
		$form_data['op'] = ( in_array( $form_data['op'], array( 'validateApiKey', 'removeApiKey', 'resetForm', 'refreshAutoresp', 'save' ), true )
			? $form_data['op'] : '' );

		if ( $form_data['op'] === '' ) { die( 'No valid operation submitted.' ); }

		$refresh = ( isset( $form_data['refresh'] ) && (int) $form_data['refresh'] === 1 );
		// Switch to action.
		global $wpdb;
		switch ( $form_data['op'] ) {
			case 'validateApiKey':
				// Get and sanitize request params.
				$params = array(
					'subdomain' => isset( $form_data['subdomain'] ) ? sanitize_text_field( $form_data['subdomain'] ) : '',
					'username'  => isset( $form_data['username'] ) ? sanitize_text_field( $form_data['username'] ) : '',
					'password'  => isset( $form_data['password'] ) ? sanitize_text_field( $form_data['password'] ) : '',
				);

				// Normalize subdomain.
				// First, try to parse as full URL. If that fails, try to parse as subdomain.sendsmaily.net, and
				// if all else fails, then clean up subdomain and pass as is.
				if ( filter_var( $params['subdomain'], FILTER_VALIDATE_URL ) ) {
					$url                 = wp_parse_url( $params['subdomain'] );
					$parts               = explode( '.', $url['host'] );
					$params['subdomain'] = count( $parts ) >= 3 ? $parts[0] : '';
				} elseif ( preg_match( '/^[^\.]+\.sendsmaily\.net$/', $params['subdomain'] ) ) {
					$parts               = explode( '.', $params['subdomain'] );
					$params['subdomain'] = $parts[0];
				}

				$params['subdomain'] = preg_replace( '/[^a-zA-Z0-9]+/', '', $params['subdomain'] );

				// Show error messages to user if no data is entered to form.
				if ( $params['subdomain'] === '' ) {
					// Don't refresh the page.
					$refresh = false;
					$result  = array(
						'message' => __( 'Please enter subdomain!', 'smaily-for-wp' ),
						'error'   => true,
					);
					break;
				} elseif ( $params['username'] === '' ) {
					// Don't refresh the page.
					$refresh = false;
					$result  = array(
						'message' => __( 'Please enter username!', 'smaily-for-wp' ),
						'error'   => true,
					);
					break;
				} elseif ( $params['password'] === '' ) {
					// Don't refresh the page.
					$refresh = false;
					$result  = array(
						'message' => __( 'Please enter password!', 'smaily-for-wp' ),
						'error'   => true,
					);
					break;
				}

				// Validate credentials with get request.
				$rqst = ( new Smaily_For_WP_Request() )
					->auth( $params['username'], $params['password'] )
					->setUrl( 'https://' . $params['subdomain'] . '.sendsmaily.net/api/workflows.php?trigger_type=form_submitted' )
					->get();

				// Error handilng.
				$code = isset( $rqst['code'] ) ? $rqst['code'] : '';
				if ( $code !== 200 ) {
					// Don't refresh the page.
					$refresh = false;
					if ( $code === 401 ) {
						// If wrong credentials.
						$result = array(
							'message' => __( 'Wrong credentials', 'smaily-for-wp' ),
							'error'   => true,
						);
						break;
					} elseif ( $code === 404 ) {
						// If wrong subdomain.
						$result = array(
							'message' => __( 'Error in subdomain', 'smaily-for-wp' ),
							'error'   => true,
						);
						break;
					} elseif ( array_key_exists( 'error', $rqst ) ) {
						// If there is WordPress error message.
						$result = array(
							'message' => __( $rqst['error'], 'smaily-for-wp' ),
							'error'   => true,
						);
						break;
					}
					// If not determined error.
					$result = array(
						'message' => __( 'Something went wrong with request to Smaily', 'smaily-for-wp' ),
						'error'   => true,
					);
					break;
				}

				// Insert item to database.
				$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
				// Add config.
				$wpdb->insert(
					$table_name,
					array(
						'api_credentials' => $params['username'] . ':' . $params['password'],
						'domain'          => $params['subdomain'],
					)
				);

				// Return result.
				$result = array(
					'error'   => false,
					'message' => __( 'Credentials validated.', 'smaily-for-wp' ),
				);
				break;

			case 'removeApiKey':
				// Delete contents of config.
				$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
				$wpdb->query( "DELETE FROM `$table_name`" );

				// Set result.
				$result = array(
					'error'   => false,
					'message' => __( 'Credentials removed.', 'smaily-for-wp' ),
				);
				break;

			case 'resetForm':
				// Generate form contents.
				$template = new Smaily_For_WP_Template( 'public/partials/smaily-for-wp-public-advanced.php' );

				// Load configuration data.
				$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
				$data       = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
				$data->form = '';
				$template->assign( (array) $data );

				// Render template.
				$result = array(
					'error'   => false,
					'message' => __( 'Newsletter subscription form reset to default.', 'smaily-for-wp' ),
					'content' => $template->render(),
				);
				break;

			case 'save':
				// Get parameters.
				$isAdvanced = ( isset( $form_data['is_advanced'] ) && ! empty( $form_data['is_advanced'] ) ) ? '1' : '0';
				$advanced   = ( isset( $form_data['advanced'] ) && is_array( $form_data['advanced'] ) ) ? $form_data['advanced'] : array();
				$form       = ( isset( $advanced['form'] ) && is_string( $advanced['form'] ) ) ? $advanced['form'] : '';

				// Generate new form (if empty).
				if ( empty( $form ) ) {
					$template = new Smaily_For_WP_Template( 'public/partials/smaily-for-wp-public-advanced.php' );

					// Load configuration data.
					$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
					$data       = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
					$template->assign( (array) $data );

					// Render template.
					$form = $template->render();
				}

				// Update configuration.
				$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
				$wpdb->query(
					$wpdb->prepare(
						"
					UPDATE `$table_name`
					SET `form` = %s, `is_advanced` = %d
					",
						$form,
						$isAdvanced
					)
				);
				// Return response.
				$result = array(
					'error'   => false,
					'message' => __( 'Changes saved.', 'smaily-for-wp' ),
				);
				break;
		}

		// Send refresh form content (if requested).
		if ( $refresh ) {
			// Generate form contents.
			$template = new Smaily_For_WP_Template( 'admin/partials/smaily-for-wp-admin-form.php' );

			// Load configuration data.
			$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
			$data       = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
			$template->assign( (array) $data );

			// Render template.
			$result['content'] = $template->render();
		}
		// Display result messages as JSON.
		echo json_encode( $result );
		wp_die();
	}

	/**
	 * Make a request to Smaily asking for autoresponders
	 * Request is authenticated via saved credentials.
	 *
	 * @return array $autoresponder_list List of autoresponders in format [id => title].
	 */
	public static function get_autoresponders() {
		global $wpdb;

		// Load configuration data.
		$table_name      = esc_sql( $wpdb->prefix . 'smaily_config' );
		$config          = (array) $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
		$api_credentials = explode( ':', $config['api_credentials'] );

		$result = ( new Smaily_For_WP_Request() )
			->setUrl( 'https://' . $config['domain'] . '.sendsmaily.net/api/workflows.php?trigger_type=form_submitted' )
			->auth( $api_credentials[0], $api_credentials[1] )
			->get();

		if ( empty( $result['body'] ) ) {
			return array();
		}

		$autoresponder_list = array();
		foreach ( $result['body'] as $autoresponder ) {
			$id                        = $autoresponder['id'];
			$title                     = $autoresponder['title'];
			$autoresponder_list[ $id ] = $title;
		}
		return $autoresponder_list;
	}
}
