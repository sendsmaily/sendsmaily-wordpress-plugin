<?php

class Smaily_Plugin_Request {

	protected $_url = NULL;

	protected $_data = array();

	private $_username = NULL;

	private $_password = NULL;

	public function auth($username, $password) {
		$this->_username = $username;
		$this->_password = $password;
		return $this;
	}

	public function setUrl($url) {
		$this->_url = $url;
		return $this;
	}

	public function setData(array $data) {
		$this->_data = $data;
		return $this;
	}

	/**
	 * Execute get request.
	 */
	public function get() {
		$response = [];
		$auth     = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $this->_username . ':' . $this->_password ),
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
	 * Execute post request.
	 *
	 * @return array
	 */
	public function post() {
		$response = [];

		$subscription_post = wp_remote_post( $this->_url, array( 'body' => http_build_query( $this->_data ) ) );
		// Response code from Smaily API.
		if ( is_wp_error( $subscription_post ) ) {
			$response = array( 'error' => $subscription_post->get_error_message() );
		} else {
			$response = json_decode( wp_remote_retrieve_body( $subscription_post ), true );
		}

		return $response;
	}

}
