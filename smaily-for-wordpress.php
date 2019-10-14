<?php
/**
 * Bootstrap file for the plugin.
 *
 * @package           Smaily
 *
 * @wordpress-plugin
 * Plugin Name:       Smaily for Wordpress
 * Plugin URI:        https://github.com/sendsmaily/sendsmaily-wordpress-plugin/
 * Text Domain:       wp_smaily
 * Description:       Smaily newsletter subscription form.
 * Version:           2.0.0
 * Author:            Sendsmaily LLC
 * Author URI:        https://smaily.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SS_PLUGIN_VERSION', '2.0.0' );

if (!defined('BP')) define( 'BP', dirname( __FILE__ ) );

if (!defined('DS')) define( 'DS', DIRECTORY_SEPARATOR );

// Get plugin path.
$exp = explode( DS, BP );
$directory = array_pop( $exp );

define( 'SS_PLUGIN_NAME', $directory );

define( 'SS_PLUGIN_URL', plugins_url( '', __FILE__ ) );

define( 'SS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require_once( SS_PLUGIN_PATH . 'includes/activator.php' );
register_activation_hook( __FILE__, 'smaily_install' );

/**
 * Initialize.
 *
 * @param mixed $hook Hook.
 * @return void
 */
function smaily_enqueue( $hook ) {
	wp_enqueue_script( 'smaily', plugins_url( '/js/default.js', __FILE__ ), array( 'jquery' ) );
	wp_localize_script( 'smaily', 'smaily', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

}
add_action( 'wp_enqueue_scripts', 'smaily_enqueue' );

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function smaily_load_textdomain() {
	load_plugin_textdomain( 'wp_smaily', false, plugin_basename( BP ) . DS . 'lang' );
}
add_action( 'plugins_loaded', 'smaily_load_textdomain' );

/**
 * Load subscribe widget.
 */
function smaily_subscription_widget_init() {
	require_once( SS_PLUGIN_PATH . 'includes/subscribe-widget.php' );
	register_widget( 'Smaily_Newsletter_Subscription_Widget' );
}
add_action( 'widgets_init', 'smaily_subscription_widget_init' );

/**
 * Render admin page.
 *
 * @return void
 */
function smaily_admin_render() {
	global $wpdb;

	// Create admin template.
	require_once( BP . DS . 'code' . DS . 'Template.php' );
	$template = new Wp_Smaily_Template( 'html' . DS . 'admin' . DS . 'page.php' );

	// Load configuration data.
	$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
	$data       = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
	$template->assign( (array) $data );

	// Load autoresponders.
	$table_name = esc_sql( $wpdb->prefix . 'smaily_autoresponders' );
	$data       = $wpdb->get_results( "SELECT * FROM `$table_name`" );
	$template->assign( 'autoresponders', $data );

	// Add menu elements.
	add_menu_page( 'smaily', 'Smaily', 'manage_options', __FILE__, '', SS_PLUGIN_URL . '/gfx/icon.png' );
	add_submenu_page( 'smaily', 'Newsletter subscription form', 'Form', 'manage_options', __FILE__, array( $template, 'dispatch' ) );
}
add_action( 'admin_menu', 'smaily_admin_render' );

function smaily_subscribe_callback() {
	global $wpdb;

	// Form data required.
	if ( ! ( isset( $_POST['form_data'] ) && ! empty( $_POST['form_data'] ) ) ) {
		echo esc_html__( 'E-mail is required!', 'wp_smaily' );
		exit;
	}

	// Parse form data out of the serialization.
	$params = array();
	parse_str( $_POST['form_data'], $params );

	// E-mail required.
	if ( ! ( isset( $params['email'] ) && ! empty( $params['email'] ) ) ) {
		echo esc_html__( 'E-mail is required!', 'wp_smaily' );
		exit;
	}

	// Get current url.
	$current_url = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	// Get data from database.
	$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
	$config     = $wpdb->get_row( "SELECT * FROM `$table_name`" );

	// Make a opt-in request to server.
	$server = 'https://' . $config->domain . '.sendsmaily.net/api/opt-in/';
	$lang   = explode( '-', $params['lang'] );
	$array  = array(
		'remote'        => 1,
		'success_url'   => $current_url,
		'failure_url'   => $current_url,
		'language'      => $lang[0],
	);

	// Add autoresponder if selected.
	if ( (int) $config->autoresponder !== 0 ) {
		$array['autoresponder'] = $config->autoresponder;
	}

	$form_values = [];
	// Add custom form values to Api request if available.
	foreach ( $params as $key => $value ) {
		if ( array_key_exists( $key, $array ) ) {
			continue;
		} else {
			$form_values[ $key ] = $value;
		}
	}

	$array = array_merge( $array, $form_values );
	require_once( BP . DS . 'code' . DS . 'Request.php' );
	$request = new Wp_Smaily_Request( $server, $array );
	$result  = $request->exec();

	if ( empty( $result ) ) {
		echo __( 'Something went wrong', 'wp_smaily' );
	} elseif ( (int) $result['code'] !== 101 ) {
		switch ( $result['code'] ) {
			case 201:
				echo __( 'Form was not sent using POST method.', 'wp_smaily' );
				break;
			case 204:
				echo __( 'Input does not contain a recognizable email address.', 'wp_smaily' );
				break;
			case 205:
				echo __( 'Could not add to subscriber list for an unknown reason. Probably something in Smaily.', 'wp_smaily' );
				break;
			default:
				echo __( 'Something went wrong', 'wp_smaily' );
				break;
		}
	}

	exit;
}
add_action( 'wp_ajax_smaily_subscribe_callback', 'smaily_subscribe_callback' );
add_action( 'wp_ajax_nopriv_smaily_subscribe_callback', 'smaily_subscribe_callback' );

// Handles action for form submit in case of no js. Like free icegram plugin.
function smaily_nojs_subscribe_callback() {
	global $wpdb;

	$referer_url = wp_get_referer();
	// Clean up arguments from referred URL.
	$redirect_url = $referer_url ? remove_query_arg( 'smaily_form_error', $referer_url ) : get_home_url();

	// Verify nonce.
	if ( ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'smaily_nonce_field' ) ) {
		$redirect_url = add_query_arg( 'smaily_form_error',
			rawurlencode( __( 'Sorry, your nonce did not verify.', 'wp_smaily' ) ), $redirect_url );
		wp_safe_redirect( $redirect_url );
		return;
	}

	// Form data required.
	if ( ! $_POST ) {
		$redirect_url = add_query_arg( 'smaily_form_error',
			rawurlencode( __( 'No form data.', 'wp_smaily' ) ), $redirect_url );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	// Parse form data out of POST and sanitize.
	$params = array();
	foreach( $_POST as $key => $value ) {
		if ( $key === "action" || $key === "nonce" ) {
			continue;
		}
		$params[ $key ] = sanitize_text_field( $value );
	}

	// E-mail required.
	if ( ! ( isset( $params['email'] ) && ! empty( $params['email'] ) ) ) {
		$redirect_url = add_query_arg( 'smaily_form_error',
					rawurlencode( __( 'Email address input is empty.', 'wp_smaily' ) ), $redirect_url );
		wp_safe_redirect( $redirect_url );
		exit;
	}



	// Get data from database.
	$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
	$config     = $wpdb->get_row( "SELECT * FROM `$table_name`" );

	// Make a opt-in request to server.
	$server = 'https://' . $config->domain . '.sendsmaily.net/api/opt-in/';
	$lang   = explode( '-', $params['lang'] );
	$array  = array(
		'remote'        => 1,
		'success_url'   => $redirect_url,
		'failure_url'   => $redirect_url,
		'language'      => $lang[0],
	);

	// Add autoresponder if selected.
	if ( (int) $config->autoresponder !== 0 ) {
		$array['autoresponder'] = $config->autoresponder;
	}

	$form_values = [];
	// Add custom form values to Api request if available.
	foreach ( $params as $key => $value ) {
		if ( array_key_exists( $key, $array ) ) {
			continue;
		} else {
			$form_values[ $key ] = $value;
		}
	}

	$array = array_merge( $array, $form_values );
	require_once( BP . DS . 'code' . DS . 'Request.php' );
	$request = new Wp_Smaily_Request( $server, $array );
	$result  = $request->exec();

	if ( empty( $result ) ) {
		$redirect_url = add_query_arg( 'smaily_form_error',
			rawurlencode( __( 'Something went wrong', 'wp_smaily' ) ), $redirect_url );
	} elseif ( (int) $result['code'] !== 101 ) {
		switch ( (int) $result['code'] ) {
			case 201:
				$redirect_url = add_query_arg( 'smaily_form_error',
					rawurlencode( __( 'Form was not sent using POST method.', 'wp_smaily' ) ), $redirect_url );
				break;
			case 204:
				$redirect_url = add_query_arg( 'smaily_form_error',
					rawurlencode( __( 'Input does not contain a recognizable email address.', 'wp_smaily' ) ), $redirect_url );
				break;
			case 205:
				$redirect_url = add_query_arg( 'smaily_form_error',
					rawurlencode( __( 'Could not add to subscriber list for an unknown reason. Probably something in Smaily.', 'wp_smaily' ) ), $redirect_url );
				break;
			default:
				$redirect_url = add_query_arg( 'smaily_form_error',
					rawurlencode( __( 'Something went wrong', 'wp_smaily' ) ), $redirect_url );
				break;
		}
	}
	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'admin_post_nopriv_smaily_nojs_subscribe_callback', 'smaily_nojs_subscribe_callback' );
add_action( 'admin_post_smaily_nojs_subscribe_callback', 'smaily_nojs_subscribe_callback' );
