<?php
/**
 * Plugin Name: Post Statistics
 * Plugin URI: https://github.com/linroex/wordpress-post-statistics-from-google
 * Description: Import Google Analytics data in WordPress.
 * Version: 0.0.1
 * Author: linroex
 * Author URI: http://me.coder.tw
 * License: MIT
 */
include('vendor/autoload.php');

function install() {
    global $wpdb;

    $table_name = $wpdb->prefix . "statistics"; 
    $charset = $wpdb->get_charset_collate();

    $sql = "
        CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            date DATE NOT NULL,
            post_id bigint(20) NOT NULL,
            UNIQUE KEY id (id)
        ) $charset;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

}

function uninstall() {
    global $wpdb;

    $table_name = $wpdb->prefix . "statistics"; 

    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

register_activation_hook(__FILE__, 'install');
register_deactivation_hook(__FILE__, 'uninstall');
