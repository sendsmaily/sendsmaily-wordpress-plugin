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
 * Version:           3.1.5
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
define( 'SMLY4WP_PLUGIN_VERSION', '3.1.5' );

/**
 * Absolute URL to the Smaily for WP plugin directory.
 */
define( 'SMLY4WP_PLUGIN_URL', plugins_url( '', __FILE__ ) );

/**
 * Absolute path to the Smaily for WP plugin directory.
 */
define( 'SMLY4WP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Absolute path to the core plugin file.
 */
define( 'SMLY4WP_PLUGIN_FILE', __FILE__ );

/**
 * The core plugin class.
 */
require SMLY4WP_PLUGIN_PATH . 'includes/class-smaily-for-wp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 3.0.0
 */
function run_smaily_for_wp() {
	$plugin = new Smaily_For_WP();
	$plugin->run();
}
run_smaily_for_wp();
