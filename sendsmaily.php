<?php
/**
 * Bootstrap file for the plugin.
 *
 * @package           Sendsmaily
 *
 * @wordpress-plugin
 * Plugin Name:       Sendsmaily
 * Plugin URI:        https://github.com/sendsmaily/sendsmaily-wordpress-plugin
 * Description:       Sendsmaily newsletter subscription form.
 * Version:           1.0.0
 * Author:            Sendsmaily
 * Author URI:        http://sendsmaily.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'SS_PLUGIN_VERSION', '1.0.0' );

define( 'BP', dirname( __FILE__ ) );

define( 'DS', DIRECTORY_SEPARATOR );

// Get plugin path.
$exp = explode( DS, BP );
$directory = array_pop( $exp );

define( 'SS_PLUGIN_NAME', $directory );

define( 'SS_PLUGIN_URL', plugins_url( '', __FILE__ ) );

define( 'SS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require_once( SS_PLUGIN_PATH . 'includes/activator.php' );
register_activation_hook( __FILE__, 'sendsmaily_install' );

/**
 * Initialize.
 * @return void
 */
function sendsmaily_init() {
	load_plugin_textdomain( 'wp_sendsmaily', $path = 'wp-content' . DS . 'plugins' . DS . SS_PLUGIN_NAME . DS . 'lang' );
	wp_enqueue_script( 'sendsmaily', SS_PLUGIN_URL . '/js/default.js', false, SS_PLUGIN_VERSION, true );
}
add_action( 'init', 'sendsmaily_init' );

/**
 * Load subscribe widget.
 */
function sendsmaily_subscription_widget_init() {
	require_once( SS_PLUGIN_PATH . 'includes/subscribe-widget.php' );
	register_widget( 'Sendsmaily_Newsletter_Subscription_Widget' );
}
add_action( 'widgets_init', 'sendsmaily_subscription_widget_init' );

/**
 * Render admin page.
 * @return void
 */
function sendsmaily_admin_render() {
	global $wpdb;

	// Create admin template.
	require_once(BP . DS . 'code' . DS . 'Template.php');
	$template = new Wp_Sendsmaily_Template( 'html' . DS . 'admin' . DS . 'page.phtml' );

	// Load configuration data.
	$table_name = $wpdb->prefix . 'sendsmaily_config';
	$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `$table_name` LIMIT 1" ) );
	$template->assign( (array) $data );

	// Load autoresponders.
	$table_name = $wpdb->prefix . 'sendsmaily_autoresp';
	$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `$table_name`" ) );
	$template->assign( 'autoresponders', $data );

	// Add menu elements.
	add_menu_page( 'sendsmaily', 'Sendsmaily', 8, __FILE__, '', SS_PLUGIN_URL . '/gfx/icon.png' );
	add_submenu_page( 'sendsmaily', 'Newsletter subscription form', 'Form', 1, __FILE__, array( $template, 'dispatch' ) );
}
add_action( 'admin_menu', 'sendsmaily_admin_render' );
