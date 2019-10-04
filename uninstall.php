<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}sendsmaily_autoresp");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}sendsmaily_config");
