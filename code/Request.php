<?php

class Wp_Sendsmaily_Request {
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
