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
	$table_name = esc_sql( $wpdb->prefix . 'sendsmaily_config' );
	$query = "SHOW TABLES LIKE `$table_name`";
	if ( ! $wpdb->get_var( $query ) ) {
		$sql = "CREATE TABLE `$table_name` (
			`key` VARCHAR(128) NOT NULL,
			`domain` VARCHAR(255) NOT NULL,
			`autoresponder` INT(16) NOT NULL,
			`form` TEXT NOT NULL,
			`is_advanced` TINYINT(1) NOT NULL,
			PRIMARY KEY(`key`)
		) $charset_collate;";
		$wpdb->query( $sql );
	}

	// Create database table - autoresponders.
	$table_name = esc_sql( $wpdb->prefix . 'sendsmaily_autoresp' );
	$query = "SHOW TABLES LIKE `$table_name`";
	if ( ! $wpdb->get_var( $query ) ) {
		$sql = trim(
			preg_replace(
				'/\s\s+/',
				' ',
				"CREATE TABLE `$table_name` (
				`id` INT(16) NOT NULL,
				`title` VARCHAR(255) NOT NULL,
				PRIMARY KEY (`id`)
				) $charset_collate;"
			)
		);
		$wpdb->query( $sql );
	}
}
