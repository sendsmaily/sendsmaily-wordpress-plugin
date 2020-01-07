<?php
/**
 * Bootstrap file for the plugin.
 *
 * @package           Smaily
 *
 * @wordpress-plugin
 * Plugin Name:       Smaily for WP
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

define( 'SMLY4WP_PLUGIN_VERSION', '2.0.0' );
// Absolute URL to the plugin, for HTML markup.
define( 'SMLY4WP_PLUGIN_URL', plugins_url( '', __FILE__ ) );
define( 'SMLY4WP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require_once( SMLY4WP_PLUGIN_PATH . 'includes/activator.php' );
require_once( SMLY4WP_PLUGIN_PATH . 'action.php' );
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
add_action( 'admin_enqueue_scripts', 'smaily_enqueue' );

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function smaily_load_textdomain() {
	load_plugin_textdomain( 'wp_smaily', false, plugin_basename( SMLY4WP_PLUGIN_PATH ) . '/lang' );
}
add_action( 'plugins_loaded', 'smaily_load_textdomain' );

/**
 * Load subscribe widget.
 */
function smaily_subscription_widget_init() {
	require_once( SMLY4WP_PLUGIN_PATH . 'includes/subscribe-widget.php' );
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
	require_once( SMLY4WP_PLUGIN_PATH . '/code/Template.php' );
	$template = new Smaily_Plugin_Template( 'html/admin/page.php' );

	// Load configuration data.
	$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
	$data       = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
	$template->assign( (array) $data );

	// Load autoresponders.
	$table_name = esc_sql( $wpdb->prefix . 'smaily_autoresponders' );
	$data       = $wpdb->get_results( "SELECT * FROM `$table_name`" );
	$template->assign( 'autoresponders', $data );

	// Add menu elements.
	add_menu_page( 'smaily', 'Smaily', 'manage_options', SMLY4WP_PLUGIN_PATH, '', plugins_url( 'gfx/icon.png', __FILE__ ) );
	add_submenu_page( 'smaily', 'Newsletter subscription form', 'Form', 'manage_options', SMLY4WP_PLUGIN_PATH, array( $template, 'dispatch' ) );
}
add_action( 'admin_menu', 'smaily_admin_render' );
