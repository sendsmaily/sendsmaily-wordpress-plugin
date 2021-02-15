<?php
/**
 * Define the plugin's reading and writing functionality, from and to WordPress' options table.
 *
 * @since      3.0.0
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/includes
 */
class Smaily_For_WP_Option_Handler {

	/**
	 * Get API credentials stored in database.
	 *
	 * @since  3.0.0
	 * @return array API credentials in proper format.
	 */
	public function get_api_credentials() {
		$credentials = get_option( 'smailyforwp_api_option', array() );
		return array_merge(
			array(
				'subdomain' => '',
				'username'  => '',
				'password'  => '',
			),
			$credentials
		);
	}

	/**
	 * Get form options stored in database.
	 *
	 * @since  3.0.0
	 * @return array Form options in proper format
	 */
	public function get_form_options() {
		$form_options = get_option( 'smailyforwp_form_option', array() );
		return array_merge(
			array(
				'form'        => '',
				'is_advanced' => '',
			),
			$form_options
		);
	}

	/**
	 * Overwrite API credentials entry in database with provided parameter.
	 * Disable auto-loading as API credentials are delicate.
	 *
	 * @since 3.0.0
	 * @param array $api_credentials Smaily API credentials.
	 */
	public function update_api_credentials( $api_credentials ) {
		update_option( 'smailyforwp_api_option', $api_credentials, false );
	}

	/**
	 * Overwrite form options entry in database with provided parameter.
	 *
	 * @since 3.0.0
	 * @param array $form_options Newsletter form options.
	 */
	public function update_form_options( $form_options ) {
		update_option( 'smailyforwp_form_option', $form_options );
	}

	/**
	 * Has user saved valid Smaily API credentials to database.
	 *
	 * @since  3.0.0
	 * @access private
	 * @param  array $credentials Smaily API credentials.
	 * @return boolean User has saved credentials to DB.
	 */
	public function has_credentials( $credentials = null ) {
		if ( ! isset( $credentials ) ) {
			$credentials = $this->get_api_credentials();
		}
		return ! empty( $credentials['subdomain'] ) && ! empty( $credentials['username'] ) && ! empty( $credentials['password'] );
	}
}
