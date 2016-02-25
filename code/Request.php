<?php

class Wp_Sendsmaily_Request
{
	/**
	 * Remote request url.
	 * @var string
	 */
	protected $_url = '';

	/**
	 * Remote request data.
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Constructor (set request variables).
	 * @param string $url
	 * @param array $data
	 * @return void|bool
	 */
	public function __construct( $url, $data = null ) {
		if ( ! is_string( $url ) or empty( $url ) ) {
			return false;
		}
		$this->_url = $url;

		// Set request data.
		if ( is_array( $data ) and ! empty( $data ) ) {
			$this->_data = $data;
		}
	}

	/**
	 * Execute remote request.
	 * @return array
	 */
	public function exec() {
		// Set always as remote request.
		$this->_data['remote'] = 1;

		// Fetch with curl.
		if ( function_exists( 'curl_init' ) ) {
			return $this->_asCurl();
		}

		// Fallback to socket.
		return $this->_asSocket();
	}

	/**
	 * Execute remote request (with CURL).
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

	/**
	 * Execute remote request (with fSockOpen).
	 * @return array
	 */
	protected function _asSocket() {
		/*$fp = fsockopen('ssl://frukt.sendsmaily.net', 443, $errno, $errstr, 15);
		if(!$fp){
	    $result = array(
	    	'code' => 200,
	    	'message' => 'Could not connect to host.'
	    );
		}else{
			// build query
			$query = http_build_query($this->_data);

	    $http  = "POST /index.php/api/get-autoresponders HTTP/1.1\r\n";
	    $http .= "Host: " . $_SERVER['HTTP_HOST'] . "\r\n";
	    $http .= "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
	    $http .= "Content-Type: application/x-www-form-urlencoded\r\n";
	    $http .= "Content-length: " . strlen($query) . "\r\n";
	    $http .= "Connection: close\r\n\r\n";
	    $http .= $query . "\r\n\r\n";
	    fwrite($fp, $http);

	    while(!feof($fp)){
	    	$result .= fgets($fp, 4096);
    	}
    	fclose($fp);
		}

		var_dump($result);

		return $result;*/
		return array(
			'code' => 200,
			'message' => 'Could not connect to remote host.',
		);
	}
}
