<?php
// Define parameters.
define( 'BP', dirname( __FILE__ ) );
define( 'DS', DIRECTORY_SEPARATOR );

// Get wpdb configuration.
$path = dirname( dirname( dirname( BP ) ) );
require_once( $path . DS . 'wp-config.php' );

// Accept ajax requests only.
if ( ! (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ) {
	echo esc_html__('Something went wrong!', 'wp_sendsmaily');
	exit;
}
// E-mail required.
if ( ! (isset($_POST['email']) && !empty($_POST['email'])) ) {
	echo esc_html__('E-mail is required!', 'wp_sendsmaily');
	exit;
}

// Get current url.
$current_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

require_once( BP . DS . 'code' . DS . 'Request.php' );

// Get data from database.
global $wpdb;
$table_name = esc_sql( $wpdb->prefix . 'sendsmaily_config' );
$config = $wpdb->get_row( "SELECT * FROM `$table_name`" );

// Make a opt-in request to server.
$server = 'https://' . $config->domain . '.sendsmaily.net/api/opt-in/';
$lang = explode('-', $_POST['lang']);
$array = array(
	'email' => $_POST['email'],
	'key' => $config->key,
	'autoresponder' => $config->autoresponder,
	'remote' => 1,
	'success_url' => $current_url,
	'failure_url' => $current_url,
	'language' => $lang[0],
);

$request = new Wp_Sendsmaily_Request( $server, $array );
$result = $request->exec();

if (empty($result)) {
	echo esc_html__('Something went wrong', 'wp_sendsmaily');
}
elseif ((int) $result['code'] === 101) {
	exit;
}
else {
	// Possible errors, for translation.
	//esc_html__('Posted fields do not contain a valid email address.', 'wp_sendsmaily');
	//esc_html__('No autoresponder data set.', 'wp_sendsmaily');

	echo esc_html__($result['message'], 'wp_sendsmaily');
}
