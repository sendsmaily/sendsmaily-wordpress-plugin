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
		$useragent = 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ) . '; smaily-for-wp/' . SMLY4WP_PLUGIN_VERSION;
		$args      = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $this->_username . ':' . $this->_password ),
			),
			'user-agent' => $useragent,
		);
		$api_call = wp_remote_get( $this->_url, $args );

		// Response code from Smaily API.
		if ( is_wp_error( $api_call ) ) {
			$response = array( 'error' => $api_call->get_error_message() );
		}
		$response['body'] = json_decode( wp_remote_retrieve_body( $api_call ), true );
		$response['code'] = wp_remote_retrieve_response_code( $api_call );

		return $response;

	}

}
