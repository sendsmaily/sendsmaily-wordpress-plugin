<?php
/**
 * Apply any database upgrades required for 3.0.0.
 *
 * Autoresponder configuration was moved from admin settings to widget settings.
 * All widgets must be updated with an autoresponder field.
 *
 * @since 3.0.0
 */
$upgrade_3_0_0 = function() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'smaily_config';

	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name ) {
		// Get saved autoresponder ID.
		$autoresponder_id = $wpdb->get_var( "SELECT autoresponder FROM `$table_name` LIMIT 1" );
		// Get widgets' options.
		$widget_options = get_option( 'widget_smaily_subscription_widget' );

		foreach ( $widget_options as &$widget ) {
			// Widgets created before 3.0.0 do not have autoresponder value, adding it here.
			if ( is_array( $widget ) && ! isset( $widget['autoresponder'] ) ) {
				$widget['autoresponder'] = $autoresponder_id;
			}
		}
		update_option( 'widget_smaily_subscription_widget', $widget_options );
	}
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smaily_autoresponders" );
	return $upgrade_3_0_0;
};
