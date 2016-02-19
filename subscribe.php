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

// define parameters
define('BP', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

require_once(BP . DS . 'code' . DS . 'Request.php');

// get wpdb configuration
if(!function_exists('add_action')){
	$path = dirname(dirname(dirname(BP)));
	require_once($path . DS . "wp-config.php");
}

// get data from database
global $wpdb;
$tableName = $wpdb->prefix.'sendsmaily_config';
$config = $wpdb->get_row($wpdb->prepare('select * from `'.$tableName.'`'));

// get posted data
$posted = array_diff_key($_POST, array('key' => '', 'autoresponder' => '', 'remote' => ''));

// make a opt-in request to server
$server = 'https://' . $config->domain . '.sendsmaily.net/api/opt-in/';
$array = array_merge(array(
	'key' => $config->key,
	'autoresponder' => $config->autoresponder,
	'remote' => 1
), $posted);
$request = new Wp_Sendsmaily_Request($server, $array);
$result = $request->exec();

// get default url
$refererUrl = $_SERVER['HTTP_REFERER'];
if(empty($refererUrl)){
	$refererUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/';
}

// get redirect urls
$successUrl = $config->success_url;
if(empty($successUrl)){
	$successUrl = $refererUrl;
}
$successUrl .= (stripos($successUrl, '?') === false ? '?' : '&') . http_build_query(array(
	'message' => $result['message']
));

$failureUrl = $config->failure_url;
if(empty($failureUrl)){
	$failureUrl = $refererUrl;
}
$failureUrl .= (stripos($failureUrl, '?') === false ? '?' : '&') . http_build_query(array(
	'message' => $result['message']
));

// redirect to failure
if(isset($result['code']) and $result['code'] > 200){
	header('Location: '.$failureUrl);
	exit;
}

// redirect to success address
header('Location: '.$successUrl);