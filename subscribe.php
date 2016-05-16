<?php

// Define parameters.
define( 'BP', dirname( __FILE__ ) );
define( 'DS', DIRECTORY_SEPARATOR );

require_once( BP . DS . 'code' . DS . 'Request.php' );

// Get wpdb configuration.
if ( ! function_exists( 'add_action' ) ) {
	$path = dirname( dirname( dirname( BP ) ) );
	require_once( $path . DS . 'wp-config.php' );
}

// Get data from database.
global $wpdb;
$table_name = esc_sql( $wpdb->prefix . 'sendsmaily_config' );
$config = $wpdb->get_row( "SELECT * FROM `$table_name`" );

// Get posted data.
$posted = array_diff_key( $_POST, array( 'key' => '', 'autoresponder' => '', 'remote' => '' ) );

// Make a opt-in request to server.
$server = 'https://' . $config->domain . '.sendsmaily.net/api/opt-in/';
$array = array_merge(array(
	'key' => $config->key,
	'autoresponder' => $config->autoresponder,
	'remote' => 1,
), $posted);
$request = new Wp_Sendsmaily_Request( $server, $array );
$result = $request->exec();

// Get default url.
$referrer_url = $_SERVER['HTTP_REFERER'];
if ( empty( $referrer_url ) ) {
	$referrer_url = site_url() . '/';
}

// Get redirect urls.
$success_url = $config->success_url;
if ( empty( $success_url ) ) {
	$success_url = $referrer_url;
}
$success_url .= ( stripos( $success_url, '?' ) === false ? '?' : '&' ) . http_build_query( array(
	'message' => $result['message'],
));

$failure_url = $config->failure_url;
if ( empty( $failure_url ) ) {
	$failure_url = $referrer_url;
}
$failure_url .= ( stripos( $failure_url, '?' ) === false ? '?' : '&' ) . http_build_query( array(
	'message' => $result['message'],
));

// Redirect to failure.
if ( isset( $result['code'] ) and $result['code'] > 200 ) {
	header( 'Location: ' . $failure_url );
	exit;
}

// Redirect to success address.
header( 'Location: ' . $success_url );
