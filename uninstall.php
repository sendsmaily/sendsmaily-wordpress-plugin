<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}smaily_autoresponders");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}smaily_config");

delete_option("widget_sendsmaily_subscription_widget");
