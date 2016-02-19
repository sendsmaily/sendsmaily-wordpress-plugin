<?php
/*
	Plugin Name: Sendsmaily Wordpress plugin
	Plugin URI: http://sendsmaily.com/
	Description: Newsletter subscription form
	Version: 0.9.1
	Author: Sendsmaily
	Author URI: http://sendsmaily.com/
 */

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

// global params
define('BP', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

// get plugin path
$exp = explode(DS, BP);
$directory = array_pop($exp);
define('SS_PLUGIN_NAME', $directory);
define('SS_PLUGIN_URL', WP_PLUGIN_URL . '/' . SS_PLUGIN_NAME . '/');

/**
 * initialize
 * @return void
 */
function wp_sendsmaily_init(){
	load_plugin_textdomain('wp_sendsmaily', $path='wp-content' . DS . 'plugins' . DS . SS_PLUGIN_NAME . DS . 'lang');
}
add_action('init', 'wp_sendsmaily_init');

/**
 * install database structure (on activation)
 * @return void
 */
register_activation_hook(__FILE__, 'wp_sendsmaily_install');
function wp_sendsmaily_install(){
	global $wpdb;
	require_once(ABSPATH . 'wp-admin' . DS . 'includes' . DS . 'upgrade.php');

	// create database table - settings
	$tableName = $wpdb->prefix . 'sendsmaily_config';
	if($wpdb->get_var('show tables like "'.$tableName.'"') != $tableName){
		$sql = "CREATE TABLE `$tableName` (`key` VARCHAR(255) NOT NULL, `domain` VARCHAR(255) NOT NULL, `autoresponder` INT(16) NOT NULL, `success_url` TEXT NOT NULL, `failure_url` TEXT NOT NULL, `form` TEXT NOT NULL, `is_advanced` TINYINT(1) NOT NULL, PRIMARY KEY(`key`,`domain`)) ENGINE=MYISAM;";
		dbDelta($sql);
	}
	
	// create database table - autoresponders	
	$tableName = $wpdb->prefix . 'sendsmaily_autoresp';
	if($wpdb->get_var('show tables like "'.$tableName.'"') != $tableName){
		$sql = "CREATE TABLE `$tableName` (`id` INT(16) NOT NULL, `title` VARCHAR(255) NOT NULL, PRIMARY KEY (`id`)) ENGINE=MYISAM;";
		dbDelta($sql);
	}
}

/**
 * add sidebar widget
 * @param array $args
 * @return void
 */
function wp_sendsmaily_widget($args){
	wp_register_sidebar_widget('wp_sendsmaily', __('Newsletter subscription', 'wp_sendsmaily'), 'the_wp_sendsmaily_form', array(
		'description' => __('Sendsmaily newsletter subscription form', 'wp_sendsmaily')
	));
}
add_action('plugins_loaded', 'wp_sendsmaily_widget');

/**
 * return subscription form
 * @return string
 */
function get_wp_sendsmaily_form(){
	global $wpdb;
	
	// load configuration data
	$tableName = $wpdb->prefix . 'sendsmaily_config';
	$config = $wpdb->get_row($wpdb->prepare('select * from `' . $tableName . '` limit 1'));
	
	// create admin template
	require_once(BP . DS . 'code' . DS . 'Template.php');
	$file = ($config->is_advanced === '1') ? 'advanced.phtml' : 'basic.phtml';
	$template = new Wp_Sendsmaily_Template('html' . DS . 'form' . DS . $file);
	$template->assign((array)$config);
	
	// render template
	return $template->render();
}

/**
 * output subscription form
 * @return void
 */
function the_wp_sendsmaily_form(){
	echo get_wp_sendsmaily_form();
}

/**
 * render admin page
 * @return void
 */
function wp_sendsmaily_admin_render(){
	global $wpdb;
	
	// create admin template
	require_once(BP . DS . 'code' . DS . 'Template.php');
	$template = new Wp_Sendsmaily_Template('html' . DS . 'admin' . DS . 'page.phtml');
	
	// load configuration data
	$tableName = $wpdb->prefix . 'sendsmaily_config';
	$data = $wpdb->get_row($wpdb->prepare('select * from `' . $tableName . '` limit 1'));
	$template->assign((array)$data);
	
	// load autoresponders
	$tableName = $wpdb->prefix . 'sendsmaily_autoresp';
	$data = $wpdb->get_results($wpdb->prepare('select * from `' . $tableName . '`'));
	$template->assign('autoresponders', $data);
	
	// add menu elements
	add_menu_page('sendsmaily', 'Sendsmaily', 8, __FILE__, '');
	add_submenu_page('sendsmaily', 'Newsletter subscription form', 'Form', 1, __FILE__, array($template, 'dispatch'));
}
add_action('admin_menu', 'wp_sendsmaily_admin_render');