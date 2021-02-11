<?php
/**
 * Apply any database upgrades required for 3.0.0.
 *
 * Autoresponder configuration was moved from admin settings to widget settings,
 * all widgets must be updated with an autoresponder field.
 *
 * Plugin settings were previously stored in smaily_config table, they must be
 * copied over to smailyforwp_api_option and smailyforwp_form_option.
 *
 * @since 3.0.0
 */
$upgrade = function() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'smaily_config';

	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name ) {
		$config = $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1", ARRAY_A );
		// Get saved autoresponder ID.
		$autoresponder_id = isset( $config['autoresponder'] ) ? $config['autoresponder'] : '';
		// Get widgets' options.
		$widget_options = get_option( 'widget_smaily_subscription_widget' );

		foreach ( $widget_options as &$widget ) {
			// Widgets created before 3.0.0 do not have autoresponder value, adding it here.
			if ( is_array( $widget ) && ! isset( $widget['autoresponder'] ) ) {
				$widget['autoresponder'] = $autoresponder_id;
			}
		}
		update_option( 'widget_smaily_subscription_widget', $widget_options );

		$api_options = array(
			'subdomain' => ! empty( $config['domain'] ) ? $config['domain'] : '',
			'username'  => '',
			'password'  => '',
		);

		// In versions before 3.0.0, credentials were stored in format username:password.
		$split_credentials = explode( ':', $config['api_credentials'] );
		if ( count( $split_credentials ) === 2 ) {
			$api_options['username'] = $split_credentials[0];
			$api_options['password'] = $split_credentials[1];
		}

		// Disable autoloading API credentials, as they are are delicate.
		update_option( 'smailyforwp_api_option', $api_options, false );

		// Copy advanced form configurations from smaily_config table to form option.
		$form_options = array(
			'form'        => ! empty( $config['form'] ) ? $config['form'] : '',
			'is_advanced' => isset( $config['is_advanced'] ) ? $config['is_advanced'] : '0',
		);
		update_option( 'smailyforwp_form_option', $form_options );

		// All settings have been copied, delete old database table.
		$wpdb->query( "DROP TABLE {$wpdb->prefix}smaily_config" );

	}
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smaily_autoresponders" );
};
