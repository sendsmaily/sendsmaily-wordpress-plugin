<?php
/**
 * Bootstrap file for the plugin.
 *
 * @package           Smaily
 *
 * @wordpress-plugin
 * Plugin Name:       Smaily for WP
 * Plugin URI:        https://github.com/sendsmaily/sendsmaily-wordpress-plugin/
 * Text Domain:       smaily-for-wp
 * Description:       Smaily newsletter subscription form.
 * Version:           3.0.0
 * Author:            Sendsmaily LLC
 * Author URI:        https://smaily.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'SMLY4WP_PLUGIN_VERSION', '3.0.0' );

/**
 * Absolute URL to the Smaily for WP plugin directory.
 */
define( 'SMLY4WP_PLUGIN_URL', plugins_url( '', __FILE__ ) );

/**
 * Absolute path to the Smaily for WP plugin directory.
 */
define( 'SMLY4WP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_smaily_for_wp() {
	require_once SMLY4WP_PLUGIN_PATH . 'includes/class-smaily-for-wp-activator.php';
	Smaily_For_WP_Activator::activate();
}

/**
 * The code that runs for plugin uninstallation.
 */
function uninstall_smaily_for_wp() {
	require_once SMLY4WP_PLUGIN_PATH . 'includes/class-smaily-for-wp-uninstaller.php';
	Smaily_For_WP_Uninstaller::uninstall();
}

register_activation_hook( __FILE__, 'activate_smaily_for_wp' );
register_uninstall_hook( __FILE__, 'uninstall_smaily_for_wp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require SMLY4WP_PLUGIN_PATH . 'includes/class-smaily-for-wp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    3.0.0
 */
function run_smaily_for_wp() {
	$plugin = new Smaily_For_WP();
	$plugin->run();
}
run_smaily_for_wp();
