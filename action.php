<?php

// Define params.
define( 'BP', dirname( __FILE__ ) );
define( 'DS', DIRECTORY_SEPARATOR );

// Disable cache.
header( 'Cache-Control: no-cache, must-revalidate' );
header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
header( 'Pragma: No-Cache' );

// Allow only posted data.
if ( empty( $_POST ) ) { die( 'Must be post method.' ); }

// Validate posted operation.
if ( ! isset( $_POST['op'] ) ) { die( 'No action or API key set.' ); }
$_POST['op'] = ( in_array( $_POST['op'], array( 'validateApiKey', 'removeApiKey', 'resetForm', 'refreshAutoresp', 'save' ), true )
	?  $_POST['op'] : '' );

// Require request class.
require_once( BP . DS . 'code' . DS . 'Request.php' );

// Get wpdb configuration.
if ( ! function_exists( 'add_action' ) ) {
	$path = dirname( dirname( dirname( BP ) ) );
	require_once( $path . DS . 'wp-config.php' );
}

$refresh = ( isset( $_POST[ 'refresh' ] ) && (int) $_POST['refresh'] === 1 );
// Switch to action.
switch ( $_POST['op'] ) {
	case 'validateApiKey':
		// Get and sanitize request params.
		$params = array(
			'subdomain' => isset( $_POST['subdomain'] ) ? sanitize_text_field( $_POST['subdomain'] ) : '',
			'username'  => isset( $_POST['username'] ) 	? sanitize_text_field( $_POST['username'] )	 : '',
			'password'  => isset( $_POST['password'] ) 	? sanitize_text_field( $_POST['password'] )	 : ''
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
			$result = array(
				'message' => __( 'Please enter subdomain!', 'wp_smaily' ),
				'error'   => true,
			);
			break;
		} elseif ( $params['username'] === '' ) {
			// Don't refresh the page.
			$refresh = false;
			$result = array(
				'message' => __( 'Please enter username!', 'wp_smaily' ),
				'error'   => true,
			);
			break;
		} elseif ( $params['password'] === '' ) {
			// Don't refresh the page.
			$refresh = false;
			$result = array(
				'message' => __( 'Please enter password!', 'wp_smaily' ),
				'error'   => true,
			);
			break;
		}

		// Validate credentials with get request.
		$rqst = new Wp_Smaily_Request(
			'https://' . $params['subdomain'] . '.sendsmaily.net/api/workflows.php?trigger_type=form_submitted',
			array(
				'username' => $params['username'],
				'password' => $params['password'],
			)
		);
		// Response.
		$rqst = $rqst->get();

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
				// If there is wordpress error message.
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
		global $wpdb;
		$table_name = $wpdb->prefix . 'smaily_config';
		// Add config.
		$wpdb->insert(
			$table_name,
			array(
				'api_credentials' => $params['username'] . ':' . $params['password'],
				'domain' => $params['subdomain'],
			)
		);

		// Get autoresponders.
		$insert_query = array();
		// Replace autoresponders.
		foreach ( $rqst['body'] as $autoresponder ) {
			$insert_query[] = $wpdb->prepare( '(%d, %s)', $autoresponder['id'], $autoresponder['title'] );
		}
		// Insert to db.
		$table_name = $wpdb->prefix . 'smaily_autoresponders';
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
		global $wpdb;

		// Delete contents of config.
		$table_name = $wpdb->prefix . 'smaily_config';
		$wpdb->query( "DELETE FROM `$table_name`" );

		// Delete contents of autoresponders.
		$table_name = $wpdb->prefix . 'smaily_autoresponders';
		$wpdb->query( "DELETE FROM `$table_name`" );

		// Set result.
		$result = array(
			'error' => false,
			'message' => __( 'Credentials removed.', 'wp_smaily' ),
		);
		break;

	case 'resetForm':
		global $wpdb;

		// Generate form contents.
		require_once( BP . DS . 'code' . DS . 'Template.php' );
		$template = new Wp_Smaily_Template( 'html' . DS . 'form' . DS . 'advanced.php' );

		// Load configuration data.
		$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
		$data       = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
		$data->form = '';
		$template->assign( (array) $data );

		// Render template.
		$result = array(
			'error' => false,
			'message' => __( 'Newsletter subscription form reset to default.', 'wp_smaily' ),
			'content' => $template->render(),
		);
		break;

	case 'refreshAutoresp':
		global $wpdb;

		// Load configuration data.
		$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
		$data       = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );

		// Credentials.
		$api_credentials = explode( ':', $data->api_credentials );
		// Get autoresponders.
		$request = new Wp_Smaily_Request(
			'https://' . $data->domain . '.sendsmaily.net/api/workflows.php?trigger_type=form_submitted',
			array(
				'username' => $api_credentials[0],
				'password' => $api_credentials[1],
			)
		);
		$result        = $request->get();
		$autoreponders = $result['body'];

		// Handle errors.
		if ( isset( $result['code'] ) && $result['code'] !== 200 ) {
			$result['error'] = true;
			break;
		} elseif ( empty( $autoreponders ) ) {
			$result = array(
				'message' => __( 'Could not find any autoresponders!', 'wp_smaily' ),
				'error'   => true,
			);
			break;
		}

		// Replace autoresponders.
		$insert_query = array();
		foreach ( $autoreponders as $autoresponder ) {
			$insert_query[] = $wpdb->prepare( '(%d, %s)', $autoresponder['id'], $autoresponder['title'] );
		}

		$table_name = $wpdb->prefix . 'smaily_autoresponders';
		$wpdb->query( "DELETE FROM `$table_name`" );
		$wpdb->query( "INSERT INTO `$table_name`(`id`, `title`) VALUES " . implode( ',', $insert_query ) );

		// Return result.
		$result = array(
			'error' => false,
			'message' => __( 'Autoresponders refreshed.', 'wp_smaily' ),
		);

		break;

	case 'save':
		global $wpdb;
		// Get parameters.
		$isAdvanced = ( isset( $_POST['is_advanced'] ) && ! empty( $_POST['is_advanced'] ) ) ? '1' : '0';

		// Get basic and advanced parameters.
		$basic    = ( isset( $_POST['basic'] ) && is_array( $_POST['basic'] ) ) ? $_POST['basic'] : array();
		$advanced = ( isset( $_POST['advanced'] ) && is_array( $_POST['advanced'] ) ) ? $_POST['advanced'] : array();

		// Validate and sanitize basic & advanced parameters values.
		$autoresponder = ( isset( $basic['autoresponder'] ) && is_int( $basic['autoresponder'] ) )
			? $basic['autoresponder'] : '';
		$form = ( isset( $advanced['form'] ) && is_string( $advanced['form'] ) ) ? $advanced['form'] : '';

		// Generate new form (if empty).
		if ( empty( $form ) ) {
			require_once( BP . DS . 'code' . DS . 'Template.php' );
			$template = new Wp_Smaily_Template( 'html' . DS . 'form' . DS . 'advanced.php' );

			// Load configuration data.
			$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
			$data = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
			$template->assign( (array) $data );

			// Render template.
			$form = $template->render();
		}

		// Update configuration.
		$table_name = $wpdb->prefix . 'smaily_config';
		$wpdb->query( $wpdb->prepare(
			"
			UPDATE `$table_name`
			SET `autoresponder` = %s, `form` = %s, `is_advanced` = %s
			",
			$autoreponder, $form, $isAdvanced
		) );

		// Return response.
		$result = array(
			'error' => false,
			'message' => __( 'Changes saved.', 'wp_smaily' ),
		);
		break;
}

// Send refresh form content (if requested).
if ( $refresh ) {
	global $wpdb;

	// Generate form contents.
	require_once( BP . DS . 'code' . DS . 'Template.php' );
	$template = new Wp_Smaily_Template( 'html' . DS . 'admin' . DS . 'html' . DS . 'form.php' );

	// Load configuration data.
	$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
	$data = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
	$template->assign( (array) $data );

	// Load autoresponders.
	$table_name = esc_sql( $wpdb->prefix . 'smaily_autoresponders' );
	$data = $wpdb->get_results( "SELECT * FROM `$table_name`" );
	$template->assign( 'autoresponders', $data );

	// Render template.
	$result['content'] = $template->render();
}

// Display result messages as JSON.
echo json_encode( $result );
