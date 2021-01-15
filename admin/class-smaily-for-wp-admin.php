<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      3.0.0
 *
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
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
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 3.0.0
	 */
	public function enqueue_styles() {
		wp_register_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/smaily-for-wp-admin.css', array(), $this->version, 'all' );
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
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/smaily-for-wp-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, $this->plugin_name, array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}

	/**
	 * Render admin page.
	 *
	 * @return void
	 */
	public function smaily_admin_render() {
		global $wpdb;

		// Create admin template.
		$template = new Smaily_For_WP_Template( 'admin/partials/smaily-for-wp-admin-page.php' );

		// Load configuration data.
		$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
		$data       = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
		$template->assign( (array) $data );

		// Load autoresponders.
		$table_name = esc_sql( $wpdb->prefix . 'smaily_autoresponders' );
		$data       = $wpdb->get_results( "SELECT * FROM `$table_name`" );
		$template->assign( 'autoresponders', $data );

		// Add menu elements.
		add_menu_page( 'smaily', 'Smaily', 'manage_options', SMLY4WP_PLUGIN_PATH, '', plugins_url( '/gfx/icon.png', dirname( __FILE__ ) ) );
		add_submenu_page( 'smaily', 'Newsletter subscription form', 'Form', 'manage_options', SMLY4WP_PLUGIN_PATH, array( $template, 'dispatch' ) );
	}

	/**
	 * Load subscribe widget.
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
						'message' => __( 'Please enter subdomain!', 'wp_smaily' ),
						'error'   => true,
					);
					break;
				} elseif ( $params['username'] === '' ) {
					// Don't refresh the page.
					$refresh = false;
					$result  = array(
						'message' => __( 'Please enter username!', 'wp_smaily' ),
						'error'   => true,
					);
					break;
				} elseif ( $params['password'] === '' ) {
					// Don't refresh the page.
					$refresh = false;
					$result  = array(
						'message' => __( 'Please enter password!', 'wp_smaily' ),
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
					if ( $code === 401) {
						// If wrong credentials.
						$result = array(
							'message' => __( 'Wrong credentials', 'wp_smaily' ),
							'error'   => true,
						);
						break;
					} elseif ( $code === 404 ) {
						// If wrong subdomain.
						$result = array(
							'message' => __( 'Error in subdomain', 'wp_smaily' ),
							'error'   => true,
						);
						break;
					} elseif ( array_key_exists( 'error', $rqst ) ) {
						// If there is WordPress error message.
						$result = array(
							'message' => __( $rqst['error'], 'wp_smaily' ),
							'error'   => true,
						);
						break;
					}
					// If not determined error.
					$result = array(
						'message' => __( 'Something went wrong with request to Smaily', 'wp_smaily' ),
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

				// Get autoresponders.
				$insert_query = array();
				// Replace autoresponders.
				foreach ( $rqst['body'] as $autoresponder ) {
					$insert_query[] = $wpdb->prepare( '(%d, %s)', $autoresponder['id'], $autoresponder['title'] );
				}
				// Insert to db.
				$table_name = esc_sql( $wpdb->prefix . 'smaily_autoresponders' );
				// Clear previous data.
				$wpdb->query( "DELETE FROM `$table_name`" );
				// Add new autoresponders if set.
				if ( ! empty( $insert_query ) ) {
					$wpdb->query( "INSERT INTO `$table_name`(`id`, `title`) VALUES " . implode( ',', $insert_query ) );
				}

				// Return result.
				$result = array(
					'error'   => false,
					'message' => __( 'Credentials validated.', 'wp_smaily' ),
				);
				break;

			case 'removeApiKey':

				// Delete contents of config.
				$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
				$wpdb->query( "DELETE FROM `$table_name`" );

				// Delete contents of autoresponders.
				$table_name = esc_sql( $wpdb->prefix . 'smaily_autoresponders' );
				$wpdb->query( "DELETE FROM `$table_name`" );

				// Set result.
				$result = array(
					'error'   => false,
					'message' => __( 'Credentials removed.', 'wp_smaily' ),
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
					'message' => __( 'Newsletter subscription form reset to default.', 'wp_smaily' ),
					'content' => $template->render(),
				);
				break;

			case 'refreshAutoresp':

				// Load configuration data.
				$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
				$data       = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );

				// Credentials.
				$api_credentials = explode( ':', $data->api_credentials );
				// Get autoresponders.
				$result         = ( new Smaily_For_WP_Request() )
					->setUrl( 'https://' . $data->domain . '.sendsmaily.net/api/workflows.php?trigger_type=form_submitted' )
					->auth( $api_credentials[0], $api_credentials[1] )
					->get();
				$autoresponders = $result['body'];

				// Handle errors.
				if ( isset( $result['code'] ) && $result['code'] !== 200 ) {
					$result['error'] = true;
					break;
				} elseif ( empty( $autoresponders ) ) {
					$result = array(
						'message' => __( 'Could not find any autoresponders!', 'wp_smaily' ),
						'error'   => true,
					);
					break;
				}

				// Get autoresponders.
				$insert_query = array();
				// Replace autoresponders.
				foreach ( $autoresponders as $autoresponder ) {
					$insert_query[] = $wpdb->prepare( '(%d, %s)', $autoresponder['id'], $autoresponder['title'] );
				}
				// Insert to db.
				$table_name = esc_sql( $wpdb->prefix . 'smaily_autoresponders' );
				// Clear previous data.
				$wpdb->query( "DELETE FROM `$table_name`" );
				// Add new autoresponders if set.
				if ( ! empty( $insert_query ) ) {
					$wpdb->query( "INSERT INTO `$table_name`(`id`, `title`) VALUES " . implode( ',', $insert_query ) );
				}

				// Return result.
				$result = array(
					'error'   => false,
					'message' => __( 'Autoresponders refreshed.', 'wp_smaily' ),
				);

				break;

			case 'save':

				// Get parameters.
				$isAdvanced = ( isset( $form_data['is_advanced'] ) && ! empty( $form_data['is_advanced'] ) ) ? '1' : '0';
				// Get basic and advanced parameters.
				$basic    = ( isset( $form_data['basic'] ) && is_array( $form_data['basic'] ) ) ? $form_data['basic'] : array();
				$advanced = ( isset( $form_data['advanced'] ) && is_array( $form_data['advanced'] ) ) ? $form_data['advanced'] : array();

				// Validate and sanitize basic & advanced parameters values.
				$autoresponder = ( isset( $basic['autoresponder'] ) ) && ( filter_var( $basic['autoresponder'], FILTER_VALIDATE_INT ) !== false )
					? $basic['autoresponder'] : 0;
				$form          = ( isset( $advanced['form'] ) && is_string( $advanced['form'] ) ) ? $advanced['form'] : '';

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
				$wpdb->query( $wpdb->prepare(
					"
					UPDATE `$table_name`
					SET `autoresponder` = %d, `form` = %s, `is_advanced` = %d
					",
					$autoresponder, $form, $isAdvanced
				) );
				// Return response.
				$result = array(
					'error'   => false,
					'message' => __( 'Changes saved.', 'wp_smaily' ),
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

			// Load autoresponders.
			$table_name = esc_sql( $wpdb->prefix . 'smaily_autoresponders' );
			$data       = $wpdb->get_results( "SELECT * FROM `$table_name`" );
			$template->assign( 'autoresponders', $data );

			// Render template.
			$result['content'] = $template->render();
		}

		// Display result messages as JSON.
		echo json_encode( $result );
		wp_die();
	}
}
