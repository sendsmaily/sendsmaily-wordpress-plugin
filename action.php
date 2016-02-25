<?php
/**
 * This file is part of Sendsmaily Wordpress plugin.
 * 
 * Sendsmaily Wordpress plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Sendsmaily Wordpress plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Sendsmaily Wordpress plugin.  If not, see <http://www.gnu.org/licenses/>.
 */

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

// require request class
require_once( BP . DS . 'code' . DS . 'Request.php' );

// get wpdb configuration
if ( ! function_exists( 'add_action' ) ) {
	$path = dirname( dirname( dirname( BP ) ) );
	require_once( $path . DS . 'wp-config.php' );
}

// switch to action
switch ( $_POST['op'] ) {
	case 'validateApiKey':
		// get request params
		$key = isset( $_POST['key'] ) ? trim( $_POST['key'] ) : '';

		// validate api key with remote request
		$request = new Wp_Sendsmaily_Request(
			'https://www.sendsmaily.net/validate_key.php',
			array( 'key' => $key )
		);
		$result = $request->exec();
		$data = $result['data'];

		// handle errors
		if ( isset( $result['code'] ) and $result['code'] >= 200 ) {
			$result['error'] = true;
			break;
		}

		// insert item to database
		global $wpdb;
		$table_name = $wpdb->prefix . 'sendsmaily_config';
		$wpdb->insert($table_name, array(
			'key' => $key,
		    'domain' => $data['domain'],
		));

		// get autoresponders
		$request = new Wp_Sendsmaily_Request(
			'https://' . $data['domain'] . '.sendsmaily.net/api/get-autoresponders/',
			array( 'key' => $key )
		);
		$result = $request->exec();
		$data = $result['data'];

		// handle errors
		if ( isset( $result['code'] ) and $result['code'] >= 200 ) {
			$result['error'] = true;
			break;
		} elseif ( empty( $data['autoresponders'] ) ) {
			$result = array(
				'message' => __( 'Could not find any autoresponders!', 'wp_sendsmaily' ),
				'error' => true,
			);
			break;
		}

		// get autoresponders
		$insertQuery = array();
		foreach ( $data['autoresponders'] as $item ) {
			$insertQuery[] = sprintf('(%s,"%s")', $item['id'], $item['title']);
		}

		// replace autoresponders
		$table_name = $wpdb->prefix . 'sendsmaily_autoresp';
		$wpdb->query(
			sprintf('delete from `%s`', $table_name)
		);
		$wpdb->query(
			sprintf('insert into `%s`(`id`,`title`) values%s', $table_name, implode(',', $insertQuery))
		);

		// return result
		$result = array(
			'error' => false,
			'message' => __( 'API key passed validation.', 'wp_sendsmaily' ),
		);
		break;

	case 'removeApiKey':
		global $wpdb;

		// delete contents of config
		$table_name = $wpdb->prefix . 'sendsmaily_config';
		$wpdb->query(
			sprintf('delete from `%s`', $table_name)
		);

		// delete contents of autoresponders
		$table_name = $wpdb->prefix . 'sendsmaily_autoresp';
		$wpdb->query(
			sprintf('delete from `%s`', $table_name)
		);

		// set result
		$result = array(
			'error' => false,
			'message' => __('API key removed.', 'wp_sendsmaily')
		);
		break;

	case 'resetForm':
		global $wpdb;

		// Generate form contents.
		require_once( BP . DS . 'code' . DS . 'Template.php' );
		$template = new Wp_Sendsmaily_Template( 'html' . DS . 'form' . DS . 'advanced.phtml' );

		// Load configuration data.
		$table_name = $wpdb->prefix . 'sendsmaily_config';
		$data = $wpdb->get_row( $wpdb->prepare( 'select * from `' . $table_name . '` limit 1' ) );
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
		$table_name = $wpdb->prefix . 'sendsmaily_config';
		$data = $wpdb->get_row($wpdb->prepare( 'SELECT * from `' . $table_name . '` limit 1'));

		// get autoresponders
		$request = new Wp_Sendsmaily_Request('https://' . $data->domain . '.sendsmaily.net/api/get-autoresponders/', array(
			'key' => $data->key,
		));
		$result = $request->exec();
		$data = $result['data'];

		// handle errors
		if ( isset( $result['code'] ) and $result['code'] >= 200 ) {
			$result['error'] = true;
			break;
		} elseif ( empty( $data['autoresponders'] ) ) {
			$result = array(
				'message' => __( 'Could not find any autoresponders!', 'wp_sendsmaily' ),
				'error' => true,
			);
			break;
		}

		// get autoresponders
		$insertQuery = array();
		foreach ( $data['autoresponders'] as $item ) {
			$insertQuery[] = sprintf('(%s,"%s")', $item['id'], $item['title']);
		}

		// replace autoresponders
		$table_name = $wpdb->prefix . 'sendsmaily_autoresp';
		$wpdb->query(
			sprintf( 'delete from `%s`', $table_name )
		);
		$wpdb->query(
			sprintf('insert into `%s`(`id`,`title`) values%s', $table_name, implode(',', $insertQuery))
		);

		// return result
		$result = array(
			'error' => false,
			'message' => __( 'Autoresponders refreshed.', 'wp_sendsmaily' ),
		);

		break;

	case 'save':
		global $wpdb;

		// get params
		$isAdvanced = (isset($_POST['is_advanced']) and !empty($_POST['is_advanced'])) ? '1' : '0';

		// get basic and advanced parameters
		$basic = (isset($_POST['basic']) and is_array($_POST['basic'])) ? $_POST['basic'] : array();
		$advanced = (isset($_POST['advanced']) and is_array($_POST['advanced'])) ? $_POST['advanced'] : array();
		if(empty($basic) or empty($advanced)){
			$result = array('error' => true, 'message' => '');
			break;
		}

		// generate new form (if empty)
		if(empty($advanced['form'])){
			require_once( BP . DS . 'code' . DS . 'Template.php' );
			$template = new Wp_Sendsmaily_Template('html' . DS . 'form' . DS . 'advanced.phtml');

			// load configuration data
			$table_name = $wpdb->prefix . 'sendsmaily_config';
			$data = $wpdb->get_row($wpdb->prepare('select * from `' . $table_name . '` limit 1'));
			$template->assign( (array) $data );

			// render template
			$advanced['form'] = $template->render();
		}

		// update configuration
		$table_name = $wpdb->prefix . 'sendsmaily_config';
		$wpdb->query(
			sprintf('update `%s` set autoresponder="%s", success_url="%s", failure_url="%s", form="%s", is_advanced="%s"',
				$table_name, $basic['autoresponder'], $basic['success_url'], $basic['failure_url'], addslashes($advanced['form']), $isAdvanced
			)
		);

		// return response
		$result = array(
			'error' => false,
			'message' => __( 'Changes saved.', 'wp_sendsmaily' ),
		);
		break;
}

// send refresh form content (if requested)
$refresh = (isset($_POST['refresh']) and $_POST['refresh'] == 1);
if ( $refresh ) {
	global $wpdb;

	// generate form contents
	require_once(BP . DS . 'code' . DS . 'Template.php');
	$template = new Wp_Sendsmaily_Template('html' . DS . 'admin' . DS . 'html' . DS . 'form.phtml');

	// load configuration data
	$table_name = $wpdb->prefix . 'sendsmaily_config';
	$data = $wpdb->get_row($wpdb->prepare('select * from `' . $table_name . '` limit 1'));
	$template->assign((array)$data);

	// load autoresponders
	$table_name = $wpdb->prefix . 'sendsmaily_autoresp';
	$data = $wpdb->get_results($wpdb->prepare('select * from `' . $table_name . '`'));
	$template->assign('autoresponders', $data);

	// render template
	$result['content'] = $template->render();
}

// display result messages as JSON
echo json_encode( $result );
