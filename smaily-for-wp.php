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
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require SMLY4WP_PLUGIN_PATH . 'includes/class-smaily-for-wp.php';

register_activation_hook( __FILE__, array( 'Smaily_For_WP', 'activate' ) );
register_uninstall_hook( __FILE__, array( 'Smaily_For_WP', 'uninstall' ) );
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
