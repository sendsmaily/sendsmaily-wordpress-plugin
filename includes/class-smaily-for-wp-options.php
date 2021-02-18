<?php
/**
 * This class is used to work with the plugin's options
 * that take user input e.g API credentials, form settings.
 *
 * @since      3.0.0
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/includes
 */
class Smaily_For_WP_Options {

	/**
	 * Smaily API credentials
	 *
	 * @since  3.0.0
	 * @access private
	 * @var    array   $api_credentials Smaily API credentials.
	 */
	private $api_credentials;

	/**
	 * Newsletter signup form settings.
	 *
	 * @since  3.0.0
	 * @access private
	 * @var    array   $form_options Newsletter signup form settings.
	 */
	private $form_options;

	/**
	 * Get API credentials.
	 *
	 * @since  3.0.0
	 * @return array   $api_credentials Smaily API credentials
	 */
	public function get_api_credentials() {
		if ( is_null( $this->api_credentials ) ) {
			$this->api_credentials = $this->get_api_credentials_from_db();
		}
		return $this->api_credentials;
	}

	/**
	 * Get form options.
	 *
	 * @since  3.0.0
	 * @return array   $form_options Newsletter signup form settings.
	 */
	public function get_form_options() {
		if ( is_null( $this->form_options ) ) {
			$this->form_options = $this->get_form_options_from_db();
		}
		return $this->form_options;
	}

	/**
	 * Get API credentials stored in database.
	 *
	 * @since  3.0.0
	 * @access private
	 * @return array   API credentials in proper format.
	 */
	private function get_api_credentials_from_db() {
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
	 * @access private
	 * @return array   Form options in proper format
	 */
	private function get_form_options_from_db() {
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
		// Update_option will sanitize input before saving. We should sanitize as well.
		if ( is_array( $api_credentials ) ) {
			$this->api_credentials = array_map( 'sanitize_text_field', $api_credentials );
		}
		update_option( 'smailyforwp_api_option', $this->api_credentials, false );
	}

	/**
	 * Overwrite form options entry in database with provided parameter.
	 *
	 * @since 3.0.0
	 * @param array $form_options Newsletter form options.
	 */
	public function update_form_options( $form_options ) {
		// Update_option will sanitize input before saving. We should sanitize as well.
		if ( is_array( $form_options ) ) {
			$this->form_options = array_map( 'sanitize_text_field', $form_options );
		}
		update_option( 'smailyforwp_form_option', $this->form_options );
	}

	/**
	 * Clear Smaily API credentials by deleting its option.
	 *
	 * @since 3.0.0
	 */
	public function remove_api_credentials() {
		$this->api_credentials = null;
		delete_option( 'smailyforwp_api_option' );
	}

	/**
	 * Clear configurations for newsletter subscription from by deleting its option.
	 *
	 * @since 3.0.0
	 */
	public function remove_form_options() {
		$this->form_options = null;
		delete_option( 'smailyforwp_form_option' );
	}

	/**
	 * Has user saved Smaily API credentials to database?
	 *
	 * @since  3.0.0
	 * @return boolean True if $api_credentials has correct key structure and no empty values.
	 */
	public function has_credentials() {
		$api_credentials = $this->get_api_credentials();
		return ! empty( $api_credentials['subdomain'] ) && ! empty( $api_credentials['username'] ) && ! empty( $api_credentials['password'] );
	}
}
