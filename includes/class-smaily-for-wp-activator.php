<?php
/**
 * Fired during plugin activation
 *

 * @since      3.0.0
 *
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      3.0.0
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/includes
 */
class Smaily_For_WP_Activator {

	/**
	 * Install database structure (on activation).
	 *
	 * @since    3.0.0
	 */
	public static function activate() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$charset_collate = $wpdb->get_charset_collate();

		// Create database table - settings.
		$table_name            = esc_sql( $wpdb->prefix . 'smaily_config' );
		$settings_table_exists = $wpdb->get_var(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$table_name
			)
		);
		if ( ! $settings_table_exists ) {
			$sql = "CREATE TABLE $table_name (
					api_credentials VARCHAR(128) NOT NULL,
					domain VARCHAR(255) NOT NULL,
					autoresponder INT(16) NOT NULL,
					form TEXT NOT NULL,
					is_advanced TINYINT(1) NOT NULL,
					PRIMARY KEY  (api_credentials)
				) $charset_collate;";
			dbDelta( $sql );
		}

		// Create database table - autoresponders.
		$table_name = esc_sql( $wpdb->prefix . 'smaily_autoresponders' );
		$sql        = "CREATE TABLE $table_name (
					id int(16) NOT NULL,
					title varchar(255) NOT NULL,
					PRIMARY KEY  (id)
				) $charset_collate;";
		dbDelta( $sql );
	}

}
