<?php
/**
 * Fired during plugin activation
 *
 * @package    Sendsmaily
 * @subpackage Sendsmaily/includes
 */

/**
 * Install database structure (on activation).
 *
 * @return void
 */
function sendsmaily_install() {
	global $wpdb;
	require_once( ABSPATH . 'wp-admin' . DS . 'includes' . DS . 'upgrade.php' );
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
