<?php

class Wp_Smaily_Request {
	/**
	 * Remote request url.
	 *
	 * @var string
	 */
	protected $_url = '';

	/**
	 * Remote request data.
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Constructor (set request variables).
	 *
	 * @param string $url Url.
	 * @param array  $data Data.
	 * @return void|bool
	 */
	public function __construct( $url, $data = null ) {
		if ( ! is_string( $url ) || empty( $url ) ) {
			return false;
		}
		$this->_url = $url;

		// Set request data.
		if ( is_array( $data ) && ! empty( $data ) ) {
			$this->_data = $data;
		}
	}

	/**
	 * Execute get request.
	 */
	public function get() {
		$response = [];
		$auth     = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $this->_data['username'] . ':' . $this->_data['password'] ),
			),
		);
		$api_call = wp_remote_get( $this->_url, $auth );

		// Response code from Smaily API.
		if ( is_wp_error( $api_call ) ) {
			$response = array( 'error' => $api_call->get_error_message() );
		}
		$response['body'] = json_decode( wp_remote_retrieve_body( $api_call ), true );
		$response['code'] = wp_remote_retrieve_response_code( $api_call );

		return $response;

	}

	/**
	 * Execute remote request.
	 *
	 * @return array
	 */
	public function exec() {
		// Set always as remote request.
		$this->_data['remote'] = 1;

		return $this->_asCurl();
	}

	/**
	 * Execute remote request (with CURL).
	 *
	 * @return array|bool
	 */
	protected function _asCurl() {
		$curl = curl_init();

		// Set curl parameters.
		curl_setopt( $curl, CURLOPT_URL, $this->_url );
		curl_setopt( $curl, CURLOPT_HEADER, false );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

		// Set request query.
		curl_setopt( $curl, CURLOPT_POST, true );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, http_build_query( $this->_data ) );

		// Execute curl.
		$result = curl_exec( $curl );
		curl_close( $curl );

		// Parse result.
		$result = json_decode( $result, true );
		return $result;
	}

}
