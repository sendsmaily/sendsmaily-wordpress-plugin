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

// @todo: clean up posted data
if ( ! isset( $_POST['op'] ) ) { die( 'No action or API key set.' ); }
$_POST['op'] = trim( $_POST['op'] );

// Require request class.
require_once( BP . DS . 'code' . DS . 'Request.php' );

// Get wpdb configuration.
if ( ! function_exists( 'add_action' ) ) {
	$path = dirname( dirname( dirname( BP ) ) );
	require_once( $path . DS . 'wp-config.php' );
}

// Switch to action.
switch ( $_POST['op'] ) {
	case 'validateApiKey':
		// Get request params.
		$key = isset( $_POST['key'] ) ? trim( $_POST['key'] ) : '';

		// Validate api key with remote request.
		$request = new Wp_Sendsmaily_Request(
			'https://www.sendsmaily.net/validate_key.php',
			array( 'key' => $key )
		);
		$result  = $request->exec();
		$data    = $result['data'];

		// Handle errors.
		if ( isset( $result['code'] ) && $result['code'] >= 200 ) {
			$result['error'] = true;
			break;
		}

		// Insert item to database.
		global $wpdb;
		$table_name = $wpdb->prefix . 'sendsmaily_config';
		$wpdb->insert(
			$table_name,
			array(
				'key'    => $key,
				'domain' => $data['domain'],
			)
		);

		// Get autoresponders.
		$request = new Wp_Sendsmaily_Request(
			'https://' . $data['domain'] . '.sendsmaily.net/api/get-autoresponders/',
			array( 'key' => $key )
		);
		$result  = $request->exec();
		$data    = $result['data'];

		// Handle errors.
		if ( isset( $result['code'] ) && $result['code'] >= 200 ) {
			$result['error'] = true;
			break;
		} elseif ( empty( $data['autoresponders'] ) ) {
			$result = array(
				'message' => __( 'Could not find any autoresponders!', 'wp_sendsmaily' ),
				'error'   => true,
			);
			break;
		}

		// Replace autoresponders.
		$insert_query = array();
		foreach ( $data['autoresponders'] as $item ) {
			$insert_query[] = $wpdb->prepare( '(%d, %s)', $item['id'], $item['title'] );
		}

		$table_name = $wpdb->prefix . 'sendsmaily_autoresp';
		$wpdb->query( "DELETE FROM `$table_name`" );
		$wpdb->query( "INSERT INTO `$table_name`(`id`, `title`) VALUES " . implode( ',', $insert_query ) );

		// Return result.
		$result = array(
			'error' => false,
			'message' => __( 'API key passed validation.', 'wp_sendsmaily' ),
		);
		break;

	case 'removeApiKey':
		global $wpdb;

		// Delete contents of config.
		$table_name = $wpdb->prefix . 'sendsmaily_config';
		$wpdb->query( "DELETE FROM `$table_name`" );

		// Delete contents of autoresponders.
		$table_name = $wpdb->prefix . 'sendsmaily_autoresp';
		$wpdb->query( "DELETE FROM `$table_name`" );

		// Set result.
		$result = array(
			'error' => false,
			'message' => __( 'API key removed.', 'wp_sendsmaily' ),
		);
		break;

	case 'resetForm':
		global $wpdb;

		// Generate form contents.
		require_once( BP . DS . 'code' . DS . 'Template.php' );
		$template = new Wp_Sendsmaily_Template( 'html' . DS . 'form' . DS . 'advanced.phtml' );

		// Load configuration data.
		$table_name = esc_sql( $wpdb->prefix . 'sendsmaily_config' );
		$data       = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
		$data->form = '';
		$template->assign( (array) $data );

		// Render template.
		$result = array(
			'error' => false,
			'message' => __( 'Newsletter subscription form reset to default.', 'wp_sendsmaily' ),
			'content' => $template->render(),
		);
		break;

	case 'refreshAutoresp':
		global $wpdb;

		// Load configuration data.
		$table_name = esc_sql( $wpdb->prefix . 'sendsmaily_config' );
		$data       = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );

		// Get autoresponders.
		$request = new Wp_Sendsmaily_Request(
			'https://' . $data->domain . '.sendsmaily.net/api/get-autoresponders/',
			array(
				'key' => $data->key,
			)
		);
		$result  = $request->exec();
		$data    = $result['data'];

		// Handle errors.
		if ( isset( $result['code'] ) && $result['code'] >= 200 ) {
			$result['error'] = true;
			break;
		} elseif ( empty( $data['autoresponders'] ) ) {
			$result = array(
				'message' => __( 'Could not find any autoresponders!', 'wp_sendsmaily' ),
				'error'   => true,
			);
			break;
		}

		// Replace autoresponders.
		$insert_query = array();
		foreach ( $data['autoresponders'] as $item ) {
			$insert_query[] = $wpdb->prepare( '(%d, %s)', $item['id'], $item['title'] );
		}

		$table_name = $wpdb->prefix . 'sendsmaily_autoresp';
		$wpdb->query( "DELETE FROM `$table_name`" );
		$wpdb->query( "INSERT INTO `$table_name`(`id`, `title`) VALUES " . implode( ',', $insert_query ) );

		// Return result.
		$result = array(
			'error' => false,
			'message' => __( 'Autoresponders refreshed.', 'wp_sendsmaily' ),
		);

		break;

	case 'save':
		global $wpdb;

		// Get params.
		$isAdvanced = ( isset( $_POST['is_advanced'] ) && ! empty( $_POST['is_advanced'] ) ) ? '1' : '0';

		// Get basic and advanced parameters.
		$basic    = ( isset( $_POST['basic'] ) && is_array( $_POST['basic'] ) ) ? $_POST['basic'] : array();
		$advanced = ( isset( $_POST['advanced'] ) && is_array( $_POST['advanced'] ) ) ? $_POST['advanced'] : array();
		if ( empty( $basic ) || empty( $advanced ) ) {
			$result = array( 'error' => true, 'message' => '' );
			break;
		}

		// Generate new form (if empty).
		if ( empty( $advanced['form'] ) ) {
			require_once( BP . DS . 'code' . DS . 'Template.php' );
			$template = new Wp_Sendsmaily_Template( 'html' . DS . 'form' . DS . 'advanced.phtml' );

			// Load configuration data.
			$table_name = esc_sql( $wpdb->prefix . 'sendsmaily_config' );
			$data = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
			$template->assign( (array) $data );

			// Render template.
			$advanced['form'] = $template->render();
		}

		// Update configuration.
		$table_name = $wpdb->prefix . 'sendsmaily_config';
		$wpdb->query( $wpdb->prepare( 
			"
			UPDATE `$table_name`
			SET `autoresponder` = %s, `form` = %s, `is_advanced` = %s
			",
			$basic['autoresponder'], $advanced['form'], $isAdvanced
		) );

		// Return response.
		$result = array(
			'error' => false,
			'message' => __( 'Changes saved.', 'wp_sendsmaily' ),
		);
		break;
}

// Send refresh form content (if requested).
$refresh = ( isset( $_POST[ 'refresh' ] ) && $_POST['refresh'] == 1 );
if ( $refresh ) {
	global $wpdb;

	// Generate form contents.
	require_once( BP . DS . 'code' . DS . 'Template.php' );
	$template = new Wp_Sendsmaily_Template( 'html' . DS . 'admin' . DS . 'html' . DS . 'form.phtml' );

	// Load configuration data.
	$table_name = esc_sql( $wpdb->prefix . 'sendsmaily_config' );
	$data = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
	$template->assign( (array) $data );

	// Load autoresponders.
	$table_name = esc_sql( $wpdb->prefix . 'sendsmaily_autoresp' );
	$data = $wpdb->get_results( "SELECT * FROM `$table_name`" );
	$template->assign( 'autoresponders', $data );

	// Render template.
	$result['content'] = $template->render();
}

// Display result messages as JSON.
echo json_encode( $result );
