<?php
/**
 * The plugin bootstrap file
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

define( 'SS_PLUGIN_VERSION', '0.9.1' );

define( 'BP', dirname( __FILE__ ) );

define( 'DS', DIRECTORY_SEPARATOR );

// Get plugin path.
$exp = explode( DS, BP );
$directory = array_pop( $exp );

define( 'SS_PLUGIN_NAME', $directory );

define( 'SS_PLUGIN_URL', plugins_url( '', __FILE__ ) );

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
 * Install database structure (on activation).
 * @return void
 */
function sendsmaily_install() {
	global $wpdb;
	require_once( ABSPATH . 'wp-admin' . DS . 'includes' . DS . 'upgrade.php' );
	$charset_collate = $wpdb->get_charset_collate();

	// Create database table - settings.
	$table_name = $wpdb->prefix . 'sendsmaily_config';
	$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
	if ( ! $wpdb->get_var( $query ) ) {
		$sql = "CREATE TABLE `$table_name` (
			`key` VARCHAR(128) NOT NULL,
			`domain` VARCHAR(255) NOT NULL,
			`autoresponder` INT(16) NOT NULL,
			`success_url` TEXT NOT NULL,
			`failure_url` TEXT NOT NULL,
			`form` TEXT NOT NULL,
			`is_advanced` TINYINT(1) NOT NULL,
			PRIMARY KEY(`key`, `domain`)
		) $charset_collate;";
		$wpdb->query( $sql );
	}

	// Create database table - autoresponders.
	$table_name = $wpdb->prefix . 'sendsmaily_autoresp';
	$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
	if ( ! $wpdb->get_var( $query ) ) {
		$sql = trim( preg_replace( '/\s\s+/', ' ', "CREATE TABLE `$table_name` (
			`id` INT(16) NOT NULL,
			`title` VARCHAR(255) NOT NULL,
			PRIMARY KEY (`id`)
		) $charset_collate;" ) );
		$wpdb->query( $sql );
	}
}
register_activation_hook( __FILE__, 'sendsmaily_install' );

/**
 * Add sidebar widget.
 * @param array $args
 * @return void
 */
function sendsmaily_widget( $args ) {
	wp_register_sidebar_widget(
		'wp_sendsmaily',
		__( 'Newsletter subscription', 'wp_sendsmaily' ),
		'the_wp_sendsmaily_form',
		array(
			'description' => __( 'Sendsmaily newsletter subscription form', 'wp_sendsmaily' ),
		)
	);
}
add_action( 'plugins_loaded', 'sendsmaily_widget' );

/**
 * Return subscription form.
 * @return string
 */
function get_wp_sendsmaily_form() {
	global $wpdb;

	// Load configuration data.
	$table_name = $wpdb->prefix . 'sendsmaily_config';
	$config = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `$table_name` LIMIT 1" ) );
	// Create admin template.
	require_once( BP . DS . 'code' . DS . 'Template.php' );
	$file = '1' === $config->is_advanced ? 'advanced.phtml' : 'basic.phtml';
	$template = new Wp_Sendsmaily_Template( 'html' . DS . 'form' . DS . $file );
	$template->assign( (array) $config );

	// Render template.
	return $template->render();
}

/**
 * Output subscription form.
 * @return void
 */
function the_wp_sendsmaily_form() {
	echo get_wp_sendsmaily_form();
}

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
	add_menu_page( 'sendsmaily', 'Sendsmaily', 8, __FILE__, '' );
	add_submenu_page( 'sendsmaily', 'Newsletter subscription form', 'Form', 1, __FILE__, array( $template, 'dispatch' ) );
}
add_action( 'admin_menu', 'sendsmaily_admin_render' );
