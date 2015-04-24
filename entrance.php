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
include('Google_Analytics.php');

function install() {
    global $wpdb;

    $table_name = $wpdb->prefix . "statistics"; 
    $charset = $wpdb->get_charset_collate();

    $sql = "
        CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            date DATE NOT NULL,
            count bigint(20) NOT NULL,
            UNIQUE KEY id (id)
        ) $charset;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

}

function getData() {
    $applicationName = 'newcongress';
    $account = '433729975312-ar2hp401av11nsgadevcqk8fhuodn8uh@developer.gserviceaccount.com';
    $key = plugin_dir_path(__FILE__) . 'newcongress-tw-be61ca6250aa.p12';

    $analytics = new Google_Analytics($applicationName, $account, $key);

    $analytics->setAccountId('逐風者')->setWebpropertieId('逐風者')->setProfileId();

    $data = $analytics->getResults(
                '2015-03-22', 
                '2015-04-24', 
                'ga:pageviews', [
                    'dimensions'=>'ga:pagePath,ga:date', 
                    'sort'=>'-ga:pageviews'
                ]
            );

    return $data->getRows();
}

function install_data() {
    global $wpdb;

    $table_name = $wpdb->prefix . "statistics"; 

    foreach (getData() as $row) {
        $data = [
            'post_id'=>url_to_postid($row[0]),
            'date'=>$row[1],
            'count'=>$row[2]
        ];
        $wpdb->insert($table_name, $data);
    }

}

function uninstall() {
    global $wpdb;

    $table_name = $wpdb->prefix . "statistics"; 

    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

register_activation_hook(__FILE__, 'install');
register_activation_hook(__FILE__, 'install_data');
register_deactivation_hook(__FILE__, 'uninstall');

// SELECT `post_id`,sum(`count`) FROM `wp_statistics` group by `post_id` order by `count` desc limit 0,5