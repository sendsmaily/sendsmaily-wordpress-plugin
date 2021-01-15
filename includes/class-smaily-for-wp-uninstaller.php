<?php
/**
 * Fired if plugin is uninstalled.
 *
 * @since      3.0.0
 *
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/includes
 */

/**
 * Fired if a user has deactivated the plugin, and then clicks the delete link within the WordPress Admin.
 *
 * This class defines all code necessary to run during the plugin's uninstallation.
 *
 * @since      3.0.0
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/includes
 */
class Smaily_For_WP_Uninstaller {
	/**
	 * Clean up plugin's database entities.
	 *
	 * @since    3.0.0
	 */
	public static function uninstall() {
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smaily_autoresponders" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smaily_config" );
		delete_option( 'widget_smaily_subscription_widget' );
	}
}
