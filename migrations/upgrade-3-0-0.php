<?php
/**
 * Apply any database upgrades required for 3.0.0.
 *
 * Autoresponder configuration was moved from admin settings to widget settings.
 * All widgets must be updated with an autoresponder field.
 *
 * @since 3.0.0
 */
function smailyforwp_upgrade_3_0_0() {
	global $wpdb;
	// Get saved autoresponder ID.
	$table_name       = esc_sql( $wpdb->prefix . 'smaily_config' );
	$autoresponder_id = $wpdb->get_col( "SELECT autoresponder FROM `$table_name` LIMIT 1" )[0];
	// Get widgets' options.
	$widget_options = get_option( 'widget_smaily_subscription_widget' );

	foreach ( $widget_options as &$widget ) {
		// Widgets created before 3.0.0 do not have autoresponder value, adding it here.
		if ( is_array( $widget ) && ! isset( $widget['autoresponder'] ) ) {
			$widget['autoresponder'] = $autoresponder_id;
		}
	}
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smaily_autoresponders" );
	update_option( 'widget_smaily_subscription_widget', $widget_options );
	update_option( 'smailyforwp_db_version', '3.0.0' );
}
