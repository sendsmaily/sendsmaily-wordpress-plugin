<?php
// Accept ajax requests only.
if ( ! (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ) {
	echo 'Something went wrong!';
	exit;
}
// E-mail required.
if ( ! (isset($_POST['email']) && !empty($_POST['email'])) ) {
	echo 'E-mail is required!';
	exit;
}

// Define parameters.
define( 'BP', dirname( __FILE__ ) );
define( 'DS', DIRECTORY_SEPARATOR );

// Get current url.
$current_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

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

// Make a opt-in request to server.
$server = 'https://' . $config->domain . '.sendsmaily.net/api/opt-in/';
$array = array(
	'email' => $_POST['email'],
	'key' => $config->key,
	'autoresponder' => $config->autoresponder,
	'remote' => 1,
	'success_url' => $current_url,
	'failure_url' => $current_url,
);

$request = new Wp_Sendsmaily_Request( $server, $array );
$result = $request->exec();

if (empty($result)) {
	echo 'Something went wrong';
}
elseif ((int) $result['code'] === 101) {
	exit;
}
else {
	echo $result['message'];
}
