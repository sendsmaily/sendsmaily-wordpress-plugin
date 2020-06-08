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
 * Version:           2.0.2
 * Author:            Sendsmaily LLC
 * Author URI:        https://smaily.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SMLY4WP_PLUGIN_VERSION', '2.0.2' );
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


/**
 * Render smaily form using shortcode.
 *
 * @param array $atts shortcode attributes.
 * @return string
 */
function smaily_shortcode_render( $atts ) {
	global $wpdb;

	// Load configuration data.
	$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
	$config = (array) $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );

	// Parse attributes out of shortcode tag.
	$shortcode_atts = shortcode_atts(
		array(
			'success_url' => get_site_url(),
			'failure_url' => get_site_url(),
			'show_name'   => false,
		),
		$atts
	);
	$config['success_url'] = $shortcode_atts['success_url'];
	$config['failure_url'] = $shortcode_atts['failure_url'];
	$config['show_name']   = $shortcode_atts['show_name'];

	// Create admin template.
	require_once( SMLY4WP_PLUGIN_PATH . '/code/Template.php' );
	$file     = ( isset( $config['is_advanced'] ) && '1' === $config['is_advanced'] ) ? 'advanced.php' : 'basic.php';
	$template = new Smaily_Plugin_Template( 'html/form/' . $file );
	$template->assign( $config );
	// Display responses on Smaily subscription form.
	$form_has_response  = false;
	$form_is_successful = false;
	$response_message   = null;

	if ( ! isset( $config['api_credentials'] ) || empty( $config['api_credentials'] ) ) {
		$form_has_response = true;
		$response_message  = __( 'Smaily credentials not validated. Subscription form will not work!', 'wp_smaily' );
	} elseif ( isset( $_GET['code'] ) && (int) $_GET['code'] === 101 ) {
		$form_is_successful = true;
	} elseif ( isset( $_GET['code'] ) || ! empty( $_GET['code'] ) ) {
		$form_has_response = true;
		switch ( (int) $_GET['code'] ) {
			case 201:
				$response_message = __( 'Form was not submitted using POST method.', 'wp_smaily' );
				break;
			case 204:
				$response_message = __( 'Input does not contain a recognizable email address.', 'wp_smaily' );
				break;
			default:
				$response_message = __( 'Could not add to subscriber list for an unknown reason. Probably something in Smaily.', 'wp_smaily' );
				break;
		}
	}

	$template->assign(
		array(
			'form_has_response'  => $form_has_response,
			'response_message'   => $response_message,
			'form_is_successful' => $form_is_successful,
		)
	);

	// Render template.
	return $template->render();
}
add_shortcode( 'smaily_newsletter_form', 'smaily_shortcode_render' );
