<?php
/**
 * Define all the logic related to plugin activation, upgrade and uninstall logic.
 *
 * @since      3.0.0
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/includes
 */
class Smaily_For_WP_Lifecycle {

	/**
	 * Callback for plugin activation hook.
	 *
	 * @since 3.0.0
	 */
	public function activate() {
		$this->run_migrations();

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset_collate = $wpdb->get_charset_collate();

		// Create database table - settings.
		$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
		$sql        = "CREATE TABLE $table_name (
			api_credentials VARCHAR(128) NOT NULL,
			domain VARCHAR(255) NOT NULL,
			form TEXT NOT NULL,
			is_advanced TINYINT(1) NOT NULL,
			PRIMARY KEY  (api_credentials)
		) $charset_collate;";
		dbDelta( $sql );
	}

	/**
	 * Callback for plugins_loaded hook.
	 *
	 * Start migrations if plugin was updated.
	 *
	 * @since 3.0.0
	 */
	public function update() {
		if ( get_transient( 'smailyforwp_plugin_updated' ) !== true ) {
			return;
		}
		$this->run_migrations();
		delete_transient( 'smailyforwp_plugin_updated' );
	}

	/**
	 * Callback for plugin uninstall hook.
	 *
	 * Clean up plugin's database entities.
	 *
	 * @since 3.0.0
	 */
	public function uninstall() {
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smaily_config" );
		delete_option( 'widget_smaily_subscription_widget' );
		delete_option( 'smailyforwp_db_version' );
		delete_transient( 'smailyforwp_plugin_updated' );
	}

	/**
	 * Callback for upgrader_process_complete hook.
	 *
	 * Check if our plugin was updated, make a transient option if so.
	 * This alows us to trigger a DB upgrade script if necessary.
	 *
	 * @since 3.0.0
	 * @param Plugin_Upgrader $upgrader_object Instance of WP_Upgrader.
	 * @param array           $options         Array of bulk item update data.
	 */
	public function check_for_update( $upgrader_object, $options ) {
		$smaily_basename = SMLY4WP_PLUGIN_BASENAME;

		$plugin_was_updated = $options['action'] === 'update' && $options['type'] === 'plugin';
		if ( ! isset( $options['plugins'] ) || ! $plugin_was_updated ) {
			return;
		}

		// $options['plugins'] is string during single update, array if multiple plugins updated.
		$updated_plugins = (array) $options['plugins'];

		foreach ( $updated_plugins as $plugin_basename ) {
			if ( $smaily_basename === $plugin_basename ) {
				return set_transient( 'smailyforwp_plugin_updated', true );
			}
		}
	}

	/**
	 * Get plugin's DB version, run any migrations the database requires.
	 * Update DB version with current plugin version.
	 *
	 * @since  3.0.0
	 * @access private
	 */
	private function run_migrations() {
		$plugin_version = SMLY4WP_PLUGIN_VERSION;
		$db_version     = get_option( 'smailyforwp_db_version', '0.0.0' );

		if ( $plugin_version === $db_version ) {
			return;
		}

		$migrations = array(
			'3.0.0' => 'upgrade-3_0_0.php',
		);

		foreach ( $migrations as $migration_version => $migration_file ) {
			// Database is up-to-date with plugin version.
			if ( version_compare( $db_version, $migration_version, '>=' ) ) {
				continue;
			}

			$migration_file = SMLY4WP_PLUGIN_PATH . 'migrations/' . $migration_file;
			if ( ! file_exists( $migration_file ) ) {
				continue;
			}

			$upgrade = null;
			require_once $migration_file;
			if ( is_callable( $upgrade ) ) {
				$upgrade();
			}
		}

		// Migrations finished.
		update_option( 'smailyforwp_db_version', $plugin_version );
	}
}
