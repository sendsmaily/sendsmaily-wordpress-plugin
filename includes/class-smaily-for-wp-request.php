<?php
/**
 * File that defines the request making functionality.
 *
 * @since      3.0.0
 *
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/includes
 */

/**
 * Defines the request making functionality of the plugin.
 *
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/includes
 */
class Smaily_For_WP_Request {

	/**
	 * Request URL.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      string    $_url    The URL endpoint against which the request is made.
	 */
	protected $_url = null;

	/**
	 * Request data.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      array    $_data    The data which is sent via request.
	 */
	protected $_data = array();

	/**
	 * Smaily API Username.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string    $_username    Smaily API username used for authentication.
	 */
	private $_username = null;

	/**
	 * Smaily API Password.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string    $_password    Smaily API password used for authentication.
	 */
	private $_password = null;

	/**
	 * Set Smaily API Credentials for request.
	 *
	 * @since  3.0.0
	 * @param  string $username Smaily API Username.
	 * @param  string $password Smaily API Password.
	 * @return Smaily_For_WP_Request For method chaining.
	 */
	public function auth( $username, $password ) {
		$this->_username = $username;
		$this->_password = $password;
		return $this;
	}

	/**
	 * Set request URL endpoint.
	 *
	 * @since  3.0.0
	 * @param  string $url Request endpoint.
	 * @return Smaily_For_WP_Request For method chaining.
	 */
	public function setUrl( $url ) {
		$this->_url = $url;
		return $this;
	}

	/**
	 * Render Smaily form using shortcode.
	 *
	 * @since  3.0.0
	 * @param  array $data Shortcode attributes.
	 * @return Smaily_For_WP_Request For method chaining.
	 */
	public function setData( array $data ) {
		$this->_data = $data;
		return $this;
	}

	/**
	 * Execute get request.
	 *
	 * @since  3.0.0
	 * @return array $response. Data recieved back from making the request.
	 */
	public function get() {
		$response  = array();
		$useragent = 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ) . '; smaily-for-wp/' . SMLY4WP_PLUGIN_VERSION;
		$args      = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $this->_username . ':' . $this->_password ),
			),
			'user-agent' => $useragent,
		);
		$api_call  = wp_remote_get( $this->_url, $args );

		// Response code from Smaily API.
		if ( is_wp_error( $api_call ) ) {
			$response = array( 'error' => $api_call->get_error_message() );
		}
		$response['body'] = json_decode( wp_remote_retrieve_body( $api_call ), true );
		$response['code'] = wp_remote_retrieve_response_code( $api_call );

		return $response;
	}

}
