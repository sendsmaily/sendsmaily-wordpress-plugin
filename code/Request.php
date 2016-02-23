<?php
/**
 * This file is part of Sendsmaily Wordpress plugin.
 *
 * Sendsmaily Wordpress plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Sendsmaily Wordpress plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Sendsmaily Wordpress plugin.  If not, see <http://www.gnu.org/licenses/>.
 */

class Wp_Sendsmaily_Request
{
	/**
	 * remote request url
	 * @var string
	 */
	protected $_url = '';

	/**
	 * remote request data
	 * @var array
	 */
	protected $_data = array();

	/**
	 * constructor (set request variables)
	 * @param string $url
	 * @param array|null $data [optional]
	 * @param string|null $method [optional]
	 * @return void|bool
	 */
	public function __construct($url, $data=null){
		if(!is_string($url) or empty($url)){ return false; }
		$this->_url = $url;

		// set request data
		if(is_array($data) and !empty($data)){ $this->_data = $data; }
	}

	/**
	 * execute remote request
	 * @return void
	 */
	public function exec() {
		// set always as remote request
		$this->_data['remote'] = 1;

		// fetch with curl
		if ( function_exists( 'curl_init' ) ) {
			return $this->_asCurl();
		}

		// fallback to socket
		return $this->_asSocket();
	}

	/**
	 * execute remote request (with CURL)
	 * @return array|bool
	 */
	protected function _asCurl() {
		$curl = curl_init();

		// set curl params
		curl_setopt( $curl, CURLOPT_URL, $this->_url );
		curl_setopt( $curl, CURLOPT_HEADER, false );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

		// set request query
		curl_setopt( $curl, CURLOPT_POST, true );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, http_build_query( $this->_data ) );

		// execute curl
		$result = curl_exec( $curl );
		curl_close( $curl );

		// parse result
		$result = json_decode( $result, true );
		return $result;
	}

	/**
	 * execute remote request (with fSockOpen)
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
