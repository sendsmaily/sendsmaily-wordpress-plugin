<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      3.0.0
 *
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      3.0.0
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/includes
 */
class Smaily_For_WP_i18n {
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    3.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'smaily-for-wp',
			false,
			plugin_basename( SMLY4WP_PLUGIN_PATH ) . '/lang/'
		);
	}
}
